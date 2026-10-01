<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use ProfanityFilter\ProfanityFilter;

final class ProfanityFilterTest extends TestCase
{
    private ProfanityFilter $filter;

    protected function setUp(): void
    {
        $this->filter = new ProfanityFilter();
    }

    public function testCanFilterBadWords(): void
    {
        $text = "This shit is funny";
        $this->assertSame('This **** is funny', $this->filter->clean($text));
    }

    public function testCanFilterSameBadWordTwice(): void
    {
        $text = "This shit is funny, shit";
        $this->assertSame('This **** is funny, ****', $this->filter->clean($text));
    }

    public function testCanFilterBadWordInTheBeginningOfASentence(): void
    {
        $text = "Shit happens";
        $this->assertSame('**** happens', $this->filter->clean($text));
    }

    public function testDoesNotCensorWordsInAnotherWord(): void
    {
        $text = "This is a class";
        $this->assertSame('This is a class', $this->filter->clean($text));
    }

    public function testCanSpotBadWords(): void
    {
        $text = "This shit is funny";
        $this->assertTrue($this->filter->containsProfanity($text));
    }

    public function testCanSpotBadWordInTheBeginningOfASentence(): void
    {
        $text = "Shit happens";
        $this->assertTrue($this->filter->containsProfanity($text));
    }

    public function testDoesNotSpotBadWordInAnotherWord(): void
    {
        $text = "This is a class";
        $this->assertFalse($this->filter->containsProfanity($text));
    }

    public function testCustomReplacement(): void
    {
        $text = "This shit is funny";
        $this->assertSame('This #### is funny', $this->filter->clean($text, '#'));
    }

    public function testReturnsBadWords(): void
    {
        $text = "This shit is funny as fuck";
        $this->assertSame(['shit', 'fuck'], $this->filter->getMatches($text));
    }

    public function testDoesNotReturnDuplicatedBadWords(): void
    {
        $text = "This shit is funny, shit";
        $this->assertSame(['shit'], $this->filter->getMatches($text));
    }
}

