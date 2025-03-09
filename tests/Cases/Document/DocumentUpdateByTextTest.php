<?php

namespace Happyphper\Dify\Tests\Cases\Document;

use Happyphper\Dify\Exceptions\ApiException;
use Happyphper\Dify\Requests\DocumentCreateByTextRequest;
use Happyphper\Dify\Tests\Cases\TestCase;

class DocumentUpdateByTextTest extends TestCase
{
    /**
     * 测试使用基本参数从文本创建文档
     * @throws ApiException
     */
    public function testUpdateWithBasicParams()
    {
        $dataset = $this->createDataset();

        $res = $this->createDocumentByTex($dataset->id);
        $document = $res->document;

        // 等待索引完成
        sleep(5);

        // 创建文档参数
        $params = new DocumentCreateByTextRequest('Test Document ' . time(), '这是一个测试文档内容，用于测试从文本创建文档功能。');

        // 创建文档
        $res = $this->client->documents()->updateByText($dataset->id, $document->id, $params);

        // 断言
        $this->assertNotNull($res);
        $this->assertNotNull($res->document->id);

        // 清理知识库
        $this->deleteDocument($dataset->id, $document->id);
        $this->deleteDataset($dataset->id);
    }
}
