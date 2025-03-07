# Dify PHP SDK

这是 Dify API 的 PHP SDK，支持 Hyperf 框架。

## 安装

```bash
composer require happyphper/dify
```

## 在 Hyperf 中使用

### 发布配置文件

```bash
php bin/hyperf.php vendor:publish happyphper/dify
```

这将会创建以下文件：

- `config/autoload/dify.php` - Dify 配置文件

### 配置

在 `.env` 文件中添加以下配置：

```
# Dify API配置
DIFY_API_KEY=your_api_key_here
DIFY_BASE_URL=https://api.dify.ai/v1
DIFY_DEBUG=false

# Dify文本分割器配置
DIFY_TEXT_SPLITTER_TYPE=chunk
DIFY_TEXT_SPLITTER_CHUNK_SIZE=1000
DIFY_TEXT_SPLITTER_CHUNK_OVERLAP=200

# Dify索引技术
DIFY_INDEXING_TECHNIQUE=high_quality
```

### 基本使用

```php
<?php

use Happyphper\Dify\Client;

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

## 在非 Hyperf 框架中使用

```php
<?php

use Happyphper\Dify\Client;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// 创建日志记录器
$logger = new Logger('dify');
$logger->pushHandler(new StreamHandler('path/to/your.log', Logger::DEBUG));

// 创建 Dify 客户端
$client = new Client(
    'your_api_key_here',
    'https://api.dify.ai/v1',
    true, // 是否开启调试模式
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

// 通过文本更新文档
$document = $client->documents()->updateByText(
    'dataset-id',
    'document-id',
    [
        'name' => '新文档名称',
        'text' => '新文档内容'
    ]
);
```

### 文档分段操作

```php
// 创建分段
$segments = $client->segments()->create('dataset-id', 'document-id', [
    [
        'content' => '分段内容',
        'answer' => '分段答案',
        'keywords' => ['关键词1', '关键词2']
    ]
]);

// 获取分段列表
$segments = $client->segments()->list('dataset-id', 'document-id');

// 更新分段
$segment = $client->segments()->update(
    'dataset-id',
    'document-id',
    'segment-id',
    [
        'content' => '新分段内容',
        'answer' => '新分段答案',
        'keywords' => ['新关键词']
    ]
);
```

## 错误处理

所有 API 调用可能抛出 `DifyException` 异常，您可以捕获它来处理错误：

```php
use Happyphper\Dify\Exception\DifyException;

try {
    $datasets = $client->datasets()->list();
} catch (DifyException $e) {
    echo '错误：' . $e->getMessage() . "\n";
    echo '状态码：' . $e->getStatusCode() . "\n";
    echo '错误代码：' . $e->getErrorCode() . "\n";
}
```

## 高级用法

### 处理大量数据

当处理大量数据时，建议使用分页和适当的错误重试机制：

```php
$page = 1;
$limit = 20;
$allDocuments = [];

do {
    $response = $client->documents()->list('dataset-id', null, $page, $limit);
    $documents = $response['data'] ?? [];
    $allDocuments = array_merge($allDocuments, $documents);
    $page++;
} while (!empty($documents) && $response['has_more'] ?? false);
```

### 故障排除

#### 常见问题

1. **API 密钥无效**：确保您的 API 密钥是正确的，并且具有所需的权限。
   
2. **网络连接问题**：检查您的网络连接和防火墙设置。
   
3. **请求格式错误**：确保请求参数格式正确。

#### 开启调试

如果您在使用过程中遇到问题，可以实现日志记录逻辑来捕获请求和响应信息，帮助调试问题。

## 完整 API 文档

有关 Dify API 的完整文档，请参阅 [Dify API 文档](https://docs.dify.ai/)。

## 许可证

MIT 