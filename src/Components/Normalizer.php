<?php

namespace ProfanityFilter\Components;

use ProfanityFilter\Components\Steps\AccentsNormalizer;
use ProfanityFilter\Components\Steps\LeetspeakNormalizer;
use ProfanityFilter\Components\Steps\LowercaseNormalizer;
use ProfanityFilter\Components\Steps\RepetitionsNormalizer;
use ProfanityFilter\Components\Steps\SeparatorsNormalizer;
use ProfanityFilter\Components\Steps\Support\NormalizerStepInterface;

final class Normalizer
{
    /**
     * @param list<NormalizerStepInterface> $steps
     */
    public function __construct(private readonly array $steps)
    {
    }

    /**
     * @return Normalizer
     */
    public static function default(): self
    {
        return new self([
            new LowercaseNormalizer(),
            new SeparatorsNormalizer(),
            new LeetspeakNormalizer(),
            new RepetitionsNormalizer(),
            new AccentsNormalizer()
        ]);
    }

    /**
     * @param string $word
     * @return string
     */
    public function normalize(string $word): string
    {
        foreach ($this->steps as $step) {
            $word = $step->apply($word);
        }
        return $word;
    }
}
