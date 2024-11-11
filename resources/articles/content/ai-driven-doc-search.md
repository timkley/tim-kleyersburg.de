---
date: 2023-04-10
title: AI-Driven Documentation Search with GPT, Weaviate, and Laravel
excerpt: Improve documentation search with AI using OpenAI's GPT, Weaviate vector search, and Laravel for a better user experience.
tags: [ai]
image: /articles/img/ogimages/ai-driven-doc-search.webp
---

As a developer, I often find myself digging through documentation to solve problems or learn about new tools. Sometimes, I encounter issues with finding the right information, or the search functionality is limited. That's when I had an idea: What if we could create a _natural language search_ for our agency's documentation using cutting-edge AI technology?

In this article, I'll walk you through my journey of using OpenAI's GPT models, their embeddings, and a [vector database called Weaviate](/articles/setup-weaviate-with-docker-and-traefik) to enhance our documentation search capabilities.

## Integrate OpenAI's API with Laravel

We'll assume you already have a Laravel application set up, or you're familiar with setting up a new Laravel project. In this section, we'll focus on integrating the OpenAI API using the `openai-php/laravel` Composer package.

### Step 1: Install the openai-php/laravel Package

To install the `openai-php/laravel` package, use the Composer command below:

```bash
composer require openai-php/laravel
php artisan vendor:publish --provider="OpenAI\Laravel\ServiceProvider"
```

### Step 2: Configure Environment Variables

Navigate to the root of your Laravel project and locate the `.env` file. This file contains environment-specific settings. We'll need to add our OpenAI API key to this file. You can obtain an API key by signing up for an OpenAI account.

Add the following line to your `.env` file:

```bash
OPENAI_API_KEY=sk-...
```

You are now ready to use the `OpenAI` facade in your Laravel application.

```php
use OpenAI\Laravel\Facades\OpenAI;

$result = OpenAI::completions()->create([
    'model' => 'text-davinci-003',
    'prompt' => 'I want to ',
]);

echo $result['choices'][0]['text'];
```

## Retrieving Documentation Data from Confluence API

In this section, we will go through the process of fetching data from the Confluence API, which stores your documentation. We will be using this data for our natural language search.

### Step 1: Set Up Confluence API Credentials

