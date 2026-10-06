<?php

namespace ProfanityFilter\Components\Steps;

use ProfanityFilter\Components\Steps\Support\NormalizerStepInterface;

final class LeetspeakNormalizer implements NormalizerStepInterface
{
    /**
     * @var array<int|string, string>
     */
    private const array LEETSPEAK = [
        '4' => 'a', '@' => 'a', '8' => 'b', '3' => 'e', '6' => 'g', '9' => 'g',
        '1' => 'i', '|' => 'i', '0' => 'o', '5' => 's', '$' => 's', '7' => 't',
        '+' => 't', '2' => 'z'
    ];

    /**
     * @inheritDoc
     */
    public function apply(string $word): string
    {
        // Avoid false positives as 455 being converted to ASS with the correspondance table
        if (is_numeric($word)) {
            return $word;
        }
        
        $purified = '';
        foreach (mb_str_split($word) as $letter) {
            if (isset(self::LEETSPEAK[$letter])) {
                $letter = self::LEETSPEAK[$letter];
            }
            $purified .= $letter;
        }
        return $purified;
    }
}
