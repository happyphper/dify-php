<?php

declare(strict_types=1);

namespace Happyphper\Dify\Public\Responses;

/**
 * 文档响应模型
 */
class DocumentCreateResponse
{
    public string $batch;
    public Document $document;

    public function __construct(array $attributes = [])
    {
        $this->batch = $attributes['batch'] ?? null;
        $this->document = new Document($attributes['document'] ?? null);
    }

    public function toArray(): array
    {
        return [
            'batch' => $this->batch,
            'document' => $this->document->toArray(),
        ];
    }
}
