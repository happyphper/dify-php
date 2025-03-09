<?php

namespace Happyphper\Dify\Requests\DocumentCreateByFile;

use GuzzleHttp\Psr7\Utils;

/**
 * 从文件创建文档参数模型
 */
class DocumentCreateByFileRequest
{
    public DocumentData $data;
    public DocumentFile $file;
    public ?DocumentRetrievalModel $retrievalModel = null;
    public ?string $embeddingModel = null;
    public ?string $embeddingModelProvider = null;

    public function __construct(DocumentData $data, DocumentFile $file, ?DocumentRetrievalModel $retrievalModel = null, ?string $embeddingModel = null, ?string $embeddingModelProvider = null)
    {
        $this->data = $data;
        $this->file = $file;
        $this->retrievalModel = $retrievalModel;
        $this->embeddingModel = $embeddingModel;
        $this->embeddingModelProvider = $embeddingModelProvider;
    }

    public function toArray(): array
    {
        $multipart = [];

        // 添加 JSON 数据
        $multipart[] = [
            'name' => 'data',
            'contents' => json_encode($this->data->toArray()),
        ];

        // 处理文件上传，确保文件是流
        $multipart[] = [
            'name' => 'file',
            'contents' => $this->file->content,
            'filename' => $this->file->filename,
        ];

        // 只有当 retrievalModel 存在时，才加入请求
        if ($this->retrievalModel) {
            $multipart[] = [
                'name' => 'retrieval_model',
                'contents' => json_encode($this->retrievalModel->toArray()),
            ];
        }

        // 只有当 embeddingModel 存在时，才加入请求
        if ($this->embeddingModel) {
            $multipart[] = [
                'name' => 'embedding_model',
                'contents' => $this->embeddingModel,
            ];
        }

        // 只有当 embeddingModelProvider 存在时，才加入请求
        if ($this->embeddingModelProvider) {
            $multipart[] = [
                'name' => 'embedding_model_provider',
                'contents' => $this->embeddingModelProvider,
            ];
        }

        return $multipart;
    }
}
