<?php

declare(strict_types=1);

namespace Happyphper\Dify\Model;

class DocumentCollection extends Collection
{
    /**
     * 构造函数
     *
     * @param array $items
     */
    public function __construct(array $items = [])
    {
        parent::__construct(array_map(function ($item) {
            return $item instanceof Document ? $item : new Document($item);
        }, $items));
    }

    /**
     * 添加文档
     *
     * @param Document|array $document
     * @return $this
     */
    public function add($document): self
    {
        if (!$document instanceof Document) {
            $document = new Document($document);
        }

        return parent::add($document);
    }

    /**
     * 设置文档
     *
     * @param int $index
     * @param Document|array $document
     * @return $this
     */
    public function set(int $index, $document): self
    {
        if (!$document instanceof Document) {
            $document = new Document($document);
        }

        return parent::set($index, $document);
    }

    /**
     * 获取第一个文档
     *
     * @return Document|null
     */
    public function first(): ?Document
    {
        return parent::first();
    }

    /**
     * 获取最后一个文档
     *
     * @return Document|null
     */
    public function last(): ?Document
    {
        return parent::last();
    }

    /**
     * 获取指定索引的文档
     *
     * @param int $index
     * @return Document|null
     */
    public function get(int $index): ?Document
    {
        return parent::get($index);
    }
} 