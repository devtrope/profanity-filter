<?php

namespace ProfanityFilter;

class ProfanityFilter
{
    protected array $blacklists = [];
    protected array $blacklist = [];

    public function __construct(string $level = 'medium', string $jsonPath = __DIR__ . '/../data/blacklist.json')
    {
        if (file_exists($jsonPath)) {
            $this->blacklists = json_decode(file_get_contents($jsonPath), true);
        }
        
        $this->blacklist = [];

        foreach (['low', 'medium', 'high'] as $key) {
            if (array_key_exists($key, $this->blacklists)) {
                $this->blacklist = array_merge($this->blacklist, $this->blacklists[$key]);
            }
            
            if ($key === $level) {
                break;
            }
        }
    }

    public function addWord(string $word): void
    {
        if (! in_array($word, $this->blacklist, true)) {
            $this->blacklist[] = $word;
        }
    }

    public function removeWord(string $word): void
    {
        $this->blacklist = array_filter($this->blacklist, function ($w) use ($word) {
            return $w !== $word;
        });
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