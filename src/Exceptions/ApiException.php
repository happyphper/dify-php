<?php

declare(strict_types=1);

namespace Happyphper\Dify\Exceptions;

use Exception;

/**
 * API 异常基类
 */
class ApiException extends Exception
{
    /**
     * 错误代码
     *
     * @var string|null
     */
    protected ?string $errorCode;

    /**
     * 错误消息
     *
     * @var string
     */
    protected string $errorMessage;

    /**
     * HTTP 状态码
     *
     * @var int
     */
    protected int $statusCode;

    /**
     * 构造函数
     *
     * @param string $message 错误消息
     * @param int $statusCode HTTP 状态码
     */
    public function __construct(string $message, int $statusCode = 500,)
    {
        parent::__construct($message, $statusCode);
        $this->errorMessage = $message;
        $this->statusCode = $statusCode;
    }

    /**
     * 获取错误代码
     *
     * @return string|null
     */
    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    /**
     * 获取错误消息
     *
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * 获取 HTTP 状态码
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
