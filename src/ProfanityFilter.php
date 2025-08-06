<?php

namespace ProfanityFilter;

class ProfanityFilter
{
    protected array $blacklist = [];

    public function __construct()
    {
        $this->blacklist = [
            'con',
            'pute'
        ];
    }

    public function containsProfanity(string $text): bool
    {
        foreach ($this->blacklist as $word) {
            if (stripos($text, $word) !== false) {
                return true;
            }
        }
        
        return false;
    }

    public function clean(string $text, string $replacement = '*'): string
    {
        foreach ($this->blacklist as $word) {
            $text = preg_replace('/\b' . preg_quote($word, '/') . '\b/i', str_repeat($replacement, strlen($word)), $text);
        }

        return $text;
    }
}