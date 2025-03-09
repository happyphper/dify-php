<?php

namespace Happyphper\Dify\Tests\Cases\Document;

use Happyphper\Dify\Exceptions\ApiException;
use Happyphper\Dify\Responses\DocumentListResponse;
use Happyphper\Dify\Tests\Cases\TestCase;

/**
 * 文档列表测试类
 */
class DocumentListTest extends TestCase
{
    /**
     * 测试获取空数据集的文档列表
     * @throws ApiException
     */
    public function testListEmptyDocument()
    {
        $dataset = $this->createDataset();

        // 获取文档列表
        $res = $this->client->documents()->list($dataset->id, 1, 1);

        // 断言
        $this->assertInstanceOf(DocumentListResponse::class, $res);
        $this->assertCount(0, $res->data);
    }

    /**
     * 测试获取文档列表的分页功能
     * @throws ApiException
     */
    public function testListPagination()
    {
        $dataset = $this->createDataset();

        $docRes = $this->createDocumentByTex($dataset->id);

        // 获取文档列表
        $listRes = $this->client->documents()->list($dataset->id, 1, 1);

        // 断言
        $this->assertInstanceOf(DocumentListResponse::class, $listRes);
        $this->assertEquals(1, $listRes->paginator->page);
        $this->assertEquals(1, $listRes->paginator->limit);
        $this->assertEquals(1, $listRes->paginator->total);
        $this->assertTrue($listRes->paginator->hasMore);

        // 清理
        $this->deleteDocument($dataset->id, $docRes->document->id);
        $this->deleteDataset($dataset->id);
    }

    /**
     * 测试搜索文档
     *
     * @throws ApiException
     */
    public function testSearchNotHit()
    {
        $dataset = $this->createDataset();

        $docRes = $this->createDocumentByTex($dataset->id);

        // 获取文档列表
        $listRes = $this->client->documents()->list($dataset->id, 1, 1, 'not exists');

        // 断言
        $this->assertInstanceOf(DocumentListResponse::class, $listRes);
        $this->assertEquals(1, $listRes->paginator->page);
        $this->assertEquals(1, $listRes->paginator->limit);
        $this->assertEquals(0, $listRes->paginator->total);
        $this->assertFalse($listRes->paginator->hasMore);

        // 清理
        $this->deleteDocument($dataset->id, $docRes->document->id);
        $this->deleteDataset($dataset->id);
    }

    /**
     * 测试搜索文档
     *
     * @throws ApiException
     */
    public function testSearchHit()
    {
        $dataset = $this->createDataset();

        $docRes = $this->createDocumentByTex($dataset->id);

        // 获取文档列表
        $listRes = $this->client->documents()->list($dataset->id, 1, 1, 'Test');

        // 断言
        $this->assertInstanceOf(DocumentListResponse::class, $listRes);
        $this->assertEquals(1, $listRes->paginator->page);
        $this->assertEquals(1, $listRes->paginator->limit);
        $this->assertEquals(1, $listRes->paginator->total);
        $this->assertTrue($listRes->paginator->hasMore);

        // 清理
        $this->deleteDocument($dataset->id, $docRes->document->id);
        $this->deleteDataset($dataset->id);
    }
}
