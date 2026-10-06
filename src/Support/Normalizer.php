<?php

namespace ProfanityFilter\Support;

final class Normalizer
{
    /**
     * @var array<int|string, string>
     */
    private const array LEETSPEAK = [
        '4' => 'A', '@' => 'A', '8' => 'B', '3' => 'E', '6' => 'G', '9' => 'G',
        '1' => 'I', '|' => 'I', '0' => 'O', '5' => 'S', '$' => 'S', '7' => 'T',
        '+' => 'T', '2' => 'Z'
    ];

    /**
     * @var array<string, string>
     */
    private const array ACCENTS = [
        'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae',
        'ç' => 'c',
        'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
        'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
        'ñ' => 'n',
        'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'œ' => 'oe',
        'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
        'ý' => 'y', 'ÿ' => 'y',
        'ß' => 'ss',
    ];

    /**
     * @param string $word
     * @return string
     */
    public function normalize(string $word): string
    {
        $word = $this->leetspeakInverter($word);
        $word = $this->removeRepeatedLetters($word);
        $word = $this->removeSeparators($word);
        $word = $this->removeAccents($word);
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
            if ((isset($split[$i - 1]) && $split[$i - 1] === $current) &&
                (isset($split[$i + 1]) && $split[$i + 1] === $current)
            ) {
                return true;
            }
        }
        return false;
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

    /**
     * @param string $string
     * @return string
     */
    private function removeAccents(string $string): string
    {
        $string = strtr(mb_strtolower($string), self::ACCENTS);
        return $string;
    }
}
