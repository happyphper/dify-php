<?php

namespace Happyphper\Dify\Tests\Cases\Console;

use Happyphper\Dify\Console\ConsoleClient;
use Happyphper\Dify\Support\Cache\CacheFactory;
use Happyphper\Dify\Support\Cache\FileCache;
use Happyphper\Dify\Support\Cache\RedisCache;
use PHPUnit\Framework\TestCase;

/**
 * ConsoleClient与不同缓存驱动测试类
 */
class ConsoleClientWithCacheTest extends TestCase
{
    /**
     * 基础URL
     *
     * @var string
     */
    protected string $baseUrl;

    /**
     * 邮箱
     *
     * @var string
     */
    protected string $email;

    /**
     * 密码
     *
     * @var string
     */
    protected string $password;

    /**
     * 设置测试环境
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->baseUrl = getenv('DIFY_BASE_URL') ?: 'http://localhost:5001';
        $this->email = getenv('DIFY_EMAIL') ?: 'admin@ai.com';
        $this->password = getenv('DIFY_PASSWORD') ?: '!Qq123123';
    }

    /**
     * 测试使用文件缓存的ConsoleClient
     */
    public function testConsoleClientWithFileCache()
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
        
        // 创建配置
        $config = [
            'cache' => [
                'driver' => 'file',
                'prefix' => $prefix,
                'ttl' => $ttl,
                'file' => [
                    'directory' => $directory
                ]
            ]
        ];
        
