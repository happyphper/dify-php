<?php

declare(strict_types=1);

namespace Happyphper\Dify\Console;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use Happyphper\Dify\Console\Api\AuthApi;
use Happyphper\Dify\Console\Api\DatasetApi;
use Happyphper\Dify\Console\Api\DocumentApi;
use Happyphper\Dify\Exceptions\ApiException;
use Happyphper\Dify\Exceptions\AuthenticationException;
use Happyphper\Dify\Exceptions\AuthorizationException;
use Happyphper\Dify\Exceptions\NotFoundException;
use Happyphper\Dify\Exceptions\RateLimitException;
use Happyphper\Dify\Exceptions\ServerException;
use Happyphper\Dify\Exceptions\ValidationException;
use Happyphper\Dify\Support\TokenStorage;
use Happyphper\Dify\Support\Cache\CacheInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Dify 控制台 API 客户端
 */
class ConsoleClient
{
    /**
     * HTTP 客户端
     *
     * @var Client
     */
    private Client $httpClient;

    /**
     * 文档 API
     *
     * @var DocumentApi
     */
    private DocumentApi $documents;

    /**
     * 认证 API
     *
     * @var AuthApi
     */
    private AuthApi $auth;

    /**
     * 数据集 API
     *
     * @var DatasetApi
     */
    private DatasetApi $datasets;

    /**
     * 认证令牌
     *
     * @var string|null
     */
    private ?string $token = null;

    /**
     * Cookie Jar
     *
     * @var CookieJar
     */
    private CookieJar $cookieJar;

    /**
     * 令牌存储
     *
     * @var TokenStorage
     */
    private TokenStorage $tokenStorage;

    /**
     * 构造函数
     *
     * @param string $baseUrl 基础 URL
     * @param string $email 邮箱
     * @param string $password 密码
     * @param bool $isDebug 是否开启调试模式
     * @param LoggerInterface|null $logger 日志记录器
     * @param CacheInterface|null $cache 缓存实例
     * @param array $config 配置
     */
    public function __construct(
        protected string $baseUrl,
        protected string $email,
        protected string $password,
        protected bool $isDebug = false,
        protected ?LoggerInterface $logger = null,
        ?CacheInterface $cache = null,
        protected array $config = []
    ) {
        $this->cookieJar = new CookieJar();
        $this->tokenStorage = new TokenStorage($cache, $this->config);
        $this->token = $this->tokenStorage->getAccessToken();
        $this->httpClient = $this->createHttpClient();
        $this->initializeApis();
    }

    private function createHttpClient(): Client
    {
        return new Client([
            'base_uri' => rtrim($this->baseUrl, '/') . '/',
            'timeout' => 10,
            'connect_timeout' => 5,
            'debug' => $this->isDebug,
            'cookies' => $this->cookieJar
        ]);
    }

    private function initializeApis(): void
    {
        $this->documents = new DocumentApi($this);
        $this->auth = new AuthApi($this);
        $this->datasets = new DatasetApi($this);
    }

    /**
     * 设置访问令牌
     *
     * @param string $accessToken 访问令牌
     * @param string|null $refreshToken 刷新令牌
     * @param int|null $accessTokenTtl 访问令牌过期时间（秒）
     * @param int|null $refreshTokenTtl 刷新令牌过期时间（秒）
     * @return void
     */
    public function setToken(string $accessToken, ?string $refreshToken = null, ?int $accessTokenTtl = null, ?int $refreshTokenTtl = null): void
    {
        $this->token = $accessToken;
        $this->tokenStorage->setAccessToken($accessToken, $accessTokenTtl);
        
        if ($refreshToken !== null) {
            $this->tokenStorage->setRefreshToken($refreshToken, $refreshTokenTtl);
        }
    }

