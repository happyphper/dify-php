<?php

require_once __DIR__ . '/vendor/autoload.php';

use Happyphper\Dify\Exceptions\ApiException;
use Happyphper\Dify\Public\PublicClient;

// 从 phpunit.xml 读取配置
$xml = simplexml_load_file(__DIR__ . '/phpunit.xml');
$apiKey = (string)$xml->xpath('//env[@name="DIFY_DATASET_KEY"]/@value')[0];
$baseUrl = (string)$xml->xpath('//env[@name="DIFY_BASE_URL"]/@value')[0];

// 初始化客户端
$client = new PublicClient($apiKey, $baseUrl);

try {
    // 获取所有知识库
    echo "正在获取知识库列表...\n";
    $response = $client->datasets()->list(1, 100);
    $datasets = $response->data ?? [];

    // 过滤出带有 TEST_ 前缀的知识库
    $testDatasets = array_filter($datasets, function($dataset) {
        return strpos($dataset->name, 'TEST_') === 0;
    });

    if (empty($testDatasets)) {
        echo "没有找到任何 TEST_ 前缀的知识库。\n";
        exit(0);
    }

    echo sprintf("找到 %d 个 TEST_ 前缀的知识库\n", count($testDatasets));

    // 遍历并删除每个知识库
    $index = 1;
    foreach ($testDatasets as $dataset) {
        echo sprintf(
            "[%d/%d] 正在删除知识库: %s (ID: %s)...\n",
            $index,
            count($testDatasets),
            $dataset->name,
            $dataset->id
        );

        try {
            $client->datasets()->delete($dataset->id);
            echo "✓ 删除成功\n";
        } catch (ApiException $e) {
            echo "✗ 删除失败: " . $e->getMessage() . "\n";
        }
        $index++;
    }

    echo "\n所有 TEST_ 前缀的知识库清理完成！\n";

} catch (ApiException $e) {
    echo "错误发生：\n";
    echo "消息: " . $e->getMessage() . "\n";
    if (method_exists($e, 'getStatusCode')) {
        echo "状态码: " . $e->getStatusCode() . "\n";
    }
    if (method_exists($e, 'getErrorCode')) {
        echo "错误代码: " . $e->getErrorCode() . "\n";
    }
    exit(1);
} catch (\Exception $e) {
    echo "发生未知错误：" . $e->getMessage() . "\n";
    exit(1);
}
