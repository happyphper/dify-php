<?php

namespace Happyphper\Dify\Tests\Cases\Api\Document;

use Happyphper\Dify\Exceptions\ApiException;
use Happyphper\Dify\Public\Requests\DocumentCreateByFile\DocumentCreateByFileRequest;
use Happyphper\Dify\Public\Requests\DocumentCreateByFile\DocumentData;
use Happyphper\Dify\Public\Requests\DocumentCreateByFile\DocumentFile;
use Happyphper\Dify\Tests\Cases\Api\TestCase;
use RuntimeException;

class DocumentUpdateByFileTest extends TestCase
{
    /**
     * 测试使用文件更新文档
     * @throws ApiException
     */
    public function testUpdateWithBasicParams()
    {
        $dataset = $this->createDataset();

        // 创建一个临时文本文件
        $tempFile = tempnam(sys_get_temp_dir(), 'dify_test_');
        file_put_contents($tempFile, '这是一个测试文档内容，用于测试从文件更新文档功能。');

        // 创建文档数据
        $data = new DocumentData();
        $data->indexingTechnique = 'economy';
        $data->docForm = 'text_model';
        $data->docLanguage = 'English';
        $data->processRule = [
            'mode' => 'automatic',
        ];

        // 创建文件对象
        $file = new DocumentFile($tempFile, 'test_document.txt');

        // 创建文档参数
        $params = new DocumentCreateByFileRequest(
            data: $data,
            file: $file
        );

        // 创建文档
        $res = $this->client->documents()->createFromFile($dataset->id, $params);
        $document = $res->document;

        // 查询索引状态，当索引完成时，再进行更新
        $maxAttempts = 60; // 最大尝试次数
        $attempt = 0;
        $interval = 1; // 间隔1秒
        $processingStarted = false;

        do {
            $attempt++;
            if ($attempt > $maxAttempts) {
                throw new RuntimeException('文档处理超时');
            }

            $statusResponse = $this->client->documents()->getIndexingStatus($dataset->id, $res->batch);

            if (empty($statusResponse->data)) {
                throw new RuntimeException('获取文档状态失败：响应数据为空');
            }

            /** @var \Happyphper\Dify\Public\Responses\DocumentStatus $status */
            $status = $statusResponse->data[0];

            // 检查是否开始处理
            if (!$processingStarted && $status->processingStartedAt !== null) {
                $processingStarted = true;
                echo "文档开始处理...\n";
            }

            echo sprintf(
                "当前状态：%s (进度: %.2f%%)\n",
                $status->indexingStatus,
                $status->getProgress()
            );

            // 如果处理出错，立即抛出异常
            if ($status->hasError()) {
                throw new RuntimeException('文档处理失败：' . $status->error);
            }

            if ($status->indexingStatus !== 'completed') {
                sleep($interval);
            }

        } while ($status->indexingStatus !== 'completed');

        // 创建一个新的临时文件用于更新
        $updateFile = tempnam(sys_get_temp_dir(), 'dify_test_update_');
        file_put_contents($updateFile, '这是更新后的文档内容。');

        // 创建更新文档数据
        $updateData = new DocumentData();
        $updateData->indexingTechnique = 'economy';
        $updateData->docForm = 'text_model';
        $updateData->docLanguage = 'English';
        $updateData->processRule = [
            'mode' => 'automatic',
        ];

        // 创建更新文件对象
        $updateFileObj = new DocumentFile($updateFile, 'test_document_updated.txt');

        // 更新文档参数
        $updateParams = new DocumentCreateByFileRequest(
            data: $updateData,
            file: $updateFileObj
        );

        // 更新文档
        $updateRes = $this->client->documents()->updateByFile($dataset->id, $document->id, $updateParams);

        // 断言
        $this->assertNotNull($updateRes);
        $this->assertNotNull($updateRes->document->id);

        // 清理临时文件
        unlink($tempFile);
        unlink($updateFile);

        // 清理知识库
        $this->deleteDocument($dataset->id, $document->id);
        $this->deleteDataset($dataset->id);
    }
}
