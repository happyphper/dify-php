<?php

namespace Happyphper\Dify\Support\Cache;

use Exception;
use Redis;

/**
 * Redis缓存驱动
 */
class RedisCache extends AbstractCache
{
    /**
     * Redis实例
     *
     * @var Redis
     */
    protected Redis $redis;

    /**
     * 构造函数
     *
     * @param Redis|null $redis Redis实例
     * @param int|null $defaultTtl 默认缓存过期时间（秒）
     * @param string|null $prefix 缓存前缀
     * @throws Exception
     */
    public function __construct(?Redis $redis = null, ?int $defaultTtl = null, ?string $prefix = null)
    {
        parent::__construct($defaultTtl, $prefix);

        if ($redis !== null) {
            $this->redis = $redis;
        } else {
            if (!extension_loaded('redis')) {
                throw new Exception('Redis扩展未安装');
            }

            $this->redis = new Redis();
            $this->redis->connect('127.0.0.1', 6379);
        }
    }

    /**
     * 实际获取缓存的方法
     *
     * @param string $key 缓存键
     * @return mixed
     */
    protected function doGet(string $key)
    {
        $value = $this->redis->get($key);

        if ($value === false) {
            return null;
        }

        return unserialize($value);
    }

    /**
     * 实际设置缓存的方法
     *
     * @param string $key 缓存键
     * @param mixed $value 缓存值
     * @param int|null $ttl 过期时间（秒）
     * @return bool
     */
    protected function doSet(string $key, $value, ?int $ttl): bool
    {
        $serializedValue = serialize($value);

        if ($ttl === null) {
            return $this->redis->set($key, $serializedValue);
        }

        return $this->redis->setex($key, $ttl, $serializedValue);
    }

    /**
     * 实际删除缓存的方法
     *
     * @param string $key 缓存键
     * @return bool
     */
    protected function doDelete(string $key): bool
    {
        return $this->redis->del($key) > 0;
    }

    /**
     * 实际判断缓存是否存在的方法
     *
     * @param string $key 缓存键
     * @return bool
     */
    protected function doHas(string $key): bool
    {
        return $this->redis->exists($key);
    }

    /**
     * 清空所有缓存
     *
     * @return bool
     */
    public function clear(): bool
    {
        $keys = $this->redis->keys($this->prefix . '*');
        
        if (empty($keys)) {
            return true;
        }

        return $this->redis->del($keys) > 0;
    }
} 