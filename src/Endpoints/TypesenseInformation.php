<?php

declare(strict_types=1);

namespace Moudarir\Typesense\Endpoints;

use Moudarir\Http\HttpClientException;
use Moudarir\Typesense\Exceptions\TypesenseException;

final class TypesenseInformation extends TypesenseAbstract
{

    /**
     * @throws TypesenseException
     */
    public function health(): array
    {
        try {
            return $this->client()->get('health')->response();
        } catch (HttpClientException $exception) {
            throw TypesenseException::generic($exception->getMessage());
        }
    }

    /**
     * @throws TypesenseException
     */
    public function stats(): array
    {
        try {
            return $this->client()->get('stats.json')->response();
        } catch (HttpClientException $exception) {
            throw TypesenseException::generic($exception->getMessage());
        }
    }

    /**
     * @throws TypesenseException
     */
    public function metrics(): array
    {
        try {
            return $this->client()->get('metrics.json')->response();
        } catch (HttpClientException $exception) {
            throw TypesenseException::generic($exception->getMessage());
        }
    }
}
