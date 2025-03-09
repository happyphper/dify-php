<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Happyphper\Dify\DifyClient;
use Happyphper\Dify\Exceptions\ApiException;
use Happyphper\Dify\Model\DatasetCreateParams;
use Happyphper\Dify\Model\DatasetListParams;
use Happyphper\Dify\Model\DocumentCreateParams;
use Happyphper\Dify\Model\DocumentListParams;
use Happyphper\Dify\Model\SegmentCreateParams;
use Happyphper\Dify\Model\SegmentListParams;

// 创建配置文件（不包含在版本控制中）
if (!file_exists(__DIR__ . '/config.php')) {
    file_put_contents(__DIR__ . '/config.php', "<?php\n\nreturn [\n    'api_key' => 'your-api-key-here',\n    'base_url' => 'https://api.dify.ai/v1',\n];\n");
    echo "请在 examples/config.php 中设置您的 API 密钥\n";
    exit(1);
}

// 加载配置
$config = require __DIR__ . '/config.php';

// 初始化客户端
$client = new DifyClient($config['api_key'], $config['base_url']);

try {
    // 获取知识库列表
    echo "获取知识库列表：\n";
    $datasets = $client->datasets()->list(new DatasetListParams());
    
    echo sprintf("找到 %d 个知识库：\n", count($datasets));
    
    foreach ($datasets as $index => $dataset) {
        echo sprintf(
            "[%d] ID: %s, 名称: %s, 文档数: %d, 创建时间: %s\n", 
            $index + 1, 
            $dataset->getId(), 
            $dataset->getName(), 
            $dataset->getDocumentCount(),
            date('Y-m-d H:i:s', $dataset->getCreatedAt())
        );
    }
    
    // 创建新知识库
    echo "\n创建新知识库：\n";
    $createParams = new DatasetCreateParams(
        '知识库管理测试-' . date('YmdHis'),
        '这是一个用于测试知识库管理功能的知识库，创建于 ' . date('Y-m-d H:i:s')
    );
    $createParams->setPermission('only_me');
    $createParams->setIndexingTechnique('high_quality');
    $newDataset = $client->datasets()->create($createParams);
    
    echo sprintf(
        "知识库创建成功，ID: %s, 名称: %s\n", 
        $newDataset->getId(), 
        $newDataset->getName()
    );
    
    // 获取知识库详情
    echo "\n获取知识库详情：\n";
    $datasetDetail = $client->datasets()->get($newDataset->getId());
    
    echo sprintf(
        "知识库详情：\n" .
        "ID: %s\n" .
        "名称: %s\n" .
        "描述: %s\n" .
        "文档数: %d\n" .
        "创建时间: %s\n" .
        "更新时间: %s\n",
        $datasetDetail->getId(),
        $datasetDetail->getName(),
        $datasetDetail->getDescription(),
        $datasetDetail->getDocumentCount(),
        date('Y-m-d H:i:s', $datasetDetail->getCreatedAt()),
        date('Y-m-d H:i:s', $datasetDetail->getUpdatedAt())
    );
    
    // 创建一个文档
    echo "\n创建文档：\n";
    $docParams = new DocumentCreateParams($newDataset->getId(), '测试文档.txt');
    $docParams->setContent('这是一个测试文档，用于测试知识库管理功能。');
    $document = $client->documents()->createFromText($docParams);
    
    echo sprintf(
        "文档创建成功，ID: %s, 名称: %s\n", 
        $document->getId(), 
        $document->getName()
    );
    
    // 获取文档列表
    echo "\n获取文档列表：\n";
    $docListParams = new DocumentListParams($newDataset->getId());
    $documents = $client->documents()->list($docListParams);
    
    echo sprintf("找到 %d 个文档：\n", count($documents));
    
    foreach ($documents as $index => $doc) {
        echo sprintf(
            "[%d] ID: %s, 名称: %s, 创建时间: %s\n", 
            $index + 1, 
            $doc->getId(), 
            $doc->getName(), 
            date('Y-m-d H:i:s', $doc->getCreatedAt())
        );
    }
    
    // 检索知识库
    echo "\n检索知识库：\n";
    $query = "测试";
    echo "查询: " . $query . "\n";
    
    $searchResults = $client->datasets()->retrieve($newDataset->getId(), $query);
    
    if (isset($searchResults['segments']) && !empty($searchResults['segments'])) {
        echo "找到 " . count($searchResults['segments']) . " 个匹配的分段:\n";
        foreach ($searchResults['segments'] as $index => $segment) {
            echo sprintf(
                "[%d] 分数: %.2f\n内容: %s\n", 
                $index + 1, 
                $segment['score'], 
                $segment['content']
            );
        }
    } else {
        echo "没有找到匹配的分段\n";
    }
    
    // 删除文档
    if (count($documents) > 0) {
        $firstDocument = $documents[0];
        echo "\n删除文档：\n";
        $client->documents()->delete($newDataset->getId(), $firstDocument->getId());
        echo "文档删除成功\n";
        
        // 再次获取文档列表
        $updatedDocsParams = new DocumentListParams($newDataset->getId());
        $updatedDocs = $client->documents()->list($updatedDocsParams);
        
        echo sprintf("删除后剩余 %d 个文档\n", count($updatedDocs));
    }
    
    // 删除知识库
    echo "\n删除知识库：\n";
    $client->datasets()->delete($newDataset->getId());
    echo "知识库删除成功\n";
    
    // 再次获取知识库列表
    $finalDatasets = $client->datasets()->list(new DatasetListParams());
    
    echo sprintf("删除后剩余 %d 个知识库\n", count($finalDatasets));
    
} catch (ApiException $e) {
    echo "错误发生：\n";
    echo "消息: " . $e->getMessage() . "\n";
    echo "状态码: " . $e->getStatusCode() . "\n";
    echo "错误代码: " . $e->getErrorCode() . "\n";
} 