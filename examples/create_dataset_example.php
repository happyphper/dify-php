<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Happyphper\Dify\Client;
use Happyphper\Dify\Model\DatasetCreateParams;

// 创建 Dify 客户端实例
$client = new Client('your-api-key', 'https://api.dify.ai/v1');

// 示例1：创建基本知识库
echo "创建基本知识库：\n";
$basicParams = new DatasetCreateParams('基本知识库');
$basicParams->setDescription('这是一个基本知识库示例');
$basicParams->setPermission('only_me');

try {
    $basicDataset = $client->datasets()->create($basicParams);
    echo "基本知识库创建成功，ID: {$basicDataset->getId()}\n";
} catch (\Exception $e) {
    echo "创建基本知识库失败：{$e->getMessage()}\n";
}

// 示例2：创建高质量索引知识库
echo "\n创建高质量索引知识库：\n";
$highQualityParams = new DatasetCreateParams('高质量知识库');
$highQualityParams->setDescription('这是一个高质量索引知识库示例');
$highQualityParams->setPermission('all_team_members');
$highQualityParams->setIndexingTechnique('high_quality');
$highQualityParams->setEmbeddingModel('text-embedding-ada-002');
$highQualityParams->setEmbeddingModelProvider('openai');
$highQualityParams->addTag('高质量');
$highQualityParams->addTag('示例');

try {
    $highQualityDataset = $client->datasets()->create($highQualityParams);
    echo "高质量索引知识库创建成功，ID: {$highQualityDataset->getId()}\n";
} catch (\Exception $e) {
    echo "创建高质量索引知识库失败：{$e->getMessage()}\n";
}

// 示例3：创建带有高级检索设置的知识库
echo "\n创建带有高级检索设置的知识库：\n";
$advancedParams = new DatasetCreateParams('高级检索知识库');
$advancedParams->setDescription('这是一个带有高级检索设置的知识库示例');
$advancedParams->setPermission('only_me');
$advancedParams->setIndexingTechnique('high_quality');
$advancedParams->setEmbeddingModel('text-embedding-ada-002');
$advancedParams->setEmbeddingModelProvider('openai');
$advancedParams->setSearchMethod('hybrid_search');
$advancedParams->setWeights(['semantic' => 0.7, 'keyword' => 0.3]);
$advancedParams->setRerankingEnable(true);
$advancedParams->setRerankingMode('model');
$advancedParams->setRerankingProviderName('cohere');
$advancedParams->setRerankingModelName('rerank-english-v2.0');
$advancedParams->setTopK(5);
$advancedParams->setScoreThresholdEnabled(true);
$advancedParams->setScoreThreshold(0.75);

try {
    $advancedDataset = $client->datasets()->create($advancedParams);
    echo "高级检索知识库创建成功，ID: {$advancedDataset->getId()}\n";
} catch (\Exception $e) {
    echo "创建高级检索知识库失败：{$e->getMessage()}\n";
}

// 示例4：创建外部知识库
echo "\n创建外部知识库：\n";
$externalParams = new DatasetCreateParams('外部知识库');
$externalParams->setDescription('这是一个外部知识库示例');
$externalParams->setPermission('only_me');
$externalParams->setProvider('external');
$externalParams->setExternalKnowledgeApiId('api-123');
$externalParams->setExternalKnowledgeId('knowledge-456');

try {
    $externalDataset = $client->datasets()->create($externalParams);
    echo "外部知识库创建成功，ID: {$externalDataset->getId()}\n";
} catch (\Exception $e) {
    echo "创建外部知识库失败：{$e->getMessage()}\n";
}

// 打印所有创建的知识库
echo "\n所有创建的知识库：\n";
$datasets = $client->datasets()->list(new \Happyphper\Dify\Model\DatasetListParams());
foreach ($datasets as $dataset) {
    echo "ID: {$dataset->getId()}, 名称: {$dataset->getName()}, 描述: {$dataset->getDescription()}\n";
} 