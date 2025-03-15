<?php

namespace Happyphper\Dify\Support\Cache;

/**
 * 抽象缓存驱动
 */
abstract class AbstractCache implements CacheInterface
{
    /**
     * 默认缓存过期时间（秒）
     *
     * @var int|null
     */
    protected ?int $defaultTtl = null;

    /**
     * 缓存前缀
     *
     * @var string
     */
    protected string $prefix = 'dify_';

    /**
     * 构造函数
     *
     * @param int|null $defaultTtl 默认缓存过期时间（秒）
     * @param string|null $prefix 缓存前缀
     */
    public function __construct(?int $defaultTtl = null, ?string $prefix = null)
    {
        if ($defaultTtl !== null) {
            $this->defaultTtl = $defaultTtl;
        }

        if ($prefix !== null) {
            $this->prefix = $prefix;
        }
    }

    /**
     * 获取带前缀的缓存键
     *
     * @param string $key 缓存键
     * @return string
     */
    protected function getPrefixedKey(string $key): string
    {
        return $this->prefix . $key;
    }

    /**
     * 获取缓存
     *
     * @param string $key 缓存键
     * @param mixed $default 默认值
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $value = $this->doGet($this->getPrefixedKey($key));

        return $value !== null ? $value : $default;
    }

    /**
     * 设置缓存
     *
     * @param string $key 缓存键
     * @param mixed $value 缓存值
     * @param int|null $ttl 过期时间（秒）
     * @return bool
     */
    public function set(string $key, $value, ?int $ttl = null): bool
    {
        return $this->doSet($this->getPrefixedKey($key), $value, $ttl ?? $this->defaultTtl);
    }

    /**
     * 删除缓存
     *
     * @param string $key 缓存键
     * @return bool
     */
    public function delete(string $key): bool
    {
        return $this->doDelete($this->getPrefixedKey($key));
    }

    /**
     * 判断缓存是否存在
     *
     * @param string $key 缓存键
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->doHas($this->getPrefixedKey($key));
    }

    /**
     * 实际获取缓存的方法
     *
     * @param string $key 缓存键
     * @return mixed
     */
    abstract protected function doGet(string $key);

    /**
     * 实际设置缓存的方法
     *
     * @param string $key 缓存键
     * @param mixed $value 缓存值
     * @param int|null $ttl 过期时间（秒）
     * @return bool
     */
    abstract protected function doSet(string $key, $value, ?int $ttl): bool;

    /**
     * 实际删除缓存的方法
     *
     * @param string $key 缓存键
     * @return bool
     */
    abstract protected function doDelete(string $key): bool;

    /**
     * 实际判断缓存是否存在的方法
     *
     * @param string $key 缓存键
     * @return bool
     */
    abstract protected function doHas(string $key): bool;
} 