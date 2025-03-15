<?php

namespace Happyphper\Dify\Tests\Cases\Api\Segment;

use Happyphper\Dify\Exceptions\ApiException;
use Happyphper\Dify\Public\Requests\SegmentCreateRequest;
use Happyphper\Dify\Tests\Cases\Api\TestCase;

class SegmentDeleteTest extends TestCase
{
    /**
     * 等待文档处理完成
     *
     * @param string $datasetId 数据集ID
     * @param string $batch 批次号
     * @param int $maxAttempts 最大尝试次数
     * @param int $interval 间隔时间（秒）
     * @throws ApiException
     */
    private function waitForDocumentComplete(string $datasetId, string $batch, int $maxAttempts = 60, int $interval = 1): void
    {
        $attempt = 0;
        $processingStarted = false;

        do {
            $attempt++;
            if ($attempt > $maxAttempts) {
                throw new ApiException('文档处理超时');
            }

            $statusResponse = $this->client->documents()->getIndexingStatus($datasetId, $batch);

            if (empty($statusResponse->data)) {
                throw new ApiException('获取文档状态失败：响应数据为空');
            }

            $status = $statusResponse->data[0];

            // 检查是否开始处理
            if (!$processingStarted && $status->processingStartedAt !== null) {
                $processingStarted = true;
                echo "文档开始处理...\n";
            }

            // 显示当前状态和进度
            echo sprintf(
                "当前状态：%s (已完成段落: %d/%d)\n",
                $status->indexingStatus,
                $status->completedSegments,
                $status->totalSegments
            );

            // 如果处理出错，立即抛出异常
            if ($status->hasError()) {
                throw new ApiException('文档处理失败：' . $status->error);
            }

            // 检查各种状态
            if (in_array($status->indexingStatus, ['waiting', 'parsing', 'cleaning', 'indexing', 'splitting'])) {
                sleep($interval);
                continue;
            }

            if ($status->indexingStatus === 'completed') {
                // 确保所有段落都已完成
                if ($status->completedSegments >= $status->totalSegments) {
                    return;
                }
            }

            sleep($interval);

        } while (true);
    }

    /**
     * 测试删除分段
     *
     * @group api
     * @throws ApiException
     */
    public function testDelete()
    {
        $dataset = $this->createDataset();
        $document = $this->createDocumentByTex($dataset->id);

        // 等待文档处理完成
        $this->waitForDocumentComplete($dataset->id, $document->batch);

        // 创建测试分段
        $segment = new SegmentCreateRequest('测试分段内容');
        $res = $this->client->segments()->create($dataset->id, $document->document->id, [$segment]);
        $segmentId = $res->data[0]->id;

        // 删除分段
        $deleteResult = $this->client->segments()->delete($dataset->id, $document->document->id, $segmentId);

        // 验证删除结果
        $this->assertTrue($deleteResult);

        // 尝试获取已删除的分段
        $segments = $this->client->segments()->list($dataset->id, $document->document->id);
        $this->assertCount(1, $segments->data);

        // 清理数据
        $this->deleteDocument($dataset->id, $document->document->id);
        $this->deleteDataset($dataset->id);
    }

    /**
     * 测试删除不存在的分段
     *
     * @group api
     * @throws ApiException
     */
    public function testDeleteNonExistentSegment()
    {
        $dataset = $this->createDataset();
        $document = $this->createDocumentByTex($dataset->id);

        // 等待文档处理完成
        $this->waitForDocumentComplete($dataset->id, $document->batch);

        // 尝试删除不存在的分段
        try {
            $this->client->segments()->delete($dataset->id, $document->document->id, 'non-existent-id');
            $this->fail('预期应该抛出 ApiException');
        } catch (ApiException $e) {
            $this->assertEquals(404, $e->getCode());
            $this->assertStringContainsString('not found', strtolower($e->getMessage()));
        }

        // 清理数据
        $this->deleteDocument($dataset->id, $document->document->id);
        $this->deleteDataset($dataset->id);
    }
}
