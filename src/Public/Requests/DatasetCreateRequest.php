<?php

declare(strict_types=1);

namespace Happyphper\Dify\Public\Requests;

class DatasetCreateRequest
{
    public string $name;
    public ?string $description = null;
    public string $indexingTechnique = 'economy';
    public string $permission = 'only_me';
    public string $provider = 'vendor';
    public ?string $externalKnowledgeApiId = null;
    public ?string $externalKnowledgeId = null;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'indexing_technique' => $this->indexingTechnique,
            'permission' => $this->permission,
            'provider' => $this->provider,
            'external_knowledge_api_id' => $this->externalKnowledgeApiId,
            'external_knowledge_id' => $this->externalKnowledgeId,
        ], fn($value) => $value !== null);
    }
}
