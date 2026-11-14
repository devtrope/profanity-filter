<?php

declare(strict_types=1);

namespace Devtrope\ProfanityFilter\Components\Steps;

use Devtrope\ProfanityFilter\Components\Steps\Support\NormalizerStepInterface;

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
