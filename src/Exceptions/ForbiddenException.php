<?php

declare(strict_types=1);

namespace Happyphper\Dify\Exceptions;

/**
 * 403 禁止访问异常
 */
class ForbiddenException extends ApiException
{
    /**
     * 构造函数
     *
     * @param string $message
     * @param string|null $type
     * @param array|null $param
     */
    public function __construct(string $message = '禁止访问', ?string $type = null, ?array $param = null)
    {
        parent::__construct($message, 403, $type, $param);
    }
} 
