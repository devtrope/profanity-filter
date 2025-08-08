<?php

namespace ProfanityFilter;

/*
 * Class ProfanityFilter
 *
 * Filters profanity from text using a customizable blacklist.
 *
 * @package ProfanityFilter
 * @version 0.0.4
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

    /**
     * @var string
     */
    private const DEFAULT_LOCALE = 'en';

    /**
     * @var string[]
     */
    private array $supportedLocales = ['en', 'fr'];

    /**
     * @var string
     */
    protected string $defaultReplacement = '*';

    /**
     * ProfanityFilter constructor.
     *
     * @param ProfanityLevel $level
     * @param string|null $language
     * @param string|null $customBlacklistPath
     * @throws \RuntimeException
     */
    public function __construct(
        ProfanityLevel $level = ProfanityLevel::MEDIUM,
        ?string $language = null,
        ?string $customBlacklistPath = null
    ) {
        if (! function_exists('mb_strlen')) {
            throw new \RuntimeException('The mbstring extension is required for this class to work.');
        }

        $locale = $this->getLocale($language);
        $jsonPath = $this->getBlacklistFile($customBlacklistPath, $locale);
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
    public function clean(?string $text, ?string $replacement = null): string|null
    {
        $replacement = $replacement ?? $this->defaultReplacement;
        
        foreach ($this->blacklist as $word) {
            $pattern = '/' . preg_quote($word, '/') . '/iu';

            $text = preg_replace_callback($pattern, function ($matches) use ($replacement) {
                return str_repeat($replacement, mb_strlen($matches[0], 'UTF-8'));
            }, (string) $text);
        }

        return $text;
    }

    /**
     * Sets the default replacement character for profanity.
     *
     * @param string $replacement
     */
    public function setDefaultReplacement(string $replacement): void
    {
        $this->defaultReplacement = $replacement;
    }

    /**
     * Returns the locale used for the blacklist.
     *
     * @param string|null $language
     * @return string
     * @throws \RuntimeException
     */
    private function getLocale(?string $language = null): string
    {
        if ($language !== null) {
            $locale = strtolower($language);

            if (! in_array($locale, $this->supportedLocales, true)) {
                throw new \RuntimeException("Unsupported language: $language");
            }

            return $locale;
        }

        if (function_exists('locale_get_default')) {
            $locale = \Locale::getDefault();

            if (! $locale) {
                throw new \RuntimeException('Failed to get the default locale.');
            }

            $locale = substr($locale, 0, 2); // Get the first two characters for language code

            if (! in_array($locale, $this->supportedLocales, true)) {
                throw new \RuntimeException("Unsupported locale: $locale");
            }

            return $locale;
        }

        return self::DEFAULT_LOCALE;
    }

    /**
     * Returns the path to the blacklist file.
     *
     * @param string|null $customBlacklistPath
     * @param string|null $locale
     * @return string
     * @throws \RuntimeException
     */
    private function getBlacklistFile(?string $customBlacklistPath, ?string $locale): string
    {
        if ($customBlacklistPath !== null) {
            if (! file_exists($customBlacklistPath)) {
                throw new \RuntimeException("Custom blacklist file does not exist: $customBlacklistPath");
            }

            return $customBlacklistPath;
        }

        return __DIR__ . '/../data/blacklist.' . $locale . '.json';
    }
}
