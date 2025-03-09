<?php

namespace Happyphper\Dify\Tests\Cases;

use Happyphper\Dify\Exceptions\ApiException;
use Happyphper\Dify\Requests\DatasetCreateRequest;
use Happyphper\Dify\Requests\DocumentCreateByFile\DocumentCreateByFileRequest;
use Happyphper\Dify\Requests\DocumentCreateByFile\DocumentData;
use Happyphper\Dify\Requests\DocumentCreateByFile\DocumentFile;
use Happyphper\Dify\Requests\DocumentCreateByTextRequest;
use Happyphper\Dify\Responses\Dataset;
use Happyphper\Dify\Responses\DocumentCreateResponse;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Happyphper\Dify\Client;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * 测试基类
 */
class TestCase extends BaseTestCase
{
    /**
     * API 客户端
     *
     * @var Client
     */
    protected Client $client;

    /**
     * 测试数据集
     *
     * @var Dataset|null
     */
    protected ?Dataset $dataset = null;

    /**
     * 测试文档
     *
     * @var DocumentCreateResponse|null
     */
    protected ?DocumentCreateResponse $docCreateRes = null;

    /**
     * 测试前的准备工作
     *
     * @return void
     * @throws ApiException
     */
    protected function setUp(): void
    {
        parent::setUp();

        // 创建日志记录器
        $logger = new class implements LoggerInterface {
            public function emergency($message, array $context = []): void
            {
                $this->log(LogLevel::EMERGENCY, $message, $context);
            }

            public function alert($message, array $context = []): void
            {
                $this->log(LogLevel::ALERT, $message, $context);
            }

            public function critical($message, array $context = []): void
            {
                $this->log(LogLevel::CRITICAL, $message, $context);
            }

            public function error($message, array $context = []): void
            {
                $this->log(LogLevel::ERROR, $message, $context);
            }

            public function warning($message, array $context = []): void
            {
                $this->log(LogLevel::WARNING, $message, $context);
            }

            public function notice($message, array $context = []): void
            {
                $this->log(LogLevel::NOTICE, $message, $context);
            }

            public function info($message, array $context = []): void
            {
                $this->log(LogLevel::INFO, $message, $context);
            }

            public function debug($message, array $context = []): void
            {
                $this->log(LogLevel::DEBUG, $message, $context);
            }

            public function log($level, $message, array $context = []): void
            {
                echo "\n[" . strtoupper($level) . "] " . $message . "\n";
                if (!empty($context)) {
                    echo "Context: " . json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
                }
            }
        };

        // 创建客户端
        $this->client = new Client(
            $_ENV['DIFY_API_KEY'],
            $_ENV['DIFY_API_BASE_URL'],
            true, // 启用调试模式
            $logger
        );

        // 初始化测试数据集
        $this->dataset = $this->createDataset();

        // 初始化测试文档
        $this->docCreateRes = $this->createDocumentByTex($this->dataset->id);
    }

    /**
     * 测试后的清理工作
     *
     * @return void
     * @throws ApiException
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        // 清理测试文档
        if ($this->docCreateRes !== null) {
            $this->deleteDocument($this->dataset->id, $this->docCreateRes->document->id);
        }

        // 清理测试数据集
        if ($this->dataset !== null) {
            $this->deleteDataset($this->dataset->id);
        }
    }

    /**
     * @throws ApiException
     */
    protected function createDataset(): Dataset
    {
        $request = new DatasetCreateRequest('TEST_' . microtime());

        return $this->client->datasets()->create($request);
    }

    /**
     * @param string $datasetId
     * @return void
     * @throws ApiException
     */
    protected function deleteDataset(string $datasetId): void
    {
        $this->client->datasets()->delete($datasetId);
    }

    /**
     * @throws ApiException
     */
    protected function createDocumentByTex(string $datasetId): DocumentCreateResponse
    {
        $request = new DocumentCreateByTextRequest('Test Dataset ' . microtime(), '测试文本');
        return $this->client->documents()->createFromText($datasetId, $request);
    }

    protected function filepath(): string
    {
        return '/app/README.md';
    }

    protected function filename(): string
    {
        return date('YmdHis') . '.md';
    }

    /**
     * @throws ApiException
     */
    protected function createDocumentByFile(string $datasetId): DocumentCreateResponse
    {
        $data = new DocumentData();
        $file = new DocumentFile($this->filepath(), $this->filename());

        $request = new DocumentCreateByFileRequest($data, $file);

        return $this->client->documents()->createFromFile($datasetId, $request);
    }

    /**
     * @param string $datasetId
     * @param string $documentId
     * @return void
     * @throws ApiException
     */
    protected function deleteDocument(string $datasetId, string $documentId): void
    {
        $this->client->documents()->delete($datasetId, $documentId);
    }
}
