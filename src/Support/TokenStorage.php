<?php

namespace Happyphper\Dify\Support;

use Happyphper\Dify\Support\Cache\CacheInterface;
use Happyphper\Dify\Support\Cache\FileCache;
use Happyphper\Dify\Support\Cache\CacheFactory;

/**
 * 令牌存储
 */
class TokenStorage
{
    /**
     * 缓存实例
     *
     * @var CacheInterface
     */
    protected CacheInterface $cache;

    /**
     * 访问令牌缓存键
     *
     * @var string
     */
    protected string $accessTokenKey = 'access_token';

    /**
     * 刷新令牌缓存键
     *
     * @var string
     */
    protected string $refreshTokenKey = 'refresh_token';

    /**
     * 构造函数
     *
     * @param CacheInterface|null $cache 缓存实例
     * @param array $config 配置
     */
    public function __construct(?CacheInterface $cache = null, array $config = [])
    {
        if ($cache === null) {
            // 如果没有提供缓存实例，则使用配置创建一个
            $cacheConfig = $config['cache'] ?? [];
            $cache = CacheFactory::create($cacheConfig);
        }
        
        $this->cache = $cache;
        
        // 设置缓存键前缀
        $prefix = $config['prefix'] ?? '';
        if (!empty($prefix)) {
            $this->accessTokenKey = $prefix . $this->accessTokenKey;
            $this->refreshTokenKey = $prefix . $this->refreshTokenKey;
        }
    }

    /**
     * 存储访问令牌
     *
     * @param string $accessToken 访问令牌
     * @param int|null $ttl 过期时间（秒）
     * @return bool
     */
    public function setAccessToken(string $accessToken, ?int $ttl = null): bool
    {
        return $this->cache->set($this->accessTokenKey, $accessToken, $ttl);
    }

    /**
     * 获取访问令牌
     *
     * @return string|null
     */
    public function getAccessToken(): ?string
    {
        return $this->cache->get($this->accessTokenKey);
    }

    /**
     * 存储刷新令牌
     *
     * @param string $refreshToken 刷新令牌
     * @param int|null $ttl 过期时间（秒）
     * @return bool
     */
    public function setRefreshToken(string $refreshToken, ?int $ttl = null): bool
    {
        return $this->cache->set($this->refreshTokenKey, $refreshToken, $ttl);
    }

    /**
     * 获取刷新令牌
     *
     * @return string|null
     */
    public function getRefreshToken(): ?string
    {
        return $this->cache->get($this->refreshTokenKey);
    }

    /**
     * 存储令牌
     *
     * @param string $accessToken 访问令牌
     * @param string $refreshToken 刷新令牌
     * @param int|null $accessTokenTtl 访问令牌过期时间（秒）
     * @param int|null $refreshTokenTtl 刷新令牌过期时间（秒）
     * @return bool
     */
    public function setTokens(string $accessToken, string $refreshToken, ?int $accessTokenTtl = null, ?int $refreshTokenTtl = null): bool
    {
        $accessTokenResult = $this->setAccessToken($accessToken, $accessTokenTtl);
        $refreshTokenResult = $this->setRefreshToken($refreshToken, $refreshTokenTtl);

        return $accessTokenResult && $refreshTokenResult;
    }

    /**
     * 清除令牌
     *
     * @return bool
     */
    public function clearTokens(): bool
    {
        $accessTokenResult = $this->cache->delete($this->accessTokenKey);
        $refreshTokenResult = $this->cache->delete($this->refreshTokenKey);

        return $accessTokenResult && $refreshTokenResult;
    }

    /**
     * 判断是否有访问令牌
     *
     * @return bool
     */
    public function hasAccessToken(): bool
    {
        return $this->cache->has($this->accessTokenKey);
    }

    /**
     * 判断是否有刷新令牌
     *
     * @return bool
     */
    public function hasRefreshToken(): bool
    {
        return $this->cache->has($this->refreshTokenKey);
    }

    /**
     * 清除刷新令牌
     *
     * @return bool
     */
    public function clearRefreshToken(): bool
    {
        return $this->cache->delete($this->refreshTokenKey);
    }
} 