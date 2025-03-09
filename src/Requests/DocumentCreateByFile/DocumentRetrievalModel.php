<?php

namespace Happyphper\Dify\Requests\DocumentCreateByFile;

class DocumentRetrievalModel
{
    public ?string $searchMethod = null;
    public ?bool $rerankingEnable = null;
    public ?array $rerankingModel = null;
    public ?int $topK = null;
    public ?bool $scoreThresholdEnabled = null;
    public ?float $scoreThreshold = null;

    public function toArray(): array
    {
        $data = [
            'search_method' => $this->searchMethod,
            'reranking_enable' => $this->rerankingEnable,
            'reranking_model' => $this->rerankingModel,
            'top_k' => $this->topK,
            'score_threshold_enabled' => $this->scoreThresholdEnabled,
            'score_threshold' => $this->scoreThreshold,
        ];

        return array_filter($data, fn($val) => !is_null($val));
    }
}