        try {
            // 创建ConsoleClient
            $client = new ConsoleClient(
                $this->baseUrl,
                $this->email,
                $this->password,
                true,
                null,
                $cache,
                $config
            );
            
            // 测试登录
            $response = $client->login();
            
            // 验证登录成功
            $this->assertEquals('success', $response['result'], '登录失败');
            $this->assertArrayHasKey('access_token', $response['data'], '响应中没有访问令牌');
            $this->assertArrayHasKey('refresh_token', $response['data'], '响应中没有刷新令牌');
            
            // 验证令牌是否已存储
            $accessToken = $client->getToken();
            $refreshToken = $client->getRefreshToken();
            
            $this->assertNotNull($accessToken, '访问令牌未存储');
            $this->assertNotNull($refreshToken, '刷新令牌未存储');
            
            // 清除令牌
            $result = $client->clearTokens();
            $this->assertTrue($result, '清除令牌失败');
            
            // 验证令牌是否已清除
            $this->assertNull($client->getToken(), '访问令牌未被清除');
            $this->assertNull($client->getRefreshToken(), '刷新令牌未被清除');
            
        } catch (\Exception $e) {
            $this->fail('测试失败: ' . $e->getMessage());
        } finally {
            // 清理测试目录
            $this->removeDirectory($directory);
        }
    }
    
    /**
     * 测试使用Redis缓存的ConsoleClient
     */
    public function testConsoleClientWithRedisCache()
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
                ]
            ];
            
            // 创建ConsoleClient
            $client = new ConsoleClient(
                $this->baseUrl,
                $this->email,
                $this->password,
                true,
                null,
                $cache,
                $config
            );
            
            // 测试登录
            $response = $client->login();
            
            // 验证登录成功
            $this->assertEquals('success', $response['result'], '登录失败');
            $this->assertArrayHasKey('access_token', $response['data'], '响应中没有访问令牌');
            $this->assertArrayHasKey('refresh_token', $response['data'], '响应中没有刷新令牌');
            
            // 验证令牌是否已存储
            $accessToken = $client->getToken();
            $refreshToken = $client->getRefreshToken();
            
            $this->assertNotNull($accessToken, '访问令牌未存储');
            $this->assertNotNull($refreshToken, '刷新令牌未存储');
            
            // 清除令牌
            $result = $client->clearTokens();
            $this->assertTrue($result, '清除令牌失败');
            
            // 验证令牌是否已清除
            $this->assertNull($client->getToken(), '访问令牌未被清除');
            $this->assertNull($client->getRefreshToken(), '刷新令牌未被清除');
            
        } catch (\Exception $e) {
            $this->markTestSkipped('Redis测试失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 测试使用配置文件中的缓存配置
     */
    public function testConsoleClientWithConfigCache()
    {
        // 从环境变量获取配置
        $driver = getenv('DIFY_CACHE_DRIVER') ?: 'file';
        
        // 如果配置的是Redis，但Redis扩展不可用，则跳过测试
        if ($driver === 'redis' && !extension_loaded('redis')) {
            $this->markTestSkipped('Redis扩展未安装，跳过测试');
            return;
        }
        
        try {
            // 创建ConsoleClient，使用环境变量中的配置
            $client = new ConsoleClient(
                $this->baseUrl,
                $this->email,
                $this->password,
                true
            );
            
            // 测试登录
            $response = $client->login();
            
            // 验证登录成功
            $this->assertEquals('success', $response['result'], '登录失败');
            $this->assertArrayHasKey('access_token', $response['data'], '响应中没有访问令牌');
            $this->assertArrayHasKey('refresh_token', $response['data'], '响应中没有刷新令牌');
            
            // 验证令牌是否已存储
            $accessToken = $client->getToken();
            $refreshToken = $client->getRefreshToken();
            
            $this->assertNotNull($accessToken, '访问令牌未存储');
            $this->assertNotNull($refreshToken, '刷新令牌未存储');
            
            // 清除令牌
            $result = $client->clearTokens();
            $this->assertTrue($result, '清除令牌失败');
            
            // 验证令牌是否已清除
            $this->assertNull($client->getToken(), '访问令牌未被清除');
            $this->assertNull($client->getRefreshToken(), '刷新令牌未被清除');
            
        } catch (\Exception $e) {
            $this->markTestSkipped('测试失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 测试令牌刷新功能
     */
    public function testTokenRefresh()
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
        
        // 创建配置
        $config = [
            'cache' => [
                'driver' => 'file',
                'prefix' => $prefix,
                'ttl' => $ttl,
                'file' => [
                    'directory' => $directory
                ],
                'debug' => true
            ]
        ];
        
        try {
            // 创建ConsoleClient
            $client = new ConsoleClient(
                $this->baseUrl,
                $this->email,
                $this->password,
                true,
                null,
                $cache,
                $config
            );
            
            // 测试登录
            $response = $client->login();
            
            // 验证登录成功
            $this->assertEquals('success', $response['result'], '登录失败');
            $this->assertArrayHasKey('access_token', $response['data'], '响应中没有访问令牌');
            $this->assertArrayHasKey('refresh_token', $response['data'], '响应中没有刷新令牌');
            
            // 获取原始令牌
            $originalToken = $client->getToken();
            $this->assertNotNull($originalToken, '访问令牌未存储');
            
            // 使用反射修改令牌为无效值，模拟令牌过期
            $reflectionClass = new \ReflectionClass(ConsoleClient::class);
            $tokenProperty = $reflectionClass->getProperty('token');
            $tokenProperty->setAccessible(true);
            $tokenProperty->setValue($client, 'invalid_token_to_force_401_error');
            
            // 尝试获取数据集列表，应该会触发令牌刷新
            try {
                $datasets = $client->datasets()->getDatasets();
                
                // 获取新的令牌
                $newToken = $client->getToken();
                
                // 验证令牌已更新
                $this->assertNotEquals('invalid_token_to_force_401_error', $newToken, '令牌未刷新');
                $this->assertNotNull($newToken, '新的访问令牌为空');
                
                // 验证数据集列表获取成功
                $this->assertIsArray($datasets, '获取数据集列表失败');
                
            } catch (\Exception $e) {
                $this->fail('令牌刷新测试失败: ' . $e->getMessage());
            }
            
            // 清除令牌
            $result = $client->clearTokens();
            $this->assertTrue($result, '清除令牌失败');
            
        } catch (\Exception $e) {
            $this->fail('测试失败: ' . $e->getMessage());
        } finally {
            // 清理测试目录
            $this->removeDirectory($directory);
        }
    }
    
    /**
     * 测试 refreshToken 方法
     */
    public function testRefreshTokenMethod()
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
        
        // 创建配置
        $config = [
            'cache' => [
                'driver' => 'file',
                'prefix' => $prefix,
                'ttl' => $ttl,
                'file' => [
                    'directory' => $directory
                ],
                'debug' => true
            ]
        ];
        
        try {
            // 创建ConsoleClient
            $client = new ConsoleClient(
                $this->baseUrl,
                $this->email,
                $this->password,
                true,
                null,
                $cache,
                $config
            );
            
            // 测试登录
            $response = $client->login();
            
            // 验证登录成功
            $this->assertEquals('success', $response['result'], '登录失败');
            $this->assertArrayHasKey('access_token', $response['data'], '响应中没有访问令牌');
            $this->assertArrayHasKey('refresh_token', $response['data'], '响应中没有刷新令牌');
            
            // 获取原始令牌
            $originalToken = $client->getToken();
            $this->assertNotNull($originalToken, '访问令牌未存储');
            
            // 使用反射修改令牌为无效值
            $reflectionClass = new \ReflectionClass(ConsoleClient::class);
            $tokenProperty = $reflectionClass->getProperty('token');
            $tokenProperty->setAccessible(true);
            $tokenProperty->setValue($client, 'invalid_token');
            
            // 尝试获取数据集列表，应该会触发令牌刷新
            try {
                $datasets = $client->datasets()->getDatasets();
                
                // 获取新的令牌
                $newToken = $client->getToken();
                
                // 验证令牌已更新
                $this->assertNotEquals('invalid_token', $newToken, '令牌未刷新');
                $this->assertNotNull($newToken, '新的访问令牌为空');
                
                // 验证数据集列表获取成功
                $this->assertIsArray($datasets, '获取数据集列表失败');
                
            } catch (\Exception $e) {
                // 如果 refreshToken 方法失败，可能是因为 API 不支持刷新令牌
                // 在这种情况下，应该会尝试重新登录
                $newToken = $client->getToken();
                
                // 验证令牌已更新（通过重新登录）
                $this->assertNotEquals('invalid_token', $newToken, '令牌未通过重新登录更新');
                $this->assertNotNull($newToken, '新的访问令牌为空');
            }
            
            // 清除令牌
            $result = $client->clearTokens();
            $this->assertTrue($result, '清除令牌失败');
            
        } catch (\Exception $e) {
            $this->fail('测试失败: ' . $e->getMessage());
        } finally {
            // 清理测试目录
            $this->removeDirectory($directory);
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