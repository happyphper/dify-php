<?php

declare(strict_types=1);

namespace Happyphper\Dify\Public;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use Happyphper\Dify\Exceptions\ApiException;
use Happyphper\Dify\Exceptions\AuthenticationException;
use Happyphper\Dify\Exceptions\AuthorizationException;
use Happyphper\Dify\Exceptions\NotFoundException;
use Happyphper\Dify\Exceptions\RateLimitException;
use Happyphper\Dify\Exceptions\ServerException;
use Happyphper\Dify\Exceptions\ValidationException;
use Happyphper\Dify\Public\Api\DatasetApi;
use Happyphper\Dify\Public\Api\DocumentApi;
use Happyphper\Dify\Public\Api\SegmentApi;
use Hyperf\Guzzle\CoroutineHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Dify API 客户端
 */
class PublicClient
{
    /**
     * @var Client
     */
    private Client $httpClient;

    /**
     * 数据集 API
     *
     * @var DatasetApi
     */
    private DatasetApi $datasets;

    /**
     * 文档 API
     *
     * @var DocumentApi
     */
    private DocumentApi $documents;

    /**
     * 分段 API
     *
     * @var SegmentApi
     */
    private SegmentApi $segments;

    /**
     * 构造函数
     *
     * @param string $baseUrl API 基础 URL
     * @param string $apiKey API 密钥
     * @param bool $isDebug
     * @param LoggerInterface|null $logger 日志记录器
     */
    public function __construct(protected string $baseUrl, protected string $apiKey, protected bool $isDebug = false, protected ?LoggerInterface $logger = null)
    {
        $this->httpClient = $this->createHttpClient();

        $this->datasets = new DatasetApi($this);
        $this->documents = new DocumentApi($this);
        $this->segments = new SegmentApi($this);
    }

    private function createHttpClient(): Client
    {
        if ($this->isDebug && $this->logger) {
            $this->logger->info('初始化 Dify HTTP 客户端');
        }

        // 创建处理器栈
        $stack = HandlerStack::create();

        // 在协程环境中使用CoroutineHandler
        if (class_exists('\Swoole\Coroutine') && \Swoole\Coroutine::getCid() > 0) {
            if ($this->isDebug && $this->logger) {
                $this->logger->info('使用 Swoole 协程处理器');
            }
            $stack = HandlerStack::create(new CoroutineHandler());
        }

        // 确保base_uri不包含v1前缀
        $baseUrl = $this->baseUrl;
        $baseUrl = rtrim($baseUrl, '/');
        if (str_ends_with($baseUrl, '/v1')) {
            $baseUrl = substr($baseUrl, 0, -3);
        } else if (str_contains($baseUrl, '/v1/')) {
            $baseUrl = str_replace('/v1/', '/', $baseUrl);
        }
        $baseUrl = rtrim($baseUrl, '/') . '/';

        if ($this->isDebug && $this->logger) {
            $this->logger->info('配置 HTTP 客户端', [
                'base_url' => $baseUrl,
                'timeout' => 10,
                'connect_timeout' => 5
            ]);
        }

        return new Client([
            'base_uri' => $baseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'handler' => $stack,
            'timeout' => 10,           // 请求超时时间（秒）
            'connect_timeout' => 5,    // 连接超时时间（秒）
            'debug' => $this->isDebug,
        ]);
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
     * 获取文档 API
     *
     * @return DocumentApi
     */
    public function documents(): DocumentApi
    {
        return $this->documents;
    }

    /**
     * 获取分段 API
     *
     * @return SegmentApi
     */
    public function segments(): SegmentApi
    {
        return $this->segments;
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
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => '*/*'
            ]
        ];

        if ($this->isDebug && $this->logger) {
            $this->logger->info('Dify API 上传请求', [
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
}
