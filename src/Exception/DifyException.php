<?php

namespace Happyphper\Dify\Exception;

use Exception;

/**
 * Dify API 异常类
 */
class DifyException extends Exception
{
    /**
     * 错误代码
     *
     * @var string|null
     */
    private $errorCode;

    /**
     * HTTP 状态码
     *
     * @var int|null
     */
    private $statusCode;

    /**
     * 原始响应数据
     *
     * @var array|null
     */
    private $responseData;

    /**
     * 创建一个新的 Dify 异常
     *
     * @param string $message 错误消息
     * @param int $statusCode HTTP状态码
     * @param string|null $errorCode API错误码
     * @param array|null $responseData 原始响应数据
     * @param \Throwable|null $previous 上一个异常
     */
    public function __construct(string $message, int $statusCode = 0, ?string $errorCode = null, ?array $responseData = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
        $this->statusCode = $statusCode;
        $this->errorCode = $errorCode;
        $this->responseData = $responseData;
    }

    /**
     * 获取 API 错误代码
     *
     * @return string|null
     */
    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    /**
     * 获取 HTTP 状态码
     *
     * @return int|null
     */
    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    /**
     * 获取原始响应数据
     *
     * @return array|null
     */
    public function getResponseData(): ?array
    {
        return $this->responseData;
    }

    /**
     * 从 API 响应创建异常
     *
     * @param array $responseData 响应数据
     * @param int $statusCode HTTP状态码
     * @return self
     */
    public static function fromResponse(array $responseData, int $statusCode): self
    {
        // 默认错误消息
        $message = '未知错误';
        $errorCode = null;
        
        // 尝试从响应中提取错误信息
        if (isset($responseData['message']) && is_string($responseData['message'])) {
            $message = $responseData['message'];
        }
        
        // 尝试从响应中提取错误代码
        if (isset($responseData['code']) && (is_string($responseData['code']) || is_numeric($responseData['code']))) {
            $errorCode = (string) $responseData['code'];
        } elseif (isset($responseData['error_code']) && (is_string($responseData['error_code']) || is_numeric($responseData['error_code']))) {
            $errorCode = (string) $responseData['error_code'];
        }
        
        // 如果响应中包含详细错误信息，添加到消息中
        if (isset($responseData['error']) && is_string($responseData['error'])) {
            $message .= ': ' . $responseData['error'];
        } elseif (isset($responseData['errors']) && is_array($responseData['errors'])) {
            $errorDetails = [];
            foreach ($responseData['errors'] as $field => $error) {
                if (is_string($error)) {
                    $errorDetails[] = "$field: $error";
                } elseif (is_array($error) && !empty($error)) {
                    $errorDetails[] = "$field: " . implode(', ', $error);
                }
            }
            if (!empty($errorDetails)) {
                $message .= ': ' . implode('; ', $errorDetails);
            }
        }
        
        // 如果响应中包含原始响应，添加到消息中
        if (isset($responseData['raw_response']) && is_string($responseData['raw_response'])) {
            $message .= ' (原始响应: ' . substr($responseData['raw_response'], 0, 100) . (strlen($responseData['raw_response']) > 100 ? '...' : '') . ')';
        }
        
        return new self($message, $statusCode, $errorCode, $responseData);
    }
} 