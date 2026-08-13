<?php

declare(strict_types=1);

namespace Moudarir\Typesense\Endpoints;

use Moudarir\Http\HttpClientException;
use Moudarir\Typesense\Exceptions\TypesenseException;

final class TypesenseSearchMultiple extends TypesenseAbstract
{

    /**
     * @var list<int, TypesenseSearchResult>
     */
    private readonly array $results;

    /**
     * @throws TypesenseException
     */
    public function __construct(array $searchData, array $config)
    {
        parent::__construct($config);

        try {
            $searches = array_map(
                static function (array $search): array {
                    $search['collection'] = self::validateCollectionName($search['collection']);
                    return $search;
                },
                $searchData
            );

            $response = $this->client()
                ->setJsonData(['searches' => $searches])
                ->post('multi_search')
                ->response();

            foreach ($response['results'] as $index => $result) {
                $this->results[$index] = new TypesenseSearchResult($result);
            }
        } catch (HttpClientException $exception) {
            throw TypesenseException::generic($exception->getMessage());
        }
    }

    /**
     * @throws TypesenseException
     */
    public function getResult(int $index): TypesenseSearchResult
    {
        return $this->results[$index] ?? throw TypesenseException::invalidParam();
    }

    /**
     * @return list<int, TypesenseSearchResult>
     */
    public function getResults(): array
    {
        return $this->results;
    }
}