To interact with the Confluence API, you will need an API token and your Confluence URL. You can create an API token by following the instructions in the [official documentation](https://support.atlassian.com/atlassian-account/docs/manage-api-tokens-for-your-atlassian-account/).

Once you have your API token, add the following lines to your .env file:

```bash
CONFLUENCE_API_USER=your_api_user_here
CONFLUENCE_API_KEY=your_api_key_here
CONFLUENCE_URL=https://your_domain.atlassian.net
```

Replace the values with your own values and make sure, the user you are using has access to the documentation you want to search.

Now add these values to your config/services.php file:

```php
    'confluence' => [
        'api_user' => env('CONFLUENCE_API_USER'),
        'api_key' => env('CONFLUENCE_API_KEY'),
        'api_url' => env('CONFLUENCE_API_URL'),
    ],
```

### Step 2: Create a ConfluenceService Class

Create a new service class called ConfluenceService in the `app/Services` directory to handle interactions with the Confluence API.

Below is a simple service class which handles the fetching of all pages from a specific parent page in Confluence.

```php
<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class ConfluenceService
{
    private string $apiUser;
    private string $apiKey;
    private string $apiUrl;

    public function __construct()
    {
        $this->apiUser = config('services.confluence.api_user');
        $this->apiKey = config('services.confluence.api_key');
        $this->apiUrl = config('services.confluence.api_url');
    }

    public function getPageDescendants(int $pageId): Response
    {
        $entityUrl = sprintf('content/%s/descendant/page', $pageId);
        $data = [
            'expand' => 'body.view',
            'limit' => 1000,
        ];
        
        $url = sprintf('%s/%s', $this->apiUrl, trim($entityUrl, '/'));

        return Http::withBasicAuth($this->apiUser, $this->apiKey)->get($url, $data);
    }
}
```

You may now use this service class to fetch data from the Confluence API.

```php
$confluenceService = new ConfluenceService();
$response = $confluenceService->getPageDescendants(123456);
```

This response contains all subpages including their contents.

## Store embeddings of documentation in Weaviate

Weaviate is a vector database that allows you to store and query data in a vector space. You can read my article on how to set up Weaviate with Docker and Traefik to learn more about Weaviate and how to set it up. [Read the article](/articles/setup-weaviate-with-docker-and-traefik). If you have no previous experience or knowledge of Weaviate, I recommend reading the article first.

Weaviate uses a schema to define its data structure. In this example, we will use the following schema:

```graphql
{
	"class": "Chunk",
	"description": "Some chunk of knowledge",
	"vectorizer": "text2vec-openai",
	"moduleConfig": {
		"text2vec-openai": {
			"model": "ada",
			"modelVersion": "002",
			"type": "text"
		}
	},
	"properties": [
		{
			"name": "identifier",
			"description": "The identifier of the particular chunk of knowledge",
			"dataType": [
				"string"
			],
			"moduleConfig": {
				"text2vec-openai": {
					"skip": true
				}
			}
		},
		{
			"name": "content",
			"description": "The contents",
			"dataType": [
				"text"
			]
		},
		{
			"name": "source",
			"description": "The source type",
			"dataType": [
				"string"
			],
			"moduleConfig": {
				"text2vec-openai": {
					"skip": true
				}
			}
		},
		{
			"name": "sourceLink",
			"description": "URL to the article",
			"dataType": [
				"string"
			],
			"moduleConfig": {
				"text2vec-openai": {
					"skip": true
				}
			}
		}
	]
}
```

We aren't storing whole pages content because this would mean that our prompts will get too big. Instead we will chunk the content and store each chunk in Weaviate. We will also store the source and sourceLink properties to be able to link back to the original source.

### Step 1: Accessing Weaviate from PHP

To access Weaviate from PHP, we will use the [weaviate-php](https://github.com/timkley/weaviate-php) package. Install the package using the Composer command below:

```bash
composer require timkley/weaviate-php
```

You can now use the Weaviate client in your PHP code.

```php
<?php

use Weaviate\Weaviate;

$weaviate = new Weaviate('http://localhost:8080', 'your-token');
```

### Step 2: Chunk and store content in Weaviate

Looping over all our pages we'll do the following things:

1. Remove all HTML tags from the content
2. Split the content into chunks manageble chunks
3. Create a new Weaviate object for each chunk
4. Store the object in Weaviate

```php
use App\Services\ConfluenceService;

$confluenceService = new ConfluenceService();

$response = $confluenceService->getPageDescendants(12345);

if ($response->successful()) {
    $pages = $response->json()['results'];

    foreach ($pages as $page) {
        $content = cleanUpContent($page['body']['view']['value']);
        $chunks = chunkContent($content);
        
        // Delete all old chunks before creating new ones
        $weaviate->batch()->delete('Chunk', [
            'path' => ['identifier'],
            'operator' => 'Equal',
            'valueString' => $chunkyBoy->identifier,
        ]);

        $count = 0;
        $objects = [];
        // Loop over the chunks and create objects matching our Weaviate schema
        foreach ($chunks as $chunk) {
            $objects[] = [
                'class' => 'Chunk',
                'properties' => [
                    'identifier' => $chunkyBoy->identifier,
                    'content' => $chunk['value'],
                    'source' => $chunkyBoy->source,
                    'sourceLink' => $chunkyBoy->sourceLink,
                ],
            ];

            if (++$count % $batchSize === 100) {
                $weaviate->batch()->create($objects);
                $objects = [];
                $count = 0;
            }
        }

        $weaviate->batch()->create($objects);
    }
} else {
    // Handle the error
    echo "Failed to fetch descendant pages: " . $response->status();
}

function cleanUpContent(string $content): string
{
    return Str::of($content)
        ->replace('<', ' <')
        ->stripTags()
        ->replace(['\r', '\n'], ' ')
        ->replaceMatches('/\s+/', ' ')
        ->trim();
}

function chunkContent(string $content): array
{
    $tokensPerCharacter = 0.4;
    $tokenLimit = 150;
    $chunkCharacterLimit = $tokenLimit / $tokensPerCharacter;

    // Split the input string into an array of sentences
    $sentences = collect(preg_split('/(?<=[.?!])\s?(?=[a-z])/i', $content));

    $chunks = $sentences->chunkWhile(
        function (string $sentence, int $key, Collection $chunk) use ($chunkCharacterLimit) {
            return $chunk->sum(fn (string $sentence) => strlen($sentence)) < $chunkCharacterLimit;
        }
    )->map(function (Collection $chunk) {
        $value = $chunk->implode(' ');
        $checksum = md5($value);

        return [
            'checksum' => $checksum,
            'value' => $value,
        ];
    });

    return $chunks->all();
}
```

## Implementing the Natural Language Search

In this section, we'll implement the natural language search feature using OpenAI's GPT models and the Weaviate vector database. Our goal is to allow users to search the documentation using natural language queries, and return the most relevant results. Here's how we'll do it:

Assume we'll have an endpoint that accepts a `question` parameter, you could implement this in your own application using the code below:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;
use Weaviate\Weaviate;

class DocSearchController extends Controller
{
    public function search(Request $request)
    {
        $question = $request->input('question');

        if ($question) {
            $chunks = $this->getChunks($question);
            $messages = $this->getMessages($question, $chunks);

            $response = OpenAI::chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => $messages,
            ]);

            $answer = $response['choices'][0]['message']['content'];
        }

        return view('docsearch', [
            'answer' => $answer ?? '',
        ]);
    }

    protected function getChunks(string $text): array
    {
        $weaviate = app(Weaviate::class);

        $query = <<<GQL
        {
          Get {
            Chunk(
              nearText: {
                  concepts: "$text"
                  certainty: 0.9
              }
              limit: 3
            ) {
                content
            }
          }
        }
GQL;

        $response = $weaviate->graphql()->get($query);

        if (isset($response['errors'])) {
            return [];
        }

        return $response ? $response['data']['Get']['Chunk'] : [];
    }

    protected function getMessages(string $question, array $chunks): array
    {
        $information = implode('\n', array_column($chunks, 'content'));

        $messages = [
            ['role' => 'system', 'content' => 'You are a helpful assistant.'],
            ['role' => 'user', 'content' => 'Here is some information: ' . $information],
            ['role' => 'user', 'content' => 'Please use this information to answer my question: ' . $question],
        ];

        return $messages;
    }
}
```

## Conclusion

In this tutorial, I've demonstrated how to integrate OpenAI's GPT models, Weaviate vector search, and Laravel to create a natural language search for your documentation. While this implementation works well, there are further optimizations we can apply to enhance the system and reduce API costs.

One area of improvement is only updating the embeddings of your documentation content when needed. Embeddings can be expensive to compute, both in terms of time and API costs. By  only updating them when the content changes, you can save on API bills and improve response times.

To achieve this, you could use MD5 hashes to check whether the content has changed or not. When you receive a new content update, calculate its MD5 hash and compare it to the hash of the previous content. If the hashes are different, update the embeddings in Weaviate and store the new hash for future comparisons. This way, you'll only update the embeddings when there's an actual change in the content.

By applying these improvements, you'll create a more efficient and cost-effective natural language search system for your documentation, while maintaining a high level of accuracy and relevance for your users.
