<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Happyphper\Dify\Console\ConsoleClient;
use Happyphper\Dify\Exceptions\ApiException;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

// 创建日志记录器
$logger = new Logger('dify');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

// 创建控制台客户端
// 注意：这里需要使用 Dify 控制台的 URL，而不是 API 的 URL
// 例如：http://localhost:8080 而不是 https://api.dify.ai/v1
$consoleClient = new ConsoleClient(
    getenv('DIFY_BASE_URL') ?: 'http://localhost:8080',
    getenv('DIFY_EMAIL') ?: 'your-email@example.com',
    getenv('DIFY_PASSWORD') ?: 'your-password',
    true,
    $logger
);

/**
 * 禁用文档
 *
 * @param ConsoleClient $consoleClient 控制台客户端
 * @param string $datasetId 数据集 ID
 * @param string|array $documentId 文档 ID 或 ID 数组
 * @return bool 是否成功
 */
function disableDocument($consoleClient, $datasetId, $documentId)
{
    echo "禁用文档...\n";

    try {
        // 调用 API 禁用文档
        // 这会自动处理登录过程，获取令牌
        $result = $consoleClient->datasets()->disableDocuments($datasetId, $documentId);

        if ($result) {
            echo "文档禁用成功\n";
        } else {
            echo "文档禁用失败\n";
        }

        return $result;
    } catch (ApiException $e) {
        echo "错误: " . $e->getMessage() . "\n";
        return false;
    }
}

/**
 * 启用文档
 *
 * @param ConsoleClient $consoleClient 控制台客户端
 * @param string $datasetId 数据集 ID
 * @param string|array $documentId 文档 ID 或 ID 数组
 * @return bool 是否成功
 */
function enableDocument($consoleClient, $datasetId, $documentId)
{
    echo "启用文档...\n";

    try {
        // 调用 API 启用文档
        // 这会自动处理登录过程，获取令牌
        $result = $consoleClient->datasets()->enableDocuments($datasetId, $documentId);

        if ($result) {
            echo "文档启用成功\n";
        } else {
            echo "文档启用失败\n";
        }

        return $result;
    } catch (ApiException $e) {
        echo "错误: " . $e->getMessage() . "\n";
        return false;
    }
}

// 根据命令行参数运行不同的示例
$action = $argv[1] ?? 'disable';
$datasetId = $argv[2] ?? null;
$documentId = $argv[3] ?? null;

if (!$datasetId || !$documentId) {
    echo "用法: php console_example.php [disable|enable] <dataset_id> <document_id>\n";
    echo "示例: php console_example.php disable dataset_123 document_456\n";
    echo "批量操作示例: php console_example.php disable dataset_123 \"doc1,doc2,doc3\"\n";
    exit(1);
}

// 检查是否是批量操作（逗号分隔的文档 ID）
if (strpos($documentId, ',') !== false) {
    $documentId = explode(',', $documentId);
    echo "检测到批量操作，将处理 " . count($documentId) . " 个文档\n";
}

switch ($action) {
    case 'disable':
        disableDocument($consoleClient, $datasetId, $documentId);
        break;
    case 'enable':
        enableDocument($consoleClient, $datasetId, $documentId);
        break;
    default:
        echo "未知的操作: $action\n";
        echo "可用操作: disable, enable\n";
        break;
}
