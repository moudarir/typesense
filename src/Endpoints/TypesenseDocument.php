<?php

declare(strict_types=1);

namespace Moudarir\Typesense\Endpoints;

use JsonException;
use Moudarir\Http\HttpClientException;
use Moudarir\Typesense\Exceptions\TypesenseException;
use Moudarir\Typesense\Helpers\CommonHelper;

final class TypesenseDocument extends TypesenseAbstract
{

    /**
     * @throws TypesenseException
     */
    public function get(int|string $id, string $name): array
    {
        $id = trim((string)$id);

        if ($id === '') {
            throw TypesenseException::invalidParam();
        }

        try {
            return $this->client()
                ->get(self::getUri(trim($name))."/$id")
                ->response();
        } catch (HttpClientException $exception) {
            throw TypesenseException::generic($exception->getMessage());
        }
    }

    /**
     * @throws TypesenseException
     */
    public function export(string $name, array $options): array
    {
        try {
            $response = $this->client()
                ->setQueryData($options)
                ->get(self::getUri($name).'/export')
                ->response(false);

            if (!array_key_exists('body', $response)) {
                return $response;
            }

            return CommonHelper::decodeFromJsonL($response['body']);
        } catch (HttpClientException|JsonException $exception) {
            throw new TypesenseException($exception->getMessage());
        }
    }

    /**
     * @throws TypesenseException
     */
    public function create(array $data, string $name, bool $batch = false): array
    {
        if ($batch === true) {
            return $this->bulk($data, $name);
        }

        if (empty($data)) {
            throw TypesenseException::unavailableData();
        }

        try {
            return $this->client()
                ->setJsonData($data)
                ->post(self::getUri($name))
                ->response();
        } catch (HttpClientException $exception) {
            throw TypesenseException::generic($exception->getMessage());
        }
    }

    /**
     * @throws TypesenseException
     */
    public function update(array $data, string $name, array|int|string $id): array
    {
        if (empty($data)) {
            throw TypesenseException::unavailableData();
        }

        // Update multiple documents
        if ($id === 'multiple') {
            return $this->sync($data, $name);
        }

        try {
            $uri = self::getUri($name);
            $client = $this->client()->setJsonData($data);

            if (is_array($id)) {
                $client->setQueryData(['filter_by' => CommonHelper::buildFilterExpression($id)]);
            } else {
                $uri .= "/$id";
            }

            return $client->patch($uri)->response();
        } catch (HttpClientException $exception) {
            throw TypesenseException::generic($exception->getMessage());
        }
    }

    /**
     * @param string $name
     * @param array|int|string $id ['field_name' => '' // 'field_name' => []]
     * @param int $batch_size
     * @return array
     * @throws TypesenseException
     */
    public function delete(string $name, array|int|string $id, int $batch_size = 1000): array
    {
        try {
            $uri = self::getUri($name);
            $client = $this->client();

            if (is_array($id)) {
                $client->setQueryData([
                    'filter_by' => CommonHelper::buildFilterExpression($id),
                    'batch_size' => $batch_size
                ]);
            } else {
                $uri .= "/$id";
            }

            return $client->delete($uri)->response();
        } catch (HttpClientException $exception) {
            throw TypesenseException::generic($exception->getMessage());
        }
    }

    /**
     * @throws TypesenseException
     */
    public function sync(array $data, string $name): array
    {
        return $this->bulk($data, $name, 'emplace');
    }

    /**
     * @throws TypesenseException
     */
    public function bulk(array $data, string $name, string $action = 'create'): array
    {
        if (empty($data)) {
            throw TypesenseException::unavailableData();
        }

        try {
            $response = $this->client()
                ->setQueryData(['action' => $action, 'batch_size' => 100])
                ->setBodyData(CommonHelper::encodeAsJsonL($data))
                ->setHeader('Content-Type', 'text/plain')
                ->post(self::getUri($name).'/import')
                ->response(false);

            if (!array_key_exists('body', $response)) {
                return $response;
            }

            return CommonHelper::decodeFromJsonL($response['body']);
        } catch (HttpClientException|JsonException $exception) {
            throw TypesenseException::generic($exception->getMessage());
        }
    }

    /**
     * @throws TypesenseException
     */
    private static function getUri(string $name): string
    {
        return sprintf(self::URIS['document'], self::validateCollectionName($name));
    }
}
