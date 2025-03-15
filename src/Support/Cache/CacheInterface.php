<?php

namespace Happyphper\Dify\Support\Cache;

/**
 * 缓存接口
 */
interface CacheInterface
{
    /**
     * 获取缓存
     *
     * @param string $key 缓存键
     * @param mixed $default 默认值
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * 设置缓存
     *
     * @param string $key 缓存键
     * @param mixed $value 缓存值
     * @param int|null $ttl 过期时间（秒）
     * @return bool
     */
    public function set(string $key, $value, ?int $ttl = null): bool;

    /**
     * 删除缓存
     *
     * @param string $key 缓存键
     * @return bool
     */
    public function delete(string $key): bool;

    /**
     * 清空所有缓存
     *
     * @return bool
     */
    public function clear(): bool;

    /**
     * 判断缓存是否存在
     *
     * @param string $key 缓存键
     * @return bool
     */
    public function has(string $key): bool;
} 