<?php

declare(strict_types=1);

namespace Happyphper\Dify\Model;

class SegmentCollection extends Collection
{
    /**
     * 构造函数
     *
     * @param array $items
     */
    public function __construct(array $items = [])
    {
        parent::__construct(array_map(function ($item) {
            return $item instanceof Segment ? $item : new Segment($item);
        }, $items));
    }

    /**
     * 添加分段
     *
     * @param Segment|array $segment
     * @return $this
     */
    public function add($segment): self
    {
        if (!$segment instanceof Segment) {
            $segment = new Segment($segment);
        }

        return parent::add($segment);
    }

    /**
     * 设置分段
     *
     * @param int $index
     * @param Segment|array $segment
     * @return $this
     */
    public function set(int $index, $segment): self
    {
        if (!$segment instanceof Segment) {
            $segment = new Segment($segment);
        }

        return parent::set($index, $segment);
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