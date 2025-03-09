<?php

declare(strict_types=1);

namespace Happyphper\Dify\Exceptions;

/**
 * 认证异常类
 */
class AuthenticationException extends ApiException
{
    /**
     * 构造函数
     *
     * @param string $message 错误消息
     * @param string|null $errorCode 错误代码
     */
    public function __construct(string $message = '认证失败', ?string $errorCode = null)
    {
        parent::__construct($message, 401, $errorCode);
    }
} 