<?php

namespace ProfanityFilter\Components\Steps\Support;

interface NormalizerStepInterface
{
    /**
     * @param string $word
     * @return string
     */
    public function apply(string $word): string;
}
