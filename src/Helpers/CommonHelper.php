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
                $field = trim($field, '!');
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

                $output[] = "$field:$negative=[".implode(',', $items).']';
            } else {
                if (is_string($value)) {
                    if (str_contains($value, ";")) {
                        $parts = explode(';', $value, 2);

                        if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                            $value = "[$parts[0]..$parts[1]]";
                        }
                    } elseif (str_contains($value, ' ')) {
                        $value = "`$value`";
                    }
                }

                $operator = str_ends_with($field, '_at') ? '' : '=';
                $output[] = "$field:$negative$operator$value";
            }
        }

        if ($output === []) {
            return '';
        }

        return implode(" && ", $output);
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
