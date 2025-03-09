<?php

declare(strict_types=1);

namespace Happyphper\Dify\Exceptions;

/**
 * 授权异常类
 */
class AuthorizationException extends ApiException
{
    /**
     * 构造函数
     *
     * @param string $message 错误消息
     * @param string|null $errorCode 错误代码
     */
    public function __construct(string $message = '没有权限访问', ?string $errorCode = null)
    {
        parent::__construct($message, 403, $errorCode);
    }
} 