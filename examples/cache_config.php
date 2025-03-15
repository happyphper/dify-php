<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Happyphper\Dify\Support\Cache\CacheFactory;
use Happyphper\Dify\Support\TokenStorage;
use Happyphper\Dify\Console\ConsoleClient;

// 配置示例
$config = [
    'cache' => [
        // 缓存驱动: file, redis
        'driver' => 'file',
        
        // 缓存前缀
        'prefix' => 'dify_',
        
        // 默认缓存过期时间（秒）
        'ttl' => 86400, // 默认24小时
        
        // 文件缓存配置
        'file' => [
            // 缓存目录，默认为系统临时目录下的dify_cache
            'directory' => __DIR__ . '/cache',
        ],
        
        // Redis缓存配置
        'redis' => [
            'host' => '127.0.0.1',
            'port' => 6379,
            'password' => null,
            'database' => 0,
            'timeout' => 0.0,
        ],
    ],
];

// 方法1：直接使用CacheFactory创建缓存实例
$cache = CacheFactory::create($config['cache']);

// 方法2：创建TokenStorage实例，并传入配置
$tokenStorage = new TokenStorage(null, $config);

// 方法3：创建ConsoleClient实例，并传入配置
$consoleClient = new ConsoleClient(
    'https://api.dify.ai',
    'your-email@example.com',
    'your-password',
    false,
    null,
    null,
    $config
);

// 测试缓存
$tokenStorage->setAccessToken('test-access-token');
$accessToken = $tokenStorage->getAccessToken();
echo "Access Token: " . $accessToken . PHP_EOL;

// 清除缓存
$tokenStorage->clearTokens();
echo "Tokens cleared." . PHP_EOL; 