<?php

namespace ProfanityFilter;

/*
 * Class ProfanityFilter
 *
 * Filters profanity from text using a customizable blacklist.
 *
 * @package ProfanityFilter
 * @version 0.0.1
 * @author Quentin SCHIFFERLE <dev.trope@gmail.com>
 * @license MIT
 */
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

    public function __construct(
        ProfanityLevel $level = ProfanityLevel::MEDIUM,
        string $jsonPath = __DIR__ . '/../data/blacklist.json'
    ) {
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

    /**
     * Adds a word to the blacklist if it does not already exist.
     *
     * @param string $word
     */
    public function addWord(string $word): void
    {
        if (! in_array($word, $this->blacklist, true)) {
            $this->blacklist[] = $word;
        }
    }

    /**
     * Removes a word from the blacklist.
     *
     * @param string $word
     */
    public function removeWord(string $word): void
    {
        $this->blacklist = array_filter($this->blacklist, function ($w) use ($word) {
            return $w !== $word;
        });
    }

    /**
     * Checks if the text contains any profanity from the blacklist.
     *
     * @param string|null $text
     * @return bool
     */
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

    /**
     * Cleans the text by replacing profanity with a specified replacement character.
     *
     * @param string|null $text
     * @param string $replacement
     * @return string|null
     */
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
