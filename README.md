# Dify PHP SDK

A PHP SDK for Dify API, with support for the Hyperf framework.

## Installation

```bash
composer require happyphper/dify
```

## Usage with Hyperf

### Publish Configuration

```bash
php bin/hyperf.php vendor:publish happyphper/dify
```

This will create the following files:

- `config/autoload/dify.php` - Dify configuration file

### Configuration

Add the following to your `.env` file:

```
# Dify API Configuration
DIFY_API_KEY=your_api_key_here
DIFY_BASE_URL=https://api.dify.ai/v1
DIFY_DEBUG=false

# Dify Text Splitter Configuration
DIFY_TEXT_SPLITTER_TYPE=chunk
DIFY_TEXT_SPLITTER_CHUNK_SIZE=1000
DIFY_TEXT_SPLITTER_CHUNK_OVERLAP=200

# Dify Indexing Technique
DIFY_INDEXING_TECHNIQUE=high_quality
```

### Basic Usage

```php
<?php

use Happyphper\Dify\Client;

class YourController
{
    /**
     * @var Client
     */
    private $dify;

    public function __construct(Client $dify)
    {
        $this->dify = $dify;
    }

    public function index()
    {
        // Get dataset list
        $datasets = $this->dify->datasets()->list();
        
        // Create dataset
        $dataset = $this->dify->datasets()->create('Test Dataset', 'This is a test dataset');
        
        // Upload document
        $document = $this->dify->documents()->createByFile(
            $dataset['id'], 
            '/path/to/file.txt', 
            [
                'name' => 'test.txt',
                'text_splitter' => [
                    'type' => 'chunk',
                    'chunk_size' => 1000,
                    'chunk_overlap' => 200
                ],
                'indexing_technique' => 'high_quality'
            ]
        );
        
        // Get document list
        $documents = $this->dify->documents()->list($dataset['id']);
        
        // Delete document
        $this->dify->documents()->delete($dataset['id'], $document['id']);
        
        // Delete dataset
        $this->dify->datasets()->delete($dataset['id']);
        
        return [
            'datasets' => $datasets,
            'dataset' => $dataset,
            'document' => $document,
            'documents' => $documents
        ];
    }
}
```

## Usage without Hyperf

```php
<?php

use Happyphper\Dify\Client;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Create logger
$logger = new Logger('dify');
$logger->pushHandler(new StreamHandler('path/to/your.log', Logger::DEBUG));

// Create Dify client
$client = new Client(
    'your_api_key_here',
    'https://api.dify.ai/v1',
    true, // Enable debug mode
    $logger
);

// Get dataset list
$datasets = $client->datasets()->list();

// Create dataset
$dataset = $client->datasets()->create('Test Dataset', 'This is a test dataset');

// Upload document
$document = $client->documents()->createByFile(
    $dataset['id'], 
    '/path/to/file.txt', 
    [
        'name' => 'test.txt',
        'text_splitter' => [
            'type' => 'chunk',
            'chunk_size' => 1000,
            'chunk_overlap' => 200
        ],
        'indexing_technique' => 'high_quality'
    ]
);

// Get document list
$documents = $client->documents()->list($dataset['id']);

// Delete document
$client->documents()->delete($dataset['id'], $document['id']);

// Delete dataset
$client->datasets()->delete($dataset['id']);
```

## API Reference

### Knowledge Base Operations

```php
// Create knowledge base
$dataset = $client->datasets()->create('Test Knowledge Base', 'This is a test knowledge base');

// Get knowledge base list
$datasets = $client->datasets()->list(1, 20);

// Delete knowledge base
$client->datasets()->delete('dataset-id');

// Knowledge base retrieval
$result = $client->datasets()->retrieve('dataset-id', 'search keywords', [
    'search_method' => 'semantic_search',
    'top_k' => 5
]);
```

### Document Operations

```php
// Create document from text
$document = $client->documents()->createByText(
    'dataset-id',
    'Document Name',
    'Document Content',
    'high_quality',
    ['mode' => 'automatic']
);

// Create document from file
$document = $client->documents()->createByFile(
    'dataset-id',
    '/path/to/file.pdf',
    [
        'indexing_technique' => 'high_quality',
        'process_rule' => [
            'mode' => 'custom',
            'rules' => [
                'pre_processing_rules' => [
                    ['id' => 'remove_extra_spaces', 'enabled' => true],
                    ['id' => 'remove_urls_emails', 'enabled' => true]
                ],
                'segmentation' => [
                    'separator' => '###',
                    'max_tokens' => 500
                ]
            ]
        ]
    ]
);

// Get document list
$documents = $client->documents()->list('dataset-id');

// Update document by text
$document = $client->documents()->updateByText(
    'dataset-id',
    'document-id',
    [
        'name' => 'New Document Name',
        'text' => 'New Document Content'
    ]
);
```

### Segment Operations

```php
// Create segments
$segments = $client->segments()->create('dataset-id', 'document-id', [
    [
        'content' => 'Segment content',
        'answer' => 'Segment answer',
        'keywords' => ['keyword1', 'keyword2']
    ]
]);

// Get segment list
$segments = $client->segments()->list('dataset-id', 'document-id');

// Update segment
$segment = $client->segments()->update(
    'dataset-id',
    'document-id',
    'segment-id',
    [
        'content' => 'New segment content',
        'answer' => 'New segment answer',
        'keywords' => ['new keyword']
    ]
);
```

## Error Handling

All API calls may throw a `DifyException`. You can catch it to handle errors:

```php
use Happyphper\Dify\Exception\DifyException;

try {
    $datasets = $client->datasets()->list();
} catch (DifyException $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    echo 'Status Code: ' . $e->getStatusCode() . "\n";
    echo 'Error Code: ' . $e->getErrorCode() . "\n";
}
```

## Advanced Usage

### Handling Large Amounts of Data

When dealing with large amounts of data, it's recommended to use pagination and appropriate error retry mechanisms:

```php
$page = 1;
$limit = 20;
$allDocuments = [];

do {
    $response = $client->documents()->list('dataset-id', null, $page, $limit);
    $documents = $response['data'] ?? [];
    $allDocuments = array_merge($allDocuments, $documents);
    $page++;
} while (!empty($documents) && $response['has_more'] ?? false);
```

### Troubleshooting

#### Common Issues

1. **Invalid API Key**: Ensure your API key is correct and has the required permissions.
   
2. **Network Connection Issues**: Check your network connection and firewall settings.
   
3. **Request Format Errors**: Ensure the request parameters are in the correct format.

#### Enable Debugging

If you encounter issues, you can implement logging logic to capture request and response information to help debug problems.

## Complete API Documentation

For complete documentation of the Dify API, please refer to the [Dify API Documentation](https://docs.dify.ai/).

## License

MIT