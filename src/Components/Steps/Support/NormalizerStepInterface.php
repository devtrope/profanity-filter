<?php

declare(strict_types=1);

namespace Devtrope\ProfanityFilter\Components\Steps\Support;

interface NormalizerStepInterface
{
    /**
     * @param string $word
     * @return string
     */
    public function apply(string $word): string;
}
