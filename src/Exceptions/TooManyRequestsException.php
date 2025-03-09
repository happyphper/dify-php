<?php

declare(strict_types=1);

namespace Happyphper\Dify\Exceptions;

/**
 * 429 请求过多异常
 */
class TooManyRequestsException extends ApiException
{
    /**
     * 构造函数
     *
     * @param string $message
     * @param string|null $type
     * @param array|null $param
     */
    public function __construct(string $message = '请求过多', ?string $type = null, ?array $param = null)
    {
        parent::__construct($message, 429, $type, $param);
    }
} 
