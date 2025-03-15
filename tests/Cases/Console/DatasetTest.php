<?php

namespace Happyphper\Dify\Tests\Cases\Console;

use Happyphper\Dify\Exceptions\ApiException;

/**
 * 知识库 API 测试
 */
class DatasetTest extends TestCase
{
    /**
     * 测试获取知识库列表
     *
     * @return void
     * @throws ApiException
     */
    public function testGetDatasets(): void
    {
        // 获取知识库列表
        $datasets = $this->client->datasets()->getDatasets();
        
        // 断言返回的数据包含必要的字段
        $this->assertIsArray($datasets);
        $this->assertArrayHasKey('data', $datasets);
        $this->assertArrayHasKey('total', $datasets);
        $this->assertArrayHasKey('page', $datasets);
        $this->assertArrayHasKey('limit', $datasets);
        
        // 如果有知识库，测试第一个知识库的结构
        if (!empty($datasets['data'])) {
            $dataset = $datasets['data'][0];
            $this->assertArrayHasKey('id', $dataset);
            $this->assertArrayHasKey('name', $dataset);
            $this->assertArrayHasKey('description', $dataset);
            $this->assertArrayHasKey('created_at', $dataset);
            
            // 记录第一个知识库的ID，用于后续测试
            echo "\n[INFO] 第一个知识库ID: " . $dataset['id'] . "\n";
            echo "\n[INFO] 第一个知识库名称: " . $dataset['name'] . "\n";
            
            return;
        }
        
        $this->markTestSkipped('没有可用的知识库，跳过后续测试');
    }
    
