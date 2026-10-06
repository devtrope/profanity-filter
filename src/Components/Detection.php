<?php

declare(strict_types=1);

namespace ProfanityFilter\Components;

final readonly class Detection
{
    public function __construct(
        public string $original,
        public string $profanity,
        public int $position
    ) {
    }
}
