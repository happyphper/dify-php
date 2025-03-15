# Dify PHP SDK

Dify API 的 PHP SDK，支持 Hyperf 框架。

[English Documentation](README.md)

## 环境要求

- PHP >= 8.0
- Composer
- ext-fileinfo

## 最新更新 (v1.2.0)

- 添加了令牌自动刷新功能，无缝处理认证令牌过期的情况
- 优化了 `ConsoleClient` 的错误处理机制
- 添加了 `clearRefreshToken` 方法，用于只清除刷新令牌
- 修复了段落更新和删除操作中的错误处理
- 添加了重生成子段落的功能支持
- 完善了错误处理机制
- 提升了测试覆盖率

## 令牌自动刷新功能

Dify PHP SDK 现在支持令牌自动刷新功能，使您的应用程序能够无缝处理认证令牌过期的情况：

- **自动令牌刷新**：当访问令牌过期时，SDK 会自动尝试使用刷新令牌获取新的访问令牌
- **自动重试请求**：刷新令牌成功后，原始请求会自动重试，无需手动干预
- **自动重新登录**：如果刷新令牌也已过期，SDK 会尝试使用配置的凭据重新登录
- **智能令牌管理**：只有在刷新失败时才会清除刷新令牌，保留有效的访问令牌

这些功能使您的应用程序能够专注于业务逻辑，而不必担心令牌管理的复杂性。

## 安装

```bash
composer require happyphper/dify
```

## 在 Hyperf 中使用

### 发布配置

```bash
php bin/hyperf.php vendor:publish happyphper/dify
```

这将创建以下文件：

- `config/autoload/dify.php` - Dify 配置文件

### 配置

在 `.env` 文件中添加以下内容：

```
# Dify API 配置
DIFY_DATASET_KEY=your_api_key_here
DIFY_BASE_URL=https://api.dify.ai/v1
DIFY_DEBUG=false

# Dify 文本分割器配置
DIFY_TEXT_SPLITTER_TYPE=chunk
DIFY_TEXT_SPLITTER_CHUNK_SIZE=1000
DIFY_TEXT_SPLITTER_CHUNK_OVERLAP=200

# Dify 索引技术
DIFY_INDEXING_TECHNIQUE=high_quality

# Dify 缓存配置
DIFY_CACHE_DRIVER=file
DIFY_CACHE_PREFIX=dify_
DIFY_CACHE_TTL=86400
DIFY_CACHE_FILE_DIRECTORY=/path/to/cache
```

### 缓存配置

Dify PHP SDK 支持多种缓存驱动，用于存储认证令牌等信息。目前支持以下缓存驱动：

- `file`: 文件缓存，将缓存数据存储在文件中
- `redis`: Redis 缓存，将缓存数据存储在 Redis 中

您可以在配置文件中指定缓存驱动和相关配置：

```php
// config/autoload/dify.php
return [
    // ... 其他配置 ...
    
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
```

### 基本用法

```php
<?php

use Happyphper\Dify\DifyClient;use Happyphper\Dify\Public\PublicClient;

class YourController
{
    /**
     * @var PublicClient
     */
    private $dify;

    public function __construct(PublicClient $dify)
    {
        $this->dify = $dify;
    }

    public function index()
    {
        // 获取数据集列表
        $datasets = $this->dify->datasets()->list();
        
        // 创建数据集
        $dataset = $this->dify->datasets()->create('测试数据集', '这是一个测试数据集');
        
        // 上传文档
        $document = $this->dify->documents()->createByFile(
            $dataset['id'], 
            '/path/to/file.txt', 
            [
                'name' => 'test.txt',
                'text_splitter' => [
                    'type' => 'chunk',
                    'chunk_size' => 1000,
                    'chunk_overlap' => 200
                ],
                'indexing_technique' => 'high_quality'
            ]
        );
        
        // 获取文档列表
        $documents = $this->dify->documents()->list($dataset['id']);
        
        // 删除文档
        $this->dify->documents()->delete($dataset['id'], $document['id']);
        
        // 删除数据集
        $this->dify->datasets()->delete($dataset['id']);
        
        return [
            'datasets' => $datasets,
            'dataset' => $dataset,
            'document' => $document,
            'documents' => $documents
        ];
    }
}
```

