<?php

declare(strict_types=1);

namespace Happyphper\Dify\Public\Api;

use Happyphper\Dify\Exceptions\ApiException;
use Happyphper\Dify\Exceptions\NotFoundException;
use Happyphper\Dify\Public\PublicClient;
use Happyphper\Dify\Public\Requests\DocumentCreateByFile\DocumentCreateByFileRequest;
use Happyphper\Dify\Public\Requests\DocumentCreateByTextRequest;
use Happyphper\Dify\Public\Responses\DocumentCreateResponse;
use Happyphper\Dify\Public\Responses\DocumentListResponse;
use Happyphper\Dify\Public\Responses\DocumentStatusResponse;
use Happyphper\Dify\Public\Responses\Upload;
use Throwable;

/**
 * Dify 文档 API
 */
class DocumentApi
{
    /**
     * HTTP 客户端
     *
     * @var PublicClient
     */
    private PublicClient $client;

    /**
     * 构造函数
     *
     * @param PublicClient $client
     */
    public function __construct(PublicClient $client)
    {
        $this->client = $client;
    }

    /**
     * 创建文档（从文本）
     *
     * @param string $datasetId
     * @param DocumentCreateByTextRequest $params
     * @return DocumentCreateResponse
     * @throws ApiException
     */
    public function createFromText(string $datasetId, DocumentCreateByTextRequest $params): DocumentCreateResponse
    {
        $res = $this->client->post("datasets/$datasetId/document/create-by-text", $params->toArray());

        return new DocumentCreateResponse($res);
    }

    /**
     * 创建文档（从文件）
     *
     * @param string $datasetId
     * @param DocumentCreateByFileRequest $params
     * @return DocumentCreateResponse
     * @throws ApiException
     */
    public function createFromFile(string $datasetId, DocumentCreateByFileRequest $params): DocumentCreateResponse
    {
        $response = $this->client->upload(
            "datasets/$datasetId/document/create-by-file",
            $params->toArray(),
        );
        return new DocumentCreateResponse($response);
    }

    /**
     * 更新文档（从文本）
     *
     * @param string $datasetId
     * @param string $documentId
     * @param DocumentCreateByTextRequest $params
     * @return DocumentCreateResponse
     * @throws ApiException
     */
    public function updateByText(string $datasetId, string $documentId, DocumentCreateByTextRequest $params): DocumentCreateResponse
    {
        $response = $this->client->post(
            "datasets/$datasetId/documents/$documentId/update-by-text",
            $params->toArray(),
        );
        return new DocumentCreateResponse($response);
    }

    /**
     * 更新文档（从文件）
     *
     * @param string $datasetId
     * @param string $documentId
     * @param DocumentCreateByFileRequest $params
     * @return DocumentCreateResponse
     * @throws ApiException
     */
    public function updateByFile(string $datasetId, string $documentId, DocumentCreateByFileRequest $params): DocumentCreateResponse
    {
        $response = $this->client->upload(
            "datasets/$datasetId/documents/$documentId/update-by-file",
            $params->toArray(),
        );
        return new DocumentCreateResponse($response);
    }

    /**
     * 获取文档列表
     *
     * @param string $datasetId
     * @param int $page
     * @param int $limit
     * @param string|null $keyword
     * @return DocumentListResponse
     * @throws ApiException
     */
    public function list(string $datasetId, int $page = 1, int $limit = 20, string $keyword = null): DocumentListResponse
    {
        $response = $this->client->get("datasets/$datasetId/documents", array_filter(compact('page', 'limit', 'keyword')));

        return new DocumentListResponse($response);
    }

    /**
     * 删除文档
     *
     * @param string $datasetId
     * @param string $documentId
     * @return void
     * @throws ApiException
     */
    public function delete(string $datasetId, string $documentId): void
    {
        try {
            $this->client->delete("datasets/$datasetId/documents/$documentId");
        } catch (Throwable $exception) {
            if (strpos($exception->getMessage(), '404 NOT FOUND') > -1) {
                throw new NotFoundException();
            }
            throw new ApiException($exception->getMessage(), $exception->getCode());
        }

    }

    /**
     * 获取文档索引状态
     *
     * @param string $datasetId
     * @param string $batch
     * @return DocumentStatusResponse
     * @throws ApiException
     */
    public function getIndexingStatus(string $datasetId, string $batch): DocumentStatusResponse
    {
        $response = $this->client->get("datasets/$datasetId/documents/$batch/indexing-status");
        return new DocumentStatusResponse($response);
    }

    /**
     * 获取上传文件
     *
     * @param string $datasetId
     * @param string $documentId
     * @return Upload
     * @throws ApiException
     */
    public function getUploadFile(string $datasetId, string $documentId): Upload
    {
        $res = $this->client->get("datasets/$datasetId/documents/$documentId/file");

        return new  Upload($res);
    }
}
