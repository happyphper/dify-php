<?php

namespace Happyphper\Dify\Tests\Cases\Api\Document;

use Happyphper\Dify\Exceptions\ApiException;
use Happyphper\Dify\Public\Requests\DocumentCreateByTextRequest;
use Happyphper\Dify\Tests\Cases\Api\TestCase;

class DocumentCreateByTextTest extends TestCase
{
    /**
     * 测试使用基本参数从文本创建文档
     * @throws ApiException
     */
    public function testCreateWithBasicParams()
    {
        $dataset = $this->createDataset();

        // 创建文档参数
        $params = new DocumentCreateByTextRequest('Test Document ' . time(), '这是一个测试文档内容，用于测试从文本创建文档功能。');

        // 创建文档
        $res = $this->client->documents()->createFromText($dataset->id, $params);

        // 断言
        $this->assertNotNull($res);
        $this->assertNotNull($res->document->id);

        // 清理知识库
        $this->deleteDataset($dataset->id);
    }

    /**
     * 测试使用高质量索引技术从文本创建文档
     * @throws ApiException
     */
    public function testCreateWithHighQualityIndexing()
    {
        $dataset = $this->createDataset();

        // 创建文档参数
        $params = new DocumentCreateByTextRequest('High Quality Document ' . time(), '这是一个使用高质量索引技术的测试文档内容。');
        $params->indexingTechnique = 'high_quality';
        $params->embeddingModel = 'nomic-embed-text';
        $params->embeddingModelProvider = 'ollama';

        // 创建文档
        $res = $this->client->documents()->createFromText($dataset->id, $params);

        // 断言
        $this->assertNotNull($res->document);
        $this->assertNotNull($res->document->id);

        // 清理知识库
        $this->deleteDataset($dataset->id);
    }
}
