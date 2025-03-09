<?php

declare(strict_types=1);

namespace Happyphper\Dify\Responses;

use Happyphper\Dify\Support\Paginator;

class DocumentListResponse
{
    public Paginator $paginator;

    public array $data;

    public function __construct(array $attributes)
    {
        $this->paginator = new Paginator($attributes);
        $this->data = array_map(fn ($item) => new Document($item), $attributes['data']);
    }
}
