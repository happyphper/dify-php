<?php

declare(strict_types=1);

namespace Happyphper\Dify\Support;

class Paginator
{
    /**
     * 当前页码
     *
     * @var int
     */
    public int $page;

    /**
     * 每页数量
     *
     * @var int
     */
    public int $limit;

    /**
     * 总数量
     *
     * @var int
     */
    public int $total;

    /**
     * 是否有更多
     *
     * @var bool
     */
    public bool $hasMore;

    /**
     * 构造函数
     *
     * @param array $attrs
     */
    public function __construct(array $attrs)
    {
        $this->page = (int)$attrs['page'];
        $this->limit = (int)$attrs['limit'];
        $this->total = (int)$attrs['total'];
        $this->hasMore = (bool)$attrs['has_more'];
    }

    /**
     * 转换为数组
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'page' => $this->page,
            'limit' => $this->limit,
            'total' => $this->total,
            'has_more' => $this->hasMore,
        ];
    }
}
