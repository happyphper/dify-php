<?php

namespace Happyphper\Dify\Requests;

/**
 * 从文本创建文档参数模型
 */
class DocumentCreateByTextRequest
{
    public string $name;
    public string $text;
    public ?string $docType = null;
    public array $docMetadata = [];
    public string $indexingTechnique = 'economy';
    public string $docForm = 'text_model';
    public string $docLanguage = 'English';
    public array $processRule = [
        'mode' => 'automatic',
    ];
    public array $retrievalModel = [];
    public ?string $embeddingModel = null;
    public ?string $embeddingModelProvider = null;

    public function __construct(string $name, string $text)
    {
        $this->name = $name;
        $this->text = $text;
    }

    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'text' => $this->text,
            'doc_type' => $this->docType,
            'doc_metadata' => $this->docMetadata,
            'indexing_technique' => $this->indexingTechnique,
            'doc_form'=>$this->docForm,
            'doc_language'=>$this->docLanguage,
            'process_rule'=>$this->processRule,
            'retrieval_model'=>$this->retrievalModel,
            'embedding_model'=>$this->embeddingModel,
            'embedding_model_provider'=>$this->embeddingModelProvider,
        ];

        return array_filter($data);
    }
}
