<?php

declare(strict_types=1);

namespace Moudarir\Typesense\Endpoints;

use Moudarir\Http\HttpClientException;
use Moudarir\Typesense\Exceptions\TypesenseException;

final class TypesenseCollection extends TypesenseAbstract
{

    /**
     * @throws TypesenseException
     */
    public function all(): array
    {
        try {
            return $this->client()->get(self::URIS['collection'])->response();
        } catch (HttpClientException $exception) {
            throw TypesenseException::generic($exception->getMessage());
        }
    }

    /**
     * @throws TypesenseException
     */
    public function get(string $name): array
    {
        try {
            return $this->client()->get(self::getUri($name))->response();
        } catch (HttpClientException $exception) {
            throw TypesenseException::generic($exception->getMessage());
        }
    }

    /**
     * @throws TypesenseException
     */
    public function create(string $name): array
    {
        try {
            return $this->client()
                ->setJsonData(self::getSchema($name))
                ->post(self::URIS['collection'])
                ->response();
        } catch (HttpClientException $exception) {
            throw TypesenseException::generic($exception->getMessage());
        }
    }

    /**
     * Only add/drop field(s) to/from collection
     *
     * @throws TypesenseException
     * @since 0.23.1
     */
    public function alter(string $name, array $schema): array
    {
        $schema = self::validateSchema($schema, $name);
        $collectionName = self::validateCollectionName($name);

        try {
            return $this->client()
                ->setJsonData($schema)
                ->patch(self::URIS['collection'].'/'.$collectionName)
                ->response();
        } catch (HttpClientException $exception) {
            throw TypesenseException::generic($exception->getMessage());
        }
    }

    /**
     * Only update field from collection
     *
     * @throws TypesenseException
     * @since 0.23.1
     */
    public function update(string $name, array $schema): array
    {
        self::validateFields($schema);
        $collectionName = self::validateCollectionName($name);

        try {
            return $this->client()
                ->setJsonData($schema)
                ->patch(self::URIS['collection'].'/'.$collectionName)
                ->response();
        } catch (HttpClientException $exception) {
            throw TypesenseException::generic($exception->getMessage());
        }
    }

    /**
     * @throws TypesenseException
     */
    public function drop(string $name): array
    {
        $collectionName = self::validateCollectionName($name);

        try {
            return $this->client()->delete(self::URIS['collection'].'/'.$collectionName)->response();
        } catch (HttpClientException $exception) {
            throw TypesenseException::generic($exception->getMessage());
        }
    }

    /**
     * @throws TypesenseException
     */
    private static function getUri(string $name): string
    {
        return self::URIS['collection'].'/'.self::validateCollectionName($name);
    }

    /**
     * @throws TypesenseException
     */
    private static function getSchema(string $name): array
    {
        if (!array_key_exists($name, self::$schemas)) {
            throw TypesenseException::generic("Le nom de la collection est invalide.");
        }

        return self::$schemas[$name];
    }

    /**
     * @throws TypesenseException
     */
    private static function validateSchema(array $schema, string $name = ''): array
    {
        if (array_key_exists('name', $schema)) {
            if (!in_array($schema['name'], self::$collections, true)) {
                throw TypesenseException::generic("Le nom de la collection est incorrect.");
            }
        } elseif (!array_key_exists($name, self::$collections)) {
            throw TypesenseException::generic("Le nom de la collection est incorrect.");
        }

        $schema['name'] = self::$collections[$name];
        self::validateFields($schema);

        return $schema;
    }

    /**
     * @throws TypesenseException
     */
    private static function validateFields(array $schema): void
    {
        if (!array_key_exists('fields', $schema)) {
            throw TypesenseException::generic("Veuillez configurer les champs de la collection.");
        }

        if (array_filter($schema['fields'], 'is_array') !== $schema['fields']) {
            throw TypesenseException::generic("Configuration incorrecte des champs de la collection.");
        }
    }
}
