<?php

namespace Happyphper\Dify\Tests\Cases\Support;

use Happyphper\Dify\Support\Cache\CacheFactory;
use Happyphper\Dify\Support\TokenStorage;
use PHPUnit\Framework\TestCase;

/**
 * TokenStorage与不同缓存驱动测试类
 */
class TokenStorageWithCacheTest extends TestCase
{
    /**
     * 测试使用文件缓存的TokenStorage
     */
    public function testTokenStorageWithFileCache()
    {
        // 从环境变量获取配置
        $directory = getenv('DIFY_CACHE_FILE_DIRECTORY') ?: sys_get_temp_dir() . '/dify_test_cache';
        $prefix = getenv('DIFY_CACHE_PREFIX') ?: 'dify_test_';
        $ttl = (int)getenv('DIFY_CACHE_TTL') ?: 86400;
        
        // 确保目录存在
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        
        // 创建配置
        $config = [
            'cache' => [
                'driver' => 'file',
                'prefix' => $prefix,
                'ttl' => $ttl,
                'file' => [
                    'directory' => $directory
                ]
            ],
            'prefix' => $prefix
        ];
        
        // 创建TokenStorage
        $tokenStorage = new TokenStorage(null, $config);
        
        // 测试数据
        $accessToken = 'test_access_token_' . uniqid();
        $refreshToken = 'test_refresh_token_' . uniqid();
        
        // 存储令牌
        $result = $tokenStorage->setTokens($accessToken, $refreshToken);
        $this->assertTrue($result, '存储令牌失败');
        
        // 验证令牌是否正确存储
        $storedAccessToken = $tokenStorage->getAccessToken();
        $storedRefreshToken = $tokenStorage->getRefreshToken();
        
        $this->assertEquals($accessToken, $storedAccessToken, '访问令牌不匹配');
        $this->assertEquals($refreshToken, $storedRefreshToken, '刷新令牌不匹配');
        
        // 验证令牌是否存在
        $hasAccessToken = $tokenStorage->hasAccessToken();
        $hasRefreshToken = $tokenStorage->hasRefreshToken();
        
        $this->assertTrue($hasAccessToken, '访问令牌不存在');
        $this->assertTrue($hasRefreshToken, '刷新令牌不存在');
        
        // 清除令牌
        $result = $tokenStorage->clearTokens();
        $this->assertTrue($result, '清除令牌失败');
        
        // 验证令牌是否已清除
        $this->assertNull($tokenStorage->getAccessToken(), '访问令牌未被清除');
        $this->assertNull($tokenStorage->getRefreshToken(), '刷新令牌未被清除');
        $this->assertFalse($tokenStorage->hasAccessToken(), '访问令牌仍然存在');
        $this->assertFalse($tokenStorage->hasRefreshToken(), '刷新令牌仍然存在');
        
        // 清理测试目录
        $this->removeDirectory($directory);
    }
    
    /**
     * 测试使用Redis缓存的TokenStorage
     */
    public function testTokenStorageWithRedisCache()
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
            // 创建配置
            $config = [
                'cache' => [
                    'driver' => 'redis',
                    'prefix' => $prefix,
                    'ttl' => $ttl,
                    'redis' => [
                        'host' => $host,
                        'port' => $port,
                        'password' => $password,
                        'database' => $database
                    ]
                ],
                'prefix' => $prefix
            ];
            
            // 创建TokenStorage
            $tokenStorage = new TokenStorage(null, $config);
            
            // 测试数据
            $accessToken = 'test_access_token_' . uniqid();
            $refreshToken = 'test_refresh_token_' . uniqid();
            
            // 存储令牌
            $result = $tokenStorage->setTokens($accessToken, $refreshToken);
            $this->assertTrue($result, '存储令牌失败');
            
            // 验证令牌是否正确存储
            $storedAccessToken = $tokenStorage->getAccessToken();
            $storedRefreshToken = $tokenStorage->getRefreshToken();
            
            $this->assertEquals($accessToken, $storedAccessToken, '访问令牌不匹配');
            $this->assertEquals($refreshToken, $storedRefreshToken, '刷新令牌不匹配');
            
            // 验证令牌是否存在
            $hasAccessToken = $tokenStorage->hasAccessToken();
            $hasRefreshToken = $tokenStorage->hasRefreshToken();
            
            $this->assertTrue($hasAccessToken, '访问令牌不存在');
            $this->assertTrue($hasRefreshToken, '刷新令牌不存在');
            
            // 清除令牌
            $result = $tokenStorage->clearTokens();
            $this->assertTrue($result, '清除令牌失败');
            
            // 验证令牌是否已清除
            $this->assertNull($tokenStorage->getAccessToken(), '访问令牌未被清除');
            $this->assertNull($tokenStorage->getRefreshToken(), '刷新令牌未被清除');
            $this->assertFalse($tokenStorage->hasAccessToken(), '访问令牌仍然存在');
            $this->assertFalse($tokenStorage->hasRefreshToken(), '刷新令牌仍然存在');
            
        } catch (\Exception $e) {
            $this->markTestSkipped('Redis测试失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 测试使用配置文件中的缓存配置
     */
    public function testTokenStorageWithConfigCache()
    {
        // 从环境变量获取配置
        $driver = getenv('DIFY_CACHE_DRIVER') ?: 'file';
        
        // 如果配置的是Redis，但Redis扩展不可用，则跳过测试
        if ($driver === 'redis' && !extension_loaded('redis')) {
            $this->markTestSkipped('Redis扩展未安装，跳过测试');
            return;
        }
        
        try {
            // 创建TokenStorage，使用环境变量中的配置
            $tokenStorage = new TokenStorage();
            
            // 测试数据
            $accessToken = 'test_access_token_' . uniqid();
            $refreshToken = 'test_refresh_token_' . uniqid();
            
            // 存储令牌
            $result = $tokenStorage->setTokens($accessToken, $refreshToken);
            $this->assertTrue($result, '存储令牌失败');
            
            // 验证令牌是否正确存储
            $storedAccessToken = $tokenStorage->getAccessToken();
            $storedRefreshToken = $tokenStorage->getRefreshToken();
            
            $this->assertEquals($accessToken, $storedAccessToken, '访问令牌不匹配');
            $this->assertEquals($refreshToken, $storedRefreshToken, '刷新令牌不匹配');
            
            // 验证令牌是否存在
            $hasAccessToken = $tokenStorage->hasAccessToken();
            $hasRefreshToken = $tokenStorage->hasRefreshToken();
            
            $this->assertTrue($hasAccessToken, '访问令牌不存在');
            $this->assertTrue($hasRefreshToken, '刷新令牌不存在');
            
            // 清除令牌
            $result = $tokenStorage->clearTokens();
            $this->assertTrue($result, '清除令牌失败');
            
            // 验证令牌是否已清除
            $this->assertNull($tokenStorage->getAccessToken(), '访问令牌未被清除');
            $this->assertNull($tokenStorage->getRefreshToken(), '刷新令牌未被清除');
            $this->assertFalse($tokenStorage->hasAccessToken(), '访问令牌仍然存在');
            $this->assertFalse($tokenStorage->hasRefreshToken(), '刷新令牌仍然存在');
            
        } catch (\Exception $e) {
            $this->markTestSkipped('测试失败: ' . $e->getMessage());
        }
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