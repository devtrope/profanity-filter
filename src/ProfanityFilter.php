<?php

declare(strict_types=1);

namespace ProfanityFilter;

final class ProfanityFilter
{
    private const PROFANITIES = ['shit', 'ass', 'fuck'];
    private const DEFAULT_REPLACEMENT = '*';
    private const LEETSPEAK = [
        '4' => 'A',
        '@' => 'A',
        '8' => 'B',
        '|3' => 'B',
        '3' => 'E',
        '6' => 'G',
        '9' => 'G',
        '1' => 'I',
        '|' => 'I',
        '0' => 'O',
        '5' => 'S',
        '$' => 'S',
        '7' => 'T',
        '+' => 'T',
        '2' => 'Z'
    ];

    public function __construct()
    {}

    public function clean(string $content, string $replacement = self::DEFAULT_REPLACEMENT): string
    {
        $words = explode(' ', $content);
        if ($this->containsProfanity($content)) {
            foreach (self::PROFANITIES as $profanity) {
                foreach ($words as $index => $word) {
                    if ($this->normalize($word) === $profanity) {
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
                if ($this->normalize($word) === $profanity) {
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
                if ($this->normalize($word) === $profanity) {
                    if (false === \in_array($profanity, $matches)) {
                        $matches[] = $profanity;
                    }
                }
            }
        }
        return $matches;
    }

    private function normalize(string $word): string
    {
        $excluded = ['.', '_', '*', '-', '/', '\\'];
        foreach ($excluded as $item) {
            if (false !== mb_stripos($word, $item)) {
                $word = str_ireplace($item, '', $word);
            }
        }

        return mb_strtolower($this->leetSpeakInverter($word));
    }

    private function leetSpeakInverter(string $word): string
    {
        $purified = '';
        foreach (mb_str_split($word) as $letter) {
            if (isset(self::LEETSPEAK[$letter])) {
                $letter = self::LEETSPEAK[$letter];
            }
            $purified .= $letter;
        }
        return $purified;
    }
}