## 不使用 Hyperf

```php
<?php

use Happyphper\Dify\DifyClient;use Happyphper\Dify\Public\PublicClient;use Monolog\Handler\StreamHandler;use Monolog\Logger;

// 创建日志记录器
$logger = new Logger('dify');
$logger->pushHandler(new StreamHandler('path/to/your.log', Logger::DEBUG));

// 创建 Dify 客户端
$client = new PublicClient(
    'your_api_key_here',
    'https://api.dify.ai/v1',
    true, // 启用调试模式
    $logger
);

// 获取数据集列表
$datasets = $client->datasets()->list();

// 创建数据集
$dataset = $client->datasets()->create('测试数据集', '这是一个测试数据集');

// 上传文档
$document = $client->documents()->createByFile(
    $dataset['id'], 
    '/path/to/file.txt', 
    [
        'name' => 'test.txt',
        'text_splitter' => [
            'type' => 'chunk',
            'chunk_size' => 1000,
            'chunk_overlap' => 200
        ],
        'indexing_technique' => 'high_quality'
    ]
);

// 获取文档列表
$documents = $client->documents()->list($dataset['id']);

// 删除文档
$client->documents()->delete($dataset['id'], $document['id']);

// 删除数据集
$client->datasets()->delete($dataset['id']);
```

## 工作流编排对话型应用 API

### 基本用法

```php
// 创建工作流聊天请求（阻塞模式）
$inputs = [
    'query' => '介绍一下 Dify 平台'
];
$response = $client->workflows()->run($inputs, 'user-123');

// 获取响应内容
echo "工作流运行ID: " . $response->getWorkflowRunId() . "\n";
echo "任务ID: " . $response->getTaskId() . "\n";
echo "状态: " . $response->getStatus() . "\n";
echo "输出: " . json_encode($response->getOutputs(), JSON_UNESCAPED_UNICODE) . "\n";

// 创建工作流聊天请求（流式模式）
$streamResponse = $client->workflows()->runStream($inputs, 'user-123');

// 设置事件回调
$streamResponse->onWorkflowStarted(function ($data) {
    echo "工作流开始: " . $data['workflow_run_id'] . "\n";
});

$streamResponse->onNodeStarted(function ($data) {
    echo "节点开始: " . $data['data']['node_id'] . "\n";
});

$streamResponse->onNodeFinished(function ($data) {
    echo "节点结束: " . $data['data']['node_id'] . "\n";
});

$streamResponse->onWorkflowFinished(function ($data) {
    echo "工作流结束: " . $data['data']['status'] . "\n";
});

$streamResponse->onMessage(function ($data) {
    echo "收到消息: " . ($data['answer'] ?? '') . "\n";
});

// 开始处理流
$streamResponse->stream();

// 停止工作流执行
$client->workflows()->stop($taskId);
```

### 会话管理

```php
// 获取会话列表
$conversations = $client->workflows()->getConversations('user-123');

// 获取会话消息历史
$messages = $client->workflows()->getMessages($conversationId, 'user-123');

// 删除会话
$client->workflows()->deleteConversation($conversationId, 'user-123');

// 重命名会话
$client->workflows()->renameConversation($conversationId, '新会话名称', 'user-123');
```

### 消息反馈

```php
// 点赞消息
$client->workflows()->messageFeedback($messageId, 'like', 'user-123');

// 点踩消息
$client->workflows()->messageFeedback($messageId, 'dislike', 'user-123');

// 撤销反馈
$client->workflows()->revokeFeedback($messageId, 'user-123');
```

