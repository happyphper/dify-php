<?php

namespace Happyphper\Dify\Requests;

/**
 * 创建分段请求类
 */
class SegmentCreateRequest
{
    /**
     * 文本内容/问题内容
     *
     * @var string
     */
    public string $content;

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
     * 构造函数
     *
     * @param string $content
     */
    public function __construct(string $content)
    {
        $this->content = $content;
    }

    /**
     * 设置答案
     *
     * @param string $answer
     * @return $this
     */
    public function setAnswer(string $answer): static
    {
        $this->answer = $answer;
        return $this;
    }

    /**
     * 设置关键字
     *
     * @param array $keywords
     * @return $this
     */
    public function setKeywords(array $keywords): static
    {
        $this->keywords = $keywords;
        return $this;
    }

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
        ], function ($value) {
            return !is_null($value);
        });

        return $data;
    }
} 