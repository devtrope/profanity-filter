<?php

declare(strict_types=1);

namespace ProfanityFilter;

use ProfanityFilter\Exceptions\InvalidBlacklistException;
use ProfanityFilter\Exceptions\MissingBlacklistFileException;
use ProfanityFilter\Support\Detection;
use UnexpectedValueException;

final class ProfanityFilter
{
    /**
     * @var array<string, true>
     */
    private array $profanities = [];
    private const string DEFAULT_REPLACEMENT = '*';
    private const array LEETSPEAK = [
        '4' => 'A',
        '@' => 'A',
        '8' => 'B',
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

    /**
     * @param string $locale
     * @throws MissingBlacklistFileException
     * @throws InvalidBlacklistException
     */
    public function __construct(private readonly string $locale = 'en')
    {
        $blacklist = dirname(__DIR__) . "/data/blacklist.{$this->locale}.json";
        if (false === file_exists($blacklist)) {
            throw new MissingBlacklistFileException("The blacklist file {$blacklist} does not exist");
        }

        try {
            $words = json_decode((string) file_get_contents($blacklist), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new InvalidBlacklistException("Invalid JSON in {$blacklist}", 0, $e);
        }

        if (false === \is_array($words)) {
            throw new InvalidBlacklistException("{$blacklist} must contain a list of words");
        }

        /**
         * @var String[] $words
         */
        foreach ($words as $word) {
            $this->profanities[$word] = true;
        }
    }

    /**
     * @param string $content
     * @param string $replacement
     * @return string
     */
    public function clean(string $content, string $replacement = self::DEFAULT_REPLACEMENT): string
    {
        $tokens = $this->tokenize($content);
        /**
         * @var Detection $match
         */
        foreach ($this->getMatches($content) as $match) {
            $tokens[$match->position * 2] = str_repeat($replacement, mb_strlen($match->original));
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
        $matches = [];
        foreach ($this->tokenize($content) as $index => $token) {
            /**
             * Every odd numbered index is a space or a line break so we ignore them
             */
            if (1 === $index % 2) {
                continue;
            }

            foreach ($this->profanities as $profanity => $value) {
                if ($this->normalize($token) === $profanity) {
                    $matches[] = new Detection($token, $profanity, intdiv($index, 2));
                }
            }
        }
        return $matches;
    }

    /**
     * @param String[]|string $words
     * @return void
     */
    public function addWords(array|string $words): void
    {
        foreach ((array) $words as $word) {
            $this->profanities[$word] = true;
        }
    }

    /**
     * @param String[]|string $words
     * @return void
     */
    public function removeWords(array|string $words): void
    {
        foreach ((array) $words as $word) {
            unset($this->profanities[$word]);
        }
    }

    /**
     * @param string $word
     * @return string
     */
    private function normalize(string $word): string
    {
        $word = $this->leetspeakInverter($word);
        $word = $this->removeRepeatedLetters($word);
        $word = $this->removeSeparators($word);
        return mb_strtolower($word);
    }

    /**
     * @param string $word
     * @return string
     */
    private function leetspeakInverter(string $word): string
    {
        // Avoid false positives as 455 being converted to ASS with the correspondance table
        if (is_numeric($this->removeSeparators($word))) {
            return $word;
        }
        
        $purified = '';
        foreach (mb_str_split($word) as $letter) {
            if (isset(self::LEETSPEAK[$letter])) {
                $letter = self::LEETSPEAK[$letter];
            }
            $purified .= $letter;
        }
        return $purified;
    }

    /**
     * @param string $word
     * @return string
     */
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
        $length = \count($split);
        for ($i = 0; $i < $length; $i++) {
            $current = $split[$i];
            /**
             * We want to check if the next and previous characters are identical to the
             * current one, because we assume that three repeated letters are enough
             */
            if (
                (isset($split[$i - 1]) && $split[$i - 1] === $current) &&
                (isset($split[$i + 1]) && $split[$i + 1] === $current)
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $content
     * @return array<int, string>
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
        if (false === $parts = preg_split('/(\s+)/', $content, -1, PREG_SPLIT_DELIM_CAPTURE)) {
            throw new UnexpectedValueException("Unexpected error on tokenize");
        }
        return $parts;
    }

    /**
     * @param string $word
     * @return string
     */
    private function removeSeparators(string $word): string
    {
        $excluded = ['.', ',', ';', ':', '!', '?', '(', ')', '[', ']', '{', '}', '"', '\''];
        foreach ($excluded as $item) {
            if (false !== mb_stripos($word, $item)) {
                $word = str_ireplace($item, '', $word);
            }
        }
        return $word;
    }
}
