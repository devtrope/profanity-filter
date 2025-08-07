<?php

namespace ProfanityFilter;

class ProfanityFilter
{
    /**
     * @var array<string, string[]>
     */
    protected array $blacklists = [];

    /**
     * @var string[]
     */
    protected array $blacklist = [];

    public function __construct(ProfanityLevel $level = ProfanityLevel::MEDIUM, string $jsonPath = __DIR__ . '/../data/blacklist.json')
    {
        if (file_exists($jsonPath)) {
            $content = file_get_contents($jsonPath);

            if (! $content) {
                throw new \RuntimeException("Failed to read the blacklist file: $jsonPath");
            }

            $jsonContent = json_decode($content, true);

            if (! is_array($jsonContent)) {
                throw new \RuntimeException("Invalid JSON format in the blacklist file: $jsonPath");
            }

            /** 
             * @var array<string, string[]> $jsonContent
             */
            $this->blacklists = $jsonContent;
        }
        
        $this->blacklist = [];

        foreach (ProfanityLevel::cases() as $key) {
            $keyName = strtolower($key->name);

            if (array_key_exists($keyName, $this->blacklists)) {
                $this->blacklist = array_merge($this->blacklist, $this->blacklists[$keyName]);
            }
            
            if ($keyName === strtolower($level->name)) {
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

    public function containsProfanity(?string $text): bool
    {
        if ($text === null) {
            return false;
        }

        foreach ($this->blacklist as $word) {
            if (stripos($text, $word) !== false) {
                return true;
            }
        }
        
        return false;
    }

    public function clean(?string $text, string $replacement = '*'): string|null
    {
        foreach ($this->blacklist as $word) {
            $text = preg_replace(
                '/\b' . preg_quote($word, '/') . '\b/i',
                str_repeat($replacement, strlen($word)),
                (string) $text
            );
        }

        return $text;
    }
}
