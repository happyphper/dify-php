<?php

declare(strict_types=1);

namespace Happyphper\Dify\Responses;

/**
 * 数据集响应模型
 */
class Upload
{
    public string $id;
    public string $name;
    public int $size;
    public string $extension;
    public string $url;
    public string $downloadUrl;
    public string $mimeType;
    public string $createdBy;
    public int $createdAt;

    /**
     * 构造函数
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->id = $attributes['id'] ?? '';
        $this->name = $attributes['name'] ?? '';
        $this->size = $attributes['size'] ?? 0;
        $this->extension = $attributes['extension'] ?? '';
        $this->url = $attributes['url'] ?? '';
        $this->downloadUrl = $attributes['download_url']??'';
        $this->mimeType = $attributes['mime_type'] ?? '';
        $this->createdBy = $attributes['created_by'] ?? '';
        $this->createdAt = $attributes['created_at'] ?? 0;
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
            'size' => $this->size,
            'extension' => $this->extension,
            'url' => $this->url,
            'download_url' => $this->downloadUrl,
            'mime_type' => $this->mimeType,
            'created_by' => $this->createdBy,
            'created_at' => $this->createdAt,
        ];
    }
}
