<?php

use PHPUnit\Framework\TestCase;
use ProfanityFilter\ProfanityLevel;
use ProfanityFilter\ProfanityFilter;

final class ProfanityFilterTest extends TestCase
{
    public function testClean()
    {
        $filter = new ProfanityFilter(ProfanityLevel::MEDIUM, 'fr');
        $text = "This is a test string with putain.";
        $cleanedText = $filter->clean($text);

        $this->assertEquals("This is a test string with ******.", $cleanedText);
    }
}