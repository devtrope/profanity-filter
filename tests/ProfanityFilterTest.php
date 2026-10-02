<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use ProfanityFilter\Detection;
use ProfanityFilter\ProfanityFilter;

#[UsesClass(Detection::class)]
final class ProfanityFilterTest extends TestCase
{
    private ProfanityFilter $filter;

    protected function setUp(): void
    {
        $this->filter = new ProfanityFilter();
    }

    #[DataProvider('cleanProvider')]
    public function testClean(string $input, string $expected): void
    {
        $this->assertSame($expected, $this->filter->clean($input));
    }

    #[DataProvider('containsProvider')]
    public function testContainsProfanity(string $input, bool $expected): void
    {
        $this->assertSame($expected, $this->filter->containsProfanity($input));
    }

    #[DataProvider('matchesProvider')]
    public function testGetMatches(string $input, array $expected): void
    {
        $this->assertEquals($expected, $this->filter->getMatches($input));
    }

    public static function cleanProvider(): iterable
    {
        yield 'mot simple'                    => ['This shit is funny', 'This **** is funny'];
        yield 'même mot deux fois'            => ['This shit is funny, shit', 'This **** is funny, ****'];
        yield 'début de phrase'               => ['Shit happens', '**** happens'];
        yield 'séparateurs'                   => ['This s.h.i.t is funny', 'This **** is funny'];
        yield 'leetspeak'                     => ['This sh1t is funny', 'This **** is funny'];
        yield 'lettres répétées'              => ['This shiiiiiit is funny', 'This **** is funny'];
        yield 'lettres répétées et casse'     => ['This sHiiIiIiT is funny', 'This **** is funny'];
        yield 'lettres répétées et leetspeak' => ['This shii111t is funny', 'This **** is funny'];
        yield 'retour à la ligne'             => ["This shit\n is funny", 'This **** is funny'];
        yield 'mot dans un autre mot'         => ['This is a class', 'This is a class'];
        yield 'aucune profanité'              => ['Hello world', 'Hello world'];
    }

    public static function containsProvider(): iterable
    {
        foreach (self::cleanProvider() as $name => [$input, $cleaned]) {
            yield $name => [$input, $input !== $cleaned];
        }
    }

    public static function matchesProvider(): iterable
    {
        yield 'deux mots différents' => [
            'This shit is funny as fuck',
            [new Detection('shit', 'shit', 1), new Detection('fuck', 'fuck', 5)],
        ];

        yield 'même mot deux fois' => [
            'This shit is funny, shit',
            [new Detection('shit', 'shit', 1), new Detection('shit', 'shit', 4)],
        ];

        yield 'leetspeak' => [
            'This sh1t is funny',
            [new Detection('sh1t', 'shit', 1)],
        ];

        yield 'séparateurs' => [
            'This s.h.i.t is funny',
            [new Detection('s.h.i.t', 'shit', 1)],
        ];

        yield 'retour à la ligne' => [
            "This shit\n is funny",
            [new Detection('shit', 'shit', 1)],
        ];

        yield 'aucune profanité' => ['This is a class', []];
    }
}