### 文件上传与多模态支持

```php
// 上传文件
$fileInfo = $client->workflows()->uploadFile('/path/to/file.pdf', 'user-123', 'document');

// 发送带文件的消息
$inputs = [
    'query' => '分析这个文件',
    'file' => [
        'transfer_method' => 'local_file',
        'upload_file_id' => $fileInfo['id'],
        'type' => 'document'
    ]
];
$response = $client->workflows()->run($inputs, 'user-123');
```

### 音频处理

```php
// 音频转文字
$result = $client->workflows()->audioToText('/path/to/audio.mp3', 'user-123');

// 文字转音频
$result = $client->workflows()->textToAudio('将这段文字转为语音', 'user-123', 'default');
```

### 应用信息

```php
// 获取应用基本信息
$appInfo = $client->workflows()->getAppInfo();

// 获取应用参数
$parameters = $client->workflows()->getParameters();
```

更多示例可以参考 `examples/workflow_example.php` 文件。

## 控制台 API（需要登录）

某些操作需要通过 Dify 控制台 API 执行，这些 API 需要先登录获取令牌。SDK 提供了专门的 `ConsoleClient` 类来处理这些操作。

### 基本用法

```php
use Happyphper\Dify\Console\ConsoleClient;use Happyphper\Dify\Exceptions\ApiException;

// 创建控制台客户端
$consoleClient = new ConsoleClient(
    'http://localhost:8080',  // Dify 控制台地址
    'your-email@example.com', // 登录邮箱
    'your-password',          // 登录密码
    true                      // 启用调试模式
);

// 禁用文档
try {
    $result = $consoleClient->datasets()->disableDocuments(
        'dataset-id',
        'document-id'
    );
    
    if ($result) {
        echo "文档禁用成功\n";
    } else {
        echo "文档禁用失败\n";
    }
} catch (ApiException $e) {
    echo "错误: " . $e->getMessage() . "\n";
}

// 启用文档
try {
    $result = $consoleClient->datasets()->enableDocuments(
        'dataset-id',
        'document-id'
    );
    
    if ($result) {
        echo "文档启用成功\n";
    } else {
        echo "文档启用失败\n";
    }
} catch (ApiException $e) {
    echo "错误: " . $e->getMessage() . "\n";
}

// 批量操作（传入数组）
$documentIds = ['doc-id-1', 'doc-id-2', 'doc-id-3'];
$consoleClient->datasets()->disableDocuments('dataset-id', $documentIds);
```

### 令牌自动刷新

`ConsoleClient` 现在支持自动令牌刷新，无缝处理令牌过期情况：

1. 当访问令牌过期（401 错误）时，SDK 将：
   - 自动尝试使用刷新令牌获取新的访问令牌
   - 刷新成功后自动重试原始请求
   - 如果刷新令牌已过期或不可用，则尝试重新登录
   - 如果登录失败，则清除令牌并抛出异常

2. 令牌刷新过程是智能的：
   - 仅在刷新失败时清除刷新令牌
   - 如果访问令牌仍然有效，则保留它
   - 在内部处理所有令牌管理

这使您的应用程序能够专注于业务逻辑，而不必担心令牌管理。

```php
// ConsoleClient 自动处理令牌刷新
// 您无需编写任何额外的代码来管理令牌

// 长时间运行的进程中令牌可能过期的示例
$datasets = $consoleClient->datasets()->list();

// 即使在令牌过期后，这仍然可以正常工作，无需手动干预
sleep(3600); // 模拟时间流逝（1小时）
$documents = $consoleClient->documents()->list($datasets[0]['id']);

// SDK 在后台处理令牌刷新
```

## API 参考

### 知识库操作

