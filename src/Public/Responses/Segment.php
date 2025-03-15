<?php

declare(strict_types=1);

namespace Happyphper\Dify\Public\Responses;

/**
 * 分段响应类
 */
class Segment
{
    /**
     * ID
     *
     * @var string
     */
    public string $id;

    /**
     * 位置
     *
     * @var int
     */
    public int $position;

    /**
     * 文档 ID
     *
     * @var string
     */
    public string $documentId;

    /**
     * 内容
     *
     * @var string
     */
    public string $content;

    /**
     * 答案
     *
     * @var string|null
     */
    public ?string $answer;

    /**
     * 字数
     *
     * @var int
     */
    public int $wordCount;

    /**
     * Token 数
     *
     * @var int
     */
    public int $tokens;

    /**
     * 关键字
     *
     * @var array
     */
    public array $keywords;

    /**
     * 索引节点 ID
     *
     * @var string|null
     */
    public ?string $indexNodeId;

    /**
     * 索引节点哈希
     *
     * @var string|null
     */
    public ?string $indexNodeHash;

    /**
     * 命中次数
     *
     * @var int
     */
    public int $hitCount;

    /**
     * 是否启用
     *
     * @var bool
     */
    public bool $enabled;

    /**
     * 禁用时间
     *
     * @var int|null
     */
    public ?int $disabledAt;

    /**
     * 禁用者
     *
     * @var string|null
     */
    public ?string $disabledBy;

    /**
     * 状态
     *
     * @var string
     */
    public string $status;

    /**
     * 创建者
     *
     * @var string
     */
    public string $createdBy;

    /**
     * 创建时间
     *
     * @var int
     */
    public int $createdAt;

    /**
     * 更新时间
     *
     * @var int|null
     */
    public ?int $updatedAt;

    /**
     * 更新者
     *
     * @var string|null
     */
    public ?string $updatedBy;

    /**
     * 索引时间
     *
     * @var int|null
     */
    public ?int $indexingAt;

    /**
     * 完成时间
     *
     * @var int|null
     */
    public ?int $completedAt;

    /**
     * 错误信息
     *
     * @var string|null
     */
    public ?string $error;

    /**
     * 停止时间
     *
     * @var int|null
     */
    public ?int $stoppedAt;

    /**
     * 子分段
     *
     * @var array
     */
    public array $childChunks;

    /**
     * 构造函数
     *
     * @param array $attributes
     */
    public function __construct($attributes)
    {
        $this->id = $attributes['id'];
        $this->position = $attributes['position'];
        $this->documentId = $attributes['document_id'];
        $this->content = $attributes['content'];
        $this->answer = $attributes['answer'];
        $this->wordCount = $attributes['word_count'];
        $this->tokens = $attributes['tokens'];
        $this->keywords = $attributes['keywords'];
        $this->indexNodeId = $attributes['index_node_id'];
        $this->indexNodeHash = $attributes['index_node_hash'];
        $this->hitCount = $attributes['hit_count'];
        $this->enabled = $attributes['enabled'];
        $this->disabledAt = $attributes['disabled_at'];
        $this->disabledBy = $attributes['disabled_by'];
        $this->status = $attributes['status'];
        $this->createdBy = $attributes['created_by'];
        $this->createdAt = $attributes['created_at'];
        $this->updatedAt = $attributes['updated_at'];
        $this->updatedBy = $attributes['updated_by'];
        $this->indexingAt = $attributes['indexing_at'];
        $this->completedAt = $attributes['completed_at'];
        $this->error = $attributes['error'];
        $this->stoppedAt = $attributes['stopped_at'];
        $this->childChunks = $attributes['child_chunks'];
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
            'position' => $this->position,
            'document_id' => $this->documentId,
            'content' => $this->content,
            'answer' => $this->answer,
            'word_count' => $this->wordCount,
            'tokens' => $this->tokens,
            'keywords' => $this->keywords,
            'index_node_id' => $this->indexNodeId,
            'index_node_hash' => $this->indexNodeHash,
            'hit_count' => $this->hitCount,
            'enabled' => $this->enabled,
            'disabled_at' => $this->disabledAt,
            'disabled_by' => $this->disabledBy,
            'status' => $this->status,
            'created_by' => $this->createdBy,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'updated_by' => $this->updatedBy,
            'indexing_at' => $this->indexingAt,
            'completed_at' => $this->completedAt,
            'error' => $this->error,
            'stopped_at' => $this->stoppedAt,
            'child_chunks' => $this->childChunks,
        ];
    }
}
