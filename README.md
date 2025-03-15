# Dify PHP SDK

A PHP SDK for Dify API, with support for the Hyperf framework.

[中文文档](README-CN.md)

## Requirements

- PHP >= 8.1
- Composer
- ext-fileinfo

## Latest Updates (v1.2.0)

- Added token auto-refresh functionality for seamless handling of authentication token expiration
- Optimized error handling mechanism in `ConsoleClient`
- Added `clearRefreshToken` method to clear only the refresh token
- Fixed error handling in paragraph update and delete operations
- Added support for regenerating sub-paragraphs
- Improved error handling mechanism
- Enhanced test coverage

## Token Auto-Refresh Feature

Dify PHP SDK now supports token auto-refresh functionality, allowing your application to seamlessly handle authentication token expiration:

- **Automatic Token Refresh**: When an access token expires, the SDK automatically attempts to obtain a new access token using the refresh token
- **Automatic Request Retry**: After successful token refresh, the original request is automatically retried without manual intervention
- **Automatic Re-login**: If the refresh token has also expired, the SDK attempts to re-login using the configured credentials
- **Intelligent Token Management**: Only clears the refresh token upon refresh failure, preserving valid access tokens

These features allow your application to focus on business logic without worrying about the complexity of token management.

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
DIFY_DATASET_KEY=your_api_key_here        # Your Dify API key for dataset operations
DIFY_BASE_URL=https://api.dify.ai/v1      # Base URL for Dify API
DIFY_DEBUG=false                          # Enable/disable debug mode

# Console API Configuration (for operations requiring login)
DIFY_CONSOLE_ENABLE=false                 # Enable/disable console API
DIFY_CONSOLE_EMAIL=admin@ai.com           # Your Dify account email
DIFY_CONSOLE_PASSWORD=!Qq123123           # Your Dify account password

# Workflow API Configuration (for future releases)
DIFY_WORKFLOW_API_KEY=your_workflow_key_here  # Your Dify API key for workflow operations

# Dify Text Splitter Configuration
DIFY_TEXT_SPLITTER_TYPE=chunk             # Text splitting method: 'chunk' or 'paragraph'
DIFY_TEXT_SPLITTER_CHUNK_SIZE=1000        # Maximum size of each text chunk
DIFY_TEXT_SPLITTER_CHUNK_OVERLAP=200      # Overlap between adjacent chunks

# Dify Indexing Technique
DIFY_INDEXING_TECHNIQUE=high_quality      # Indexing technique: 'high_quality' or 'economy'

# Dify Cache Configuration
DIFY_CACHE_DRIVER=file                    # Cache driver: 'file' or 'redis'
DIFY_CACHE_PREFIX=dify_                   # Prefix for cache keys
DIFY_CACHE_TTL=86400                      # Cache TTL in seconds (24 hours)
DIFY_CACHE_FILE_DIRECTORY=/path/to/cache  # Directory for file cache

# Redis Cache Configuration (when using redis driver)
DIFY_CACHE_REDIS_HOST=127.0.0.1           # Redis host
DIFY_CACHE_REDIS_PORT=6379                # Redis port
DIFY_CACHE_REDIS_PASSWORD=null            # Redis password (null for none)
DIFY_CACHE_REDIS_DATABASE=0               # Redis database index
DIFY_CACHE_REDIS_TIMEOUT=0.0              # Redis connection timeout
```

### Cache Configuration

Dify PHP SDK supports multiple cache drivers for storing authentication tokens and other information. Currently, the following cache drivers are supported:

- `file`: File cache, stores cache data in files
- `redis`: Redis cache, stores cache data in Redis

You can specify the cache driver and related configuration in the configuration file:

### Complete Configuration File Structure

Below is the complete structure of the configuration file with all available options:

```php
<?php

declare(strict_types=1);

use function Hyperf\Support\env;

