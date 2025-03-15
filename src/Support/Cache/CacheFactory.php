<?php

namespace Happyphper\Dify\Support\Cache;

use Exception;
use Redis;

/**
 * 缓存工厂
 */
class CacheFactory
{
    /**
     * 创建文件缓存
     *
     * @param string|null $directory 缓存目录
     * @param int|null $defaultTtl 默认缓存过期时间（秒）
     * @param string|null $prefix 缓存前缀
     * @return FileCache
     */
    public static function createFileCache(?string $directory = null, ?int $defaultTtl = null, ?string $prefix = null): FileCache
    {
        return new FileCache($directory, $defaultTtl, $prefix);
    }

    /**
     * 创建Redis缓存
     *
     * @param array $config Redis配置
     * @param int|null $defaultTtl 默认缓存过期时间（秒）
     * @param string|null $prefix 缓存前缀
     * @return RedisCache
     * @throws Exception
     */
    public static function createRedisCache(array $config = [], ?int $defaultTtl = null, ?string $prefix = null): RedisCache
    {
        if (!extension_loaded('redis')) {
            throw new Exception('Redis扩展未安装');
        }

        $redis = new Redis();
        
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 6379;
        $timeout = $config['timeout'] ?? 0.0;
        $password = $config['password'] ?? null;
        $database = $config['database'] ?? 0;
        
        // 调试信息
        $debug = $config['debug'] ?? false;
        if ($debug) {
            echo "\n[DEBUG] 连接到 Redis: $host:$port\n";
        }
        
        try {
            $connected = $redis->connect($host, $port, $timeout);
            
            if (!$connected) {
                throw new Exception("无法连接到 Redis 服务器: $host:$port");
            }
            
            // 处理密码认证
            if ($password !== null && $password !== '') {
                if ($debug) {
                    echo "\n[DEBUG] 使用密码进行 Redis 认证\n";
                }
                
                $authResult = $redis->auth($password);
                
                if (!$authResult) {
                    throw new Exception('Redis 认证失败: ' . $redis->getLastError());
                }
            } else {
                if ($debug) {
                    echo "\n[DEBUG] 不使用密码进行 Redis 认证\n";
                }
                
                // 尝试执行一个简单的命令，检查是否需要密码
                try {
                    $redis->ping();
                } catch (\Exception $e) {
                    if (strpos($e->getMessage(), 'NOAUTH') !== false) {
                        throw new Exception('Redis 服务器需要密码，但未提供密码');
                    }
                    throw $e;
                }
            }
            
            // 选择数据库
            if ($database !== 0) {
                if ($debug) {
                    echo "\n[DEBUG] 选择 Redis 数据库: $database\n";
                }
                
                $selectResult = $redis->select($database);
                
                if (!$selectResult) {
                    throw new Exception('Redis 选择数据库失败: ' . $redis->getLastError());
                }
            }
            
            return new RedisCache($redis, $defaultTtl, $prefix);
        } catch (\Exception $e) {
            throw new Exception('Redis 连接失败: ' . $e->getMessage());
        }
    }

    /**
     * 根据配置创建缓存
     *
     * @param array $config 缓存配置
     * @return CacheInterface
     * @throws Exception
     */
    public static function create(array $config = []): CacheInterface
    {
        $driver = $config['driver'] ?? 'file';
        $ttl = $config['ttl'] ?? null;
        $prefix = $config['prefix'] ?? null;
        
        switch ($driver) {
            case 'redis':
                $redisConfig = $config['redis'] ?? [];
                return self::createRedisCache($redisConfig, $ttl, $prefix);
            case 'file':
            default:
                $fileConfig = $config['file'] ?? [];
                $directory = $fileConfig['directory'] ?? null;
                return self::createFileCache($directory, $ttl, $prefix);
        }
    }
} 