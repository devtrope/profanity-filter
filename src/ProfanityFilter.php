<?php

declare(strict_types=1);

namespace ProfanityFilter;

final class ProfanityFilter
{
    private const array PROFANITIES = ['shit', 'ass', 'fuck'];
    private const string DEFAULT_REPLACEMENT = '*';
    private const array LEETSPEAK = [
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
        $word = $this->removeRepeatedLetters($word);
        $excluded = ['.', '_', '*', '-', '/', '\\'];
        foreach ($excluded as $item) {
            if (false !== mb_stripos($word, $item)) {
                $word = str_ireplace($item, '', $word);
            }
        }
        return mb_strtolower($this->leetspeakInverter($word));
    }

    private function leetspeakInverter(string $word): string
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

    private function removeRepeatedLetters(string $word): string
    {
        if (false === $this->hasRepeatedLetters($word)) {
            return $word;
        }
        
        $purified = '';
        $lastLetters = [];
        foreach (mb_str_split($word) as $letter) {
            if (2 === \count($lastLetters)) {
                if (end($lastLetters) === $letter) {
                    continue;
                }
                array_shift($lastLetters);
            }
            $lastLetters[] = $letter;
            $purified .= $letter;
        }
        return $purified;
    }

    /**
     * Determine if the provided word seems to have repeated letters
     *
     * @param string $word
     * @return bool
     */
    private function hasRepeatedLetters(string $word): bool
    {
        $split = mb_str_split($word);
        $countedValues = array_count_values($split);
        foreach ($countedValues as $value) {
            if (3 <= $value) {
                return true;
            }
        }
        return false;
    }
}
