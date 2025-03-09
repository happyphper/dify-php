<?php

declare(strict_types=1);

namespace Happyphper\Dify\Exceptions;

/**
 * 速率限制异常类
 */
class RateLimitException extends ApiException
{
    /**
     * 构造函数
     *
     * @param string $message 错误消息
     * @param string|null $errorCode 错误代码
     */
    public function __construct(string $message = '请求过于频繁', ?string $errorCode = null)
    {
        parent::__construct($message, 429, $errorCode);
    }
} 