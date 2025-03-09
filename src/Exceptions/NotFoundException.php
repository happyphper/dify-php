<?php

declare(strict_types=1);

namespace Happyphper\Dify\Exceptions;

/**
 * 资源不存在异常类
 */
class NotFoundException extends ApiException
{
    /**
     * 构造函数
     *
     * @param string $message 错误消息
     * @param string|null $errorCode 错误代码
     */
    public function __construct(string $message = '资源不存在', ?string $errorCode = null)
    {
        parent::__construct($message, 404, $errorCode);
    }
} 