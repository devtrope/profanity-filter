<?php

namespace ProfanityFilter;

enum ProfanityLevel
{
    case LOW;
    case MEDIUM;
    case HIGH;

    public static function fromString(string $level): self
    {
        return match (strtolower($level)) {
            'low' => self::LOW,
            'medium' => self::MEDIUM,
            'high' => self::HIGH,
            default => throw new \InvalidArgumentException("Invalid profanity level: $level"),
        };
    }
}
