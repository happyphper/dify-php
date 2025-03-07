<?php

declare(strict_types=1);

namespace Happyphper\Dify;

use Happyphper\Dify\Api\DatasetApi;
use Happyphper\Dify\Api\DocumentApi;
use Happyphper\Dify\Api\SegmentApi;
use Psr\Log\LoggerInterface;

/**
 * Dify API 客户端
 */
class Client
{
    /**
     * HTTP 客户端
     *
     * @var HttpClient
     */
    private $httpClient;

    /**
     * 配置
     *
     * @var Config
     */
    private $config;

    /**
     * 数据集 API
     *
     * @var DatasetApi
     */
    private $datasets;

    /**
     * 文档 API
     *
     * @var DocumentApi
     */
    private $documents;

    /**
     * 分段 API
     *
     * @var SegmentApi
     */
    private $segments;

    /**
     * 构造函数
     *
     * @param string $apiKey API 密钥
     * @param string|null $baseUrl API 基础 URL
     * @param bool $debug 是否启用调试模式
     * @param LoggerInterface|null $logger 日志记录器
     */
    public function __construct(string $apiKey, ?string $baseUrl = null, bool $debug = false, ?LoggerInterface $logger = null)
    {
        $this->config = new Config(
            $apiKey,
            $baseUrl ?? 'https://api.dify.ai/v1',
            $debug,
            $logger
        );

        $this->httpClient = new HttpClient($this->config);
        $this->initializeApis();
    }

    /**
     * 初始化 API 实例
     */
    private function initializeApis(): void
    {
        $this->datasets = new DatasetApi($this->httpClient);
        $this->documents = new DocumentApi($this->httpClient);
        $this->segments = new SegmentApi($this->httpClient);
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
     * 获取配置
     *
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * 获取 HTTP 客户端
     *
     * @return HttpClient
     */
    public function getHttpClient(): HttpClient
    {
        return $this->httpClient;
    }

    /**
     * 设置 API 密钥
     *
     * @param string $apiKey
     * @return self
     */
    public function setApiKey(string $apiKey): self
    {
        $this->config->setApiKey($apiKey);
        $this->httpClient = new HttpClient($this->config);
        $this->initializeApis();
        return $this;
    }

    /**
     * 设置 API 基础 URL
     *
     * @param string $baseUrl
     * @return self
     */
    public function setBaseUrl(string $baseUrl): self
    {
        $this->config->setBaseUrl($baseUrl);
        $this->httpClient = new HttpClient($this->config);
        $this->initializeApis();
        return $this;
    }

    /**
     * 设置调试模式
     *
     * @param bool $debug
     * @return self
     */
    public function setDebug(bool $debug): self
    {
        $this->config->setDebug($debug);
        return $this;
    }

    /**
     * 设置日志记录器
     *
     * @param LoggerInterface $logger
     * @return self
     */
    public function setLogger(LoggerInterface $logger): self
    {
        $this->config->setLogger($logger);
        return $this;
    }
} 