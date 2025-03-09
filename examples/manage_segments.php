<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Happyphper\Dify\DifyClient;
use Happyphper\Dify\Exceptions\ApiException;
use Happyphper\Dify\Exceptions\ApiException;

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
    $datasets = $client->datasets()->list(new \Happyphper\Dify\Model\DatasetListParams());
    
    if (count($datasets) === 0) {
        echo "没有找到知识库，正在创建新知识库...\n";
        $createParams = new \Happyphper\Dify\Model\DatasetCreateParams('分段管理测试', '用于测试分段管理功能的知识库');
        $dataset = $client->datasets()->create($createParams);
    } else {
        $dataset = $datasets[0];
    }
    
    echo sprintf("使用知识库: %s (ID: %s)\n", $dataset->getName(), $dataset->getId());
    
    // 创建一个文档
    echo "\n创建文档：\n";
    $docParams = new \Happyphper\Dify\Model\DocumentCreateParams($dataset->getId(), '分段测试文档.txt');
    $docParams->setContent('这是一个用于测试分段管理的文档。');
    $docParams->addMetadata('indexing_technique', 'high_quality');
    $document = $client->documents()->createFromText($docParams);
    
    echo sprintf(
        "文档创建成功，ID: %s, 名称: %s\n", 
        $document->getId(), 
        $document->getName()
    );
    
    // 创建分段
    echo "\n创建分段：\n";
    $segments = [
        [
            'content' => '这是第一个分段，包含一些基本信息。',
            'keywords' => ['基本', '信息', '第一']
        ],
        [
            'content' => '这是第二个分段，包含一些详细说明。',
            'keywords' => ['详细', '说明', '第二']
        ],
        [
            'content' => '这是第三个分段，包含一些高级功能介绍。',
            'keywords' => ['高级', '功能', '第三']
        ]
    ];
    
    $segmentsCollection = $client->segments()->create($dataset->getId(), $document->getId(), $segments);
    
    echo sprintf("成功创建 %d 个分段\n", count($segmentsCollection));
    
    // 获取分段列表
    echo "\n获取分段列表：\n";
    $segmentsResult = $client->segments()->list($dataset->getId(), $document->getId());
    $segmentsList = $segmentsResult['data'];
    
    echo sprintf("找到 %d 个分段：\n", count($segmentsList));
    $segmentIds = [];
    
    foreach ($segmentsList as $index => $segment) {
        $segmentIds[] = $segment->getId();
        echo sprintf(
            "[%d] ID: %s, 内容: %s\n", 
            $index + 1, 
            $segment->getId(), 
            $segment->getContent()
        );
    }
    
    // 更新第一个分段
    if (count($segmentIds) > 0) {
        echo "\n更新第一个分段：\n";
        $firstSegmentId = $segmentIds[0];
        
        $updatedSegment = $client->segments()->update(
            $dataset->getId(),
            $document->getId(),
            $firstSegmentId,
            [
                'content' => '这是更新后的第一个分段，包含了更新后的基本信息。',
                'keywords' => ['基本', '信息', '更新', '第一']
            ]
        );
        
        echo sprintf(
            "分段更新成功，ID: %s, 新内容: %s\n", 
            $updatedSegment->getId(), 
            $updatedSegment->getContent()
        );
        
        // 再次获取分段列表，查看更新结果
        echo "\n更新后的分段列表：\n";
        $updatedSegmentsResult = $client->segments()->list($dataset->getId(), $document->getId());
        $updatedSegmentsList = $updatedSegmentsResult['data'];
        
        foreach ($updatedSegmentsList as $index => $segment) {
            echo sprintf(
                "[%d] ID: %s, 内容: %s\n", 
                $index + 1, 
                $segment->getId(), 
                $segment->getContent()
            );
        }
        
        // 删除最后一个分段
        if (count($segmentIds) > 2) {
            echo "\n删除最后一个分段：\n";
            $lastSegmentId = $segmentIds[count($segmentIds) - 1];
            
            $deleteResult = $client->segments()->delete(
                $dataset->getId(),
                $document->getId(),
                $lastSegmentId
            );
            
            echo "分段删除" . (isset($deleteResult['result']) && $deleteResult['result'] === true ? '成功' : '失败') . "\n";
            
            // 再次获取分段列表，查看删除结果
            echo "\n删除后的分段列表：\n";
            $finalSegmentsResult = $client->segments()->list($dataset->getId(), $document->getId());
            $finalSegmentsList = $finalSegmentsResult['data'];
            
            foreach ($finalSegmentsList as $index => $segment) {
                echo sprintf(
                    "[%d] ID: %s, 内容: %s\n", 
                    $index + 1, 
                    $segment->getId(), 
                    $segment->getContent()
                );
            }
        }
    }
    
    // 检索知识库
    echo "\n检索知识库：\n";
    $query = "基本信息";
    echo "查询: " . $query . "\n";
    
    $searchResults = $client->datasets()->retrieve($dataset->getId(), $query);
    
    if (isset($searchResults['segments']) && !empty($searchResults['segments'])) {
        echo "找到 " . count($searchResults['segments']) . " 个匹配的分段:\n";
        foreach ($searchResults['segments'] as $index => $segment) {
            echo sprintf(
                "[%d] 分数: %.2f\n内容: %s\n", 
                $index + 1, 
                $segment['score'], 
                $segment['content']
            );
            echo "------------------------\n";
        }
    } else {
        echo "没有找到匹配的分段\n";
    }
    
} catch (ApiException $e) {
    echo "错误发生：\n";
    echo "消息: " . $e->getMessage() . "\n";
    echo "状态码: " . $e->getStatusCode() . "\n";
    echo "错误代码: " . $e->getErrorCode() . "\n";
} catch (ApiException $e) {
    // ... existing code ...
} 
