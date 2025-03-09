<?php

declare(strict_types=1);

namespace Happyphper\Dify\Responses;

/**
 * 文档响应模型
 */
class Document
{
    /**
     * 文档ID
     *
     * @var string|null
     */
    public ?string $id;

    /**
     * 文档位置
     *
     * @var int|null
     */
    public ?int $position;

    /**
     * 文档数据源类型
     *
     * @var string|null
     */
    public ?string $dataSourceType;

    /**
     * 文档数据源信息
     *
     * @var array|null
     */
    public ?array $dataSourceInfo;

    /**
     * 文档数据源详情字典
     *
     * @var array|null
     */
    public ?array $dataSourceDetailDict;

    /**
     * 文档数据集处理规则ID
     *
     * @var string|null
     */
    public ?string $datasetProcessRuleId;

    /**
     * 文档名称
     *
     * @var string|null
     */
    public ?string $name;

    /**
     * 文档创建来源
     *
     * @var string|null
     */
    public ?string $createdFrom;

    /**
     * 文档创建者
     *
     * @var string|null
     */
    public ?string $createdBy;

    /**
     * 文档创建时间
     *
     * @var int|null
     */
    public ?int $createdAt;

    /**
     * 文档令牌数
     *
     * @var int|null
     */
    public ?int $tokens;

    /**
     * 文档索引状态
     *
     * @var string|null
     */
    public ?string $indexingStatus;

    /**
     * 文档错误信息
     *
     * @var string|null
     */
    public ?string $error;

    /**
     * 文档是否启用
     *
     * @var bool|null
     */
    public ?bool $enabled;

    /**
     * 文档禁用时间
     *
     * @var int|null
     */
    public ?int $disabledAt;

    /**
     * 文档禁用者
     *
     * @var string|null
     */
    public ?string $disabledBy;

    /**
     * 文档是否归档
     *
     * @var bool|null
     */
    public ?bool $archived;

    /**
     * 文档显示状态
     *
     * @var string|null
     */
    public ?string $displayStatus;

    /**
     * 文档字数
     *
     * @var int|null
     */
    public ?int $wordCount;

    /**
     * 文档命中次数
     *
     * @var int|null
     */
    public ?int $hitCount;

    /**
     * 文档形式
     *
     * @var string|null
     */
    public ?string $docForm;

    /**
     * 文档完成时间
     *
     * @var int|null
     */
    public ?int $completedAt;

    /**
     * 已处理段落数
     *
     * @var int
     */
    public int $completedSegments = 0;

    /**
     * 总段落数
     *
     * @var int
     */
    public int $totalSegments = 0;

    /**
     * 构造函数
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->id = $attributes['id'] ?? null;
        $this->position = $attributes['position'] ?? null;
        $this->dataSourceType = $attributes['data_source_type'] ?? null;
        $this->dataSourceInfo = $attributes['data_source_info'] ?? null;
        $this->dataSourceDetailDict = $attributes['data_source_detail_dict'] ?? null;
        $this->datasetProcessRuleId = $attributes['dataset_process_rule_id'] ?? null;
        $this->name = $attributes['name'] ?? null;
        $this->createdFrom = $attributes['created_from'] ?? null;
        $this->createdBy = $attributes['created_by'] ?? null;
        $this->createdAt = $attributes['created_at'] ?? null;
        $this->tokens = $attributes['tokens'] ?? null;
        $this->indexingStatus = $attributes['indexing_status'] ?? null;
        $this->error = $attributes['error'] ?? null;
        $this->enabled = $attributes['enabled'] ?? null;
        $this->disabledAt = $attributes['disabled_at'] ?? null;
        $this->disabledBy = $attributes['disabled_by'] ?? null;
        $this->archived = $attributes['archived'] ?? null;
        $this->displayStatus = $attributes['display_status'] ?? null;
        $this->wordCount = $attributes['word_count'] ?? null;
        $this->hitCount = $attributes['hit_count'] ?? null;
        $this->docForm = $attributes['doc_form'] ?? null;
        $this->completedAt = $attributes['completed_at'] ?? null;
        $this->completedSegments = $attributes['completed_segments'] ?? 0;
        $this->totalSegments = $attributes['total_segments'] ?? 0;
    }

    /**
     * 获取上传文件信息
     *
     * @return array|null
     */
    public function getUploadFile(): ?array
    {
        return $this->dataSourceDetailDict['upload_file'] ?? null;
    }

    /**
     * 获取上传文件ID
     *
     * @return string|null
     */
    public function getUploadFileId(): ?string
    {
        $uploadFile = $this->getUploadFile();
        return $uploadFile['id'] ?? null;
    }

    /**
     * 获取上传文件名称
     *
     * @return string|null
     */
    public function getUploadFileName(): ?string
    {
        $uploadFile = $this->getUploadFile();
        return $uploadFile['name'] ?? null;
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
            'data_source_type' => $this->dataSourceType,
            'data_source_info' => $this->dataSourceInfo,
            'data_source_detail_dict' => $this->dataSourceDetailDict,
            'dataset_process_rule_id' => $this->datasetProcessRuleId,
            'name' => $this->name,
            'created_from' => $this->createdFrom,
            'created_by' => $this->createdBy,
            'created_at' => $this->createdAt,
            'tokens' => $this->tokens,
            'indexing_status' => $this->indexingStatus,
            'error' => $this->error,
            'enabled' => $this->enabled,
            'disabled_at' => $this->disabledAt,
            'disabled_by' => $this->disabledBy,
            'archived' => $this->archived,
            'display_status' => $this->displayStatus,
            'word_count' => $this->wordCount,
            'hit_count' => $this->hitCount,
            'doc_form' => $this->docForm,
            'completed_at' => $this->completedAt,
            'completed_segments' => $this->completedSegments,
            'total_segments' => $this->totalSegments,
        ];
    }
} 