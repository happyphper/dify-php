<?php

namespace Happyphper\Dify\Tests\Cases\Console;

use Happyphper\Dify\Console\ConsoleClient;
use Happyphper\Dify\Exceptions\ApiException;
use Happyphper\Dify\Support\Cache\CacheInterface;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * 控制台测试基类
 */
class TestCase extends BaseTestCase
{
    /**
     * 控制台客户端
     *
     * @var ConsoleClient
     */
    protected ConsoleClient $client;

    /**
     * 测试前的准备工作
     *
     * @return void
     * @throws ApiException
     */
    protected function setUp(): void
    {
        parent::setUp();

        // 创建控制台客户端
        $this->client = $this->createClient();
    }

    /**
     * 创建控制台客户端
     *
     * @param CacheInterface|null $cache 缓存实例
     * @return ConsoleClient
     */
    protected function createClient(?CacheInterface $cache = null): ConsoleClient
    {
        // 创建日志记录器
        $logger = new class implements LoggerInterface {
            public function emergency($message, array $context = []): void
            {
                $this->log(LogLevel::EMERGENCY, $message, $context);
            }

            public function alert($message, array $context = []): void
            {
                $this->log(LogLevel::ALERT, $message, $context);
            }

            public function critical($message, array $context = []): void
            {
                $this->log(LogLevel::CRITICAL, $message, $context);
            }

            public function error($message, array $context = []): void
            {
                $this->log(LogLevel::ERROR, $message, $context);
            }

            public function warning($message, array $context = []): void
            {
                $this->log(LogLevel::WARNING, $message, $context);
            }

            public function notice($message, array $context = []): void
            {
                $this->log(LogLevel::NOTICE, $message, $context);
            }

            public function info($message, array $context = []): void
            {
                $this->log(LogLevel::INFO, $message, $context);
            }

            public function debug($message, array $context = []): void
            {
                $this->log(LogLevel::DEBUG, $message, $context);
            }

            public function log($level, $message, array $context = []): void
            {
                echo "\n[" . strtoupper($level) . "] " . $message . "\n";
                if (!empty($context)) {
                    echo "Context: " . json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
                }
            }
        };

        // 创建控制台客户端
        return new ConsoleClient(
            $_ENV['DIFY_BASE_URL'],
            $_ENV['DIFY_EMAIL'],
            $_ENV['DIFY_PASSWORD'],
            true, // 启用调试模式
            $logger,
            $cache
        );
    }

    /**
     * 测试后的清理工作
     *
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }
} 