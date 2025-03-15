<?php

namespace Happyphper\Dify\Public\Requests\DocumentCreateByFile;

use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;

/**
 * 从文本创建文档参数模型
 */
class DocumentFile
{
    public StreamInterface $content;
    public string $filename;
    public string $filepath;

    public function __construct(string $filepath, string $filename)
    {
        $this->filepath = $filepath;
        $this->filename = $filename;

        $f = fopen($filepath, 'r');
        $this->content = Utils::streamFor($f);
    }

    public function toArray(): array
    {
        return [
            'content' => $this->content,
            'filename' => $this->filename,
            'filepath' => $this->filepath,
        ];
    }
}
