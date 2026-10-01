<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use ProfanityFilter\Configuration\FilterConfig;
use ProfanityFilter\ProfanityFilter;

final class ProfanityFilterTest extends TestCase
{
    private ProfanityFilter $filter;

    protected function setUp(): void
    {
        $this->filter = new ProfanityFilter(new FilterConfig());
    }

    public function testCanFilterBadWords(): void
    {
        $text = "This shit is funny";
        $this->assertSame('This **** is funny', $this->filter->clean($text));
    }

    public function testCanSpotBadWords(): void
    {
        $text = "This shit is funny";
        $this->assertTrue($this->filter->containsProfanity($text));
    }

    public function testCustomReplacement(): void
    {
        $text = "This shit is funny";
        $this->assertSame('This #### is funny', $this->filter->clean($text, '#'));
    }
}

