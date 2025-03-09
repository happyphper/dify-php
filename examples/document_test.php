<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Happyphper\Dify\Client;
use Happyphper\Dify\Model\DocumentCreateParams;
use Happyphper\Dify\Model\DocumentListParams;
use Happyphper\Dify\Model\DocumentUpdateParams;

// 创建配置文件（不包含在版本控制中）
if (!file_exists(__DIR__ . '/config.php')) {
    file_put_contents(__DIR__ . '/config.php', "<?php\n\nreturn [\n    'api_key' => 'your-api-key-here',\n    'base_url' => 'https://api.dify.ai/v1',\n];\n");
    echo "请在 examples/config.php 中设置您的 API 密钥\n";
    exit(1);
}

// 加载配置
$config = require __DIR__ . '/config.php';

// 初始化Dify客户端
$client = new Client($config['api_key'], $config['base_url']);

// 设置数据集ID
$datasetId = 'your_dataset_id'; // 替换为你的数据集ID

// 测试从文本创建文档
function testCreateFromText($client, $datasetId)
{
    echo "测试从文本创建文档...\n";
    
    $params = new DocumentCreateParams($datasetId, '测试文档');
    $params->setContent('这是一个测试文档的内容，用于测试Dify PHP SDK的文档API。');
    $params->addMetadata('source', 'test');
    $params->addMetadata('author', 'Dify PHP SDK');
    
    try {
        $document = $client->documents()->createFromText($params);
        echo "文档创建成功，ID: {$document->getId()}\n";
        return $document->getId();
    } catch (\Exception $e) {
        echo "文档创建失败: {$e->getMessage()}\n";
        return null;
    }
}

// 测试从文件创建文档
function testCreateFromFile($client, $datasetId)
{
    echo "测试从文件创建文档...\n";
    
    // 创建一个临时文本文件
    $tempFile = tempnam(sys_get_temp_dir(), 'dify_test_');
    file_put_contents($tempFile, '这是一个测试文件的内容，用于测试Dify PHP SDK的文档API。');
    
    $params = new DocumentCreateParams($datasetId, '测试文件文档');
    $params->setFilepath($tempFile);
    $params->addMetadata('source', 'test_file');
    $params->addMetadata('author', 'Dify PHP SDK');
    
    try {
        $document = $client->documents()->createFromFile($params);
        echo "文档创建成功，ID: {$document->getId()}\n";
        
        // 删除临时文件
        unlink($tempFile);
        
        return $document->getId();
    } catch (\Exception $e) {
        echo "文档创建失败: {$e->getMessage()}\n";
        
        // 删除临时文件
        unlink($tempFile);
        
        return null;
    }
}

// 测试获取文档列表
function testListDocuments($client, $datasetId)
{
    echo "测试获取文档列表...\n";
    
    $params = new DocumentListParams($datasetId);
    $params->setPage(1);
    $params->setLimit(10);
    
    try {
        $collection = $client->documents()->list($params);
        echo "文档列表获取成功，总数: {$collection->getTotal()}\n";
        
        foreach ($collection as $index => $document) {
            echo "  {$index}. {$document->getName()} (ID: {$document->getId()})\n";
        }
    } catch (\Exception $e) {
        echo "文档列表获取失败: {$e->getMessage()}\n";
    }
}

// 测试获取文档详情
function testGetDocument($client, $datasetId, $documentId)
{
    echo "测试获取文档详情...\n";
    
    try {
        $document = $client->documents()->get($datasetId, $documentId);
        echo "文档详情获取成功:\n";
        echo "  ID: {$document->getId()}\n";
        echo "  名称: {$document->getName()}\n";
        echo "  创建时间: {$document->getCreatedAt()}\n";
        echo "  更新时间: {$document->getUpdatedAt()}\n";
    } catch (\Exception $e) {
        echo "文档详情获取失败: {$e->getMessage()}\n";
    }
}

// 测试更新文档
function testUpdateDocument($client, $datasetId, $documentId)
{
    echo "测试更新文档...\n";
    
    $params = new DocumentUpdateParams($datasetId, $documentId);
    $params->setName('更新后的文档名称');
    $params->setMetadata(['updated' => true, 'update_time' => date('Y-m-d H:i:s')]);
    
    try {
        $document = $client->documents()->update($params);
        echo "文档更新成功，新名称: {$document->getName()}\n";
    } catch (\Exception $e) {
        echo "文档更新失败: {$e->getMessage()}\n";
    }
}

// 测试删除文档
function testDeleteDocument($client, $datasetId, $documentId)
{
    echo "测试删除文档...\n";
    
    try {
        $result = $client->documents()->delete($datasetId, $documentId);
        echo "文档删除成功\n";
    } catch (\Exception $e) {
        echo "文档删除失败: {$e->getMessage()}\n";
    }
}

// 执行测试
echo "开始测试文档API...\n\n";

// 创建文档
$textDocumentId = testCreateFromText($client, $datasetId);
echo "\n";

$fileDocumentId = testCreateFromFile($client, $datasetId);
echo "\n";

// 获取文档列表
testListDocuments($client, $datasetId);
echo "\n";

// 如果文本文档创建成功，测试获取详情和更新
if ($textDocumentId) {
    testGetDocument($client, $datasetId, $textDocumentId);
    echo "\n";
    
    testUpdateDocument($client, $datasetId, $textDocumentId);
    echo "\n";
}

// 如果文件文档创建成功，测试删除
if ($fileDocumentId) {
    testDeleteDocument($client, $datasetId, $fileDocumentId);
    echo "\n";
}

echo "文档API测试完成\n"; 