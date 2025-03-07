<?php

namespace Happyphper\Dify;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use Hyperf\Guzzle\CoroutineHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Happyphper\Dify\Exception\DifyException;

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
    private $client;

    /**
     * 配置
     *
     * @var Config
     */
    private $config;

    /**
     * 日志记录器
     *
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * 构造函数
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->logger = $config->getLogger();

        // 创建处理器栈
        $stack = HandlerStack::create();
        
        // 在协程环境中使用CoroutineHandler
        if (class_exists('\Swoole\Coroutine') && \Swoole\Coroutine::getCid() > 0) {
            $stack = HandlerStack::create(new CoroutineHandler());
        }

        // 确保base_uri不包含v1前缀
        $baseUrl = $config->getBaseUrl();
        $baseUrl = rtrim($baseUrl, '/');
        if (substr($baseUrl, -3) === '/v1') {
            $baseUrl = substr($baseUrl, 0, -3);
        } else if (strpos($baseUrl, '/v1/') !== false) {
            $baseUrl = str_replace('/v1/', '/', $baseUrl);
        }
        $baseUrl = rtrim($baseUrl, '/') . '/';

        $this->client = new Client([
            'base_uri' => $baseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $config->getApiKey(),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'handler' => $stack,
        ]);
    }

    /**
     * 发送 GET 请求
     *
     * @param string $uri
     * @param array $query
     * @return array
     * @throws DifyException
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
     * @throws DifyException
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
     * @throws DifyException
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
     * @throws DifyException
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
     * @throws DifyException
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
     * @throws DifyException
     */
    private function request(string $method, string $uri, array $options = []): array
    {
        try {
            // 确保URI始终添加v1前缀
            if (strpos($uri, 'v1/') !== 0 && strpos($uri, '/v1/') !== 0) {
                $uri = 'v1/' . ltrim($uri, '/');
            }
            
            // 记录请求日志
            if ($this->config->isDebug() && $this->logger) {
                $this->logger->info('Dify API 请求', [
                    'method' => $method,
                    'uri' => $uri,
                    'options' => $this->sanitizeOptions($options),
                ]);
            }

            $response = $this->client->request($method, $uri, $options);
            return $this->handleResponse($response, $method, $uri, $options);
        } catch (GuzzleException $e) {
            // 记录错误日志
            if ($this->config->isDebug() && $this->logger) {
                $this->logger->error('Dify API 请求错误', [
                    'method' => $method,
                    'uri' => $uri,
                    'options' => $this->sanitizeOptions($options),
                    'error' => $e->getMessage(),
                ]);
            }

            throw new DifyException('请求失败: ' . $e->getMessage(), $e->getCode(), null, null, $e);
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
     * @throws DifyException
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
                // JSON解析错误
                if ($this->config->isDebug() && $this->logger) {
                    $this->logger->error('Dify API 响应JSON解析错误', [
                        'method' => $method,
                        'uri' => $uri,
                        'statusCode' => $statusCode,
                        'response' => $contents,
                        'jsonError' => json_last_error_msg()
                    ]);
                }
                $data = ['message' => '响应格式错误: ' . json_last_error_msg(), 'raw_response' => $contents];
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

        if ($statusCode < 200 || $statusCode >= 300) {
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
            
            // 确保data是数组
            if (!is_array($data)) {
                $data = ['message' => '未知错误', 'raw_response' => $contents];
            }
            
            throw DifyException::fromResponse($data, $statusCode);
        }

        // 确保返回数组
        return is_array($data) ? $data : ['data' => $data, 'raw_response' => $contents];
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