<?php

declare(strict_types=1);

namespace Happyphper\Dify\Exceptions;

/**
 * 409 资源冲突异常
 */
class ConflictException extends ApiException
{
    /**
     * 构造函数
     *
     * @param string $message
     * @param string|null $type
     * @param array|null $param
     */
    public function __construct(string $message = '资源冲突', ?string $type = null, ?array $param = null)
    {
        parent::__construct($message, 409, $type, $param);
    }
} 
