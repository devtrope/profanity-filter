<?php

namespace ProfanityFilter\Exceptions;

use Throwable;

final class LocaleException extends MissingBlacklistFileException
{
    public function __construct(string $message = "", int $code = 0, Throwable|null $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
