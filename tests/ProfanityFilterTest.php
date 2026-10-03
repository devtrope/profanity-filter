<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use ProfanityFilter\Support\Detection;
use ProfanityFilter\Exceptions\MissingBlacklistFileException;
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

    public function testCleanWithFrenchLocale(): void
    {
        $filter = new ProfanityFilter(locale: 'fr');
        $this->assertSame('Fils de ****', $filter->clean('Fils de pute'));
    }

    public function testCleanWithAccentInWord(): void
    {
        $filter = new ProfanityFilter(locale: 'fr');
        $this->assertSame('T\'es un ******', $filter->clean('T\'es un enculé'));
    }

    public function testCleanWithGermanLocale(): void
    {
        $filter = new ProfanityFilter(locale: 'de');
        $this->assertSame('*******', $filter->clean('scheiße'));
    }

    public function testThrowsWhenLocaleDoesNotExist(): void
    {
        $this->expectException(MissingBlacklistFileException::class);
        new ProfanityFilter(locale: 'pl');
    }

    public function testCleanWithCustomWord(): void
    {
        $filter = new ProfanityFilter();
        $filter->addWords('test');
        $this->assertSame('This **** is a ****', $filter->clean('This shit is a test'));
    }

    public function testCleanWithCustomWords(): void
    {
        $filter = new ProfanityFilter();
        $filter->addWords(['this', 'test']);
        $this->assertSame('**** **** is a ****', $filter->clean('This shit is a test'));
    }

    public function testCleanWithRemovedWord(): void
    {
        $filter = new ProfanityFilter();
        $filter->removeWords('shit');
        $this->assertSame('This shit is funny', $filter->clean('This shit is funny'));
    }

    public function testCleanWithRemovedWords(): void
    {
        $filter = new ProfanityFilter();
        $filter->removeWords(['shit', 'fuck']);
        $this->assertSame('This shit is funny as fuck', $filter->clean('This shit is funny as fuck'));
    }

    public function testCleanWithPartialCensor(): void
    {
        $this->assertSame('This s**t is funny as f**k', $this->filter->clean(content: 'This shit is funny as fuck', partial: true));
    }

    public function testContainsWithGermanLocale(): void
    {
        $filter = new ProfanityFilter(locale: 'de');
        $this->assertTrue( $filter->containsProfanity('scheiße'));
    }

    public static function cleanProvider(): iterable
    {
        yield 'mot simple'                         => ['This shit is funny', 'This **** is funny'];
        yield 'même mot deux fois'                 => ['This shit is funny, shit', 'This **** is funny, ****'];
        yield 'début de phrase'                    => ['Shit happens', '**** happens'];
        yield 'séparateurs'                        => ['This s.h.i.t is funny', 'This ******* is funny'];
        yield 'point en fin de mot'                => ['This shit. is funny', 'This ***** is funny'];
        yield 'point d\'exclamation en fin de mot' => ['This shit! is funny', 'This ***** is funny'];
        yield 'mot entre parenthèses'              => ['This (shit) is funny', 'This ****** is funny'];
        yield 'leetspeak'                          => ['This sh1t is funny', 'This **** is funny'];
        yield 'lettres répétées'                   => ['This shiiiiiit is funny', 'This ********* is funny'];
        yield 'lettres répétées et casse'          => ['This sHiiIiIiT is funny', 'This ********* is funny'];
        yield 'lettres répétées et leetspeak'      => ['This shii111t is funny', 'This ******** is funny'];
        yield 'retour à la ligne'                  => ["This shit\nis funny", "This ****\nis funny"];
        yield 'retour + espace'                    => ["This shit\n is funny", "This ****\n is funny"];
        yield 'espaces multiples'                  => ["This  shit   is funny", "This  ****   is funny"];
        yield 'texte avec \r\n'                    => ["This shit\r\nis funny", "This ****\r\nis funny"];
        yield 'texte entre espaces'                => ["   This shit\r\nis funny   ", "   This ****\r\nis funny   "];
        yield 'texte entre sauts de ligne'         => ["\n\n\n\nThis shit\r\nis funny\n\n\n\n", "\n\n\n\nThis ****\r\nis funny\n\n\n\n"];
        yield 'mot dans un autre mot'              => ['This is a class', 'This is a class'];
        yield 'aucune profanité'                   => ['Hello world', 'Hello world'];
        yield 'faux leetspeak'                     => ['The total is 455', 'The total is 455'];
        yield 'faux leetspeak avec caractère'      => ['The total is 455€', 'The total is 455€'];
        yield 'faux leetspeak avec séparateurs'    => ['The total is 4.5.5', 'The total is 4.5.5'];
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

