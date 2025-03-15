<?php

declare(strict_types=1);

namespace Happyphper\Dify;

use Happyphper\Dify\Console\ConsoleClient;
use Happyphper\Dify\Public\PublicClient;
use Happyphper\Dify\Support\Cache\CacheFactory;
use Happyphper\Dify\Support\Cache\CacheInterface;
use Happyphper\Dify\Support\TokenStorage;
use Hyperf\Contract\ContainerInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                // 注册缓存接口
                CacheInterface::class => function (ContainerInterface $container) {
                    $config = $container->get(\Hyperf\Contract\ConfigInterface::class);
                    $cacheConfig = $config->get('dify.cache', []);
                    
                    return CacheFactory::create($cacheConfig);
                },
                
                // 注册令牌存储
                TokenStorage::class => function (ContainerInterface $container) {
                    $cache = $container->get(CacheInterface::class);
                    return new TokenStorage($cache);
                },
                
                // 注册 Dify 客户端
                PublicClient::class => function (ContainerInterface $container) {
                    $config = $container->get(\Hyperf\Contract\ConfigInterface::class);
                    $logger = $container->get(\Hyperf\Logger\LoggerFactory::class)->get('dify');

                    $apiKey = $config->get('dify.api_key', '');
                    $baseUrl = $config->get('dify.base_url', 'https://api.dify.ai');
                    $debug = $config->get('dify.debug', false);

                    return new PublicClient($baseUrl, $apiKey, $debug, $logger);
                },
                // 注册 Diyf 控制台客户端
                ConsoleClient::class => function (ContainerInterface $container) {
                  $config = $container->get(\Hyperf\Contract\ConfigInterface::class);
                  $logger = $container->get(\Hyperf\Logger\LoggerFactory::class)->get('dify');

                  $email = $config->get('dify.console.email', '');
                  $password = $config->get('dify.console.password', '');
                  $baseUrl = $config->get('dify.base_url', 'https://api.dify.ai');
                  $debug = $config->get('dify.debug', false);
                  $cache = $container->get(CacheInterface::class);
                  $difyConfig = $config->get('dify', []);

                  return new ConsoleClient($baseUrl, $email, $password, $debug, $logger, $cache, $difyConfig);
                },
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'Dify 配置文件',
                    'source' => dirname(__DIR__) . '/publish/dify.php',
                    'destination' => $this->getBasePath() . '/config/autoload/dify.php',
                ],
            ],
        ];
    }
    
    /**
     * 获取基础路径
     *
     * @return string
     */
    private function getBasePath(): string
    {
        return defined('BASE_PATH') ? BASE_PATH : '';
    }
}
