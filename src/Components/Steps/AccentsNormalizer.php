<?php

namespace ProfanityFilter\Components\Steps;

use ProfanityFilter\Components\Steps\Support\NormalizerStepInterface;

final class AccentsNormalizer implements NormalizerStepInterface
{
    /**
     * @var array<string, string>
     */
    private const array ACCENTS = [
        'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae',
        'ç' => 'c',
        'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
        'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
        'ñ' => 'n',
        'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'œ' => 'oe',
        'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
        'ý' => 'y', 'ÿ' => 'y',
        'ß' => 'ss',
    ];

    /**
     * @inheritDoc
     */
    public function apply(string $word): string
    {
        $word = strtr(mb_strtolower($word), self::ACCENTS);
        return $word;
    }
}
