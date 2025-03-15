<?php

declare(strict_types=1);

namespace Happyphper\Dify\Console\Api;

use Happyphper\Dify\Console\ConsoleClient;
use Happyphper\Dify\Exceptions\ApiException;

/**
 * Dify 文档控制台 API
 */
class DocumentApi
{
    /**
     * 控制台客户端
     *
     * @var ConsoleClient
     */
    private ConsoleClient $client;

    /**
     * 构造函数
     *
     * @param ConsoleClient $client
     */
    public function __construct(ConsoleClient $client)
    {
        $this->client = $client;
    }

    /**
     * 获取知识库文档列表
     *
     * @param string $datasetId 知识库ID
     * @param int $page 页码，从1开始
     * @param int $limit 每页数量，默认20
     * @param string|null $keyword 搜索关键词
     * @param string|null $status 文档状态，可选值：indexing, parsed, error, available, unavailable
     * @return array 文档列表数据
     * @throws ApiException
     */
    public function getDocuments(string $datasetId, int $page = 1, int $limit = 20, ?string $keyword = null, ?string $status = null): array
    {
        try {
            // 确保登录并获取令牌
            $token = $this->client->login();

            $query = [
                'page' => $page,
                'limit' => $limit
            ];

            if ($keyword !== null) {
                $query['keyword'] = $keyword;
            }

            if ($status !== null) {
                $query['status'] = $status;
            }

            $response = $this->client->get(
                "console/api/datasets/{$datasetId}/documents",
                $query
            );

            return $response;
        } catch (\Exception $e) {
            throw new ApiException('获取知识库文档列表失败: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * 获取知识库文档详情
     *
     * @param string $datasetId 知识库ID
     * @param string $documentId 文档ID
     * @return array 文档详情数据
     * @throws ApiException
     */
    public function getDocument(string $datasetId, string $documentId): array
    {
        try {
            // 确保登录并获取令牌
            $token = $this->client->login();

            $response = $this->client->get(
                "console/api/datasets/{$datasetId}/documents/{$documentId}"
            );

            return $response;
        } catch (\Exception $e) {
            throw new ApiException('获取知识库文档详情失败: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * 批量禁用文档
     *
     * @param string $datasetId 知识库ID
     * @param array|string $documentIds 文档ID或ID数组
     * @return bool 是否成功
     * @throws ApiException
     */
    public function disableDocuments(string $datasetId, array|string $documentIds): bool
    {
        try {
            // 确保登录并获取令牌
            $token = $this->client->login();

            // 准备文档ID参数
            $documentId = is_array($documentIds) ? implode(',', $documentIds) : $documentIds;

            $response = $this->client->patch(
                "console/api/datasets/{$datasetId}/documents/status/disable/batch?document_id={$documentId}"
            );

            return $response['result'] === 'success';
        } catch (\Exception $e) {
            throw new ApiException('禁用文档失败: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * 批量启用文档
     *
     * @param string $datasetId 知识库ID
     * @param array|string $documentIds 文档ID或ID数组
     * @return bool 是否成功
     * @throws ApiException
     */
    public function enableDocuments(string $datasetId, array|string $documentIds): bool
    {
        try {
            // 确保登录并获取令牌
            $token = $this->client->login();

            // 准备文档ID参数
            $documentId = is_array($documentIds) ? implode(',', $documentIds) : $documentIds;

            $response = $this->client->patch(
                "console/api/datasets/{$datasetId}/documents/status/enable/batch?document_id={$documentId}"
            );

            return $response['result'] === 'success';
        } catch (\Exception $e) {
            throw new ApiException('启用文档失败: ' . $e->getMessage(), 0, $e);
        }
    }
}
