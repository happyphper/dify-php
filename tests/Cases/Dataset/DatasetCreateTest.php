<?php

namespace Happyphper\Dify\Tests\Cases\Dataset;

use Happyphper\Dify\Exceptions\ApiException;
use Happyphper\Dify\Requests\DatasetCreateRequest;
use Happyphper\Dify\Tests\Cases\TestCase;

class DatasetCreateTest extends TestCase
{
    /**
     * 测试使用基本参数创建知识库
     *
     * @group api
     * @throws ApiException
     */
    public function testCreateWithBasicParams()
    {
        // 创建知识库参数
        $datasetName = 'TEST_基本参数知识库-' . date('YmdHis');
        $description = '这是一个使用基本参数创建的知识库';
        $permission = 'only_me';
        $provider = 'vendor';

        $params = new DatasetCreateRequest($datasetName);
        $params->description = $description;
        $params->permission = $permission;
        $params->provider = $provider;

        // 创建知识库
        $dataset = $this->client->datasets()->create($params);

        // 验证创建结果
        $this->assertNotNull($dataset);
        $this->assertNotEmpty($dataset->id);
        $this->assertEquals($datasetName, $dataset->name);
        $this->assertEquals($description, $dataset->description);
        $this->assertEquals($permission, $dataset->permission);
        $this->assertEquals('vendor', $dataset->provider);
    }

    /**
     * 测试使用高质量索引技术参数创建知识库
     *
     * @group api
     * @throws ApiException
     */
    public function testCreateWithHighQualityIndexing()
    {
        // 创建知识库参数
        $datasetName = 'TEST_高质量索引知识库-' . date('YmdHis');
        $description = '这是一个使用高质量索引技术创建的知识库';
        $indexingTechnique = 'high_quality';


        $params = new DatasetCreateRequest($datasetName);
        $params->description = $description;
        $params->indexingTechnique = $indexingTechnique;

        // 创建知识库
        $dataset = $this->client->datasets()->create($params);

        // 验证创建结果
        $this->assertNotNull($dataset);
        $this->assertNotEmpty($dataset->id);
        $this->assertEquals($datasetName, $dataset->name);
        $this->assertEquals($description, $dataset->description);
        $this->assertEquals($indexingTechnique, $dataset->indexingTechnique);
    }
}
