<?php

namespace Happyphper\Dify;

use Psr\Log\LoggerInterface;

/**
 * Dify API 客户端配置类
 */
class Config
{
    /**
     * API 基础 URL
     *
     * @var string
     */
    private $baseUrl;

    /**
     * API 密钥
     *
     * @var string
     */
    private $apiKey;

    /**
     * 调试模式
     *
     * @var bool
     */
    private $debug;

    /**
     * 日志记录器
     *
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * 初始化配置
     *
     * @param string $apiKey API 密钥
     * @param string $baseUrl API 基础 URL
     * @param bool $debug 是否启用调试模式
     * @param LoggerInterface|null $logger 日志记录器
     */
    public function __construct(string $apiKey, string $baseUrl = 'https://api.dify.ai/v1', bool $debug = false, ?LoggerInterface $logger = null)
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->debug = $debug;
        $this->logger = $logger;
    }

    /**
     * 获取 API 密钥
     *
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * 获取 API 基础 URL
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * 是否启用调试模式
     *
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * 获取日志记录器
     *
     * @return LoggerInterface|null
     */
    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    /**
     * 设置 API 密钥
     *
     * @param string $apiKey
     * @return $this
     */
    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * 设置 API 基础 URL
     *
     * @param string $baseUrl
     * @return $this
     */
    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = rtrim($baseUrl, '/');
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
        $this->debug = $debug;
        return $this;
    }

    /**
     * 设置日志记录器
     *
     * @param LoggerInterface|null $logger
     * @return self
     */
    public function setLogger(?LoggerInterface $logger): self
    {
        $this->logger = $logger;
        return $this;
    }
} 