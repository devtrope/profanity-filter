<?php

require_once 'vendor/autoload.php';

use ProfanityFilter\ProfanityFilter;

$filter = new ProfanityFilter();
$text = "This is a test with con and pute in it.";

if ($filter->containsProfanity($text)) {
    echo "Profanity detected!\n";
    $cleanedText = $filter->clean($text);
    echo "Cleaned text: " . $cleanedText . "\n";
} else {
    echo "No profanity detected.\n";
}