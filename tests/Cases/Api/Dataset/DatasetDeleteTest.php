<?php

namespace Happyphper\Dify\Tests\Cases\Api\Dataset;

use Happyphper\Dify\Exceptions\ApiException;
use Happyphper\Dify\Exceptions\NotFoundException;
use Happyphper\Dify\Tests\Cases\Api\TestCase;

class DatasetDeleteTest extends TestCase
{
    /**
     * 测试删除知识库
     *
     * @group api
     * @throws ApiException
     */
    public function testDeleteDataset()
    {
        // 创建知识库
        $dataset = $this->createDataset();

        // 验证创建成功
        $this->assertNotNull($dataset);
        $this->assertNotEmpty($dataset->id);

        // 删除知识库
        $this->client->datasets()->delete($dataset->id);
    }

    /**
     * 测试删除不存在的知识库
     *
     * @group api
     * @throws ApiException
     */
    public function testDeleteNonExistentDataset()
    {
        try {
            $this->client->datasets()->delete('non-existent-dataset-id');
        } catch (NotFoundException $exception) {
            $this->assertTrue(true);
        }
    }
}
