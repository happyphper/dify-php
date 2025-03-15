<?php

namespace Happyphper\Dify\Tests\Cases\Console;

use Happyphper\Dify\Console\Api\AuthApi;
use Happyphper\Dify\Exceptions\ApiException;
use Happyphper\Dify\Support\Cache\FileCache;

/**
 * 控制台 Auth API 测试
 */
class AuthTest extends TestCase
{
    /**
     * Auth API 实例
     *
     * @var AuthApi
     */
    protected AuthApi $authApi;

    /**
     * 临时缓存目录
     *
     * @var string
     */
    protected string $tempDir;

    /**
     * 测试前的准备工作
     *
     * @return void
     * @throws ApiException
     */
    protected function setUp(): void
    {
        // 创建临时目录
        $this->tempDir = sys_get_temp_dir() . '/dify_test_' . uniqid();
        mkdir($this->tempDir, 0777, true);

        // 创建文件缓存
        $cache = new FileCache($this->tempDir);

        // 创建控制台客户端，使用文件缓存
        $this->client = $this->createClient($cache);
        
        // 获取 Auth API 实例
        $this->authApi = $this->client->auth();
    }

    /**
     * 测试后的清理工作
     *
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        
        // 清理临时目录
        $this->removeDirectory($this->tempDir);
    }

    /**
     * 测试登录功能
     *
     * @return void
     * @throws ApiException
     */
    public function testLogin(): void
    {
        // 使用环境变量中的凭据进行登录测试
        $response = $this->authApi->login(
            $_ENV['DIFY_EMAIL'],
            $_ENV['DIFY_PASSWORD']
        );

        // 断言响应中包含必要的字段
        $this->assertIsArray($response);
        $this->assertArrayHasKey('result', $response);
        $this->assertEquals('success', $response['result']);
        
        // 检查是否包含数据字段
        $this->assertArrayHasKey('data', $response);
        $this->assertIsArray($response['data']);
        
        // 检查数据中是否包含访问令牌和刷新令牌
        $data = $response['data'];
        $this->assertArrayHasKey('access_token', $data);
        $this->assertNotEmpty($data['access_token']);
        $this->assertArrayHasKey('refresh_token', $data);
        $this->assertNotEmpty($data['refresh_token']);
    }

    /**
     * 测试登录并存储令牌
     *
     * @return void
     * @throws ApiException
     */
    public function testLoginAndStoreToken(): void
    {
        // 使用客户端的login方法登录
        $response = $this->client->login();

        // 断言响应中包含必要的字段
        $this->assertIsArray($response);
        $this->assertArrayHasKey('result', $response);
        $this->assertEquals('success', $response['result']);
        
        // 检查是否包含数据字段
        $this->assertArrayHasKey('data', $response);
        $this->assertIsArray($response['data']);
        
        // 检查数据中是否包含访问令牌和刷新令牌
        $data = $response['data'];
        $this->assertArrayHasKey('access_token', $data);
        $this->assertNotEmpty($data['access_token']);
        $this->assertArrayHasKey('refresh_token', $data);
        $this->assertNotEmpty($data['refresh_token']);
        
        // 验证令牌是否已存储
        $this->assertEquals($data['access_token'], $this->client->getToken());
        $this->assertEquals($data['refresh_token'], $this->client->getRefreshToken());
        
        // 清除令牌
        $this->client->clearTokens();
        
        // 验证令牌是否已清除
        $this->assertNull($this->client->getToken());
        $this->assertNull($this->client->getRefreshToken());
    }

    /**
     * 测试使用错误凭据登录
     *
     * @return void
     */
    public function testLoginWithInvalidCredentials(): void
    {
        // 期望抛出异常
        $this->expectException(ApiException::class);
        
        // 使用错误的凭据尝试登录
        $this->authApi->login(
            'invalid_email@example.com',
            'invalid_password'
        );
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