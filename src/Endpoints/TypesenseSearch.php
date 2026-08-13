<?php

declare(strict_types=1);

namespace Moudarir\Typesense\Endpoints;

use Moudarir\Http\HttpClientException;
use Moudarir\Typesense\Exceptions\TypesenseException;

final class TypesenseSearch extends TypesenseAbstract
{

    private readonly TypesenseSearchResult $result;

    /**
     * @throws TypesenseException
     */
    public function __construct(string $name, array $options, array $config)
    {
        parent::__construct($config);

        try {
            $results = $this->client()
                ->setQueryData($options)
                ->get(self::getUri($name))
                ->response();

            $this->result = new TypesenseSearchResult($results);
        } catch (HttpClientException $exception) {
            throw TypesenseException::generic($exception->getMessage());
        }
    }

    public function getResult(): TypesenseSearchResult
    {
        return $this->result;
    }

    /**
     * @throws TypesenseException
     */
    private static function getUri(string $name): string
    {
        $collectionName = self::validateCollectionName($name);
        return sprintf(self::URIS['document'], $collectionName).'/search';
    }
}