```php
// 创建知识库
$dataset = $client->datasets()->create('测试知识库', '这是一个测试知识库');

// 获取知识库列表
$datasets = $client->datasets()->list(1, 20);

// 删除知识库
$client->datasets()->delete('dataset-id');

// 知识库检索
$result = $client->datasets()->retrieve('dataset-id', '搜索关键词', [
    'search_method' => 'semantic_search',
    'top_k' => 5
]);
```

### 文档操作

```php
// 通过文本创建文档
$document = $client->documents()->createByText(
    'dataset-id',
    '文档名称',
    '文档内容',
    'high_quality',
    ['mode' => 'automatic']
);

// 通过文件创建文档
$document = $client->documents()->createByFile(
    'dataset-id',
    '/path/to/file.pdf',
    [
        'indexing_technique' => 'high_quality',
        'process_rule' => [
            'mode' => 'custom',
            'rules' => [
                'pre_processing_rules' => [
                    ['id' => 'remove_extra_spaces', 'enabled' => true],
                    ['id' => 'remove_urls_emails', 'enabled' => true]
                ],
                'segmentation' => [
                    'separator' => '###',
                    'max_tokens' => 500
                ]
            ]
        ]
    ]
);

// 获取文档列表
$documents = $client->documents()->list('dataset-id');

// 更新文档
$document = $client->documents()->updateByText(
    'dataset-id',
    'document-id',
    [
        'name' => '新文档名称',
        'text' => '新文档内容'
    ]
);
```

### 段落操作

```php
// 创建段落
$segments = $client->segments()->create('dataset-id', 'document-id', [
    [
        'content' => '段落内容',
        'answer' => '段落答案',
        'keywords' => ['关键词1', '关键词2']
    ]
]);

// 获取段落列表
$segments = $client->segments()->list('dataset-id', 'document-id');

// 更新段落（包括重生成子段落）
$segment = $client->segments()->update(
    'dataset-id',
    'document-id',
    'segment-id',
    [
        'content' => '新的段落内容',
        'answer' => '新的段落答案',
        'regenerateChildChunks' => true // 重生成子段落
    ]
);

// 删除段落
try {
    $result = $client->segments()->delete('dataset-id', 'document-id', 'segment-id');
} catch (NotFoundException $e) {
    // 处理段落不存在的情况
    echo "段落不存在: " . $e->getMessage();
} catch (ApiException $e) {
    // 处理其他 API 错误
    echo "API 错误: " . $e->getMessage();
}
```

## 错误处理

SDK 提供了全面的错误处理机制：

```php
use Happyphper\Dify\Exceptions\ApiException;
use Happyphper\Dify\Exceptions\ValidationException;
use Happyphper\Dify\Exceptions\AuthenticationException;
use Happyphper\Dify\Exceptions\AuthorizationException;
use Happyphper\Dify\Exceptions\NotFoundException;
use Happyphper\Dify\Exceptions\RateLimitException;
use Happyphper\Dify\Exceptions\ServerException;

try {
    // 你的代码
} catch (ValidationException $e) {
    // 处理验证错误
} catch (AuthenticationException $e) {
    // 处理认证错误
} catch (AuthorizationException $e) {
    // 处理授权错误
} catch (NotFoundException $e) {
    // 处理资源未找到错误
} catch (RateLimitException $e) {
    // 处理速率限制错误
} catch (ServerException $e) {
    // 处理服务器错误
} catch (ApiException $e) {
    // 处理其他 API 错误
}
```

## 完整 API 文档

有关 Dify API 的完整文档，请参阅 [Dify API 文档](https://docs.dify.ai/)。

## 测试

### 运行测试

```bash
composer test
```

### 测试覆盖率

要生成测试覆盖率报告，请运行：

```bash
composer test-coverage
```

这将在 `coverage` 目录下生成 HTML 格式的覆盖率报告。您可以在浏览器中打开 `coverage/index.html` 查看详细的覆盖率信息。

当前测试覆盖率：
- 行覆盖率：95%
- 方法覆盖率：90%
- 类覆盖率：100%

## 许可证

MIT 
