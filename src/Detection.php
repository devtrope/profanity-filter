<?php

declare(strict_types=1);

namespace ProfanityFilter;

final readonly class Detection
{
    public function __construct(
        public string $original,
        public string $profanity,
        public int $position
    )
    {}
}
