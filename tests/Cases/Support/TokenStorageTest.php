<?php

namespace Happyphper\Dify\Tests\Cases\Support;

use Happyphper\Dify\Support\Cache\CacheFactory;
use Happyphper\Dify\Support\TokenStorage;
use PHPUnit\Framework\TestCase;

/**
 * TokenStorage测试类
 */
class TokenStorageTest extends TestCase
{
    /**
     * 测试使用缓存存储令牌
     */
    public function testStoreTokensWithFileCache()
    {
        // 创建临时目录
        $tempDir = sys_get_temp_dir() . '/dify_test_' . uniqid();
        mkdir($tempDir, 0777, true);
        echo "\n[INFO] 创建临时目录: $tempDir\n";

        // 获取缓存驱动类型
        $driver = getenv('DIFY_CACHE_DRIVER') ?: 'file';
        
        // 创建配置
        $config = [
            'cache' => [
                'driver' => $driver,
                'prefix' => 'dify_',
                'ttl' => 86400,
                'file' => [
                    'directory' => $tempDir
                ],
                'redis' => [
                    'host' => getenv('DIFY_CACHE_REDIS_HOST') ?: 'redis',
                    'port' => (int)getenv('DIFY_CACHE_REDIS_PORT') ?: 6379,
                    'password' => getenv('DIFY_CACHE_REDIS_PASSWORD') ?: null,
                    'database' => (int)getenv('DIFY_CACHE_REDIS_DATABASE') ?: 0,
                    'debug' => true
                ]
            ]
        ];
        
        try {
            // 使用 CacheFactory 创建缓存实例
            $cache = CacheFactory::create($config['cache']);
            
            // 创建令牌存储
            $tokenStorage = new TokenStorage($cache);
            
            // 测试数据
            $accessToken = 'test_access_token_' . uniqid();
            $refreshToken = 'test_refresh_token_' . uniqid();
            echo "\n[INFO] 测试访问令牌: $accessToken\n";
            echo "\n[INFO] 测试刷新令牌: $refreshToken\n";
            
            // 存储令牌
            $result = $tokenStorage->setTokens($accessToken, $refreshToken);
            $this->assertTrue($result, '存储令牌失败');
            echo "\n[INFO] 存储令牌结果: 成功\n";
            
            // 验证令牌是否存储成功
            $this->assertEquals($accessToken, $tokenStorage->getAccessToken(), '访问令牌存储失败');
            $this->assertEquals($refreshToken, $tokenStorage->getRefreshToken(), '刷新令牌存储失败');
            echo "\n[INFO] 存储的访问令牌: " . $tokenStorage->getAccessToken() . "\n";
            echo "\n[INFO] 存储的刷新令牌: " . $tokenStorage->getRefreshToken() . "\n";
            
            // 验证令牌是否存在
            $this->assertTrue($tokenStorage->hasAccessToken(), '访问令牌不存在');
            $this->assertTrue($tokenStorage->hasRefreshToken(), '刷新令牌不存在');
            echo "\n[INFO] 是否有访问令牌: 是\n";
            echo "\n[INFO] 是否有刷新令牌: 是\n";
            
            // 清除令牌
            $result = $tokenStorage->clearTokens();
            $this->assertTrue($result, '清除令牌失败');
            echo "\n[INFO] 清除令牌结果: 成功\n";
            
            // 验证令牌是否被清除
            $this->assertNull($tokenStorage->getAccessToken(), '访问令牌未被清除');
            $this->assertNull($tokenStorage->getRefreshToken(), '刷新令牌未被清除');
            $this->assertFalse($tokenStorage->hasAccessToken(), '访问令牌仍然存在');
            $this->assertFalse($tokenStorage->hasRefreshToken(), '刷新令牌仍然存在');
        } catch (\Exception $e) {
            // 如果 Redis 服务器不需要密码但测试尝试使用密码，则跳过测试
            if (strpos($e->getMessage(), 'ERR AUTH') !== false && 
                strpos($e->getMessage(), 'without any password configured') !== false) {
                $this->markTestSkipped('Redis 服务器不需要密码，但测试尝试使用密码，跳过测试');
                return;
            }
            throw $e;
        } finally {
            // 清理临时目录
            $this->removeDirectory($tempDir);
        }
    }
    
