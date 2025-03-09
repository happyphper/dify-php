<?php

declare(strict_types=1);

namespace Happyphper\Dify\Responses;

/**
 * 分段
 */
class SegmentResponse
{
    /**
     * 分段数据
     *
     * @var array
     */
    public array $data;

    /**
     * 文档形式
     *
     * @var string
     */
    public string $docForm;

    /**
     * 构造函数
     *
     * @param array $attrs
     */
    public function __construct(array $attrs)
    {
        $this->docForm = $attrs['doc_form'];

        if (isset($attrs['data']['id'])) {
            $this->data = [new Segment($attrs['data'])];
        } else {
            $this->data = array_map(fn($item) => new Segment($item), $attrs['data']);
        }
    }
}
