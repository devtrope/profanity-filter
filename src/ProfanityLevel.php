<?php

namespace ProfanityFilter;

/*
 * Enum ProfanityLevel
 *
 * Defines the levels of profanity that can be used to filter text.
 *
 * @package ProfanityFilter
 * @version 0.0.1
 * @author Quentin SCHIFFERLE <dev.trope@gmail.com>
 * @license MIT
 */
enum ProfanityLevel
{
    case LOW;
    case MEDIUM;
    case HIGH;
}
