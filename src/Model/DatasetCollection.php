<?php

declare(strict_types=1);

namespace Happyphper\Dify\Model;

class DatasetCollection extends Collection
{
    /**
     * 构造函数
     *
     * @param array $items
     */
    public function __construct(array $items = [])
    {
        parent::__construct(array_map(function ($item) {
            return $item instanceof Dataset ? $item : new Dataset($item);
        }, $items));
    }

    /**
     * 添加数据集
     *
     * @param Dataset|array $dataset
     * @return $this
     */
    public function add($dataset): self
    {
        if (!$dataset instanceof Dataset) {
            $dataset = new Dataset($dataset);
        }

        return parent::add($dataset);
    }

    /**
     * 设置数据集
     *
     * @param int $index
     * @param Dataset|array $dataset
     * @return $this
     */
    public function set(int $index, $dataset): self
    {
        if (!$dataset instanceof Dataset) {
            $dataset = new Dataset($dataset);
        }

        return parent::set($index, $dataset);
    }

    /**
     * 获取第一个数据集
     *
     * @return Dataset|null
     */
    public function first(): ?Dataset
    {
        return parent::first();
    }

    /**
     * 获取最后一个数据集
     *
     * @return Dataset|null
     */
    public function last(): ?Dataset
    {
        return parent::last();
    }

    /**
     * 获取指定索引的数据集
     *
     * @param int $index
     * @return Dataset|null
     */
    public function get(int $index): ?Dataset
    {
        return parent::get($index);
    }
} 