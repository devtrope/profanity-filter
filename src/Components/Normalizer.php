<?php

declare(strict_types=1);

namespace Devtrope\ProfanityFilter\Components;

use Devtrope\ProfanityFilter\Components\Steps\AccentsNormalizer;
use Devtrope\ProfanityFilter\Components\Steps\LeetspeakNormalizer;
use Devtrope\ProfanityFilter\Components\Steps\LowercaseNormalizer;
use Devtrope\ProfanityFilter\Components\Steps\RepetitionsNormalizer;
use Devtrope\ProfanityFilter\Components\Steps\SeparatorsNormalizer;
use Devtrope\ProfanityFilter\Components\Steps\Support\NormalizerStepInterface;

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