    /**
     * 获取访问令牌
     *
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->token ?? $this->tokenStorage->getAccessToken();
    }

    /**
     * 获取刷新令牌
     *
     * @return string|null
     */
    public function getRefreshToken(): ?string
    {
        return $this->tokenStorage->getRefreshToken();
    }

    /**
     * 清除令牌
     *
     * @return bool
     */
    public function clearTokens(): bool
    {
        $this->token = null;
        return $this->tokenStorage->clearTokens();
    }

    /**
     * 登录并获取令牌
     *
     * @return array
     * @throws ApiException
     */
    public function login(): array
    {
        $response = $this->auth()->login($this->email, $this->password);
        
        if (!isset($response['result']) || $response['result'] !== 'success' || !isset($response['data']['access_token'])) {
            throw new ApiException('登录失败');
        }
        
        $accessToken = $response['data']['access_token'];
        $refreshToken = $response['data']['refresh_token'] ?? null;
        
        // 默认令牌有效期为24小时
        $accessTokenTtl = 24 * 60 * 60;
        // 默认刷新令牌有效期为30天
        $refreshTokenTtl = 30 * 24 * 60 * 60;
        
        $this->setToken($accessToken, $refreshToken, $accessTokenTtl, $refreshTokenTtl);
        
        return $response;
    }

    /**
     * 发送 GET 请求
     *
     * @param string $uri
     * @param array $query
     * @return array
     * @throws ApiException
     */
    public function get(string $uri, array $query = []): array
    {
        return $this->request('GET', $uri, ['query' => $query]);
    }

    /**
     * 发送 POST 请求
     *
     * @param string $uri
     * @param array $data
     * @return array
     * @throws ApiException
     */
    public function post(string $uri, array $data = []): array
    {
        return $this->request('POST', $uri, ['json' => $data]);
    }

    /**
     * 发送 PATCH 请求
     *
     * @param string $uri
     * @param array $data
     * @return array
     * @throws ApiException
     */
    public function patch(string $uri, array $data = []): array
    {
        return $this->request('PATCH', $uri, ['json' => $data]);
    }

    /**
     * 发送文件上传请求
     *
     * @param string $uri
     * @param array $multipart
     * @return array
     * @throws ApiException
     */
    public function upload(string $uri, array $multipart): array
    {
        // 确保在上传文件时正确设置Authorization头
        $options = [
            'multipart' => $multipart,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getToken(),
                'Accept' => '*/*'
            ]
        ];

        if ($this->isDebug && $this->logger) {
            $this->logger->info('Dify Console 上传请求', [
                'uri' => $uri,
                'multipart' => array_map(function ($item) {
                    if (isset($item['contents']) && is_resource($item['contents'])) {
                        return [
                            'name' => $item['name'],
                            'filename' => $item['filename'] ?? null,
                            'headers' => $item['headers'] ?? null,
                            'contents' => '[FILE RESOURCE]'
                        ];
                    }
                    return $item;
                }, $multipart)
            ]);
        }

