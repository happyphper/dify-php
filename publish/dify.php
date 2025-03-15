<?php

declare(strict_types=1);

use function Hyperf\Support\env;

return [
    // 开放 API 基础URL
    'base_url' => env('DIFY_BASE_URL', 'https://api.dify.ai'),

    // 知识库密钥
    'dataset_key' => env('DIFY_DATASET_KEY', ''),

    // 工作流密钥
    'workflow_keys' => [
        // 默认密钥
        'default' => env('DIFY_WORKFLOW_API_KEY')

        // 如果有多个，您可以自定义键名

    ],

    // 是否启用调试模式
    'debug' => (bool)env('DIFY_DEBUG', false),

    // 文本分割器配置
    'text_splitter' => [
        'type' => env('DIFY_TEXT_SPLITTER_TYPE', 'chunk'),
        'chunk_size' => (int)env('DIFY_TEXT_SPLITTER_CHUNK_SIZE', 1000),
        'chunk_overlap' => (int)env('DIFY_TEXT_SPLITTER_CHUNK_OVERLAP', 200),
    ],

    // 索引技术
    'indexing_technique' => env('DIFY_INDEXING_TECHNIQUE', 'high_quality'),

    /**
     * 账号密码用于调用控制台的非公开接口
     */
    'console' => [
        // 是否启用控制台接口
        'enable' => env('DIFY_CONSOLE_ENABLE', false),
        // 控制台邮箱
        'email' => env('DIFY_CONSOLE_EMAIL', 'admin@ai.com'),
        // 控制台密码
        'password' => env('DIFY_CONSOLE_PASSWORD', '!Qq123123'),
    ],

    /**
     * 缓存配置
     */
    'cache' => [
        // 缓存驱动: file, redis
        'driver' => env('DIFY_CACHE_DRIVER', 'file'),

        // 缓存前缀
        'prefix' => env('DIFY_CACHE_PREFIX', 'dify_'),

        // 默认缓存过期时间（秒）
        'ttl' => (int)env('DIFY_CACHE_TTL', 86400), // 默认24小时

        // 文件缓存配置
        'file' => [
            // 缓存目录，默认为系统临时目录下的dify_cache
            'directory' => env('DIFY_CACHE_FILE_DIRECTORY', sys_get_temp_dir() . '/dify_cache'),
        ],

        // Redis缓存配置
        'redis' => [
            'host' => env('DIFY_CACHE_REDIS_HOST', '127.0.0.1'),
            'port' => (int)env('DIFY_CACHE_REDIS_PORT', 6379),
            'password' => env('DIFY_CACHE_REDIS_PASSWORD', null),
            'database' => (int)env('DIFY_CACHE_REDIS_DATABASE', 0),
            'timeout' => (float)env('DIFY_CACHE_REDIS_TIMEOUT', 0.0),
        ],
    ],
];
