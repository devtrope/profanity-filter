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

    #[DataProvider('cleanProvider')]
    public function testCleanIsIdempotent(string $input, string $expected): void
    {
        $once = $this->filter->clean($input);
        $this->assertSame($once, $this->filter->clean($once));
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

    public function testCleanWithCustomReplacement(): void
    {
        $this->assertSame('You are a piece of ####', $this->filter->clean('You are a piece of shit', replacement: '#'));
    }

    public function testSuccessiveCallsAreIdempotent(): void
    {
        $this->assertSame('This **** is funny', $this->filter->clean('This shit is funny'));
        $this->assertSame('Hello world', $this->filter->clean('Hello world'));
        $this->assertSame([], $this->filter->getMatches('Hello world'));
    }

    #[DataProvider('partialProvider')]
    public function testCleanWithPartialCensor(string $locale, string $input, string $expected): void
    {
        $filter = new ProfanityFilter(locale: $locale);
        $this->assertSame($expected, $filter->clean($input, partial: true));
    }

    #[DataProvider('localeProvider')]
    public function testCleanWithLocale(string $locale, string $input, string $expected): void
    {
        $filter = new ProfanityFilter(locale: $locale);
        $this->assertSame($expected, $filter->clean($input));
    }

    #[DataProvider('localeProvider')]
    public function testContainsProfanityWithLocale(string $locale, string $input, string $expected): void
    {
        $filter = new ProfanityFilter(locale: $locale);
        $this->assertSame($input !== $expected, $filter->containsProfanity($input));
    }

    public function testGetMatchesWithAccentedWord(): void
    {
        $filter = new ProfanityFilter(locale: 'fr');
        $this->assertEquals([new Detection('enculé', 'encule', 3)], $filter->getMatches('Tu es un enculé'));
    }

    #[DataProvider('unknownLocaleProvider')]
    public function testThrowsWhenLocaleDoesNotExist(string $locale): void
    {
        $this->expectException(MissingBlacklistFileException::class);
        new ProfanityFilter(locale: $locale);
    }

    #[DataProvider('addedWordsProvider')]
    public function testAddWords(array|string $words, string $input, string $expected): void
    {
        $this->filter->addWords($words);
        $this->assertSame($expected, $this->filter->clean($input));
    }

    public function testAddEmptyWordIsIgnored(): void
    {
        $this->filter->addWords('');
        $this->assertFalse($this->filter->containsProfanity(' Hello World '));
    }

    #[DataProvider('removedWordsProvider')]
    public function testRemoveWords(array|string $words, string $input): void
    {
        $this->filter->removeWords($words);
        $this->assertSame($input, $this->filter->clean($input));
        $this->assertFalse($this->filter->containsProfanity($input));
    }

    public function testRemoveUnknownWordDoesNothing(): void
    {
        $this->filter->removeWords('unknown');
        $this->assertSame('This **** is funny', $this->filter->clean('This shit is funny'));
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

    public static function partialProvider(): iterable
    {
        yield 'mots de quatre lettres'      => ['en', 'This shit is funny as fuck', 'This s**t is funny as f**k'];
        yield 'mot de trois lettres'        => ['en', 'What an ass', 'What an a*s'];
        yield 'mot de deux lettres'         => ['pt', 'cu', '**'];
        yield 'lettre accentuée conservée'  => ['fr', 'enculé', 'e****é'];
    }

    public static function localeProvider(): iterable
    {
        yield 'fr mot simple'               => ['fr', 'Fils de pute', 'Fils de ****'];
        yield 'fr accent'                   => ['fr', 'Tu es un enculé', 'Tu es un ******'];
        yield 'fr majuscules accentuées'    => ['fr', 'ENCULÉ', '******'];
        yield 'fr ligature'                 => ['fr', 'Mon cœur', 'Mon cœur'];
        yield 'fr accents sans profanité'   => ['fr', 'Où est l\'été', 'Où est l\'été'];
        yield 'de eszett'                   => ['de', 'scheiße', '*******'];
        yield 'de sans eszett'              => ['de', 'Scheisse', '********'];
        yield 'de majuscules'               => ['de', 'ARSCHLOCH', '*********'];
        yield 'es mot simple'               => ['es', 'Qué mierda', 'Qué ******'];
        yield 'es accent'                   => ['es', 'cabrón', '******'];
        yield 'it mot simple'               => ['it', 'Che cazzo vuoi', 'Che ***** vuoi'];
        yield 'pt mot simple'               => ['pt', 'Que merda', 'Que *****'];
        yield 'pt mot de deux lettres'      => ['pt', 'cu', '**'];
        yield 'pt accent'                   => ['pt', 'seu otário', 'seu ******'];
    }
 
    public static function unknownLocaleProvider(): iterable
    {
        yield 'locale inconnue' => ['pl'];
        yield 'locale vide'     => [''];
        yield 'chemin relatif'  => ['../x'];
    }
 
    public static function addedWordsProvider(): iterable
    {
        yield 'un mot'               => ['test', 'This shit is a test', 'This **** is a ****'];
        yield 'plusieurs mots'       => [['this', 'test'], 'This shit is a test', '**** **** is a ****'];
        yield 'casse différente'     => ['Troll', 'Big TROLL here', 'Big ***** here'];
        yield 'mot accentué'         => ['café', 'Un café', 'Un ****'];
        yield 'texte en leetspeak'   => ['troll', 'Big tr0ll here', 'Big ***** here'];
    }
 
    public static function removedWordsProvider(): iterable
    {
        yield 'un mot'            => ['shit', 'This shit is funny'];
        yield 'plusieurs mots'    => [['shit', 'fuck'], 'This shit is funny as fuck'];
        yield 'casse différente'  => ['SHIT', 'This shit is funny'];
    }
}
