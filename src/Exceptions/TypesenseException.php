<?php

declare(strict_types=1);

namespace Moudarir\Typesense\Exceptions;

use Exception;

final class TypesenseException extends Exception
{

    public static function missingConfig(): self
    {
        return new self("Missing Typesense API configuration.");
    }

    public static function missingConfigKey(string $key): self
    {
        return new self(sprintf("Typesense API missing `%s` key configuration.", $key));
    }

    public static function collectionNotFound(string $name): self
    {
        return new self(sprintf("The collection name `%s` was not found.", $name));
    }

    public static function documentNotFound(?string $message = null): self
    {
        return new self($message ?: "Document Not found.");
    }

    public static function unavailableData(): self
    {
        return new self("No data available for processing.");
    }

    public static function invalidParam(): self
    {
        return new self("Invalid parameter(s).");
    }

    public static function generic(string $message): self
    {
        return new self($message);
    }
}
