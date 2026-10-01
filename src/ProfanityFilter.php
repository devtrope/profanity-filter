<?php

declare(strict_types=1);

namespace ProfanityFilter;

final class ProfanityFilter
{
    private const PROFANITIES = ['shit', 'ass', 'fuck'];
    private const DEFAULT_REPLACEMENT = '*';

    public function __construct()
    {}

    public function clean(string $content, string $replacement = self::DEFAULT_REPLACEMENT): string
    {
        $words = explode(' ', $content);
        if ($this->containsProfanity($content)) {
            foreach (self::PROFANITIES as $profanity) {
                foreach ($words as $index => $word) {
                    if (mb_strtolower($word) === $profanity) {
                        $words[$index] = str_repeat($replacement, mb_strlen($profanity));
                    }
                }
            }
        }
        return implode(' ', $words);
    }

    public function containsProfanity(string $content): bool
    {
        foreach (self::PROFANITIES as $profanity) {
            foreach (explode(' ', $content) as $word) {
                if (mb_strtolower($word) === $profanity) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getMatches(string $content): array
    {
        $matches = [];
        foreach (self::PROFANITIES as $profanity) {
            foreach (explode(' ', $content) as $word) {
                if (mb_strtolower($word) === $profanity) {
                    if (false === \in_array($profanity, $matches)) {
                        $matches[] = $profanity;
                    }
                }
            }
        }
        return $matches;
    }
}
