<?php

declare(strict_types=1);

namespace Happyphper\Dify\Model;

class Document extends Model
{
    /**
     * 获取文档ID
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->getAttribute('id');
    }

    /**
     * 获取文档位置
     *
     * @return int|null
     */
    public function getPosition(): ?int
    {
        return $this->getAttribute('position');
    }

    /**
     * 获取文档数据源类型
     *
     * @return string|null
     */
    public function getDataSourceType(): ?string
    {
        return $this->getAttribute('data_source_type');
    }

    /**
     * 获取文档数据源信息
     *
     * @return array|null
     */
    public function getDataSourceInfo(): ?array
    {
        return $this->getAttribute('data_source_info');
    }

    /**
     * 获取文档数据源详情字典
     *
     * @return array|null
     */
    public function getDataSourceDetailDict(): ?array
    {
        return $this->getAttribute('data_source_detail_dict');
    }

    /**
     * 获取文档数据集处理规则ID
     *
     * @return string|null
     */
    public function getDatasetProcessRuleId(): ?string
    {
        return $this->getAttribute('dataset_process_rule_id');
    }

    /**
     * 获取文档名称
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->getAttribute('name');
    }

    /**
     * 获取文档创建来源
     *
     * @return string|null
     */
    public function getCreatedFrom(): ?string
    {
        return $this->getAttribute('created_from');
    }

    /**
     * 获取文档创建者
     *
     * @return string|null
     */
    public function getCreatedBy(): ?string
    {
        return $this->getAttribute('created_by');
    }

    /**
     * 获取文档创建时间
     *
     * @return int|null
     */
    public function getCreatedAt(): ?int
    {
        return $this->getAttribute('created_at');
    }

    /**
     * 获取文档令牌数
     *
     * @return int|null
     */
    public function getTokens(): ?int
    {
        return $this->getAttribute('tokens');
    }

    /**
     * 获取文档索引状态
     *
     * @return string|null
     */
    public function getIndexingStatus(): ?string
    {
        return $this->getAttribute('indexing_status');
    }

    /**
     * 获取文档错误信息
     *
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->getAttribute('error');
    }

    /**
     * 获取文档是否启用
     *
     * @return bool|null
     */
    public function getEnabled(): ?bool
    {
        return $this->getAttribute('enabled');
    }

    /**
     * 获取文档禁用时间
     *
     * @return int|null
     */
    public function getDisabledAt(): ?int
    {
        return $this->getAttribute('disabled_at');
    }

    /**
     * 获取文档禁用者
     *
     * @return string|null
     */
    public function getDisabledBy(): ?string
    {
        return $this->getAttribute('disabled_by');
    }

    /**
     * 获取文档是否归档
     *
     * @return bool|null
     */
    public function getArchived(): ?bool
    {
        return $this->getAttribute('archived');
    }

    /**
     * 获取文档显示状态
     *
     * @return string|null
     */
    public function getDisplayStatus(): ?string
    {
        return $this->getAttribute('display_status');
    }

    /**
     * 获取文档字数
     *
     * @return int|null
     */
    public function getWordCount(): ?int
    {
        return $this->getAttribute('word_count');
    }

    /**
     * 获取文档命中次数
     *
     * @return int|null
     */
    public function getHitCount(): ?int
    {
        return $this->getAttribute('hit_count');
    }

    /**
     * 获取文档形式
     *
     * @return string|null
     */
    public function getDocForm(): ?string
    {
        return $this->getAttribute('doc_form');
    }

    /**
     * 获取上传文件信息
     *
     * @return array|null
     */
    public function getUploadFile(): ?array
    {
        $dataSourceDetailDict = $this->getDataSourceDetailDict();
        return $dataSourceDetailDict['upload_file'] ?? null;
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
     * 获取上传文件大小
     *
     * @return int|null
     */
    public function getUploadFileSize(): ?int
    {
        $uploadFile = $this->getUploadFile();
        return $uploadFile['size'] ?? null;
    }

    /**
     * 获取上传文件扩展名
     *
     * @return string|null
     */
    public function getUploadFileExtension(): ?string
    {
        $uploadFile = $this->getUploadFile();
        return $uploadFile['extension'] ?? null;
    }

    /**
     * 获取上传文件MIME类型
     *
     * @return string|null
     */
    public function getUploadFileMimeType(): ?string
    {
        $uploadFile = $this->getUploadFile();
        return $uploadFile['mime_type'] ?? null;
    }
} 