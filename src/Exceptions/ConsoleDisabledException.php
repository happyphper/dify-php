<?php

declare(strict_types=1);

namespace Happyphper\Dify\Exceptions;

/**
 * 控制台接口未启用异常
 */
class ConsoleDisabledException extends ApiException
{
    /**
     * 构造函数
     *
     * @param string $message 错误消息
     */
    public function __construct(string $message = '控制台接口未启用，请在配置文件中设置 console.enable = true')
    {
        parent::__construct($message, 403);
        $this->errorCode = 'console_disabled';
    }
} 