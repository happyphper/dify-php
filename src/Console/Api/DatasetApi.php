<?php

declare(strict_types=1);

namespace Happyphper\Dify\Console\Api;

use Happyphper\Dify\Console\ConsoleClient;
use Happyphper\Dify\Exceptions\ApiException;

/**
 * Dify 知识库控制台 API
 */
class DatasetApi
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
     * 获取知识库列表
     *
     * @param int $page 页码，从1开始
     * @param int $limit 每页数量，默认20
     * @return array 知识库列表数据
     * @throws ApiException
     */
    public function getDatasets(int $page = 1, int $limit = 20): array
    {
        try {
            // 确保登录并获取令牌
            $token = $this->client->login();

            $response = $this->client->get(
                "console/api/datasets?page={$page}&limit={$limit}"
            );

            return $response;
        } catch (\Exception $e) {
            throw new ApiException('获取知识库列表失败: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * 获取知识库详情
     *
     * @param string $datasetId 知识库ID
     * @return array 知识库详情数据
     * @throws ApiException
     */
    public function getDataset(string $datasetId): array
    {
        try {
            // 确保登录并获取令牌
            $token = $this->client->login();

            $response = $this->client->get(
                "console/api/datasets/{$datasetId}"
            );

            return $response;
        } catch (\Exception $e) {
            throw new ApiException('获取知识库详情失败: ' . $e->getMessage(), 0, $e);
        }
    }
} 