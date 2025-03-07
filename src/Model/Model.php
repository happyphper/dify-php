<?php

declare(strict_types=1);

namespace Happyphper\Dify\Model;

abstract class Model implements \ArrayAccess, \JsonSerializable
{
    /**
     * 模型属性
     *
     * @var array
     */
    protected array $attributes = [];

    /**
     * 构造函数
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    /**
     * 填充属性
     *
     * @param array $attributes
     * @return $this
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * 设置属性
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setAttribute(string $key, $value): self
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * 获取属性
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getAttribute(string $key, $default = null)
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * 获取所有属性
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * 检查属性是否存在
     *
     * @param string $key
     * @return bool
     */
    public function hasAttribute(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * 魔术方法：获取属性
     *
     * @param string $key
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->getAttribute($key);
    }

    /**
     * 魔术方法：设置属性
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set(string $key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * 魔术方法：检查属性是否存在
     *
     * @param string $key
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return $this->hasAttribute($key);
    }

    /**
     * 魔术方法：删除属性
     *
     * @param string $key
     */
    public function __unset(string $key)
    {
        unset($this->attributes[$key]);
    }

    /**
     * ArrayAccess 接口：检查偏移量是否存在
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return $this->hasAttribute($offset);
    }

    /**
     * ArrayAccess 接口：获取偏移量的值
     *
     * @param mixed $offset
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
    }

    /**
     * ArrayAccess 接口：设置偏移量的值
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * ArrayAccess 接口：删除偏移量
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->attributes[$offset]);
    }

    /**
     * JsonSerializable 接口：序列化为 JSON
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->getAttributes();
    }

    /**
     * 转换为数组
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->getAttributes();
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
     * 魔术方法：转换为字符串
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJson();
    }
} 