# Dify PHP SDK

Dify API 的 PHP SDK，支持 Hyperf 框架。

[English Documentation](README.md)

## 环境要求

- PHP >= 8.0
- Composer
- ext-fileinfo

## 最新更新 (v1.0.1)

- 修复了段落更新和删除操作中的错误处理
- 添加了重生成子段落的功能支持
- 完善了错误处理机制
- 提升了测试覆盖率

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
DIFY_API_KEY=your_api_key_here
DIFY_BASE_URL=https://api.dify.ai/v1
DIFY_DEBUG=false

# Dify 文本分割器配置
DIFY_TEXT_SPLITTER_TYPE=chunk
DIFY_TEXT_SPLITTER_CHUNK_SIZE=1000
DIFY_TEXT_SPLITTER_CHUNK_OVERLAP=200

# Dify 索引技术
DIFY_INDEXING_TECHNIQUE=high_quality
```

### 基本用法

```php
<?php

use Happyphper\Dify\Client;
use Happyphper\Dify\DifyClient;
use Happyphper\Dify\Exceptions\ApiException;

class YourController
{
    /**
     * @var Client
     */
    private $dify;

    public function __construct(Client $dify)
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

use Happyphper\Dify\Client;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Happyphper\Dify\DifyClient;
use Happyphper\Dify\Exceptions\ApiException;

// 创建日志记录器
$logger = new Logger('dify');
$logger->pushHandler(new StreamHandler('path/to/your.log', Logger::DEBUG));

// 创建 Dify 客户端
$client = new Client(
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
