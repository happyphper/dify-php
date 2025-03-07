<?php

declare(strict_types=1);

namespace Happyphper\Dify\Model;

class Dataset extends Model
{
    /**
     * 获取数据集ID
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->getAttribute('id');
    }

    /**
     * 获取数据集名称
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->getAttribute('name');
    }

    /**
     * 获取数据集描述
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->getAttribute('description');
    }

    /**
     * 获取数据集提供者
     *
     * @return string|null
     */
    public function getProvider(): ?string
    {
        return $this->getAttribute('provider');
    }

    /**
     * 获取数据集权限
     *
     * @return string|null
     */
    public function getPermission(): ?string
    {
        return $this->getAttribute('permission');
    }

    /**
     * 获取数据集数据源类型
     *
     * @return string|null
     */
    public function getDataSourceType(): ?string
    {
        return $this->getAttribute('data_source_type');
    }

    /**
     * 获取数据集索引技术
     *
     * @return string|null
     */
    public function getIndexingTechnique(): ?string
    {
        return $this->getAttribute('indexing_technique');
    }

    /**
     * 获取数据集应用数量
     *
     * @return int|null
     */
    public function getAppCount(): ?int
    {
        return $this->getAttribute('app_count');
    }

    /**
     * 获取数据集文档数量
     *
     * @return int|null
     */
    public function getDocumentCount(): ?int
    {
        return $this->getAttribute('document_count');
    }

    /**
     * 获取数据集字数
     *
     * @return int|null
     */
    public function getWordCount(): ?int
    {
        return $this->getAttribute('word_count');
    }

    /**
     * 获取数据集创建者
     *
     * @return string|null
     */
    public function getCreatedBy(): ?string
    {
        return $this->getAttribute('created_by');
    }

    /**
     * 获取数据集创建时间
     *
     * @return int|null
     */
    public function getCreatedAt(): ?int
    {
        return $this->getAttribute('created_at');
    }

    /**
     * 获取数据集更新者
     *
     * @return string|null
     */
    public function getUpdatedBy(): ?string
    {
        return $this->getAttribute('updated_by');
    }

    /**
     * 获取数据集更新时间
     *
     * @return int|null
     */
    public function getUpdatedAt(): ?int
    {
        return $this->getAttribute('updated_at');
    }

    /**
     * 获取数据集嵌入模型
     *
     * @return string|null
     */
    public function getEmbeddingModel(): ?string
    {
        return $this->getAttribute('embedding_model');
    }

    /**
     * 获取数据集嵌入模型提供者
     *
     * @return string|null
     */
    public function getEmbeddingModelProvider(): ?string
    {
        return $this->getAttribute('embedding_model_provider');
    }

    /**
     * 获取数据集嵌入是否可用
     *
     * @return bool|null
     */
    public function getEmbeddingAvailable(): ?bool
    {
        return $this->getAttribute('embedding_available');
    }

    /**
     * 获取数据集检索模型字典
     *
     * @return array|null
     */
    public function getRetrievalModelDict(): ?array
    {
        return $this->getAttribute('retrieval_model_dict');
    }

    /**
     * 获取数据集标签
     *
     * @return array|null
     */
    public function getTags(): ?array
    {
        return $this->getAttribute('tags');
    }

    /**
     * 获取数据集文档形式
     *
     * @return string|null
     */
    public function getDocForm(): ?string
    {
        return $this->getAttribute('doc_form');
    }

    /**
     * 获取数据集外部知识信息
     *
     * @return array|null
     */
    public function getExternalKnowledgeInfo(): ?array
    {
        return $this->getAttribute('external_knowledge_info');
    }

    /**
     * 获取数据集外部检索模型
     *
     * @return array|null
     */
    public function getExternalRetrievalModel(): ?array
    {
        return $this->getAttribute('external_retrieval_model');
    }
} 