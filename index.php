<?php

require_once 'vendor/autoload.php';

use ProfanityFilter\ProfanityFilter;

$filter = new ProfanityFilter();
$text = "This is a test with con and pute in it.";
$cleanedText = $filter->clean($text);

echo $cleanedText;