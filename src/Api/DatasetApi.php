<?php

declare(strict_types=1);

namespace Happyphper\Dify\Api;

use Happyphper\Dify\Exception\DifyException;
use Happyphper\Dify\HttpClient;
use Happyphper\Dify\Model\Dataset;
use Happyphper\Dify\Model\DatasetCollection;

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
    private $httpClient;

    /**
     * 初始化知识库 API
     *
     * @param HttpClient $httpClient
     */
    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * 创建数据集
     *
     * @param string $name 数据集名称
     * @param string $description 数据集描述
     * @param string $permission 数据集权限，可选值：only_me, all_team_members
     * @param string $indexingTechnique 索引技术，可选值：high_quality, economy
     * @return Dataset
     */
    public function create(string $name, string $description = '', string $permission = 'only_me', string $indexingTechnique = 'high_quality'): Dataset
    {
        $data = [
            'name' => $name,
            'description' => $description,
            'permission' => $permission,
            'indexing_technique' => $indexingTechnique,
        ];

        $response = $this->httpClient->post('datasets', $data);
        return new Dataset($response);
    }

    /**
     * 获取数据集列表
     *
     * @param int $page 页码
     * @param int $limit 每页数量
     * @return array 包含数据集集合和分页信息
     */
    public function list(int $page = 1, int $limit = 20): array
    {
        $query = [
            'page' => $page,
            'limit' => $limit,
        ];

        $response = $this->httpClient->get('datasets', $query);
        
        $datasets = new DatasetCollection($response['data'] ?? []);
        
        return [
            'data' => $datasets,
            'has_more' => $response['has_more'] ?? false,
            'limit' => $response['limit'] ?? $limit,
            'total' => $response['total'] ?? count($datasets),
            'page' => $response['page'] ?? $page,
        ];
    }

    /**
     * 获取数据集详情
     *
     * @param string $datasetId 数据集ID
     * @return Dataset
     */
    public function get(string $datasetId): Dataset
    {
        $response = $this->httpClient->get("datasets/{$datasetId}");
        return new Dataset($response);
    }

    /**
     * 更新数据集
     *
     * @param string $datasetId 数据集ID
     * @param array $data 更新数据
     * @return Dataset
     */
    public function update(string $datasetId, array $data): Dataset
    {
        $response = $this->httpClient->put("datasets/{$datasetId}", $data);
        return new Dataset($response);
    }

    /**
     * 删除数据集
     *
     * @param string $datasetId 数据集ID
     * @return array
     */
    public function delete(string $datasetId): array
    {
        return $this->httpClient->delete("datasets/{$datasetId}");
    }

    /**
     * 数据集检索
     *
     * @param string $datasetId 数据集ID
     * @param string $query 查询内容
     * @param array $options 检索选项
     * @return array
     */
    public function retrieve(string $datasetId, string $query, array $options = []): array
    {
        $data = array_merge([
            'query' => $query,
        ], $options);

        return $this->httpClient->post("datasets/{$datasetId}/retrieve", $data);
    }
} 