<?php

declare(strict_types=1);

namespace Happyphper\Dify\Responses;

class DocumentStatusResponse
{
    /**
     * @var DocumentStatus[]
     */
    public array $data;

    public function __construct(array $attributes)
    {
        $this->data = array_map(fn ($item) => new DocumentStatus($item), $attributes['data']);
    }
}