    /**
     * 测试获取知识库详情
     *
     * @depends testGetDatasets
     * @return void
     * @throws ApiException
     */
    public function testGetDataset(): void
    {
        // 获取知识库列表
        $datasets = $this->client->datasets()->getDatasets();
        
        // 如果有知识库，测试获取第一个知识库的详情
        if (!empty($datasets['data'])) {
            $datasetId = $datasets['data'][0]['id'];
            
            // 获取知识库详情
            $dataset = $this->client->datasets()->getDataset($datasetId);
            
            // 断言返回的数据包含必要的字段
            $this->assertIsArray($dataset);
            $this->assertArrayHasKey('id', $dataset);
            $this->assertArrayHasKey('name', $dataset);
            $this->assertArrayHasKey('description', $dataset);
            $this->assertArrayHasKey('created_at', $dataset);
            
            echo "\n[INFO] 知识库详情: " . json_encode($dataset, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
            
            return;
        }
        
        $this->markTestSkipped('没有可用的知识库，跳过测试');
    }
    
    /**
     * 测试获取知识库文档列表
     *
     * @depends testGetDatasets
     * @return array|null 返回第一个文档的ID和所属知识库ID，用于后续测试
     * @throws ApiException
     */
    public function testGetDocuments(): ?array
    {
        // 获取知识库列表
        $datasets = $this->client->datasets()->getDatasets();
        
        // 如果有知识库，测试获取第一个知识库的文档列表
        if (!empty($datasets['data'])) {
            $datasetId = $datasets['data'][0]['id'];
            
            // 获取知识库文档列表
            $documents = $this->client->documents()->getDocuments($datasetId);
            
            // 断言返回的数据包含必要的字段
            $this->assertIsArray($documents);
            $this->assertArrayHasKey('data', $documents);
            $this->assertArrayHasKey('total', $documents);
            $this->assertArrayHasKey('page', $documents);
            $this->assertArrayHasKey('limit', $documents);
            
            echo "\n[INFO] 文档总数: " . $documents['total'] . "\n";
            
            // 如果有文档，测试第一个文档的结构
            if (!empty($documents['data'])) {
                $document = $documents['data'][0];
                $this->assertArrayHasKey('id', $document);
                $this->assertArrayHasKey('name', $document);
                $this->assertArrayHasKey('display_status', $document);
                $this->assertArrayHasKey('created_at', $document);
                
                echo "\n[INFO] 第一个文档ID: " . $document['id'] . "\n";
                echo "\n[INFO] 第一个文档名称: " . $document['name'] . "\n";
                echo "\n[INFO] 第一个文档状态: " . $document['display_status'] . "\n";
                
                // 返回第一个文档的ID和所属知识库ID，用于后续测试
                return [
                    'documentId' => $document['id'],
                    'datasetId' => $datasetId
                ];
            }
            
            $this->markTestSkipped('知识库中没有可用的文档，跳过后续测试');
            return null;
        }
        
        $this->markTestSkipped('没有可用的知识库，跳过测试');
        return null;
    }
    
    /**
     * 测试获取知识库文档详情
     *
     * @depends testGetDocuments
     * @param array|null $data 上一个测试返回的数据
     * @return void
     * @throws ApiException
     */
    public function testGetDocument(?array $data): void
    {
        if ($data === null) {
            $this->markTestSkipped('没有可用的文档，跳过测试');
            return;
        }
        
        $datasetId = $data['datasetId'];
        $documentId = $data['documentId'];
        
        // 获取知识库文档详情
        $document = $this->client->documents()->getDocument($datasetId, $documentId);
        
        // 断言返回的数据包含必要的字段
        $this->assertIsArray($document);
        $this->assertArrayHasKey('id', $document);
        $this->assertArrayHasKey('name', $document);
        $this->assertArrayHasKey('display_status', $document);
        $this->assertArrayHasKey('created_at', $document);
        
        echo "\n[INFO] 文档详情: " . json_encode($document, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
    }
    
    /**
     * 测试禁用和启用知识库文档
     *
     * @depends testGetDocuments
     * @param array|null $data 上一个测试返回的数据
     * @return void
     * @throws ApiException
     */
    public function testDisableAndEnableDocument(?array $data): void
    {
        if ($data === null) {
            $this->markTestSkipped('没有可用的文档，跳过测试');
            return;
        }
        
        $datasetId = $data['datasetId'];
        $documentId = $data['documentId'];
        
        // 获取文档当前状态
        $document = $this->client->documents()->getDocument($datasetId, $documentId);
        $originalStatus = $document['display_status'];
        
        echo "\n[INFO] 文档原始状态: " . $originalStatus . "\n";
        
        // 禁用文档
        $disableResult = $this->client->documents()->disableDocuments($datasetId, $documentId);
        $this->assertTrue($disableResult, '禁用文档失败');
        
        echo "\n[INFO] 禁用文档结果: " . ($disableResult ? '成功' : '失败') . "\n";
        
        // 获取文档状态，验证是否已禁用
        $document = $this->client->documents()->getDocument($datasetId, $documentId);
        $this->assertEquals('disabled', $document['display_status'], '文档未被成功禁用');
        
        echo "\n[INFO] 禁用后文档状态: " . $document['display_status'] . "\n";
        
        // 启用文档
        $enableResult = $this->client->documents()->enableDocuments($datasetId, $documentId);
        $this->assertTrue($enableResult, '启用文档失败');
        
        echo "\n[INFO] 启用文档结果: " . ($enableResult ? '成功' : '失败') . "\n";
        
        // 获取文档状态，验证是否已启用
        $document = $this->client->documents()->getDocument($datasetId, $documentId);
        $this->assertEquals('available', $document['display_status'], '文档未被成功启用');
        
        echo "\n[INFO] 启用后文档状态: " . $document['display_status'] . "\n";
        
        // 如果原始状态是禁用状态，则恢复原始状态
        if ($originalStatus === 'disabled') {
            try {
                $disableResult = $this->client->documents()->disableDocuments($datasetId, $documentId);
                $this->assertTrue($disableResult, '恢复原始状态失败');
                
                echo "\n[INFO] 恢复原始状态结果: " . ($disableResult ? '成功' : '失败') . "\n";
            } catch (\Exception $e) {
                echo "\n[WARN] 恢复原始状态失败，可能是文档正在被索引: " . $e->getMessage() . "\n";
                // 不将此视为测试失败，因为主要功能已经测试通过
            }
        }
    }
} 