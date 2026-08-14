<?php

declare(strict_types=1);

namespace Moudarir\Typesense;

use Moudarir\Typesense\Endpoints\TypesenseApiKey;
use Moudarir\Typesense\Endpoints\TypesenseCollection;
use Moudarir\Typesense\Endpoints\TypesenseDocument;
use Moudarir\Typesense\Endpoints\TypesenseInformation;
use Moudarir\Typesense\Endpoints\TypesenseOperation;
use Moudarir\Typesense\Endpoints\TypesenseSearch;
use Moudarir\Typesense\Endpoints\TypesenseSearchMultiple;
use Moudarir\Typesense\Endpoints\TypesenseSearchResult;
use Moudarir\Typesense\Exceptions\TypesenseException;

final class Typesense
{

    /**
     * @throws TypesenseException
     */
    public static function apiKey(array $config): TypesenseApiKey
    {
        return new TypesenseApiKey($config);
    }

    /**
     * @throws TypesenseException
     */
    public static function collection(array $config): TypesenseCollection
    {
        return new TypesenseCollection($config);
    }

    /**
     * @throws TypesenseException
     */
    public static function document(array $config): TypesenseDocument
    {
        return new TypesenseDocument($config);
    }

    /**
     * @throws TypesenseException
     */
    public static function information(array $config): TypesenseInformation
    {
        return new TypesenseInformation($config);
    }

    /**
     * @throws TypesenseException
     */
    public static function operation(array $config): TypesenseOperation
    {
        return new TypesenseOperation($config);
    }

    /**
     * @throws TypesenseException
     */
    public static function search(string $name, array $options, array $config): TypesenseSearchResult
    {
        return new TypesenseSearch(
            $name,
            $options,
            $config
        )->getResult();
    }

    /**
     * @throws TypesenseException
     *
     * @return list<int, TypesenseSearchResult>|TypesenseSearchResult
     */
    public static function searchMultiple(array $searchData, array $config, ?int $index = null): array|TypesenseSearchResult
    {
        $search = new TypesenseSearchMultiple($searchData, $config);

        if (null !== $index) {
            return $search->getResult($index);
        }

        return $search->getResults();
    }
}