    /**
     * 测试使用 Redis 缓存存储令牌（有密码）
     */
    public function testStoreTokensWithRedisWithPassword()
    {
        // 检查 Redis 扩展是否可用
        if (!extension_loaded('redis')) {
            $this->markTestSkipped('Redis 扩展未安装，跳过测试');
            return;
        }
        
        echo "\n[INFO] 测试 Redis 缓存（有密码）\n";
        
        // 创建配置
        $config = [
            'cache' => [
                'driver' => 'redis',
                'prefix' => 'dify_pwd_',
                'ttl' => 86400,
                'redis' => [
                    'host' => 'redis',
                    'port' => 6379,
                    'password' => 'dify123456', // 使用密码
                    'database' => 0,
                    'debug' => true
                ]
            ]
        ];
        
        // 使用 CacheFactory 创建缓存实例
        try {
            $cache = CacheFactory::create($config['cache']);
            
            // 创建令牌存储
            $tokenStorage = new TokenStorage($cache);
            
            // 测试数据
            $accessToken = 'test_pwd_access_token_' . uniqid();
            $refreshToken = 'test_pwd_refresh_token_' . uniqid();
            echo "\n[INFO] 测试访问令牌: $accessToken\n";
            echo "\n[INFO] 测试刷新令牌: $refreshToken\n";
            
            // 存储令牌
            $result = $tokenStorage->setTokens($accessToken, $refreshToken);
            $this->assertTrue($result, '存储令牌失败');
            echo "\n[INFO] 存储令牌结果: " . ($result ? '成功' : '失败') . "\n";
            
            // 验证令牌是否正确存储
            $storedAccessToken = $tokenStorage->getAccessToken();
            $storedRefreshToken = $tokenStorage->getRefreshToken();
            echo "\n[INFO] 存储的访问令牌: " . ($storedAccessToken ?? 'null') . "\n";
            echo "\n[INFO] 存储的刷新令牌: " . ($storedRefreshToken ?? 'null') . "\n";
            
            $this->assertEquals($accessToken, $storedAccessToken, '访问令牌不匹配');
            $this->assertEquals($refreshToken, $storedRefreshToken, '刷新令牌不匹配');
            
            // 验证令牌是否存在
            $hasAccessToken = $tokenStorage->hasAccessToken();
            $hasRefreshToken = $tokenStorage->hasRefreshToken();
            echo "\n[INFO] 是否有访问令牌: " . ($hasAccessToken ? '是' : '否') . "\n";
            echo "\n[INFO] 是否有刷新令牌: " . ($hasRefreshToken ? '是' : '否') . "\n";
            
            $this->assertTrue($hasAccessToken, '访问令牌不存在');
            $this->assertTrue($hasRefreshToken, '刷新令牌不存在');
            
            // 清除令牌
            $result = $tokenStorage->clearTokens();
            $this->assertTrue($result, '清除令牌失败');
            echo "\n[INFO] 清除令牌结果: " . ($result ? '成功' : '失败') . "\n";
            
            // 验证令牌是否已清除
            $this->assertNull($tokenStorage->getAccessToken(), '访问令牌未被清除');
            $this->assertNull($tokenStorage->getRefreshToken(), '刷新令牌未被清除');
            $this->assertFalse($tokenStorage->hasAccessToken(), '访问令牌仍然存在');
            $this->assertFalse($tokenStorage->hasRefreshToken(), '刷新令牌仍然存在');
        } catch (\Exception $e) {
            // 如果 Redis 服务器不需要密码但测试尝试使用密码，则跳过测试
            if (strpos($e->getMessage(), 'ERR AUTH') !== false && 
                strpos($e->getMessage(), 'without any password configured') !== false) {
                $this->markTestSkipped('Redis 服务器不需要密码，但测试尝试使用密码，跳过测试');
                return;
            }
            $this->fail('Redis 测试失败（有密码）: ' . $e->getMessage());
        }
    }
    
