<?php

namespace ProfanityFilter\Components\Steps;

use ProfanityFilter\Components\Steps\Support\NormalizerStepInterface;

final class SeparatorsNormalizer implements NormalizerStepInterface
{
    /**
     * @var String[]
     */
    private const array EXCLUDED = ['.', ',', ';', ':', '!', '?', '(', ')', '[', ']', '{', '}', '"', '\''];
    
    /**
     * @inheritDoc
     */
    public function apply(string $word): string
    {
        return str_ireplace(self::EXCLUDED, '', $word);
    }
}
