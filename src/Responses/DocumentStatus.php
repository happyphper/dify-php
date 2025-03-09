<?php

declare(strict_types=1);

namespace Happyphper\Dify\Responses;

use Happyphper\Dify\Support\Paginator;

class DocumentStatus
{
    /**
     * 文档ID
     * 
     * @var string
     */
    public string $id;

    /**
     * 索引状态
     * 
     * @var string
     */
    public string $indexingStatus;

    /**
     * 处理开始时间
     * 
     * @var float|null
     */
    public ?float $processingStartedAt;

    /**
     * 解析完成时间
     * 
     * @var float|null
     */
    public ?float $parsingCompletedAt;

    /**
     * 清理完成时间
     * 
     * @var float|null
     */
    public ?float $cleaningCompletedAt;

    /**
     * 分割完成时间
     * 
     * @var float|null
     */
    public ?float $splittingCompletedAt;

    /**
     * 完成时间
     * 
     * @var float|null
     */
    public ?float $completedAt;

    /**
     * 暂停时间
     * 
     * @var float|null
     */
    public ?float $pausedAt;

    /**
     * 错误信息
     * 
     * @var string|null
     */
    public ?string $error;

    /**
     * 停止时间
     * 
     * @var float|null
     */
    public ?float $stoppedAt;

    /**
     * 已完成的段落数
     * 
     * @var int
     */
    public int $completedSegments;

    /**
     * 总段落数
     * 
     * @var int
     */
    public int $totalSegments;

    /**
     * 构造函数
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->id = $attributes['id'];
        $this->indexingStatus = $attributes['indexing_status'];
        $this->processingStartedAt = $attributes['processing_started_at'];
        $this->parsingCompletedAt = $attributes['parsing_completed_at'];
        $this->cleaningCompletedAt = $attributes['cleaning_completed_at'];
        $this->splittingCompletedAt = $attributes['splitting_completed_at'];
        $this->completedAt = $attributes['completed_at'];
        $this->pausedAt = $attributes['paused_at'];
        $this->error = $attributes['error'];
        $this->stoppedAt = $attributes['stopped_at'];
        $this->completedSegments = $attributes['completed_segments'];
        $this->totalSegments = $attributes['total_segments'];
    }

    /**
     * 转换为数组
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'indexing_status' => $this->indexingStatus,
            'processing_started_at' => $this->processingStartedAt,
            'parsing_completed_at' => $this->parsingCompletedAt,
            'cleaning_completed_at' => $this->cleaningCompletedAt,
            'splitting_completed_at' => $this->splittingCompletedAt,
            'completed_at' => $this->completedAt,
            'paused_at' => $this->pausedAt,
            'error' => $this->error,
            'stopped_at' => $this->stoppedAt,
            'completed_segments' => $this->completedSegments,
            'total_segments' => $this->totalSegments,
        ];
    }

    /**
     * 检查文档是否已完成处理
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->indexingStatus === 'completed';
    }

    /**
     * 检查文档是否处理失败
     *
     * @return bool
     */
    public function hasError(): bool
    {
        return $this->indexingStatus === 'error' || $this->error !== null;
    }

    /**
     * 获取处理进度百分比
     *
     * @return float
     */
    public function getProgress(): float
    {
        if ($this->totalSegments === 0) {
            return 0.0;
        }
        
        return round(($this->completedSegments / $this->totalSegments) * 100, 2);
    }
}
