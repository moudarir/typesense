<?php

declare(strict_types=1);

namespace Moudarir\Typesense\Endpoints;

use Moudarir\Typesense\Helpers\CommonHelper;

final readonly class TypesenseSearchResult
{
    public function __construct(private array $results)
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

    public function getItems(): array
    {
        return array_key_exists('grouped_hits', $this->results)
            ? $this->getGroupedHits()
            : $this->getHits();
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
            $key = (string) $group['group_key'][0];

            $groupedHits[$key] = array_map(
                CommonHelper::normalizeDocument(...),
                $group['hits']
            );
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