<?php

namespace ProfanityFilter;

class ProfanityFilter
{
    private const PROFANITIES = ['shit'];
    private const DEFAULT_REPLACEMENT = '*';

    public function __construct()
    {}

    public function clean(string $content, string $replacement = self::DEFAULT_REPLACEMENT): string
    {
        foreach (self::PROFANITIES as $profanity) {
            if (false !== mb_stripos($content, $profanity)) {
                $censor = str_repeat($replacement, mb_strlen($profanity));
                $content = str_ireplace($profanity, $censor, $content);
            }
        }
        return $content;
    }

    public function containsProfanity(null|string $content): bool
    {
        foreach (self::PROFANITIES as $profanity) {
            if (false !== mb_stripos($content, $profanity)) {
                return true;
            }
        }
        return false;
    }

    public function getMatches(): array
    {
        return [];
    }
}
