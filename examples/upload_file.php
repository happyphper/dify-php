<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Happyphper\Dify\DifyClient;
use Happyphper\Dify\Exceptions\ApiException;
use Happyphper\Dify\Exceptions\ApiException;
use Happyphper\Dify\Model\DocumentCreateParams;

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

// 创建一个示例文件
$exampleFilePath = __DIR__ . '/example.txt';
file_put_contents($exampleFilePath, "这是一个示例文件，用于测试 Happyphper Dify PHP SDK 的文件上传功能。\n\n它包含多行文本，用于演示文本分割和索引功能。\n\nDify 是一个强大的 AI 应用开发平台，可以帮助开发者快速构建基于大语言模型的应用。");

try {
    // 获取知识库列表
    echo "获取知识库列表：\n";
    $datasets = $client->datasets()->list(new \Happyphper\Dify\Model\DatasetListParams());
    
    if (count($datasets) === 0) {
        echo "没有找到知识库，正在创建新知识库...\n";
        $datasetParams = new \Happyphper\Dify\Model\DatasetCreateParams('文件上传测试', '用于测试文件上传功能的知识库');
        $dataset = $client->datasets()->create($datasetParams);
    } else {
        $dataset = $datasets[0];
    }
    
    echo sprintf("使用知识库: %s (ID: %s)\n", $dataset->getName(), $dataset->getId());
    
    // 上传文件
    echo "\n上传文件：\n";
    $docParams = new DocumentCreateParams($dataset->getId(), 'example.txt');
    $docParams->setFilepath($exampleFilePath);
    $docParams->addMetadata('indexing_technique', 'high_quality');
    
    $document = $client->documents()->createFromFile($docParams);
    
    echo sprintf(
        "文件上传成功，文档ID: %s, 名称: %s, 状态: %s\n", 
        $document->getId(), 
        $document->getName(), 
        $document->getIndexingStatus()
    );
    
    // 等待索引完成
    echo "\n等待文档索引完成...\n";
    $maxAttempts = 10;
    $attempts = 0;
    $indexed = false;
    
    while ($attempts < $maxAttempts && !$indexed) {
        $attempts++;
        sleep(2); // 等待2秒
        
        $status = $client->documents()->getIndexingStatus($dataset->getId(), $document->getId());
        echo sprintf(
            "尝试 %d/%d: 索引状态: %s\n", 
            $attempts, 
            $maxAttempts, 
            $status->getIndexingStatus()
        );
        
        if ($status->getIndexingStatus() === 'completed') {
            $indexed = true;
            echo "文档索引已完成！\n";
        } elseif ($status->getIndexingStatus() === 'error') {
            echo sprintf("索引出错: %s\n", $status->getError() ?: '未知错误');
            break;
        }
    }
    
    if (!$indexed) {
        echo "索引未在预期时间内完成，请稍后检查状态。\n";
    }
    
    // 检索知识库
    if ($indexed) {
        echo "\n检索知识库：\n";
        $query = "Dify 是什么？";
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
    }
    
    // 清理示例文件
    unlink($exampleFilePath);
    echo "\n示例文件已删除\n";
    
} catch (ApiException $e) {
    echo "错误发生：\n";
    echo "消息: " . $e->getMessage() . "\n";
    echo "状态码: " . $e->getStatusCode() . "\n";
    echo "错误代码: " . $e->getErrorCode() . "\n";
    
    // 清理示例文件
    if (file_exists($exampleFilePath)) {
        unlink($exampleFilePath);
    }
} catch (ApiException $e) {
    // ... existing code ...
} 
