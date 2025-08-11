<?php

use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use ProfanityFilter\Configuration\FilterLevel;
use ProfanityFilter\ProfanityFilter;

#[CoversMethod(ProfanityFilter::class, 'clean')]
final class ProfanityFilterTest extends TestCase
{
    public function testClean()
    {
        $filter = ProfanityFilter::create()->build();
        $text = "This is a test string with chiant.";
        $cleanedText = $filter->clean($text);

        $this->assertEquals("This is a test string with ******.", $cleanedText);
    }
    public function testCleanLow()
    {
        $filter = ProfanityFilter::create()->level(FilterLevel::LOW)->build();
        $text = "This is a test string with chiant.";
        $cleanedText = $filter->clean($text);

        $this->assertEquals("This is a test string with chiant.", $cleanedText);
    }
}
