<?php

declare(strict_types=1);

namespace Happyphper\Dify\Responses;

/**
 * 数据集响应模型
 */
class Dataset
{
    /**
     * 数据集ID
     *
     * @var string|null
     */
    public ?string $id;

    /**
     * 数据集名称
     *
     * @var string|null
     */
    public ?string $name;

    /**
     * 数据集描述
     *
     * @var string|null
     */
    public ?string $description;

    /**
     * 数据集提供者
     *
     * @var string|null
     */
    public ?string $provider;

    /**
     * 数据集权限
     *
     * @var string|null
     */
    public ?string $permission;

    /**
     * 数据集数据源类型
     *
     * @var string|null
     */
    public ?string $dataSourceType;

    /**
     * 数据集索引技术
     *
     * @var string|null
     */
    public ?string $indexingTechnique;

    /**
     * 数据集应用数量
     *
     * @var int|null
     */
    public ?int $appCount;

    /**
     * 数据集文档数量
     *
     * @var int|null
     */
    public ?int $documentCount;

    /**
     * 数据集字数
     *
     * @var int|null
     */
    public ?int $wordCount;

    /**
     * 数据集创建者
     *
     * @var string|null
     */
    public ?string $createdBy;

    /**
     * 数据集创建时间
     *
     * @var int|null
     */
    public ?int $createdAt;

    /**
     * 数据集更新者
     *
     * @var string|null
     */
    public ?string $updatedBy;

    /**
     * 数据集更新时间
     *
     * @var int|null
     */
    public ?int $updatedAt;

    /**
     * 数据集嵌入模型
     *
     * @var string|null
     */
    public ?string $embeddingModel;

    /**
     * 数据集嵌入模型提供者
     *
     * @var string|null
     */
    public ?string $embeddingModelProvider;

    /**
     * 数据集嵌入是否可用
     *
     * @var bool|null
     */
    public ?bool $embeddingAvailable;

    /**
     * 数据集标签
     *
     * @var array
     */
    public array $tags = [];

    /**
     * 构造函数
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->id = $attributes['id'] ?? null;
        $this->name = $attributes['name'] ?? null;
        $this->description = $attributes['description'] ?? null;
        $this->provider = $attributes['provider'] ?? null;
        $this->permission = $attributes['permission'] ?? null;
        $this->dataSourceType = $attributes['data_source_type'] ?? null;
        $this->indexingTechnique = $attributes['indexing_technique'] ?? null;
        $this->appCount = $attributes['app_count'] ?? null;
        $this->documentCount = $attributes['document_count'] ?? null;
        $this->wordCount = $attributes['word_count'] ?? null;
        $this->createdBy = $attributes['created_by'] ?? null;
        $this->createdAt = $attributes['created_at'] ?? null;
        $this->updatedBy = $attributes['updated_by'] ?? null;
        $this->updatedAt = $attributes['updated_at'] ?? null;
        $this->embeddingModel = $attributes['embedding_model'] ?? null;
        $this->embeddingModelProvider = $attributes['embedding_model_provider'] ?? null;
        $this->embeddingAvailable = $attributes['embedding_available'] ?? null;
        $this->tags = $attributes['tags'] ?? [];
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
            'name' => $this->name,
            'description' => $this->description,
            'provider' => $this->provider,
            'permission' => $this->permission,
            'data_source_type' => $this->dataSourceType,
            'indexing_technique' => $this->indexingTechnique,
            'app_count' => $this->appCount,
            'document_count' => $this->documentCount,
            'word_count' => $this->wordCount,
            'created_by' => $this->createdBy,
            'created_at' => $this->createdAt,
            'updated_by' => $this->updatedBy,
            'updated_at' => $this->updatedAt,
            'embedding_model' => $this->embeddingModel,
            'embedding_model_provider' => $this->embeddingModelProvider,
            'embedding_available' => $this->embeddingAvailable,
            'tags' => $this->tags,
        ];
    }
} 