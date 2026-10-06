<?php

declare(strict_types=1);

namespace ProfanityFilter;

use ProfanityFilter\Components\Detection;
use ProfanityFilter\Components\Normalizer;
use ProfanityFilter\Components\WordsList;
use ProfanityFilter\Exceptions\InvalidBlacklistException;
use ProfanityFilter\Exceptions\LocaleException;
use ProfanityFilter\Exceptions\MissingBlacklistFileException;
use UnexpectedValueException;

final class ProfanityFilter
{
    /**
     * @var array<string, true>
     */
    private array $profanities = [];

    /**
     * @var Normalizer
     */
    private Normalizer $normalizer;

    /**
     * @var string
     */
    private const string DEFAULT_REPLACEMENT = '*';

    /**
     * @param string $locale
     * @throws LocaleException
     * @throws MissingBlacklistFileException
     * @throws InvalidBlacklistException
     */
    public function __construct(private string $locale = 'en')
    {
        $this->normalizer = Normalizer::default();
        $words = new WordsList($locale);
        foreach ($words->getAll() as $word) {
            $this->profanities[$this->normalizer->normalize($word)] = true;
        }
    }

    /**
     * @param string $content
     * @param string $replacement
     * @return string
     */
    public function clean(
        string $content,
        string $replacement = self::DEFAULT_REPLACEMENT,
        bool $partial = false
    ): string {
        $tokens = $this->tokenize($content);
        foreach ($this->detect($tokens) as $match) {
            $tokens[$match->position * 2] = $this->censorWord($match->original, $replacement, $partial);
        }
        return implode('', $tokens);
    }

    /**
     * @param string $content
     * @return bool
     */
    public function containsProfanity(string $content): bool
    {
        return false === empty($this->getMatches($content));
    }

    /**
     * @param string $content
     * @return Detection[]
     */
    public function getMatches(string $content): array
    {
        return $this->detect($this->tokenize($content));
    }

    /**
     * @param String[]|string $words
     * @return void
     */
    public function addWords(array|string $words): void
    {
        foreach ((array) $words as $word) {
            if ('' === trim($word)) {
                continue;
            }
            $this->profanities[$this->normalizer->normalize($word)] = true;
        }
    }

    /**
     * @param String[]|string $words
     * @return void
     */
    public function removeWords(array|string $words): void
    {
        foreach ((array) $words as $word) {
            if ('' === trim($word)) {
                continue;
            }
            unset($this->profanities[$this->normalizer->normalize($word)]);
        }
    }

    /**
     * @param string $content
     * @return String[]
     */
    private function tokenize(string $content): array
    {
        /**
         * It's really important to understand what will be returned by this method because it can be
         * hard to understand in the clean and the getMatches methods what the division and the multiplication
         * by 2 are meaning.
         * For example, if the sentence is "This shit is funny", tokenize will return:
         * 0: This
         * 1: " "
         * 2: shit
         * 3: " "
         * 4: is
         *
         * And so on, BUT the position in matches has to be the position of the word in the sentence not in this array.
         * So again, here "shit" is at the position number 1 in the sentence but in the position number 2 in this array.
         * We have to adjust this index in the clean and the getMatches methods to return the good results.
         */
        if (false === $parts = preg_split('/(\s+|\')/', $content, -1, PREG_SPLIT_DELIM_CAPTURE)) {
            throw new UnexpectedValueException("Unexpected error on tokenize");
        }
        return $parts;
    }

    /**
     * @param string $word
     * @param string $replacement
     * @param bool $partial
     * @return string
     */
    private function censorWord(string $word, string $replacement, bool $partial): string
    {
        if (true === $partial && mb_strlen($word) > 2) {
            $firstLetter = mb_substr($word, 0, 1);
            $lastLetter = mb_substr($word, mb_strlen($word) - 1, 1);
            return $firstLetter . str_repeat($replacement, mb_strlen($word) - 2) . $lastLetter;
        }
        return str_repeat($replacement, mb_strlen($word));
    }

    /**
     * @param String[] $tokens
     * @return Detection[]
     */
    private function detect(array $tokens): array
    {
        $matches = [];
        foreach ($tokens as $index => $token) {
            /**
             * Every odd numbered index is a space or a line break so we ignore them
             */
            if (1 === $index % 2) {
                continue;
            }

            $normalized = $this->normalizer->normalize($token);
            if (isset($this->profanities[$normalized])) {
                $matches[] = new Detection($token, $normalized, intdiv($index, 2));
            }
        }
        return $matches;
    }
}
