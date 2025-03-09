<?php

declare(strict_types=1);

namespace Happyphper\Dify\Support;

abstract class Collection implements \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     * 集合项目
     *
     * @var array
     */
    protected array $items = [];

    /**
     * 分页器
     *
     * @var Paginator|null
     */
    protected ?Paginator $paginator;

    /**
     * 构造函数
     *
     * @param array $items
     * @param array|Paginator|null $paginator
     */
    public function __construct(array $items = [], array|Paginator|null $paginator = null)
    {
        $this->items = $items;

        if (is_array($paginator)) {
            $this->paginator = new Paginator(
                $paginator['page'] ?? 1,
                $paginator['limit'] ?? 20,
                $paginator['total'] ?? 0,
                $paginator['has_more'] ?? false
            );
        } else {
            $this->paginator = $paginator;
        }
    }

    /**
     * 获取分页器
     *
     * @return Paginator|null
     */
    public function getPaginator(): ?Paginator
    {
        return $this->paginator;
    }

    /**
     * 添加项目
     *
     * @param array|object $item
     * @return void
     */
    public function add(array|object $item): void
    {
        $this->items[] = $item;
    }

    /**
     * 设置项目
     *
     * @param int $index
     * @param array|object $item
     * @return void
     */
    public function set(int $index, array|object $item): void
    {
        $this->items[$index] = $item;
    }

    /**
     * 获取第一个项目
     *
     * @return mixed|null
     */
    public function first(): mixed
    {
        return $this->items[0] ?? null;
    }

    /**
     * 获取最后一个项目
     *
     * @return mixed|null
     */
    public function last(): mixed
    {
        return $this->items[count($this->items) - 1] ?? null;
    }

    /**
     * 获取指定索引的项目
     *
     * @param int $index
     * @return mixed|null
     */
    public function get(int $index): mixed
    {
        return $this->items[$index] ?? null;
    }

    /**
     * 获取所有项目
     *
     * @return array
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * 获取项目数量
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * 获取迭代器
     *
     * @return \ArrayIterator
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * 检查偏移量是否存在
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * 获取偏移量的值
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset];
    }

    /**
     * 设置偏移量的值
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * 删除偏移量
     *
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }
} 