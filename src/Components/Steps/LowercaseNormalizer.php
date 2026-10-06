<?php

namespace ProfanityFilter\Components\Steps;

use ProfanityFilter\Components\Steps\Support\NormalizerStepInterface;

final class LowercaseNormalizer implements NormalizerStepInterface
{
    /**
     * @inheritDoc
     */
    public function apply(string $word): string
    {
        return mb_strtolower($word);
    }
}
