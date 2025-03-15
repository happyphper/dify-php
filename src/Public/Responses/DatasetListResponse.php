<?php

declare(strict_types=1);

namespace Happyphper\Dify\Public\Responses;

use Happyphper\Dify\Support\Paginator;

class DatasetListResponse
{
    public Paginator $paginator;

    public array $data;

    public function __construct(array $attributes)
    {
        $this->paginator = new Paginator($attributes);
        $this->data = array_map(fn ($item) => new Dataset($item), $attributes['data']);
    }
}
