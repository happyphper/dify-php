<?php

namespace Happyphper\Dify\Tests\Cases\Api\Document;

use Happyphper\Dify\Exceptions\ApiException;
use Happyphper\Dify\Exceptions\NotFoundException;
use Happyphper\Dify\Tests\Cases\Api\TestCase;

class DocumentDeleteTest extends TestCase
{
    /**
     * 测试正常删除文档
     * @throws ApiException
     */
    public function testDeleteDocument()
    {
        // 创建数据集
        $dataset = $this->createDataset();

        // 创建文档
        $docRes = $this->createDocumentByTex($dataset->id);

        // 删除文档
        $this->client->documents()->delete($dataset->id, $docRes->document->id);

        // 验证删除成功
        $this->assertTrue(true);

        // 清理数据集
        $this->deleteDataset($dataset->id);
    }

    /**
     * 测试删除不存在的文档
     * @throws ApiException
     */
    public function testDeleteNonExistentDocument()
    {
        // 创建数据集
        $dataset = $this->createDataset();

        // 期望抛出 NotFoundException
        $this->expectException(NotFoundException::class);

        // 尝试删除不存在的文档
        $this->client->documents()->delete($dataset->id, 'non_existent_document_id');

        // 清理数据集
        $this->deleteDataset($dataset->id);
    }
}
