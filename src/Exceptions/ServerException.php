<?php

declare(strict_types=1);

namespace Happyphper\Dify\Exceptions;

/**
 * 服务器异常类
 */
class ServerException extends ApiException
{
    /**
     * 构造函数
     *
     * @param string $message 错误消息
     * @param string|null $errorCode 错误代码
     */
    public function __construct(string $message = '服务器内部错误', ?string $errorCode = null)
    {
        parent::__construct($message, 500, $errorCode);
    }
} 