<?php

namespace Happyphper\Dify\Tests\Cases\Document;

use Happyphper\Dify\Exceptions\ApiException;
use Happyphper\Dify\Requests\DocumentCreateByTextRequest;
use Happyphper\Dify\Tests\Cases\TestCase;
use RuntimeException;

class DocumentUpdateByTextTest extends TestCase
{
    /**
     * 测试使用基本参数从文本创建文档
     * @throws ApiException
     */
    public function testUpdateWithBasicParams()
    {
        $dataset = $this->createDataset();

        $res = $this->createDocumentByTex($dataset->id);
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
            
            /** @var \Happyphper\Dify\Responses\DocumentStatus $status */
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

        // 创建文档参数
        $params = new DocumentCreateByTextRequest('Test Document ' . time(), '这是一个测试文档内容，用于测试从文本创建文档功能。');
        $params->text = '测试文本';
        $params->indexingTechnique = 'economy';
        $params->docForm = 'text_model';
        $params->docLanguage = 'English';

        // 创建文档
        $res = $this->client->documents()->updateByText($dataset->id, $document->id, $params);

        // 断言
        $this->assertNotNull($res);
        $this->assertNotNull($res->document->id);

        // 清理知识库
        $this->deleteDocument($dataset->id, $document->id);
        $this->deleteDataset($dataset->id);
    }
}
