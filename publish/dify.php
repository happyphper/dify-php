<?php

declare(strict_types=1);

use function Hyperf\Support\env;

return [
    // Dify API密钥
    'api_key' => env('DIFY_API_KEY', ''),
    
    // Dify API基础URL
    'base_url' => env('DIFY_BASE_URL', 'https://api.dify.ai/v1'),
    
    // 是否启用调试模式
    'debug' => (bool) env('DIFY_DEBUG', false),
    
    // 文本分割器配置
    'text_splitter' => [
        'type' => env('DIFY_TEXT_SPLITTER_TYPE', 'chunk'),
        'chunk_size' => (int) env('DIFY_TEXT_SPLITTER_CHUNK_SIZE', 1000),
        'chunk_overlap' => (int) env('DIFY_TEXT_SPLITTER_CHUNK_OVERLAP', 200),
    ],
    
    // 索引技术
    'indexing_technique' => env('DIFY_INDEXING_TECHNIQUE', 'high_quality'),
]; 