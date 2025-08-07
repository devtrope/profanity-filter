<?php

require_once 'vendor/autoload.php';

use ProfanityFilter\ProfanityFilter;
use ProfanityFilter\ProfanityLevel;

$filter = new ProfanityFilter(ProfanityLevel::LOW);
$text = "T'es vraiment un fils de pute putain de merde.";
$cleanedText = $filter->clean($text);

echo $cleanedText;