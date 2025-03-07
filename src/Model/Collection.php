<?php

declare(strict_types=1);

namespace Happyphper\Dify\Model;

class Collection implements \ArrayAccess, \Countable, \IteratorAggregate, \JsonSerializable
{
    /**
     * 集合项
     *
     * @var array
     */
    protected array $items = [];

    /**
     * 构造函数
     *
     * @param array $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * 获取所有项
     *
     * @return array
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * 获取第一项
     *
     * @return mixed|null
     */
    public function first()
    {
        return $this->items[0] ?? null;
    }

    /**
     * 获取最后一项
     *
     * @return mixed|null
     */
    public function last()
    {
        return $this->items[count($this->items) - 1] ?? null;
    }

    /**
     * 获取指定索引的项
     *
     * @param int $index
     * @return mixed|null
     */
    public function get(int $index)
    {
        return $this->items[$index] ?? null;
    }

    /**
     * 添加项
     *
     * @param mixed $item
     * @return $this
     */
    public function add($item): self
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * 设置项
     *
     * @param int $index
     * @param mixed $item
     * @return $this
     */
    public function set(int $index, $item): self
    {
        $this->items[$index] = $item;

        return $this;
    }

    /**
     * 移除项
     *
     * @param int $index
     * @return $this
     */
    public function remove(int $index): self
    {
        unset($this->items[$index]);

        return $this;
    }

    /**
     * 清空集合
     *
     * @return $this
     */
    public function clear(): self
    {
        $this->items = [];

        return $this;
    }

    /**
     * 检查集合是否为空
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * 检查集合是否不为空
     *
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    /**
     * 检查集合是否包含指定项
     *
     * @param mixed $item
     * @return bool
     */
    public function contains($item): bool
    {
        return in_array($item, $this->items, true);
    }

    /**
     * 过滤集合
     *
     * @param callable $callback
     * @return static
     */
    public function filter(callable $callback): self
    {
        return new static(array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * 映射集合
     *
     * @param callable $callback
     * @return static
     */
    public function map(callable $callback): self
    {
        return new static(array_map($callback, $this->items));
    }

    /**
     * 遍历集合
     *
     * @param callable $callback
     * @return $this
     */
    public function each(callable $callback): self
    {
        foreach ($this->items as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }

        return $this;
    }

    /**
     * 转换为数组
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_map(function ($item) {
            return $item instanceof \Happyphper\Dify\Model\Model ? $item->toArray() : $item;
        }, $this->items);
    }

    /**
     * 转换为 JSON
     *
     * @param int $options
     * @return string
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
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
     * 获取集合项数量
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * 检查偏移量是否存在
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * 获取偏移量的值
     *
     * @param mixed $offset
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    /**
     * 设置偏移量的值
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
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
     */
    public function offsetUnset($offset): void
    {
        unset($this->items[$offset]);
    }

    /**
     * 序列化为 JSON
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return array_map(function ($item) {
            if ($item instanceof \JsonSerializable) {
                return $item->jsonSerialize();
            }

            return $item;
        }, $this->items);
    }

    /**
     * 魔术方法：转换为字符串
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJson();
    }
} 