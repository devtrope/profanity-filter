<?php

namespace ProfanityFilter\Components\Steps;

use ProfanityFilter\Components\Steps\Support\NormalizerStepInterface;

final class SeparatorsNormalizer implements NormalizerStepInterface
{
    /**
     * @inheritDoc
     */
    public function apply(string $word): string
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
