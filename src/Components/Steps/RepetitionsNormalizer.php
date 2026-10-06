<?php

declare(strict_types=1);

namespace ProfanityFilter\Components\Steps;

use ProfanityFilter\Components\Steps\Support\NormalizerStepInterface;

final class RepetitionsNormalizer implements NormalizerStepInterface
{
    /**
     * @inheritDoc
     */
    public function apply(string $word): string
    {
        if (false === $this->hasRepeatedLetters($word)) {
            return $word;
        }
        
        $purified = '';
        $lastLetters = [];
        foreach (mb_str_split($word) as $letter) {
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
        $split = mb_str_split($word);
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
}
