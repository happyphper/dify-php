<?php

namespace Happyphper\Dify\Requests\DocumentCreateByFile;

use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;

/**
 * 从文本创建文档参数模型
 */
class DocumentFile
{
    public StreamInterface $content;
    public string $filename;

    public function __construct(string $filepath, string $filename)
    {
        $f = fopen($filepath, 'r');

        $this->content = Utils::streamFor($f);
        $this->filename = $filename;
    }

    public function toArray(): array
    {
        return [
            'content' => $this->content,
            'filename' => $this->filename,
        ];
    }
}
