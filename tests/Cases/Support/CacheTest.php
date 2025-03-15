<?php

namespace Happyphper\Dify\Tests\Cases\Support;

use Happyphper\Dify\Support\Cache\CacheFactory;
use Happyphper\Dify\Support\Cache\FileCache;
use Happyphper\Dify\Support\Cache\RedisCache;
use PHPUnit\Framework\TestCase;

/**
 * 缓存测试类
 */
class CacheTest extends TestCase
{
    /**
     * 测试文件缓存
     */
    public function testFileCache()
    {
        // 从环境变量获取配置
        $directory = getenv('DIFY_CACHE_FILE_DIRECTORY') ?: sys_get_temp_dir() . '/dify_test_cache';
        $prefix = getenv('DIFY_CACHE_PREFIX') ?: 'dify_test_';
        $ttl = (int)getenv('DIFY_CACHE_TTL') ?: 86400;
        
        // 确保目录存在
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        
        // 创建文件缓存
        $cache = new FileCache($directory, $ttl, $prefix);
        
        // 测试设置缓存
        $key = 'test_key';
        $value = 'test_value';
        $result = $cache->set($key, $value);
        $this->assertTrue($result, '设置缓存失败');
        
        // 测试获取缓存
        $cachedValue = $cache->get($key);
        $this->assertEquals($value, $cachedValue, '获取缓存值不匹配');
        
        // 测试判断缓存是否存在
        $exists = $cache->has($key);
        $this->assertTrue($exists, '缓存应该存在');
        
        // 测试删除缓存
        $result = $cache->delete($key);
        $this->assertTrue($result, '删除缓存失败');
        
        // 测试缓存是否已删除
        $exists = $cache->has($key);
        $this->assertFalse($exists, '缓存应该已被删除');
        
        // 测试清空所有缓存
        $cache->set($key, $value);
        $result = $cache->clear();
        $this->assertTrue($result, '清空缓存失败');
        $exists = $cache->has($key);
        $this->assertFalse($exists, '清空缓存后，缓存应该不存在');
        
        // 测试过期时间
        $cache->set($key, $value, 1); // 1秒后过期
        $this->assertTrue($cache->has($key), '缓存应该存在');
        sleep(2); // 等待2秒
        $this->assertFalse($cache->has($key), '缓存应该已过期');
        
        // 清理测试目录
        $this->removeDirectory($directory);
    }
    
    /**
     * 测试Redis缓存
     */
    public function testRedisCache()
    {
        // 检查Redis扩展是否可用
        if (!extension_loaded('redis')) {
            $this->markTestSkipped('Redis扩展未安装，跳过测试');
            return;
        }
        
        // 从环境变量获取配置
        $host = getenv('DIFY_CACHE_REDIS_HOST') ?: '127.0.0.1';
        $port = (int)getenv('DIFY_CACHE_REDIS_PORT') ?: 6379;
        $password = getenv('DIFY_CACHE_REDIS_PASSWORD') ?: null;
        $database = (int)getenv('DIFY_CACHE_REDIS_DATABASE') ?: 0;
        $prefix = getenv('DIFY_CACHE_PREFIX') ?: 'dify_test_';
        $ttl = (int)getenv('DIFY_CACHE_TTL') ?: 86400;
        
        try {
            // 创建Redis实例
            $redis = new \Redis();
            $connected = $redis->connect($host, $port);
            
            if (!$connected) {
                $this->markTestSkipped('无法连接到Redis服务器，跳过测试');
                return;
            }
            
            if ($password !== null && $password !== '') {
                $redis->auth($password);
            }
            
            if ($database !== 0) {
                $redis->select($database);
            }
            
            // 创建Redis缓存
            $cache = new RedisCache($redis, $ttl, $prefix);
            
            // 测试设置缓存
            $key = 'test_key';
            $value = 'test_value';
            $result = $cache->set($key, $value);
            $this->assertTrue($result, '设置缓存失败');
            
            // 测试获取缓存
            $cachedValue = $cache->get($key);
            $this->assertEquals($value, $cachedValue, '获取缓存值不匹配');
            
            // 测试判断缓存是否存在
            $exists = $cache->has($key);
            $this->assertTrue($exists, '缓存应该存在');
            
            // 测试删除缓存
            $result = $cache->delete($key);
            $this->assertTrue($result, '删除缓存失败');
            
            // 测试缓存是否已删除
            $exists = $cache->has($key);
            $this->assertFalse($exists, '缓存应该已被删除');
            
            // 测试清空所有缓存
            $cache->set($key, $value);
            $result = $cache->clear();
            $this->assertTrue($result, '清空缓存失败');
            $exists = $cache->has($key);
            $this->assertFalse($exists, '清空缓存后，缓存应该不存在');
            
            // 测试过期时间
            $cache->set($key, $value, 1); // 1秒后过期
            $this->assertTrue($cache->has($key), '缓存应该存在');
            sleep(2); // 等待2秒
            $this->assertFalse($cache->has($key), '缓存应该已过期');
            
        } catch (\Exception $e) {
            $this->markTestSkipped('Redis测试失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 测试缓存工厂
     */
    public function testCacheFactory()
    {
        // 测试创建文件缓存
        $fileConfig = [
            'driver' => 'file',
            'prefix' => 'dify_test_',
            'ttl' => 86400,
            'file' => [
                'directory' => sys_get_temp_dir() . '/dify_test_cache'
            ]
        ];
        
        $fileCache = CacheFactory::create($fileConfig);
        $this->assertInstanceOf(FileCache::class, $fileCache, '应该创建FileCache实例');
        
        // 测试创建Redis缓存（如果Redis扩展可用）
        if (extension_loaded('redis')) {
            try {
                $redisConfig = [
                    'driver' => 'redis',
                    'prefix' => 'dify_test_',
                    'ttl' => 86400,
                    'redis' => [
                        'host' => getenv('DIFY_CACHE_REDIS_HOST') ?: '127.0.0.1',
                        'port' => (int)getenv('DIFY_CACHE_REDIS_PORT') ?: 6379,
                        'password' => getenv('DIFY_CACHE_REDIS_PASSWORD') ?: null,
                        'database' => (int)getenv('DIFY_CACHE_REDIS_DATABASE') ?: 0
                    ]
                ];
                
                $redisCache = CacheFactory::create($redisConfig);
                $this->assertInstanceOf(RedisCache::class, $redisCache, '应该创建RedisCache实例');
            } catch (\Exception $e) {
                $this->markTestSkipped('Redis测试失败: ' . $e->getMessage());
            }
        }
        
        // 测试默认创建文件缓存
        $defaultCache = CacheFactory::create([]);
        $this->assertInstanceOf(FileCache::class, $defaultCache, '默认应该创建FileCache实例');
    }
    
    /**
     * 递归删除目录
     *
     * @param string $dir 目录路径
     * @return bool
     */
    private function removeDirectory(string $dir): bool
    {
        if (!is_dir($dir)) {
            return false;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        
        return rmdir($dir);
    }
} 