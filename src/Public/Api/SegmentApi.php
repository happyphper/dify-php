<?php

declare(strict_types=1);

namespace Happyphper\Dify\Public\Api;

use Happyphper\Dify\Exceptions\ApiException;
use Happyphper\Dify\Public\PublicClient;
use Happyphper\Dify\Public\Requests\SegmentCreateRequest;
use Happyphper\Dify\Public\Requests\SegmentUpdateRequest;
use Happyphper\Dify\Public\Responses\SegmentListResponse;
use Happyphper\Dify\Public\Responses\SegmentResponse;

/**
 * 文档分段 API 类
 */
class SegmentApi
{
    /**
     * HTTP 客户端
     *
     * @var PublicClient
     */
    private PublicClient $client;

    /**
     * 构造函数
     *
     * @param PublicClient $client
     */
    public function __construct(PublicClient $client)
    {
        $this->client = $client;
    }

    /**
     * 创建分段
     *
     * @param string $datasetId 知识库 ID
     * @param string $documentId 文档 ID
     * @param SegmentCreateRequest[] $segments 分段列表
     * @return SegmentResponse
     * @throws ApiException
     */
    public function create(string $datasetId, string $documentId, array $segments): SegmentResponse
    {
        $segmentData = array_map(function (SegmentCreateRequest $segment) {
            return $segment->toArray();
        }, $segments);

        $response = $this->client->post("datasets/$datasetId/documents/$documentId/segments", [
            'segments' => $segmentData
        ]);

        return new SegmentResponse($response);
    }

    /**
     * 获取分段列表
     *
     * @param string $datasetId 知识库 ID
     * @param string $documentId 文档 ID
     * @param string|null $keyword 搜索关键词
     * @param string|null $status 搜索状态
     * @return SegmentListResponse
     * @throws ApiException
     */
    public function list(string $datasetId, string $documentId, ?string $keyword = null, ?string $status = null): SegmentListResponse
    {
        $query = array_filter([
            'keyword' => $keyword,
            'status' => $status,
        ]);

        $response = $this->client->get("datasets/$datasetId/documents/$documentId/segments", $query);

        return new SegmentListResponse($response);
    }

    /**
     * 更新分段
     *
     * @param string $datasetId 知识库 ID
     * @param string $documentId 文档 ID
     * @param string $segmentId 分段 ID
     * @param SegmentUpdateRequest $request 更新请求
     * @return SegmentResponse
     * @throws ApiException
     */
    public function update(string $datasetId, string $documentId, string $segmentId, SegmentUpdateRequest $request): SegmentResponse
    {
        $data = $request->toArray();

        $response = $this->client->post("datasets/$datasetId/documents/$documentId/segments/$segmentId", [
            'segment' => $data
        ]);

        return new SegmentResponse($response);
    }

    /**
     * 删除分段
     *
     * @param string $datasetId 知识库 ID
     * @param string $documentId 文档 ID
     * @param string $segmentId 分段 ID
     * @return bool
     * @throws ApiException
     */
    public function delete(string $datasetId, string $documentId, string $segmentId): bool
    {
        $response = $this->client->delete("datasets/$datasetId/documents/$documentId/segments/$segmentId");

        return $response['result'] === 'success';
    }
}
