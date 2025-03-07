<?php

declare(strict_types=1);

namespace Happyphper\Dify\Api;

use Happyphper\Dify\HttpClient;
use Happyphper\Dify\Model\Document;
use Happyphper\Dify\Model\DocumentCollection;

/**
 * Dify 文档 API
 */
class DocumentApi
{
    /**
     * HTTP 客户端
     *
     * @var HttpClient
     */
    private $httpClient;

    /**
     * 构造函数
     *
     * @param HttpClient $httpClient
     */
    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * 通过文本创建文档
     *
     * @param string $datasetId 数据集ID
     * @param string $text 文档文本内容
     * @param array $data 其他数据，如文档名称、文本分割器等
     * @return Document
     */
    public function createByText(string $datasetId, string $text, array $data = []): Document
    {
        $data['text'] = $text;
        $response = $this->httpClient->post("datasets/{$datasetId}/document/create-by-text", $data);
        return new Document($response['document'] ?? []);
    }

    /**
     * 通过文件创建文档
     *
     * @param string $datasetId 数据集ID
     * @param string $filePath 文件路径
     * @param array $data 其他数据，如文档名称、文本分割器等
     * @return Document
     */
    public function createByFile(string $datasetId, string $filePath, array $data = []): Document
    {
        // 确保文件存在
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("文件不存在: {$filePath}");
        }

        // 获取文件名
        $fileName = basename($filePath);
        
        // 确保文件名包含扩展名
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        if (empty($fileExtension)) {
            // 尝试根据MIME类型猜测扩展名
            $mimeType = mime_content_type($filePath);
            $mimeToExt = [
                'text/plain' => 'txt',
                'text/html' => 'html',
                'application/pdf' => 'pdf',
                'application/msword' => 'doc',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
                'application/vnd.ms-excel' => 'xls',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
                'application/vnd.ms-powerpoint' => 'ppt',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            ];
            $guessedExt = $mimeToExt[$mimeType] ?? 'txt';
            $fileName = $fileName . '.' . $guessedExt;
        }

        // 获取MIME类型
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';

        // 构建multipart数据
        $multipart = [];
        
        // 添加文件
        $multipart[] = [
            'name' => 'file',
            'contents' => fopen($filePath, 'r'),
            'filename' => isset($data['name']) ? $data['name'] : $fileName,
            'headers' => [
                'Content-Type' => $mimeType
            ]
        ];

        // 添加其他数据
        if (!isset($data['name'])) {
            $data['name'] = $fileName;
        }
        
        // 确保包含文本分割器配置
        if (!isset($data['text_splitter'])) {
            $data['text_splitter'] = [
                'type' => 'chunk',
                'chunk_size' => 1000,
                'chunk_overlap' => 200
            ];
        }
        
        // 添加索引技术
        if (!isset($data['indexing_technique'])) {
            $data['indexing_technique'] = 'high_quality';
        }
        
        $multipart[] = [
            'name' => 'data',
            'contents' => json_encode($data),
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ];

        $response = $this->httpClient->upload("datasets/{$datasetId}/document/create-by-file", $multipart);
        return new Document($response['document'] ?? []);
    }

    /**
     * 获取文档列表
     *
     * @param string $datasetId 数据集ID
     * @param string|null $keyword 搜索关键词
     * @param int $page 页码
     * @param int $limit 每页数量
     * @return array 包含文档集合和分页信息
     */
    public function list(string $datasetId, ?string $keyword = null, int $page = 1, int $limit = 20): array
    {
        $query = [
            'page' => $page,
            'limit' => $limit
        ];

        if ($keyword !== null) {
            $query['keyword'] = $keyword;
        }

        $response = $this->httpClient->get("datasets/{$datasetId}/documents", $query);
        
        $documents = new DocumentCollection($response['data'] ?? []);
        
        return [
            'data' => $documents,
            'has_more' => $response['has_more'] ?? false,
            'limit' => $response['limit'] ?? $limit,
            'total' => $response['total'] ?? count($documents),
            'page' => $response['page'] ?? $page,
        ];
    }

    /**
     * 删除文档
     *
     * @param string $datasetId 数据集ID
     * @param string $documentId 文档ID
     * @return array
     */
    public function delete(string $datasetId, string $documentId): array
    {
        return $this->httpClient->delete("datasets/{$datasetId}/documents/{$documentId}");
    }

    /**
     * 通过文本更新文档
     *
     * @param string $datasetId 数据集ID
     * @param string $documentId 文档ID
     * @param array $data 更新数据
     * @return Document
     */
    public function updateByText(string $datasetId, string $documentId, array $data): Document
    {
        $response = $this->httpClient->put("datasets/{$datasetId}/documents/{$documentId}/update-by-text", $data);
        return new Document($response['document'] ?? []);
    }

    /**
     * 通过文件更新文档
     *
     * @param string $datasetId 数据集ID
     * @param string $documentId 文档ID
     * @param string $filePath 文件路径
     * @param array $data 其他数据，如文档名称、文本分割器等
     * @return Document
     */
    public function updateByFile(string $datasetId, string $documentId, string $filePath, array $data = []): Document
    {
        // 确保文件存在
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("文件不存在: {$filePath}");
        }

        // 获取文件名
        $fileName = basename($filePath);
        
        // 确保文件名包含扩展名
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        if (empty($fileExtension)) {
            // 尝试根据MIME类型猜测扩展名
            $mimeType = mime_content_type($filePath);
            $mimeToExt = [
                'text/plain' => 'txt',
                'text/html' => 'html',
                'application/pdf' => 'pdf',
                'application/msword' => 'doc',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
                'application/vnd.ms-excel' => 'xls',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
                'application/vnd.ms-powerpoint' => 'ppt',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            ];
            $guessedExt = $mimeToExt[$mimeType] ?? 'txt';
            $fileName = $fileName . '.' . $guessedExt;
        }

        // 获取MIME类型
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';

        // 构建multipart数据
        $multipart = [];
        
        // 添加文件
        $multipart[] = [
            'name' => 'file',
            'contents' => fopen($filePath, 'r'),
            'filename' => isset($data['name']) ? $data['name'] : $fileName,
            'headers' => [
                'Content-Type' => $mimeType
            ]
        ];

        // 添加其他数据
        if (!isset($data['name'])) {
            $data['name'] = $fileName;
        }
        
        $multipart[] = [
            'name' => 'data',
            'contents' => json_encode($data),
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ];

        $response = $this->httpClient->upload("datasets/{$datasetId}/documents/{$documentId}/update-by-file", $multipart);
        return new Document($response['document'] ?? []);
    }

    /**
     * 获取文档索引状态
     *
     * @param string $datasetId 数据集ID
     * @param string $documentId 文档ID
     * @return Document
     */
    public function getIndexingStatus(string $datasetId, string $documentId): Document
    {
        $response = $this->httpClient->get("datasets/{$datasetId}/documents/{$documentId}/indexing-status");
        return new Document($response['document'] ?? []);
    }

    /**
     * 获取上传文件信息
     *
     * @param string $datasetId 数据集ID
     * @param string $documentId 文档ID
     * @return Document
     */
    public function getUploadFile(string $datasetId, string $documentId): Document
    {
        $response = $this->httpClient->get("datasets/{$datasetId}/documents/{$documentId}/upload-file");
        return new Document($response['document'] ?? []);
    }
} 