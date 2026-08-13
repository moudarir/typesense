<?php

declare(strict_types=1);

namespace Moudarir\Typesense\Endpoints;

use Moudarir\Http\HttpClientException;
use Moudarir\Typesense\Exceptions\TypesenseException;

final class TypesenseOperation extends TypesenseAbstract
{

    /**
     * @throws TypesenseException
     */
    public function snapshot(string $path): array
    {
        try {
            return $this->client()
                ->setQueryData(['snapshot_path' => $path])
                ->post(self::URIS['operation'].'/snapshot')
                ->response();
        } catch (HttpClientException $exception) {
            throw TypesenseException::generic($exception->getMessage());
        }
    }

    /**
     * @throws TypesenseException
     */
    public function clearCache(): array
    {
        try {
            return $this->client()
                ->post(self::URIS['operation'].'/cache/clear')
                ->response();
        } catch (HttpClientException $exception) {
            throw TypesenseException::generic($exception->getMessage());
        }
    }

    /**
     * @throws TypesenseException
     */
    public function compact(): array
    {
        try {
            return $this->client()
                ->post(self::URIS['operation'].'/db/compact')
                ->response();
        } catch (HttpClientException $exception) {
            throw TypesenseException::generic($exception->getMessage());
        }
    }
}
