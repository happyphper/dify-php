<?php

declare(strict_types=1);

namespace Happyphper\Dify\Exceptions;

/**
 * 500 服务器内部错误异常
 */
class InternalServerErrorException extends ApiException
{
    /**
     * 构造函数
     *
     * @param string $message
     * @param string|null $type
     * @param array|null $param
     */
    public function __construct(string $message = '服务器内部错误', ?string $type = null, ?array $param = null)
    {
        parent::__construct($message, 500, $type, $param);
    }
} 
