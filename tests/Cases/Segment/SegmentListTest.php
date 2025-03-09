<?php

namespace Happyphper\Dify\Tests\Cases\Segment;

use Happyphper\Dify\Exceptions\ApiException;
use Happyphper\Dify\Requests\SegmentCreateRequest;
use Happyphper\Dify\Tests\Cases\TestCase;

class SegmentListTest extends TestCase
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

            /** @var \Happyphper\Dify\Responses\DocumentStatus $status */
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
     * 测试获取分段列表
     *
     * @group api
     * @throws ApiException
     */
    public function testList()
    {
        $dataset = $this->createDataset();
        $document = $this->createDocumentByTex($dataset->id);

        // 等待文档处理完成
        $this->waitForDocumentComplete($dataset->id, $document->batch);

        // 创建测试分段
        $segments = [
            (new SegmentCreateRequest('第一个测试分段内容'))->setAnswer('第一个测试答案')->setKeywords(['测试1']),
            (new SegmentCreateRequest('第二个测试分段内容'))->setAnswer('第二个测试答案')->setKeywords(['测试2']),
        ];

        $this->client->segments()->create($dataset->id, $document->document->id, $segments);

        // 获取分段列表
        $res = $this->client->segments()->list($dataset->id, $document->document->id);

        // 验证结果
        $this->assertNotNull($res);
        $this->assertCount(3, $res->data);

        // 验证第一个分段
        $firstSegment = $res->data[1];
        $this->assertNotEmpty($firstSegment->id);
        $this->assertEquals('第一个测试分段内容', $firstSegment->content);
//        $this->assertEquals('第一个测试答案', $firstSegment->answer);
        $this->assertEquals(['测试1'], $firstSegment->keywords);

        // 验证第二个分段
        $secondSegment = $res->data[2];
        $this->assertNotEmpty($secondSegment->id);
        $this->assertEquals('第二个测试分段内容', $secondSegment->content);
//        $this->assertEquals('第二个测试答案', $secondSegment->answer);
        $this->assertEquals(['测试2'], $secondSegment->keywords);

        // 清理数据
        $this->deleteDocument($dataset->id, $document->document->id);
        $this->deleteDataset($dataset->id);
    }

    /**
     * 测试使用关键词搜索分段
     *
     * @group api
     * @throws ApiException
     */
    public function testListWithKeyword()
    {
        $dataset = $this->createDataset();
        $document = $this->createDocumentByTex($dataset->id);

        // 等待文档处理完成
        $this->waitForDocumentComplete($dataset->id, $document->batch);

        // 创建测试分段
        $segments = [
            (new SegmentCreateRequest('第一个测试分段内容'))->setAnswer('第一个测试答案')->setKeywords(['测试1']),
            (new SegmentCreateRequest('第二个测试分段内容'))->setAnswer('第二个测试答案')->setKeywords(['测试2']),
        ];

        $this->client->segments()->create($dataset->id, $document->document->id, $segments);

        // 使用关键词搜索分段
        $res = $this->client->segments()->list($dataset->id, $document->document->id, '第一个');

        // 验证结果
        $this->assertNotNull($res);
        $this->assertCount(1, $res->data);

        // 验证搜索结果
        $segment = $res->data[0];
        $this->assertNotEmpty($segment->id);
        $this->assertEquals('第一个测试分段内容', $segment->content);
//        $this->assertEquals('第一个测试答案', $segment->answer);
        $this->assertEquals(['测试1'], $segment->keywords);

        // 清理数据
        $this->deleteDocument($dataset->id, $document->document->id);
        $this->deleteDataset($dataset->id);
    }

    /**
     * 测试使用状态过滤分段
     *
     * @group api
     * @throws ApiException
     */
    public function testListWithStatus()
    {
        $dataset = $this->createDataset();
        $document = $this->createDocumentByTex($dataset->id);

        // 等待文档处理完成
        $this->waitForDocumentComplete($dataset->id, $document->batch);

        // 创建测试分段
        $segments = [
            (new SegmentCreateRequest('测试分段内容'))->setAnswer('测试答案')->setKeywords(['测试']),
        ];

        $this->client->segments()->create($dataset->id, $document->document->id, $segments);

        // 使用状态过滤分段
        $res = $this->client->segments()->list($dataset->id, $document->document->id, null, 'completed');

        // 验证结果
        $this->assertNotNull($res);
        $this->assertGreaterThan(0, count($res->data));

        // 验证所有分段的状态
        foreach ($res->data as $segment) {
            $this->assertEquals('completed', $segment->status);
        }

        // 清理数据
        $this->deleteDocument($dataset->id, $document->document->id);
        $this->deleteDataset($dataset->id);
    }
}
