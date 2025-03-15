<?php

namespace Happyphper\Dify\Public\Requests;

/**
 * 更新分段请求类
 */
class SegmentUpdateRequest
{
    /**
     * 文本内容/问题内容
     *
     * @var string|null
     */
    public ?string $content = null;

    /**
     * 答案内容
     *
     * @var string|null
     */
    public ?string $answer = null;

    /**
     * 关键字
     *
     * @var array|null
     */
    public ?array $keywords = null;

    /**
     * 是否启用
     *
     * @var bool|null
     */
    public ?bool $enabled = null;

    /**
     * 是否重新生成子分段
     *
     * @var bool|null
     */
    public ?bool $regenerateChildChunks = null;

    /**
     * 转换为数组
     *
     * @return array
     */
    public function toArray(): array
    {
        $data = array_filter([
            'content' => $this->content,
            'answer' => $this->answer,
            'keywords' => $this->keywords,
            'enabled' => $this->enabled,
            'regenerate_child_chunks' => $this->regenerateChildChunks,
        ], function ($value) {
            return !is_null($value);
        });

        return $data;
    }
}
