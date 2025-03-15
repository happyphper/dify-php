<?php

namespace Happyphper\Dify\Support\Cache;

use Exception;

/**
 * 文件缓存驱动
 */
class FileCache extends AbstractCache
{
    /**
     * 缓存目录
     *
     * @var string
     */
    protected string $directory;

    /**
     * 构造函数
     *
     * @param string|null $directory 缓存目录
     * @param int|null $defaultTtl 默认缓存过期时间（秒）
     * @param string|null $prefix 缓存前缀
     */
    public function __construct(?string $directory = null, ?int $defaultTtl = null, ?string $prefix = null)
    {
        parent::__construct($defaultTtl, $prefix);

        $this->directory = $directory ?? sys_get_temp_dir() . '/dify_cache';

        // 确保缓存目录存在
        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0777, true);
        }
    }

    /**
     * 获取缓存文件路径
     *
     * @param string $key 缓存键
     * @return string
     */
    protected function getFilePath(string $key): string
    {
        return $this->directory . '/' . md5($key);
    }

    /**
     * 实际获取缓存的方法
     *
     * @param string $key 缓存键
     * @return mixed
     */
    protected function doGet(string $key)
    {
        $filePath = $this->getFilePath($key);
        echo "\n[DEBUG] 获取缓存: $key => $filePath\n";

        if (!file_exists($filePath)) {
            echo "\n[DEBUG] 缓存文件不存在: $filePath\n";
            return null;
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            echo "\n[ERROR] 无法读取缓存文件: $filePath\n";
            return null;
        }

        echo "\n[DEBUG] 读取到的内容: " . substr($content, 0, 100) . (strlen($content) > 100 ? '...' : '') . "\n";
        
        try {
            $data = unserialize($content);
            echo "\n[DEBUG] 反序列化结果: " . print_r($data, true) . "\n";
            
            if (!is_array($data)) {
                echo "\n[ERROR] 反序列化结果不是数组: " . gettype($data) . "\n";
                return null;
            }
            
            // 直接检查数组键是否存在
            if (!array_key_exists('value', $data)) {
                echo "\n[ERROR] 缓存数据中没有value字段\n";
                return null;
            }
            
            if (!array_key_exists('expiry', $data)) {
                echo "\n[ERROR] 缓存数据中没有expiry字段\n";
                return null;
            }

            // 检查是否过期
            if ($data['expiry'] !== null && time() > $data['expiry']) {
                echo "\n[DEBUG] 缓存已过期: $filePath\n";
                $this->doDelete($key);
                return null;
            }

            echo "\n[DEBUG] 成功读取缓存: " . (is_string($data['value']) ? $data['value'] : gettype($data['value'])) . "\n";
            return $data['value'];
        } catch (Exception $e) {
            echo "\n[ERROR] 反序列化失败: " . $e->getMessage() . "\n";
            return null;
        }
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
        $filePath = $this->getFilePath($key);
        echo "\n[DEBUG] 设置缓存: $key => $filePath\n";

        $expiry = $ttl !== null ? time() + $ttl : null;
        $data = [
            'value' => $value,
            'expiry' => $expiry,
        ];

        try {
            // 确保目录存在
            if (!is_dir($this->directory)) {
                mkdir($this->directory, 0777, true);
                echo "\n[DEBUG] 创建缓存目录: {$this->directory}\n";
            }
            
            // 序列化数据
            $serialized = serialize($data);
            echo "\n[DEBUG] 序列化数据: " . substr($serialized, 0, 100) . (strlen($serialized) > 100 ? '...' : '') . "\n";
            
            // 写入文件
            $result = file_put_contents($filePath, $serialized);
            echo "\n[DEBUG] 写入文件结果: " . ($result !== false ? "成功 ($result 字节)" : "失败") . "\n";
            
            // 设置文件权限
            if ($result !== false) {
                chmod($filePath, 0666);
                echo "\n[DEBUG] 设置文件权限: 0666\n";
                
                // 验证写入
                if (file_exists($filePath)) {
                    $content = file_get_contents($filePath);
                    $readData = unserialize($content);
                    if (is_array($readData) && isset($readData['value'])) {
                        echo "\n[DEBUG] 验证写入成功: " . (is_string($readData['value']) ? $readData['value'] : gettype($readData['value'])) . "\n";
                    } else {
                        echo "\n[ERROR] 验证写入失败: 无法反序列化数据\n";
                    }
                } else {
                    echo "\n[ERROR] 验证写入失败: 文件不存在\n";
                }
            }
            
            return $result !== false;
        } catch (Exception $e) {
            echo "\n[ERROR] 缓存写入失败: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * 实际删除缓存的方法
     *
     * @param string $key 缓存键
     * @return bool
     */
    protected function doDelete(string $key): bool
    {
        $filePath = $this->getFilePath($key);

        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return true;
    }

    /**
     * 实际判断缓存是否存在的方法
     *
     * @param string $key 缓存键
     * @return bool
     */
    protected function doHas(string $key): bool
    {
        return $this->doGet($key) !== null;
    }

    /**
     * 清空所有缓存
     *
     * @return bool
     */
    public function clear(): bool
    {
        $files = glob($this->directory . '/*');
        
        if ($files === false) {
            return false;
        }

        $success = true;
        foreach ($files as $file) {
            if (is_file($file)) {
                $success = $success && unlink($file);
            }
        }

        return $success;
    }
} 