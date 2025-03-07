<?php

declare(strict_types=1);

namespace Happyphper\Dify;

use Hyperf\Contract\ContainerInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                // 注册 Dify 客户端
                Client::class => function (ContainerInterface $container) {
                    $config = $container->get(\Hyperf\Contract\ConfigInterface::class);
                    $logger = $container->get(\Hyperf\Logger\LoggerFactory::class)->get('dify');
                    
                    $apiKey = $config->get('dify.api_key', '');
                    $baseUrl = $config->get('dify.base_url', 'https://api.dify.ai/v1');
                    $debug = $config->get('dify.debug', false);
                    
                    return new Client($apiKey, $baseUrl, $debug, $logger);
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
                    'destination' => BASE_PATH . '/config/autoload/dify.php',
                ],
            ],
        ];
    }
} 