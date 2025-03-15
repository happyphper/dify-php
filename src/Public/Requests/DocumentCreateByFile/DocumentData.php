<?php

namespace Happyphper\Dify\Public\Requests\DocumentCreateByFile;

class DocumentData
{
    public ?string $originalDocumentId = null;
    public string $indexingTechnique = 'economy';
    public string $docForm = 'text_model';
    public ?string $docType = null;
    public array $docMetadata = [];
    public string $docLanguage = 'English';
    public array $processRule = [
        'mode' => 'automatic',
    ];

    public function toArray(): array
    {
        $data = [
            'original_document_id' => $this->originalDocumentId,
            'indexing_technique' => $this->indexingTechnique,
            'doc_form' => $this->docForm,
            'doc_type' => $this->docType,
            'doc_metadata' => $this->docMetadata,
            'doc_language' => $this->docLanguage,
            'process_rule' => $this->processRule,
        ];

        return array_filter($data);
    }
}
