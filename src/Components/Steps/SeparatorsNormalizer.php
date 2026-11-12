<?php

declare(strict_types=1);

namespace ProfanityFilter\Components\Steps;

use ProfanityFilter\Components\Steps\Support\NormalizerStepInterface;

final class SeparatorsNormalizer implements NormalizerStepInterface
{
    /**
     * @var list<string>
     */
    private const array EXCLUDED = ['.', ',', ';', ':', '!', '?', '(', ')', '[', ']', '{', '}', '"', '\'', '/', '\\', '-', '_'];

    /**
     * @inheritDoc
     */
    public function apply(string $word): string
    {
        return str_ireplace(self::EXCLUDED, '', $word);
    }
}