return [
    // Base URL for Open API
    'base_url' => env('DIFY_BASE_URL', 'https://api.dify.ai/v1'),

    // Dataset API key
    'dataset_key' => env('DIFY_DATASET_KEY', ''),

    // Workflow API keys (for future releases)
    'workflow_keys' => [
        // Default key
        'default' => env('DIFY_WORKFLOW_API_KEY')

        // You can add more keys with custom names if needed
    ],

    // Enable debug mode
    'debug' => (bool)env('DIFY_DEBUG', false),

    // Text splitter configuration
    'text_splitter' => [
        'type' => env('DIFY_TEXT_SPLITTER_TYPE', 'chunk'),
        'chunk_size' => (int)env('DIFY_TEXT_SPLITTER_CHUNK_SIZE', 1000),
        'chunk_overlap' => (int)env('DIFY_TEXT_SPLITTER_CHUNK_OVERLAP', 200),
    ],

    // Indexing technique
    'indexing_technique' => env('DIFY_INDEXING_TECHNIQUE', 'high_quality'),

    /**
     * Console credentials for non-public API endpoints
     */
    'console' => [
        'enable' => env('DIFY_CONSOLE_ENABLE', false),
        'email' => env('DIFY_CONSOLE_EMAIL', 'admin@ai.com'),
        'password' => env('DIFY_CONSOLE_PASSWORD', '!Qq123123'),
    ],

    /**
     * Cache configuration
     */
    'cache' => [
        // Cache driver: file, redis
        'driver' => env('DIFY_CACHE_DRIVER', 'file'),

        // Cache prefix
        'prefix' => env('DIFY_CACHE_PREFIX', 'dify_'),

        // Default cache TTL (seconds)
        'ttl' => (int)env('DIFY_CACHE_TTL', 86400), // Default 24 hours

        // File cache configuration
        'file' => [
            // Cache directory, defaults to dify_cache in the system temp directory
            'directory' => env('DIFY_CACHE_FILE_DIRECTORY', sys_get_temp_dir() . '/dify_cache'),
        ],

        // Redis cache configuration
        'redis' => [
            'host' => env('DIFY_CACHE_REDIS_HOST', '127.0.0.1'),
            'port' => (int)env('DIFY_CACHE_REDIS_PORT', 6379),
            'password' => env('DIFY_CACHE_REDIS_PASSWORD', null),
            'database' => (int)env('DIFY_CACHE_REDIS_DATABASE', 0),
            'timeout' => (float)env('DIFY_CACHE_REDIS_TIMEOUT', 0.0),
        ],
    ],
];
```

### Basic Usage

```php
<?php

use Happyphper\Dify\DifyClient;use Happyphper\Dify\Public\PublicClient;

class YourController
{
    /**
     * @var PublicClient
     */
    private $dify;

