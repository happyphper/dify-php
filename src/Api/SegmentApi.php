<?php

declare(strict_types=1);

namespace Happyphper\Dify\Api;

use Happyphper\Dify\Exception\DifyException;
use Happyphper\Dify\HttpClient;
use Happyphper\Dify\Model\Segment;
use Happyphper\Dify\Model\SegmentCollection;

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
    private $httpClient;

    /**
     * 初始化文档分段 API
     *
     * @param HttpClient $httpClient
     */
    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * 创建分段
     *
     * @param string $datasetId 数据集ID
     * @param string $documentId 文档ID
     * @param array $segments 分段数据
     * @return SegmentCollection
     */
    public function create(string $datasetId, string $documentId, array $segments): SegmentCollection
    {
        $response = $this->httpClient->post("datasets/{$datasetId}/documents/{$documentId}/segments", [
            'segments' => $segments
        ]);
        
        return new SegmentCollection($response['segments'] ?? []);
    }

    /**
     * 获取分段列表
     *
     * @param string $datasetId 数据集ID
     * @param string $documentId 文档ID
     * @param int $page 页码
     * @param int $limit 每页数量
     * @return array 包含分段集合和分页信息
     */
    public function list(string $datasetId, string $documentId, int $page = 1, int $limit = 20): array
    {
        $query = [
            'page' => $page,
            'limit' => $limit
        ];

        $response = $this->httpClient->get("datasets/{$datasetId}/documents/{$documentId}/segments", $query);
        
        $segments = new SegmentCollection($response['data'] ?? []);
        
        return [
            'data' => $segments,
            'has_more' => $response['has_more'] ?? false,
            'limit' => $response['limit'] ?? $limit,
            'total' => $response['total'] ?? count($segments),
            'page' => $response['page'] ?? $page,
        ];
    }

    /**
     * 更新分段
     *
     * @param string $datasetId 数据集ID
     * @param string $documentId 文档ID
     * @param string $segmentId 分段ID
     * @param array $data 更新数据
     * @return Segment
     */
    public function update(string $datasetId, string $documentId, string $segmentId, array $data): Segment
    {
        $response = $this->httpClient->put("datasets/{$datasetId}/documents/{$documentId}/segments/{$segmentId}", $data);
        return new Segment($response);
    }

    /**
     * 删除分段
     *
     * @param string $datasetId 数据集ID
     * @param string $documentId 文档ID
     * @param string $segmentId 分段ID
     * @return array
     */
    public function delete(string $datasetId, string $documentId, string $segmentId): array
    {
        return $this->httpClient->delete("datasets/{$datasetId}/documents/{$documentId}/segments/{$segmentId}");
    }
} 