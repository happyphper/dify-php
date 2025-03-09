<?php

declare(strict_types=1);

namespace Happyphper\Dify\Api;

use Happyphper\Dify\Exceptions\ApiException;
use Happyphper\Dify\Exceptions\NotFoundException;
use Happyphper\Dify\HttpClient;
use Happyphper\Dify\Requests\DatasetCreateRequest;
use Happyphper\Dify\Responses\Dataset;
use Happyphper\Dify\Responses\DatasetListResponse;
use Throwable;

/**
 * 知识库 API 类
 */
class DatasetApi
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
     * 获取数据集列表
     *
     * @param int $page
     * @param int $limit
     * @return DatasetListResponse
     * @throws ApiException
     */
    public function list(int $page = 1, int $limit = 20): DatasetListResponse
    {
        $response = $this->client->get('/datasets', compact('page', 'limit'));

        return new DatasetListResponse($response);
    }

    /**
     * 创建数据集
     *
     * @param DatasetCreateRequest $params
     * @return Dataset
     * @throws ApiException
     */
    public function create(DatasetCreateRequest $params): Dataset
    {
        $response = $this->client->post('/datasets', $params->toArray());

        return new Dataset($response);
    }

    /**
     * 删除数据集
     *
     * @param string $id
     * @return void
     * @throws ApiException
     */
    public function delete(string $id): void
    {
        try {
            $this->client->delete("/datasets/$id");
        } catch (Throwable $exception) {
            if (strpos($exception->getMessage(), '404 NOT FOUND') > -1) {
                throw new NotFoundException();
            }
            throw new ApiException($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * 数据集检索
     *
     * @param string $datasetId 数据集ID
     * @param string $query 查询内容
     * @param array|null $retrievalModel
     * @param string|null $externalRetrievalModel
     * @return array
     * @throws ApiException
     */
    public function retrieve(string $datasetId, string $query, ?array $retrievalModel, ?string $externalRetrievalModel): array
    {
        $data = [
            'query' => $query,
            'retrieval_model' => $retrievalModel,
            'external_retrieval_model' => $externalRetrievalModel,
        ];
        return $this->client->post("/datasets/$datasetId/retrieve", array_filter($data));
    }
}