    /**
     * 测试使用 Redis 缓存存储令牌（无密码）
     */
    public function testStoreTokensWithRedisWithoutPassword()
    {
        // 检查 Redis 扩展是否可用
        if (!extension_loaded('redis')) {
            $this->markTestSkipped('Redis 扩展未安装，跳过测试');
            return;
        }
        
        echo "\n[INFO] 测试 Redis 缓存（无密码）\n";
        
        // 创建配置
        $config = [
            'cache' => [
                'driver' => 'redis',
                'prefix' => 'dify_no_pwd_',
                'ttl' => 86400,
                'redis' => [
                    'host' => 'redis',
                    'port' => 6379,
                    'password' => null, // 不使用密码
                    'database' => 0,
                    'debug' => true
                ]
            ]
        ];
        
        // 使用 CacheFactory 创建缓存实例
        try {
            $cache = CacheFactory::create($config['cache']);
            
            // 创建令牌存储
            $tokenStorage = new TokenStorage($cache);
            
            // 测试数据
            $accessToken = 'test_no_pwd_access_token_' . uniqid();
            $refreshToken = 'test_no_pwd_refresh_token_' . uniqid();
            echo "\n[INFO] 测试访问令牌: $accessToken\n";
            echo "\n[INFO] 测试刷新令牌: $refreshToken\n";
            
            // 存储令牌
            $result = $tokenStorage->setTokens($accessToken, $refreshToken);
            $this->assertTrue($result, '存储令牌失败');
            echo "\n[INFO] 存储令牌结果: " . ($result ? '成功' : '失败') . "\n";
            
            // 验证令牌是否正确存储
            $storedAccessToken = $tokenStorage->getAccessToken();
            $storedRefreshToken = $tokenStorage->getRefreshToken();
            echo "\n[INFO] 存储的访问令牌: " . ($storedAccessToken ?? 'null') . "\n";
            echo "\n[INFO] 存储的刷新令牌: " . ($storedRefreshToken ?? 'null') . "\n";
            
            $this->assertEquals($accessToken, $storedAccessToken, '访问令牌不匹配');
            $this->assertEquals($refreshToken, $storedRefreshToken, '刷新令牌不匹配');
            
            // 验证令牌是否存在
            $hasAccessToken = $tokenStorage->hasAccessToken();
            $hasRefreshToken = $tokenStorage->hasRefreshToken();
            echo "\n[INFO] 是否有访问令牌: " . ($hasAccessToken ? '是' : '否') . "\n";
            echo "\n[INFO] 是否有刷新令牌: " . ($hasRefreshToken ? '是' : '否') . "\n";
            
            $this->assertTrue($hasAccessToken, '访问令牌不存在');
            $this->assertTrue($hasRefreshToken, '刷新令牌不存在');
            
            // 清除令牌
            $result = $tokenStorage->clearTokens();
            $this->assertTrue($result, '清除令牌失败');
            echo "\n[INFO] 清除令牌结果: " . ($result ? '成功' : '失败') . "\n";
            
            // 验证令牌是否已清除
            $this->assertNull($tokenStorage->getAccessToken(), '访问令牌未被清除');
            $this->assertNull($tokenStorage->getRefreshToken(), '刷新令牌未被清除');
            $this->assertFalse($tokenStorage->hasAccessToken(), '访问令牌仍然存在');
            $this->assertFalse($tokenStorage->hasRefreshToken(), '刷新令牌仍然存在');
        } catch (\Exception $e) {
            // 如果 Redis 服务器需要密码但测试尝试不使用密码，则跳过测试
            if (strpos($e->getMessage(), 'NOAUTH Authentication required') !== false || 
                strpos($e->getMessage(), 'Redis 服务器需要密码') !== false) {
                $this->markTestSkipped('Redis 服务器需要密码，但测试尝试不使用密码，跳过测试');
                return;
            }
            $this->fail('Redis 测试失败（无密码）: ' . $e->getMessage());
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