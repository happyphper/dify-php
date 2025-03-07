<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Happyphper\Dify\Client;
use Happyphper\Dify\Exception\DifyException;
use Happyphper\Dify\Model\Dataset;
use Happyphper\Dify\Model\Document;
use Happyphper\Dify\Model\Segment;

// 创建配置文件（不包含在版本控制中）
if (!file_exists(__DIR__ . '/config.php')) {
    file_put_contents(__DIR__ . '/config.php', "<?php\n\nreturn [\n    'api_key' => 'your-api-key-here',\n    'base_url' => 'https://api.dify.ai/v1',\n];\n");
    echo "请在 examples/config.php 中设置您的 API 密钥\n";
    exit(1);
}

// 加载配置
$config = require __DIR__ . '/config.php';

// 初始化客户端
$client = new Client($config['api_key'], $config['base_url']);

try {
    // 获取知识库列表
    echo "获取知识库列表：\n";
    $result = $client->datasets()->list();
    $datasets = $result['data'];
    echo "找到 " . count($datasets) . " 个知识库\n";
    
    // 显示知识库信息
    if ($datasets->isNotEmpty()) {
        foreach ($datasets as $index => $dataset) {
            echo sprintf(
                "[%d] ID: %s, 名称: %s, 文档数: %d\n", 
                $index + 1, 
                $dataset->getId(), 
                $dataset->getName(), 
                $dataset->getDocumentCount()
            );
        }
    }

    // 如果有知识库，使用第一个知识库
    if ($datasets->isNotEmpty()) {
        $dataset = $datasets->first();
        $datasetId = $dataset->getId();
        echo "\n使用知识库 ID: " . $datasetId . ", 名称: " . $dataset->getName() . "\n";

        // 创建一个简单的文档
        echo "\n通过文本创建文档：\n";
        $document = $client->documents()->createByText(
            $datasetId,
            '这是一个通过 Happyphper Dify PHP 客户端创建的测试文档。它用于演示 API 的基本功能。',
            [
                'name' => '测试文档.txt',
                'text_splitter' => [
                    'type' => 'chunk',
                    'chunk_size' => 1000,
                    'chunk_overlap' => 200
                ],
                'indexing_technique' => 'high_quality'
            ]
        );
        
        echo sprintf(
            "文档创建成功，ID: %s, 名称: %s, 状态: %s\n", 
            $document->getId(), 
            $document->getName(), 
            $document->getIndexingStatus()
        );

        // 获取文档列表
        echo "\n获取文档列表：\n";
        $documentsResult = $client->documents()->list($datasetId);
        $documents = $documentsResult['data'];
        echo "找到 " . count($documents) . " 个文档\n";
        
        // 显示文档信息
        if ($documents->isNotEmpty()) {
            foreach ($documents as $index => $doc) {
                echo sprintf(
                    "[%d] ID: %s, 名称: %s, 状态: %s, 字数: %d\n", 
                    $index + 1, 
                    $doc->getId(), 
                    $doc->getName(), 
                    $doc->getIndexingStatus(), 
                    $doc->getWordCount()
                );
            }
        }

        // 添加文档分段
        echo "\n添加文档分段：\n";
        $segmentsCollection = $client->segments()->create($datasetId, $document->getId(), [
            [
                'content' => '这是第一个分段，用于测试 Happyphper Dify PHP SDK。',
                'keywords' => ['测试', 'Dify', 'PHP', 'SDK']
            ],
            [
                'content' => '这是第二个分段，展示了如何使用 Happyphper Dify PHP 客户端。',
                'keywords' => ['API', '客户端', '示例']
            ]
        ]);
        
        echo "成功创建 " . count($segmentsCollection) . " 个分段\n";

        // 获取分段列表
        echo "\n获取分段列表：\n";
        $segmentsResult = $client->segments()->list($datasetId, $document->getId());
        $segments = $segmentsResult['data'];
        echo "找到 " . count($segments) . " 个分段\n";
        
        // 显示分段信息
        if ($segments->isNotEmpty()) {
            foreach ($segments as $index => $segment) {
                echo sprintf(
                    "[%d] ID: %s, 内容: %s\n", 
                    $index + 1, 
                    $segment->getId(), 
                    mb_substr($segment->getContent(), 0, 30) . (mb_strlen($segment->getContent()) > 30 ? '...' : '')
                );
            }
        }

        // 检索知识库
        echo "\n检索知识库：\n";
        $searchResults = $client->datasets()->retrieve($datasetId, '测试');
        if (isset($searchResults['segments']) && !empty($searchResults['segments'])) {
            echo "找到 " . count($searchResults['segments']) . " 个匹配的分段\n";
            foreach ($searchResults['segments'] as $index => $segment) {
                echo sprintf(
                    "[%d] 分数: %.2f, 内容: %s\n", 
                    $index + 1, 
                    $segment['score'], 
                    mb_substr($segment['content'], 0, 30) . (mb_strlen($segment['content']) > 30 ? '...' : '')
                );
            }
        } else {
            echo "没有找到匹配的分段\n";
        }

        // 获取文档索引状态
        echo "\n获取文档索引状态：\n";
        $statusDoc = $client->documents()->getIndexingStatus($datasetId, $document->getId());
        echo sprintf(
            "文档索引状态: %s, 错误: %s\n", 
            $statusDoc->getIndexingStatus(), 
            $statusDoc->getError() ?: '无'
        );
    } else {
        // 如果没有知识库，创建一个
        echo "\n创建知识库：\n";
        $newDataset = $client->datasets()->create('测试知识库', '通过 Happyphper Dify PHP 客户端创建的测试知识库');
        echo sprintf(
            "知识库创建成功，ID: %s, 名称: %s\n", 
            $newDataset->getId(), 
            $newDataset->getName()
        );
    }
} catch (DifyException $e) {
    echo "错误发生：\n";
    echo "消息: " . $e->getMessage() . "\n";
    echo "状态码: " . $e->getStatusCode() . "\n";
    echo "错误代码: " . $e->getErrorCode() . "\n";
} 