<?php

declare(strict_types=1);

namespace Happyphper\Dify\Responses;

use Happyphper\Dify\Support\Collection;
use Happyphper\Dify\Support\Paginator;

class SegmentCollection extends Collection
{
    /**
     * 构造函数
     *
     * @param array $items
     * @param array|Paginator|null $paginator
     */
    public function __construct(array $items = [], array|Paginator|null $paginator = null)
    {
        $items = array_map(function ($item) {
            if ($item instanceof Segment) {
                return $item;
            }

            return new Segment($item);
        }, $items);

        parent::__construct($items, $paginator);
    }

    /**
     * 添加分段
     *
     * @param array|Segment $segment
     * @return void
     */
    public function add(array|Segment $segment): void
    {
        if (!$segment instanceof Segment) {
            $segment = new Segment($segment);
        }

        parent::add($segment);
    }

    /**
     * 设置分段
     *
     * @param int $index
     * @param array|Segment $segment
     * @return void
     */
    public function set(int $index, array|Segment $segment): void
    {
        if (!$segment instanceof Segment) {
            $segment = new Segment($segment);
        }

        parent::set($index, $segment);
    }

    /**
     * 获取第一个分段
     *
     * @return Segment|null
     */
    public function first(): ?Segment
    {
        return parent::first();
    }

    /**
     * 获取最后一个分段
     *
     * @return Segment|null
     */
    public function last(): ?Segment
    {
        return parent::last();
    }

    /**
     * 获取指定索引的分段
     *
     * @param int $index
     * @return Segment|null
     */
    public function get(int $index): ?Segment
    {
        return parent::get($index);
    }
} 