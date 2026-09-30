<?php

declare(strict_types=1);

namespace Moudarir\Typesense\Helpers;

use JsonException;

final class CommonHelper
{

    /**
     * @param array<string, mixed> $filters
     */
    public static function buildFilterExpression(array $filters): string
    {
        if ($filters === []) {
            return '';
        }

        $output = [];

        foreach ($filters as $field => $value) {
            if ($value === [] || $value === '' || $value === null) {
                continue;
            }

            $negative = "";

            if (str_contains($field, "!")) {
                $negative = "!";
                $field = str_replace("!", "", $field);
            }


            if (is_array($value)) {
                $items = [];

                foreach ($value as $item) {
                    if (is_string($item) && str_contains($item, ' ')) {
                        $items[] = "`$item`";
                    } else {
                        $items[] = (string)$item;
                    }
                }

                $output[] = sprintf(
                    '%s:%s=[%s]',
                    $field,
                    $negative,
                    implode(',', $items)
                );
            } else {
                if (str_contains($value, ";")) {
                    $interval = explode(';', $value);

                    if (count($interval) === 2) {
                        [$from, $to] = $interval;

                        if (is_numeric($from) && is_numeric($to)) {
                            $value = "[$from..$to]";
                        }
                    }
                }

                if (str_contains($value, ' ')) {
                    $value = "`$value`";
                }

                $output[] = sprintf(
                    '%s:%s%s%s',
                    $field,
                    $negative,
                    str_ends_with($field, '_at') ? '' : '=',
                    $value
                );
            }
        }

        if ($output === []) {
            return '';
        }

        return join(" && ", $output);
    }

    /**
     * @param array{
     *     document: array,
     *     highlight?: array,
     *     geo_distance_meters?: array
     * } $hit
     */
    public static function normalizeDocument(array $hit): array
    {
        $document = $hit['document'];
        $highlight = $hit['highlight'] ?? [];
        $distances = $hit['geo_distance_meters'] ?? [];

        if (is_array($distances) && array_key_exists('location', $distances)) {
            $document['distance'] = self::formatDistance($distances['location']);
        }

        if ($highlight !== []) {
            $document['highlights'] = array_map(
                static fn ($item) => $item['snippet'] ?? $item,
                $highlight
            );
        }

        return $document;
    }

    /**
     * @param list<array<string, mixed>> $data
     * @throws JsonException
     */
    public static function encodeAsJsonL(array $data): string
    {
        return implode("\n", array_map(static fn (array $d) => json_encode($d, JSON_THROW_ON_ERROR), $data));
    }

    /**
     * @throws JsonException
     */
    public static function decodeFromJsonL(string $response): array
    {
        $lines = array_filter(
            explode("\n", trim($response)),
            static fn (string $line): bool => $line !== ''
        );

        return array_map(
            static fn (string $line): array => json_decode($line, true, 512, JSON_THROW_ON_ERROR),
            $lines
        );
    }

    private static function formatDistance(int $meters): string
    {
        return round($meters / 1000).' Km';
    }
}
