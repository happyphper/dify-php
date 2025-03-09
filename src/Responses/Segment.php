<?php

declare(strict_types=1);

namespace Happyphper\Dify\Responses;

class Segment
{
    /**
     * 属性
     *
     * @var array
     */
    private array $attributes;

    /**
     * 构造函数
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    /**
     * 获取属性
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function getAttribute(string $key, $default = null)
    {
        return $this->attributes[$key] ?? $default;
    }

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
     * 获取分段内容
     *
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->getAttribute('content');
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
     * 获取分段关键词
     *
     * @return array|null
     */
    public function getKeywords(): ?array
    {
        return $this->getAttribute('keywords');
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
     * 获取分段更新时间
     *
     * @return int|null
     */
    public function getUpdatedAt(): ?int
    {
        return $this->getAttribute('updated_at');
    }

    /**
     * 转换为数组
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }
} 