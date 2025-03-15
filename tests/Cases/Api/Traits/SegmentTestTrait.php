<?php

namespace Happyphper\Dify\Tests\Cases\Api\Traits;

use Happyphper\Dify\Exceptions\ApiException;
use Happyphper\Dify\Public\Requests\SegmentCreateRequest;

trait SegmentTestTrait
{
    /**
     * 创建测试分段
     *
     * @param string $content 分段内容
     * @param string|null $answer 答案
     * @param array|null $keywords 关键词
     * @return string 分段ID
     * @throws ApiException
     */
    protected function createTestSegment(string $content, ?string $answer = null, ?array $keywords = null): string
    {
        // 等待文档处理完成
        $this->waitForDocumentComplete($this->dataset->id, $this->docCreateRes->batch);

        $segment = new SegmentCreateRequest($content);

        if ($answer !== null) {
            $segment->setAnswer($answer);
        }

        if ($keywords !== null) {
            $segment->setKeywords($keywords);
        }

        $res = $this->client->segments()->create($this->dataset->id, $this->docCreateRes->document->id, [$segment]);
        return $res->data[0]->id;
    }

    /**
     * 等待文档处理完成
     *
     * @param string $datasetId 数据集ID
     * @param string $batch 批次号
     * @param int $maxAttempts 最大尝试次数
     * @param int $interval 间隔时间（秒）
     * @throws ApiException
     */
    protected function waitForDocumentComplete(string $datasetId, string $batch, int $maxAttempts = 60, int $interval = 1): void
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

            /** @var \Happyphper\Dify\Public\Responses\DocumentStatus $status */
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

            if ($status->indexingStatus !== 'completed') {
                sleep($interval);
                continue;
            }

            return;

        } while (true);
    }
}
