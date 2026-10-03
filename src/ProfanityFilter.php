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
    public function clean(string $content, string $replacement = self::DEFAULT_REPLACEMENT, bool $partial = false): string
    {
        $tokens = $this->tokenize($content);
        /**
         * @var Detection $match
         */
        foreach ($this->getMatches($content) as $match) {
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
        $countedValues = array_count_values($split);
        foreach ($countedValues as $value) {
            if (3 <= $value) {
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

    private function censorWord(string $word, string $replacement, bool $partial): string
    {
        if (true === $partial) {
            $firstLetter = substr($word, 0, 1);
            $lastLetter = substr($word, mb_strlen($word) - 1, 1);
            return $firstLetter . str_repeat($replacement, mb_strlen($word) - 2) . $lastLetter;
        }
        return str_repeat($replacement, mb_strlen($word));
    }

    // This method is based on a response on a StackOverflow topic:
    // Source - https://stackoverflow.com/a/10790734
    // Posted by dynamic, modified by community. See post 'Timeline' for change history
    // Retrieved 2026-10-03, License - CC BY-SA 4.0
    private function removeAccents(string $string): string
    {
        if (false === preg_match('/[\x80-\xff]/', $string)) {
            return $string;
        }

        $chars = [
            // Decompositions for Latin-1 Supplement
            \chr(195).\chr(128) => 'A', \chr(195).\chr(129) => 'A',
            \chr(195).\chr(130) => 'A', \chr(195).\chr(131) => 'A',
            \chr(195).\chr(132) => 'A', \chr(195).\chr(133) => 'A',
            \chr(195).\chr(135) => 'C', \chr(195).\chr(136) => 'E',
            \chr(195).\chr(137) => 'E', \chr(195).\chr(138) => 'E',
            \chr(195).\chr(139) => 'E', \chr(195).\chr(140) => 'I',
            \chr(195).\chr(141) => 'I', \chr(195).\chr(142) => 'I',
            \chr(195).\chr(143) => 'I', \chr(195).\chr(145) => 'N',
            \chr(195).\chr(146) => 'O', \chr(195).\chr(147) => 'O',
            \chr(195).\chr(148) => 'O', \chr(195).\chr(149) => 'O',
            \chr(195).\chr(150) => 'O', \chr(195).\chr(153) => 'U',
            \chr(195).\chr(154) => 'U', \chr(195).\chr(155) => 'U',
            \chr(195).\chr(156) => 'U', \chr(195).\chr(157) => 'Y',
            \chr(195).\chr(159) => 'ss', \chr(195).\chr(160) => 'a',
            \chr(195).\chr(161) => 'a', \chr(195).\chr(162) => 'a',
            \chr(195).\chr(163) => 'a', \chr(195).\chr(164) => 'a',
            \chr(195).\chr(165) => 'a', \chr(195).\chr(167) => 'c',
            \chr(195).\chr(168) => 'e', \chr(195).\chr(169) => 'e',
            \chr(195).\chr(170) => 'e', \chr(195).\chr(171) => 'e',
            \chr(195).\chr(172) => 'i', \chr(195).\chr(173) => 'i',
            \chr(195).\chr(174) => 'i', \chr(195).\chr(175) => 'i',
            \chr(195).\chr(177) => 'n', \chr(195).\chr(178) => 'o',
            \chr(195).\chr(179) => 'o', \chr(195).\chr(180) => 'o',
            \chr(195).\chr(181) => 'o', \chr(195).\chr(182) => 'o',
            \chr(195).\chr(182) => 'o', \chr(195).\chr(185) => 'u',
            \chr(195).\chr(186) => 'u', \chr(195).\chr(187) => 'u',
            \chr(195).\chr(188) => 'u', \chr(195).\chr(189) => 'y',
            \chr(195).\chr(191) => 'y',
            // Decompositions for Latin Extended-A
            \chr(196).\chr(128) => 'A', \chr(196).\chr(129) => 'a',
            \chr(196).\chr(130) => 'A', \chr(196).\chr(131) => 'a',
            \chr(196).\chr(132) => 'A', \chr(196).\chr(133) => 'a',
            \chr(196).\chr(134) => 'C', \chr(196).\chr(135) => 'c',
            \chr(196).\chr(136) => 'C', \chr(196).\chr(137) => 'c',
            \chr(196).\chr(138) => 'C', \chr(196).\chr(139) => 'c',
            \chr(196).\chr(140) => 'C', \chr(196).\chr(141) => 'c',
            \chr(196).\chr(142) => 'D', \chr(196).\chr(143) => 'd',
            \chr(196).\chr(144) => 'D', \chr(196).\chr(145) => 'd',
            \chr(196).\chr(146) => 'E', \chr(196).\chr(147) => 'e',
            \chr(196).\chr(148) => 'E', \chr(196).\chr(149) => 'e',
            \chr(196).\chr(150) => 'E', \chr(196).\chr(151) => 'e',
            \chr(196).\chr(152) => 'E', \chr(196).\chr(153) => 'e',
            \chr(196).\chr(154) => 'E', \chr(196).\chr(155) => 'e',
            \chr(196).\chr(156) => 'G', \chr(196).\chr(157) => 'g',
            \chr(196).\chr(158) => 'G', \chr(196).\chr(159) => 'g',
            \chr(196).\chr(160) => 'G', \chr(196).\chr(161) => 'g',
            \chr(196).\chr(162) => 'G', \chr(196).\chr(163) => 'g',
            \chr(196).\chr(164) => 'H', \chr(196).\chr(165) => 'h',
            \chr(196).\chr(166) => 'H', \chr(196).\chr(167) => 'h',
            \chr(196).\chr(168) => 'I', \chr(196).\chr(169) => 'i',
            \chr(196).\chr(170) => 'I', \chr(196).\chr(171) => 'i',
            \chr(196).\chr(172) => 'I', \chr(196).\chr(173) => 'i',
            \chr(196).\chr(174) => 'I', \chr(196).\chr(175) => 'i',
            \chr(196).\chr(176) => 'I', \chr(196).\chr(177) => 'i',
            \chr(196).\chr(178) => 'IJ',\chr(196).\chr(179) => 'ij',
            \chr(196).\chr(180) => 'J', \chr(196).\chr(181) => 'j',
            \chr(196).\chr(182) => 'K', \chr(196).\chr(183) => 'k',
            \chr(196).\chr(184) => 'k', \chr(196).\chr(185) => 'L',
            \chr(196).\chr(186) => 'l', \chr(196).\chr(187) => 'L',
            \chr(196).\chr(188) => 'l', \chr(196).\chr(189) => 'L',
            \chr(196).\chr(190) => 'l', \chr(196).\chr(191) => 'L',
            \chr(197).\chr(128) => 'l', \chr(197).\chr(129) => 'L',
            \chr(197).\chr(130) => 'l', \chr(197).\chr(131) => 'N',
            \chr(197).\chr(132) => 'n', \chr(197).\chr(133) => 'N',
            \chr(197).\chr(134) => 'n', \chr(197).\chr(135) => 'N',
            \chr(197).\chr(136) => 'n', \chr(197).\chr(137) => 'N',
            \chr(197).\chr(138) => 'n', \chr(197).\chr(139) => 'N',
            \chr(197).\chr(140) => 'O', \chr(197).\chr(141) => 'o',
            \chr(197).\chr(142) => 'O', \chr(197).\chr(143) => 'o',
            \chr(197).\chr(144) => 'O', \chr(197).\chr(145) => 'o',
            \chr(197).\chr(146) => 'OE',\chr(197).\chr(147) => 'oe',
            \chr(197).\chr(148) => 'R',\chr(197).\chr(149) => 'r',
            \chr(197).\chr(150) => 'R',\chr(197).\chr(151) => 'r',
            \chr(197).\chr(152) => 'R',\chr(197).\chr(153) => 'r',
            \chr(197).\chr(154) => 'S',\chr(197).\chr(155) => 's',
            \chr(197).\chr(156) => 'S',\chr(197).\chr(157) => 's',
            \chr(197).\chr(158) => 'S',\chr(197).\chr(159) => 's',
            \chr(197).\chr(160) => 'S', \chr(197).\chr(161) => 's',
            \chr(197).\chr(162) => 'T', \chr(197).\chr(163) => 't',
            \chr(197).\chr(164) => 'T', \chr(197).\chr(165) => 't',
            \chr(197).\chr(166) => 'T', \chr(197).\chr(167) => 't',
            \chr(197).\chr(168) => 'U', \chr(197).\chr(169) => 'u',
            \chr(197).\chr(170) => 'U', \chr(197).\chr(171) => 'u',
            \chr(197).\chr(172) => 'U', \chr(197).\chr(173) => 'u',
            \chr(197).\chr(174) => 'U', \chr(197).\chr(175) => 'u',
            \chr(197).\chr(176) => 'U', \chr(197).\chr(177) => 'u',
            \chr(197).\chr(178) => 'U', \chr(197).\chr(179) => 'u',
            \chr(197).\chr(180) => 'W', \chr(197).\chr(181) => 'w',
            \chr(197).\chr(182) => 'Y', \chr(197).\chr(183) => 'y',
            \chr(197).\chr(184) => 'Y', \chr(197).\chr(185) => 'Z',
            \chr(197).\chr(186) => 'z', \chr(197).\chr(187) => 'Z',
            \chr(197).\chr(188) => 'z', \chr(197).\chr(189) => 'Z',
            \chr(197).\chr(190) => 'z', \chr(197).\chr(191) => 's'
        ];

        $string = strtr($string, $chars);
        return $string;
    }

}
