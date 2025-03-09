<?php

declare(strict_types=1);

namespace Happyphper\Dify;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use Hyperf\Guzzle\CoroutineHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Happyphper\Dify\Exceptions\ApiException;
use Happyphper\Dify\Exceptions\ValidationException;
use Happyphper\Dify\Exceptions\AuthenticationException;
use Happyphper\Dify\Exceptions\AuthorizationException;
use Happyphper\Dify\Exceptions\NotFoundException;
use Happyphper\Dify\Exceptions\RateLimitException;
use Happyphper\Dify\Exceptions\ServerException;

/**
 * Dify HTTP 客户端
 */
class HttpClient
{
    /**
     * Guzzle HTTP 客户端
     *
     * @var Client
     */
    private Client $client;

    /**
     * 配置
     *
     * @var Config
     */
    private Config $config;

    /**
     * 日志记录器
     *
     * @var LoggerInterface|null
     */
    private ?LoggerInterface $logger = null;

    /**
     * 构造函数
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->logger = $config->getLogger();

        if ($this->config->isDebug() && $this->logger) {
            $this->logger->info('初始化 Dify HTTP 客户端');
        }

        // 创建处理器栈
        $stack = HandlerStack::create();

        // 在协程环境中使用CoroutineHandler
        if (class_exists('\Swoole\Coroutine') && \Swoole\Coroutine::getCid() > 0) {
            if ($this->config->isDebug() && $this->logger) {
                $this->logger->info('使用 Swoole 协程处理器');
            }
            $stack = HandlerStack::create(new CoroutineHandler());
        }

        // 确保base_uri不包含v1前缀
        $baseUrl = $config->getBaseUrl();
        $baseUrl = rtrim($baseUrl, '/');
        if (str_ends_with($baseUrl, '/v1')) {
            $baseUrl = substr($baseUrl, 0, -3);
        } else if (str_contains($baseUrl, '/v1/')) {
            $baseUrl = str_replace('/v1/', '/', $baseUrl);
        }
        $baseUrl = rtrim($baseUrl, '/') . '/';

        if ($this->config->isDebug() && $this->logger) {
            $this->logger->info('配置 HTTP 客户端', [
                'base_url' => $baseUrl,
                'timeout' => 10,
                'connect_timeout' => 5
            ]);
        }

        $this->client = new Client([
            'base_uri' => $baseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $config->getApiKey(),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'handler' => $stack,
            'timeout' => 10,           // 请求超时时间（秒）
            'connect_timeout' => 5,    // 连接超时时间（秒）
            'debug' => $config->isDebug(),
        ]);
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
                'Authorization' => 'Bearer ' . $this->config->getApiKey(),
                'Accept' => '*/*'
            ]
        ];

        if ($this->config->isDebug() && $this->logger) {
            $this->logger->info('Dify API 上传请求', [
                'uri' => $uri,
                'multipart' => array_map(function($item) {
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
        // 确保 URI 以斜杠开头，并添加 /v1 前缀
        $uri = '/v1/' . ltrim($uri, '/');

        if ($this->config->isDebug() && $this->logger) {
            $this->logger->info('准备发送 API 请求', [
                'method' => $method,
                'uri' => $uri,
                'base_url' => $this->config->getBaseUrl(),
                'options' => $this->sanitizeOptions($options)
            ]);
        }

        try {
            echo "\n[DEBUG] 正在发送请求: {$method} {$uri}\n";

            $response = $this->client->request($method, $uri, $options);

            echo "\n[DEBUG] 请求已发送，正在处理响应\n";

            return $this->handleResponse($response, $method, $uri, $options);
        } catch (GuzzleException $e) {
            if ($this->config->isDebug() && $this->logger) {
                $this->logger->error('Dify API 请求失败', [
                    'method' => $method,
                    'uri' => $uri,
                    'options' => $this->sanitizeOptions($options),
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'error_trace' => $e->getTraceAsString()
                ]);
            }
            throw new ServerException('请求失败: ' . $e->getMessage() . ' (' . get_class($e) . ')', (string) $e->getCode());
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
                if ($this->config->isDebug() && $this->logger) {
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
        if ($this->config->isDebug() && $this->logger) {
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
            if ($this->config->isDebug() && $this->logger) {
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

            switch ($statusCode) {
                case 400:
                    throw new ValidationException($errorMessage, $errorCode);
                case 401:
                    throw new AuthenticationException($errorMessage, $errorCode);
                case 403:
                    throw new AuthorizationException($errorMessage, $errorCode);
                case 404:
                    throw new NotFoundException($errorMessage, $errorCode);
                case 429:
                    throw new RateLimitException($errorMessage, $errorCode);
                default:
                    throw new ServerException($errorMessage, $errorCode);
            }
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
}
