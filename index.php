<?php

require_once 'vendor/autoload.php';

use ProfanityFilter\ProfanityFilter;

$filter = new ProfanityFilter('high');
$text = "This is a test with con and pute in it, and punaise and merde too.";
$cleanedText = $filter->clean($text);

echo $cleanedText;