<?php

declare(strict_types=1);

namespace Happyphper\Dify\Tests\Cases\Console;

use Happyphper\Dify\Console\ConsoleClient;
use Happyphper\Dify\Exceptions\ConsoleDisabledException;
use Happyphper\Dify\Support\Cache\FileCache;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

/**
 * 控制台接口未启用测试
 */
class ConsoleClientDisabledTest extends TestCase
{
    /**
     * 测试当控制台接口未启用时是否会抛出 ConsoleDisabledException 异常
     */
    public function testConsoleDisabled(): void
    {
        // 创建日志记录器
        $logger = new Logger('dify');
        $logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

        // 创建缓存实例
        $cache = new FileCache(
            sys_get_temp_dir() . '/dify_cache',
            3600,
            'dify_test_'
        );

        // 创建配置
        $config = [
            'console' => [
                'enable' => false,
            ],
        ];

        // 断言会抛出 ConsoleDisabledException 异常
        $this->expectException(ConsoleDisabledException::class);
        $this->expectExceptionMessage('控制台接口未启用，请在配置文件中设置 console.enable = true');

        // 创建控制台客户端
        new ConsoleClient(
            'https://api.dify.ai',
            'test@example.com',
            'password',
            true,
            $logger,
            $cache,
            $config
        );
    }
} 