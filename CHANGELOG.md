# Changelog

All notable changes to this project are documented in this file.

## [1.0.2] - 2026-09-27

### Added

* Added somme helpers for `TypesenseSearchResult`: `first()`, `last()` and `itemsAfter()`.

### Changed

* Result items `TypesenseSearchResult::getItems()` are now cached.

[1.0.2]: https://github.com/moudarir/typesense/releases/tag/1.0.2

## [1.0.1] - 2026-08-14

### Fixed

* Fixed handling of Typesense `group_by` search results.
* Fixed grouped results when `group_key` contains multiple values, such as with `string[]` fields.

[1.0.1]: https://github.com/moudarir/typesense/releases/tag/1.0.1

## [1.0.0] - 2026-08-13

### Added

* Initial release of `moudarir/typesense`.
* Typesense API support for:

    * API keys
    * collections
    * documents
    * information and statistics
    * operations
    * regular searches
    * multiple searches
* Collection schema configuration and validation.
* Document creation, update, deletion, export and synchronization.
* `JSONL` encoding and decoding for Typesense document operations.
* Search result normalization.
* Facet count normalization.
* `TypesenseException` for package-level errors.
* `TypesenseSearchResult` as the normalized representation of a search response.
* `Typesense::search()` now returns a `TypesenseSearchResult` directly.
* `Typesense::searchMultiple()` can return either all search results or a single result selected by index.
* Support for accessing regular hits, grouped hits, pagination data and facet counts through `TypesenseSearchResult`.

[1.0.0]: https://github.com/moudarir/typesense/releases/tag/1.0.0
