<?php

declare(strict_types=1);

namespace Happyphper\Dify\Model;

class Segment extends Model
{
    /**
     * 获取分段ID
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->getAttribute('id');
    }

    /**
     * 获取分段位置
     *
     * @return int|null
     */
    public function getPosition(): ?int
    {
        return $this->getAttribute('position');
    }

    /**
     * 获取分段文档ID
     *
     * @return string|null
     */
    public function getDocumentId(): ?string
    {
        return $this->getAttribute('document_id');
    }

    /**
     * 获取分段内容
     *
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->getAttribute('content');
    }

    /**
     * 获取分段答案
     *
     * @return string|null
     */
    public function getAnswer(): ?string
    {
        return $this->getAttribute('answer');
    }

    /**
     * 获取分段关键词
     *
     * @return array|null
     */
    public function getKeywords(): ?array
    {
        return $this->getAttribute('keywords');
    }

    /**
     * 获取分段创建者
     *
     * @return string|null
     */
    public function getCreatedBy(): ?string
    {
        return $this->getAttribute('created_by');
    }

    /**
     * 获取分段创建时间
     *
     * @return int|null
     */
    public function getCreatedAt(): ?int
    {
        return $this->getAttribute('created_at');
    }

    /**
     * 获取分段更新者
     *
     * @return string|null
     */
    public function getUpdatedBy(): ?string
    {
        return $this->getAttribute('updated_by');
    }

    /**
     * 获取分段更新时间
     *
     * @return int|null
     */
    public function getUpdatedAt(): ?int
    {
        return $this->getAttribute('updated_at');
    }

    /**
     * 获取分段令牌数
     *
     * @return int|null
     */
    public function getTokens(): ?int
    {
        return $this->getAttribute('tokens');
    }

    /**
     * 获取分段索引状态
     *
     * @return string|null
     */
    public function getIndexingStatus(): ?string
    {
        return $this->getAttribute('indexing_status');
    }

    /**
     * 获取分段命中次数
     *
     * @return int|null
     */
    public function getHitCount(): ?int
    {
        return $this->getAttribute('hit_count');
    }

    /**
     * 获取分段是否启用
     *
     * @return bool|null
     */
    public function getEnabled(): ?bool
    {
        return $this->getAttribute('enabled');
    }

    /**
     * 获取分段禁用时间
     *
     * @return int|null
     */
    public function getDisabledAt(): ?int
    {
        return $this->getAttribute('disabled_at');
    }

    /**
     * 获取分段禁用者
     *
     * @return string|null
     */
    public function getDisabledBy(): ?string
    {
        return $this->getAttribute('disabled_by');
    }
} 