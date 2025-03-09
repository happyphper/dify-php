<?php

namespace Happyphper\Dify\Tests\Cases\Document;

use Happyphper\Dify\Exceptions\ApiException;
use Happyphper\Dify\Requests\DocumentCreateByFile\DocumentCreateByFileRequest;
use Happyphper\Dify\Requests\DocumentCreateByFile\DocumentData;
use Happyphper\Dify\Requests\DocumentCreateByFile\DocumentFile;
use Happyphper\Dify\Tests\Cases\TestCase;

class DocumentCreateByFileTest extends TestCase
{
    /**
     * 测试使用基本参数从文本创建文档
     * @throws ApiException
     */
    public function testCreateWithBasicParams()
    {
        $dataset = $this->createDataset();

        // 数据
        $data = new DocumentData();

        // 文件
        $file = new DocumentFile($this->filepath(), $this->filename());

        // 创建文档参数
        $params = new DocumentCreateByFileRequest(data: $data, file: $file);

        // 创建文档
        $res = $this->client->documents()->createFromFile($dataset->id, $params);

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

        // 数据
        $data = new DocumentData();
        $data->indexingTechnique = 'high_quality';

        // 文件
        $file = new DocumentFile($this->filepath(), '高质量索引技术' . $this->filename());

        // 创建文档参数
        $params = new DocumentCreateByFileRequest(data: $data, file: $file, embeddingModel: 'nomic-embed-text', embeddingModelProvider: 'ollama');

        // 创建文档
        $res = $this->client->documents()->createFromFile($dataset->id, $params);

        // 断言
        $this->assertNotNull($res->document);
        $this->assertNotNull($res->document->id);

        // 清理知识库
        $this->deleteDataset($dataset->id);
    }
}