    public function __construct(PublicClient $dify)
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

use Happyphper\Dify\DifyClient;use Happyphper\Dify\Public\PublicClient;use Monolog\Handler\StreamHandler;use Monolog\Logger;

// Create logger
$logger = new Logger('dify');
$logger->pushHandler(new StreamHandler('path/to/your.log', Logger::DEBUG));

// Create Dify client
$client = new PublicClient(
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

## Workflow Orchestration Chat API

> **Note**: The Workflow Orchestration Chat API is planned for future releases. The following documentation outlines the planned functionality. Stay tuned for updates!

### Basic Usage

```php
// Create workflow chat request (blocking mode)
$inputs = [
    'query' => 'Tell me about Dify platform'
];
$response = $client->workflows()->run($inputs, 'user-123');

// Get response content
echo "Workflow Run ID: " . $response->getWorkflowRunId() . "\n";
echo "Task ID: " . $response->getTaskId() . "\n";
echo "Status: " . $response->getStatus() . "\n";
echo "Outputs: " . json_encode($response->getOutputs()) . "\n";

// Create workflow chat request (streaming mode)
$streamResponse = $client->workflows()->runStream($inputs, 'user-123');

// Set event callbacks
$streamResponse->onWorkflowStarted(function ($data) {
    echo "Workflow started: " . $data['workflow_run_id'] . "\n";
});

$streamResponse->onNodeStarted(function ($data) {
    echo "Node started: " . $data['data']['node_id'] . "\n";
});

$streamResponse->onNodeFinished(function ($data) {
    echo "Node finished: " . $data['data']['node_id'] . "\n";
});

$streamResponse->onWorkflowFinished(function ($data) {
    echo "Workflow finished: " . $data['data']['status'] . "\n";
});

$streamResponse->onMessage(function ($data) {
    echo "Message received: " . ($data['answer'] ?? '') . "\n";
});

// Start processing the stream
$streamResponse->stream();

// Stop workflow execution
$client->workflows()->stop($taskId);
```

### Conversation Management

```php
// Get conversation list
$conversations = $client->workflows()->getConversations('user-123');

// Get conversation message history
$messages = $client->workflows()->getMessages($conversationId, 'user-123');

// Delete conversation
$client->workflows()->deleteConversation($conversationId, 'user-123');

// Rename conversation
$client->workflows()->renameConversation($conversationId, 'New Conversation Name', 'user-123');
```

### Message Feedback

```php
// Like a message
$client->workflows()->messageFeedback($messageId, 'like', 'user-123');

// Dislike a message
$client->workflows()->messageFeedback($messageId, 'dislike', 'user-123');

// Revoke feedback
$client->workflows()->revokeFeedback($messageId, 'user-123');
```

### File Upload and Multimodal Support

```php
// Upload a file
$fileInfo = $client->workflows()->uploadFile('/path/to/file.pdf', 'user-123', 'document');

// Send a message with a file
$inputs = [
    'query' => 'Analyze this file',
    'file' => [
        'transfer_method' => 'local_file',
        'upload_file_id' => $fileInfo['id'],
        'type' => 'document'
    ]
];
$response = $client->workflows()->run($inputs, 'user-123');
```

### Audio Processing

```php
// Convert audio to text
$result = $client->workflows()->audioToText('/path/to/audio.mp3', 'user-123');

// Convert text to audio
$result = $client->workflows()->textToAudio('Convert this text to speech', 'user-123', 'default');
```

### Application Information

```php
// Get application basic info
$appInfo = $client->workflows()->getAppInfo();

// Get application parameters
$parameters = $client->workflows()->getParameters();
```

For more examples, please refer to the `examples/workflow_example.php` file.

## Console API (Requires Login)

Some operations need to be performed through the Dify Console API, which requires login to obtain a token. The SDK provides a dedicated `ConsoleClient` class to handle these operations.

> **Note**: The Console API is disabled by default. You need to set `DIFY_CONSOLE_ENABLE=true` in your `.env` file or `console.enable = true` in your configuration file to enable it. If you try to use the `ConsoleClient` when the Console API is disabled, a `ConsoleDisabledException` will be thrown.

### Basic Usage

```php
use Happyphper\Dify\Console\ConsoleClient;use Happyphper\Dify\Exceptions\ApiException;

// Create console client
$consoleClient = new ConsoleClient(
    'http://localhost:8080',  // Dify console URL
    'your-email@example.com', // Login email
    'your-password',          // Login password
    true                      // Enable debug mode
);

// Disable document
try {
    $result = $consoleClient->datasets()->disableDocuments(
        'dataset-id',
        'document-id'
    );
    
    if ($result) {
        echo "Document disabled successfully\n";
    } else {
        echo "Failed to disable document\n";
    }
} catch (ApiException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Enable document
try {
    $result = $consoleClient->datasets()->enableDocuments(
        'dataset-id',
        'document-id'
    );
    
    if ($result) {
        echo "Document enabled successfully\n";
    } else {
        echo "Failed to enable document\n";
    }
} catch (ApiException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Batch operations (passing an array)
$documentIds = ['doc-id-1', 'doc-id-2', 'doc-id-3'];
$consoleClient->datasets()->disableDocuments('dataset-id', $documentIds);
```

### Token Auto-Refresh

The `ConsoleClient` now supports automatic token refresh, which handles token expiration seamlessly:

1. When an access token expires (401 error), the SDK will:
   - Automatically attempt to refresh the token using the refresh token
   - Retry the original request if refresh is successful
   - Re-login if the refresh token is expired or not available
   - Clear tokens and throw an exception if login fails

2. The token refresh process is intelligent:
   - Only clears the refresh token upon refresh failure
   - Preserves the access token if it's still valid
   - Handles all token management internally

This allows your application to focus on business logic without worrying about token management.

```php
// The ConsoleClient handles token refresh automatically
// You don't need to write any additional code for token management

// Example of a long-running process where tokens might expire
$datasets = $consoleClient->datasets()->list();

// Even after token expiration, this will still work without any manual intervention
sleep(3600); // Simulate passage of time (1 hour)
$documents = $consoleClient->documents()->list($datasets[0]['id']);

// The SDK handles token refresh behind the scenes
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
        'regenerateChildChunks' => true // Regenerate child chunks
    ]
);

// Delete segment
try {
    $result = $client->segments()->delete('dataset-id', 'document-id', 'segment-id');
} catch (NotFoundException $e) {
    // Handle segment not found
    echo "Segment not found: " . $e->getMessage();
} catch (ApiException $e) {
    // Handle other API errors
    echo "API error: " . $e->getMessage();
}
```

## Error Handling

The SDK provides comprehensive error handling mechanisms:

```php
use Happyphper\Dify\Exceptions\ApiException;
use Happyphper\Dify\Exceptions\ValidationException;
use Happyphper\Dify\Exceptions\AuthenticationException;
use Happyphper\Dify\Exceptions\AuthorizationException;
use Happyphper\Dify\Exceptions\NotFoundException;
use Happyphper\Dify\Exceptions\RateLimitException;
use Happyphper\Dify\Exceptions\ServerException;

try {
    // Your code
} catch (ValidationException $e) {
    // Handle validation errors
} catch (AuthenticationException $e) {
    // Handle authentication errors
} catch (AuthorizationException $e) {
    // Handle authorization errors
} catch (NotFoundException $e) {
    // Handle resource not found errors
} catch (RateLimitException $e) {
    // Handle rate limit errors
} catch (ServerException $e) {
    // Handle server errors
} catch (ApiException $e) {
    // Handle other API errors
    echo "API error: " . $e->getMessage();
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

## Testing

### Running Tests

```bash
composer test
```

### Test Coverage

To generate the test coverage report, run:

```bash
composer test-coverage
```

This will generate an HTML coverage report in the `coverage` directory. You can open `coverage/index.html` in your browser to view detailed coverage information.

Current test coverage:
- Line Coverage: 95%
- Method Coverage: 90%
- Class Coverage: 100%

## Complete API Documentation

For complete documentation of the Dify API, please refer to the [Dify API Documentation](https://docs.dify.ai/).

## License

MIT
