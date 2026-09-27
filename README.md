# Moudarir Typesense

A PHP client for managing Typesense collections, documents, API keys, searches and server operations.

## Requirements

* PHP 8.4 or higher
* [moudarir/http](https://github.com/moudarir/http)

## Installation

```bash
composer require moudarir/typesense
```

## Configuration

The package uses a single configuration array containing the Typesense endpoint, API key, collections and schemas.

For a project using one Typesense configuration, it can be defined as a class constant:

```php
$config = [
    'collections' => [
        'post' => 'posts_collection',
    ],
    'schemas' => [
        'post' => [
            'name' => 'posts_collection',
            'fields' => [
                ['name' => 'category', 'type' => 'object', 'facet' => true],
                ['name' => 'tags', 'type' => 'string[]'],
                ['name' => 'title', 'type' => 'string'],
                ['name' => 'content', 'type' => 'string'],
                ['name' => 'published_at', 'type' => 'int64'],
                ['name' => 'created_at', 'type' => 'int64', 'index' => false, 'optional' => true, 'sort' => false],
                ['name' => 'updated_at', 'type' => 'int64', 'index' => false, 'optional' => true, 'sort' => false],
                ['name' => 'url', 'type' => 'string', 'index' => false, 'optional' => true, 'sort' => false],
                ['name' => 'slug', 'type' => 'string', 'index' => false, 'optional' => true, 'sort' => false],
                ['name' => 'picture', 'type' => 'object', 'index' => false, 'optional' => true, 'sort' => false],
            ],
            'enable_nested_fields' => true,
            'default_sorting_field' => 'published_at',
        ],
    ],
    'endpoint' => 'http://localhost:45554',
    'apiKey' => 'your-api-key',
];
```

## Usage

All operations are exposed through the `Typesense` facade.

### Collections

```php
use Moudarir\Typesense\Typesense;

$collection = Typesense::collection($config);

$result = $collection->create('post');
```

The collection endpoint provides operations for creating, updating, altering, retrieving and deleting collections.

### Documents

```php
$document = Typesense::document($config);

$post = $document->get('Kje324kj', 'post');
```

Document operations include retrieving, creating, updating, deleting, exporting and synchronizing documents.

### Search

A regular search returns a `TypesenseSearchResult` directly:

```php
$search = Typesense::search(
    'post',
    [
        'q' => '*',
        'sort_by' => 'published_at:desc',
        'page' => 1,
        'per_page' => 20,
    ],
    $config
);

$total = $search->getTotal();
$posts = $search->getItems();
```

The result object provides access to the raw response as well as normalized search data:

```php
$results = $search->getResults();
$total = $search->getTotal();
$page = $search->getPage();
$items = $search->getItems();
$facets = $search->getFacetCounts();
```

### Grouped search

Typesense changes the meaning of some pagination information when `group_by` is used.

Without `group_by`:

* `per_page` represents the number of documents returned per page.
* `found` represents the number of matching documents.

With `group_by`:

* `per_page` represents the number of groups returned per page.
* the top-level `found` represents the matching groups.
* each group has its own `found` value representing the matching documents in that group.

`getItems()` automatically returns either regular hits or grouped hits depending on the response:

```php
$search = Typesense::search(
    'post',
    [
        'q' => '*',
        'group_by' => 'author',
        'group_limit' => 5,
        'per_page' => 10,
    ],
    $config
);

$groups = $search->getItems();
```

For grouped searches, `getGroupedHits()` exposes the documents indexed by their group key.

The implementation also handles `group_by` fields containing multiple values, such as a `string[]` field.

### Multiple searches

Typesense supports sending multiple searches in a single request:

```php
$results = Typesense::searchMultiple(
    [
        [
            'collection' => 'post',
            'q' => 'Jetour',
            'query_by' => 'title,content',
        ],
        [
            'collection' => 'post',
            'q' => 'SUV',
            'query_by' => 'title,content',
        ],
    ],
    $config
);
```

This returns a list of `TypesenseSearchResult` objects.

A single result can also be retrieved directly by its index:

```php
$result = Typesense::searchMultiple(
    [
        [
            'collection' => 'post',
            'q' => 'Jetour',
            'query_by' => 'title',
        ],
        [
            'collection' => 'post',
            'q' => 'SUV',
            'query_by' => 'title',
        ],
    ],
    $config,
    0
);
```

### API keys

```php
$apiKey = Typesense::apiKey($config);

$result = $apiKey->get($id);
```

The API key endpoint supports retrieving, creating and deleting keys.

### Information

```php
$information = Typesense::information($config);

$health = $information->health();
$stats = $information->stats();
$metrics = $information->metrics();
```

### Operations

```php
$operation = Typesense::operation($config);

$result = $operation->snapshot();
```

The operations endpoint provides Typesense maintenance operations such as snapshots, cache clearing and compaction.

## Exceptions

Operations can throw `TypesenseException`:

```php
use Moudarir\Typesense\Exceptions\TypesenseException;

try {
    $search = Typesense::search(
        'post',
        [
            'q' => 'Jetour',
            'query_by' => 'title',
        ],
        $config
    );
} catch (TypesenseException $exception) {
    error_log($exception->getMessage());
}
```

## Design

The package intentionally provides a small API around the Typesense HTTP API.

The facade keeps the common usage simple:

```php
Typesense::collection($config);
Typesense::document($config);
Typesense::search($name, $options, $config);
Typesense::searchMultiple($searchData, $config);
```

Search responses are represented by `TypesenseSearchResult`, which centralizes access to pagination, hits, grouped hits and facet counts.

## License

MIT
