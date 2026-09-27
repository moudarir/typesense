<?php

declare(strict_types=1);

namespace Moudarir\Typesense\Endpoints;

use Moudarir\Typesense\Helpers\CommonHelper;

final class TypesenseSearchResult
{

    private ?array $items = null;

    public function __construct(private readonly array $results)
    {
    }

    public function getResults(): array
    {
        return $this->results;
    }

    public function getTotal(): int
    {
        return $this->results['found'] ?? 0;
    }

    public function getPage(): int
    {
        return $this->results['page'] ?? 1;
    }

    /*
     * Without `group_by`, `per_page` limits the number of documents returned
     * and `found` is the total number of matching documents.
     *
     * With `group_by`, `per_page` limits the number of groups returned and
     * `found` is the total number of matching groups. Documents are nested
     * inside `grouped_hits` and must therefore be processed separately.
     */
    public function getItems(): array
    {
        return $this->items ??= (
            array_key_exists('grouped_hits', $this->results)
                ? $this->getGroupedHits()
                : $this->getHits()
        );
    }

    public function first(): ?array
    {
        $items = $this->getItems();

        if ($items === []) {
            return null;
        }

        return $items[array_key_first($items)];
    }

    public function last(): ?array
    {
        $items = $this->getItems();

        if ($items === []) {
            return null;
        }

        return $items[array_key_last($items)];
    }

    public function itemsAfter(): array
    {
        $items = $this->getItems();

        if ($items === []) {
            return [];
        }

        if ($this->first() !== null) {
            $items = array_slice($items, 1);
        }

        if ($this->last() !== null) {
            $items = array_slice($items, 0, -1);
        }

        return $items;
    }

    public function getHits(): array
    {
        if (empty($this->results['hits'])) {
            return [];
        }

        return array_map(
            CommonHelper::normalizeDocument(...),
            $this->results['hits']
        );
    }

    public function getGroupedHits(): array
    {
        if (empty($this->results['grouped_hits'])) {
            return [];
        }

        $groupedHits = [];

        foreach ($this->results['grouped_hits'] as $group) {
            $key = $group['group_key'][0];
            $key = is_array($key) ? $key : [$key];

            foreach ($key as $k) {
                $groupedHits[$k] = array_map(
                    CommonHelper::normalizeDocument(...),
                    $group['hits']
                );
            }
        }

        return $groupedHits;
    }

    public function getFacetCounts(): array
    {
        if (empty($this->results['facet_counts'])) {
            return [];
        }

        $result = [];

        foreach ($this->results['facet_counts'] as $facet) {
            $result[$facet['field_name']] = [];

            foreach ($facet['counts'] as $count) {
                $result[$facet['field_name']][$count['value']] = $count['count'];
            }
        }

        return $result;
    }
}
