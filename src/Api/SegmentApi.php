<?php

declare(strict_types=1);

namespace Happyphper\Dify\Api;

use Happyphper\Dify\Exceptions\ApiException;
use Happyphper\Dify\HttpClient;
use Happyphper\Dify\Responses\Segment;
use Happyphper\Dify\Responses\SegmentCollection;
use Happyphper\Dify\Support\Paginator;

/**
 * 文档分段 API 类
 */
class SegmentApi
{
    /**
     * HTTP 客户端
     *
     * @var HttpClient
     */
    private HttpClient $client;

    /**
     * 构造函数
     *
     * @param HttpClient $client
     */
    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * 创建分段
     *
     * @param string $datasetId
     * @param string $documentId
     * @param array $segments
     * @return SegmentCollection
     * @throws ApiException
     */
    public function create(string $datasetId, string $documentId, array $segments): SegmentCollection
    {
        $response = $this->client->post("datasets/{$datasetId}/documents/{$documentId}/segments", [
            'segments' => $segments
        ]);
        
        return new SegmentCollection($response['data'] ?? []);
    }

    /**
     * 获取分段列表
     *
     * @param string $datasetId
     * @param string $documentId
     * @param int $page
     * @param int $limit
     * @return SegmentCollection
     * @throws ApiException
     */
    public function list(string $datasetId, string $documentId, int $page = 1, int $limit = 20): SegmentCollection
    {
        $query = [
            'page' => $page,
            'limit' => $limit
        ];

        $response = $this->client->get("datasets/{$datasetId}/documents/{$documentId}/segments", $query);
        
        $paginator = new Paginator(
            $response['page'] ?? $page,
            $response['limit'] ?? $limit,
            $response['total'] ?? 0,
            $response['has_more'] ?? false
        );
        
        return new SegmentCollection($response['data'] ?? [], $paginator);
    }

    /**
     * 更新分段
     *
     * @param string $datasetId
     * @param string $documentId
     * @param string $segmentId
     * @param array $data
     * @return Segment
     * @throws ApiException
     */
    public function update(string $datasetId, string $documentId, string $segmentId, array $data): Segment
    {
        $response = $this->client->post("datasets/{$datasetId}/documents/{$documentId}/segments/{$segmentId}", [
            'segment' => $data
        ]);
        
        return new Segment($response['data'][0] ?? []);
    }

    /**
     * 删除分段
     *
     * @param string $datasetId
     * @param string $documentId
     * @param string $segmentId
     * @return void
     * @throws ApiException
     */
    public function delete(string $datasetId, string $documentId, string $segmentId): void
    {
        $this->client->delete("datasets/{$datasetId}/documents/{$documentId}/segments/{$segmentId}");
    }
} 