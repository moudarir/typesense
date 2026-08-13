<?php

declare(strict_types=1);

namespace Moudarir\Typesense\Endpoints;

use Moudarir\Http\HttpClient;
use Moudarir\Typesense\Exceptions\TypesenseException;

abstract class TypesenseAbstract
{

    protected const array URIS = [
        'key' => 'keys',
        'collection' => 'collections',
        'operation' => 'operations',
        'document' => 'collections/%s/documents',
    ];

    public static array $collections;

    public static array $schemas;

    public static string $endpoint;

    public static string $apiKey;

    /**
     * @throws TypesenseException
     */
    public function __construct(array $config)
    {
        if ($config === []) {
            throw TypesenseException::missingConfig();
        }

        foreach (['collections', 'schemas'] as $key) {
            if (!is_array($config[$key]) || empty($config[$key])) {
                throw TypesenseException::missingConfigKey($key);
            }
        }

        foreach (['endpoint', 'apiKey'] as $key) {
            if (!is_string($config[$key]) || empty($config[$key])) {
                throw TypesenseException::missingConfigKey($key);
            }
        }

        self::$collections ??= $config['collections'];
        self::$schemas ??= $config['schemas'];
        self::$endpoint ??= $config['endpoint'];
        self::$apiKey ??= $config['apiKey'];
    }

    /**
     * @throws TypesenseException
     */
    protected static function validateCollectionName(string $name): string
    {
        if (!array_key_exists($name, self::$collections)) {
            throw TypesenseException::collectionNotFound($name);
        }

        return self::$collections[$name];
    }

    protected function client(): HttpClient
    {
        return new HttpClient(self::$endpoint)
            ->setParam('http_errors', false)
            ->setHeader('X-TYPESENSE-API-KEY', self::$apiKey)
            ->setContentType();
    }
}
