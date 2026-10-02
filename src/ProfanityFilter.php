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
        // Remove all the new lines from the content
        $content = trim(preg_replace('/\s\s+/', ' ', $content));
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
        return false === empty($this->getMatches($content));
    }

    public function getMatches(string $content): array
    {
        // Remove all the new lines from the content
        $content = trim(preg_replace('/\s\s+/', ' ', $content));
        $matches = [];
        foreach (self::PROFANITIES as $profanity) {
            foreach (explode(' ', $content) as $index => $word) {
                if ($this->normalize($word) === $profanity) {
                    $matches[] = new Detection($word, $profanity, $index);
                }
            }
        }
        return $matches;
    }

    private function normalize(string $word): string
    {
        $word = $this->leetspeakInverter($word);
        $word = $this->removeRepeatedLetters($word);
        $excluded = ['.', '_', '*', '-', '/', '\\'];
        foreach ($excluded as $item) {
            if (false !== mb_stripos($word, $item)) {
                $word = str_ireplace($item, '', $word);
            }
        }
        return mb_strtolower($word);
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
        foreach (mb_str_split(mb_strtolower($word)) as $letter) {
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
        $split = mb_str_split(mb_strtolower($word));
        $countedValues = array_count_values($split);
        foreach ($countedValues as $value) {
            if (3 <= $value) {
                return true;
            }
        }
        return false;
    }
}
