<?php

declare(strict_types=1);

namespace Moudarir\Typesense\Endpoints;

use Moudarir\Http\HttpClientException;
use Moudarir\Typesense\Exceptions\TypesenseException;

final class TypesenseApiKey extends TypesenseAbstract
{

    /**
     * @throws TypesenseException
     */
    public function get(int|string $id): array
    {
        if (is_int($id) && $id <= 0) {
            throw TypesenseException::invalidParam();
        }

        try {
            return $this->client()->get(self::URIS['key']."/$id")->response();
        } catch (HttpClientException $exception) {
            throw TypesenseException::generic($exception->getMessage());
        }
    }

    /**
     * @throws TypesenseException
     */
    public function create(array $data): array
    {
        if (empty($data)) {
            throw TypesenseException::unavailableData();
        }

        try {
            return $this->client()->setJsonData($data)->post(self::URIS['key'])->response();
        } catch (HttpClientException $exception) {
            throw TypesenseException::generic($exception->getMessage());
        }
    }

    /**
     * @throws TypesenseException
     */
    public function delete(string $id): array
    {
        try {
            return $this->client()->delete(self::URIS['key']."/$id")->response();
        } catch (HttpClientException $exception) {
            throw TypesenseException::generic($exception->getMessage());
        }
    }
}