        return $this->request('POST', $uri, $options);
    }

    /**
     * 发送 DELETE 请求
     *
     * @param string $uri
     * @return array
     * @throws ApiException
     */
    public function delete(string $uri): array
    {
        return $this->request('DELETE', $uri);
    }

    /**
     * 发送 PUT 请求
     *
     * @param string $uri
     * @param array $data
     * @return array
     * @throws ApiException
     */
    public function put(string $uri, array $data = []): array
    {
        return $this->request('PUT', $uri, ['json' => $data]);
    }

    /**
     * 发送请求
     *
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return array
     * @throws ApiException
     */
    private function request(string $method, string $uri, array $options = []): array
    {
        // 如果有令牌，自动添加到请求头
        if ($this->token && !str_contains($uri, '/console/api/login') && !str_contains($uri, '/console/api/oauth/token/refresh')) {
            if (!isset($options['headers'])) {
                $options['headers'] = [];
            }
            $options['headers']['Authorization'] = 'Bearer ' . $this->token;
        }

        if ($this->isDebug && $this->logger) {
            $this->logger->info('准备发送 API 请求', [
                'method' => $method,
                'uri' => $uri,
                'base_url' => $this->baseUrl,
                'options' => $this->sanitizeOptions($options)
            ]);
        }

        try {
            echo "\n[DEBUG] 正在发送请求: $method $uri\n";

            $response = $this->httpClient->request($method, $uri, $options);

            echo "\n[DEBUG] 请求已发送，正在处理响应\n";

            return $this->handleResponse($response, $method, $uri, $options);
        } catch (GuzzleException $e) {
            if ($this->isDebug && $this->logger) {
                $this->logger->error('Dify API 请求失败', [
                    'method' => $method,
                    'uri' => $uri,
                    'options' => $this->sanitizeOptions($options),
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'error_trace' => $e->getTraceAsString()
                ]);
            }

            // 处理 401 错误，尝试刷新 token 或重新登录
            if ($e->getCode() == 401 && !str_contains($uri, '/console/api/login') && !str_contains($uri, '/console/api/oauth/token/refresh')) {
                echo "\n[DEBUG] 收到 401 错误，尝试刷新 token 或重新登录\n";
                
                try {
                    // 先尝试刷新 token
                    if ($this->getRefreshToken() && $this->refreshToken()) {
                        echo "\n[DEBUG] Token 刷新成功，重试请求\n";
                        
                        // 更新请求头中的 token
                        if (!isset($options['headers'])) {
                            $options['headers'] = [];
                        }
                        $options['headers']['Authorization'] = 'Bearer ' . $this->token;
                        
                        // 重试请求
                        return $this->request($method, $uri, $options);
                    } else {
                        echo "\n[DEBUG] Token 刷新失败或没有刷新令牌，尝试重新登录\n";
                        
                        // 如果刷新失败，尝试重新登录
                        $this->login();
                        
                        // 更新请求头中的 token
                        if (!isset($options['headers'])) {
                            $options['headers'] = [];
                        }
                        $options['headers']['Authorization'] = 'Bearer ' . $this->token;
                        
                        // 重试请求
                        return $this->request($method, $uri, $options);
                    }
                } catch (ApiException $loginException) {
                    // 如果登录也失败，清除令牌并抛出异常
                    $this->clearTokens();
                    throw $loginException;
                }
            }

            // 处理 404 错误
            if ($e->getCode() == 404) {
                throw new NotFoundException('资源未找到: ' . $e->getMessage(), (string)$e->getCode());
            }

            throw new ServerException('请求失败: ' . $e->getMessage() . ' (' . get_class($e) . ')', (string)$e->getCode());
        }
    }

    /**
     * 处理响应
     *
     * @param ResponseInterface $response
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return array
     * @throws ApiException
     */
    private function handleResponse(ResponseInterface $response, string $method, string $uri, array $options): array
    {
        $statusCode = $response->getStatusCode();
        $contents = $response->getBody()->getContents();

        // 尝试解析JSON响应
        $data = [];
        if (!empty($contents)) {
            $data = json_decode($contents, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                if ($this->isDebug && $this->logger) {
                    $this->logger->error('Dify API 响应JSON解析错误', [
                        'method' => $method,
                        'uri' => $uri,
                        'statusCode' => $statusCode,
                        'response' => $contents,
                        'jsonError' => json_last_error_msg()
                    ]);
                }
                throw new ServerException('响应格式错误: ' . json_last_error_msg());
            }
        }

        // 记录响应日志
        if ($this->isDebug && $this->logger) {
            $this->logger->info('Dify API 响应', [
                'method' => $method,
                'uri' => $uri,
                'statusCode' => $statusCode,
                'response' => $contents,
                'parsedData' => $data
            ]);
        }

        if ($statusCode >= 400) {
            // 记录错误日志
            if ($this->isDebug && $this->logger) {
                $this->logger->error('Dify API 响应错误', [
                    'method' => $method,
                    'uri' => $uri,
                    'options' => $this->sanitizeOptions($options),
                    'statusCode' => $statusCode,
                    'response' => $contents,
                    'parsedData' => $data
                ]);
            }

            $errorMessage = $data['message'] ?? '未知错误';
            $errorCode = $data['code'] ?? null;

            throw match ($statusCode) {
                400 => new ValidationException($errorMessage, $errorCode),
                401 => new AuthenticationException($errorMessage, $errorCode),
                403 => new AuthorizationException($errorMessage, $errorCode),
                404 => new NotFoundException($errorMessage, $errorCode),
                429 => new RateLimitException($errorMessage, $errorCode),
                default => new ServerException($errorMessage, $errorCode),
            };
        }

        // 确保返回数组
        return is_array($data) ? $data : ['data' => $data];
    }

    /**
     * 清理请求选项，移除敏感信息
     *
     * @param array $options
     * @return array
     */
    private function sanitizeOptions(array $options): array
    {
        $sanitized = $options;

        // 移除敏感信息
        if (isset($sanitized['headers'])) {
            foreach ($sanitized['headers'] as $key => $value) {
                if (strtolower($key) === 'authorization') {
                    $sanitized['headers'][$key] = 'Bearer ***';
                }
            }
        }

        return $sanitized;
    }

    /**
     * 获取数据集控制台 API
     *
     * @return DocumentApi
     */
    public function documents(): DocumentApi
    {
        return $this->documents;
    }

    /**
     * 获取认证 API
     *
     * @return AuthApi
     */
    public function auth(): AuthApi
    {
        return $this->auth;
    }

    /**
     * 获取数据集 API
     *
     * @return DatasetApi
     */
    public function datasets(): DatasetApi
    {
        return $this->datasets;
    }

    /**
     * 使用 refresh token 刷新 access token
     *
     * @return bool 是否成功刷新
     */
    public function refreshToken(): bool
    {
        $refreshToken = $this->getRefreshToken();
        if (!$refreshToken) {
            echo "\n[DEBUG] 没有可用的 refresh token\n";
            return false;
        }

        try {
            echo "\n[DEBUG] 尝试使用 refresh token 刷新 access token\n";
            
            // 使用正确的 Dify API 刷新令牌端点
            $response = $this->httpClient->post('console/api/oauth/token/refresh', [
                'json' => [
                    'refresh_token' => $refreshToken
                ]
            ]);

            $contents = $response->getBody()->getContents();
            $data = json_decode($contents, true);
            
            if (isset($data['result']) && $data['result'] === 'success' && isset($data['data']['access_token'])) {
                echo "\n[DEBUG] 成功刷新 token\n";
                
                $this->setToken(
                    $data['data']['access_token'],
                    $data['data']['refresh_token'] ?? null
                );
                return true;
            }
            
            echo "\n[DEBUG] 刷新 token 响应格式不正确\n";
            // 刷新失败，清除刷新令牌
            $this->tokenStorage->clearRefreshToken();
            return false;
        } catch (\Exception $e) {
            echo "\n[DEBUG] 刷新 token 失败: " . $e->getMessage() . "\n";
            // 刷新失败，清除刷新令牌
            $this->tokenStorage->clearRefreshToken();
            return false;
        }
    }

    /**
     * 检查 access token 是否过期
     *
     * @return bool
     */
    public function isTokenExpired(): bool
    {
        if (!$this->token) {
            return true;
        }
        
        // 解析 JWT token
        $parts = explode('.', $this->token);
        if (count($parts) !== 3) {
            return true;
        }
        
        try {
            $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
            if (!$payload || !isset($payload['exp'])) {
                return true;
            }
            
            // 检查是否过期（提前 30 秒判断，避免边界问题）
            return $payload['exp'] - 30 < time();
        } catch (\Exception $e) {
            return true;
        }
    }
}
