# ProfanityFilter

A lightweight and customizable PHP package for detecting and censoring profanity in text.  
Includes multiple sensitivity levels, support for custom word lists, and is PSR-12 and PHPStan compatible.

[![Latest Stable Version](https://img.shields.io/packagist/v/devtrope/profanity-filter.svg)](https://packagist.org/packages/devtrope/profanity-filter)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![Static Analysis: PHPStan](https://img.shields.io/badge/static%20analysis-phpstan-blue)](https://phpstan.org)
[![Code Style: PSR-12](https://img.shields.io/badge/code%20style-PSR--12-lightgrey.svg)](https://www.php-fig.org/psr/psr-12/)
[![Downloads](https://img.shields.io/packagist/dt/devtrope/profanity-filter.svg)](https://packagist.org/packages/devtrope/profanity-filter)
[![PHP Version](https://img.shields.io/packagist/php-v/devtrope/profanity-filter.svg)](https://packagist.org/packages/devtrope/profanity-filter)


---

## Features

- Detects offensive words in strings
- Supports multiple filtering levels: `low`, `medium`, `high`
- Supports multiple languages (en, fr)
- Custom blacklist support via JSON file
- Add or remove words dynamically at runtime
- Fully typed and ready for static analysis
- Easy integration with existing projects

---

## Installation

Install via Composer:

```bash
composer require devtrope/profanity-filter
```

---

## Usage

### Basic usage

```php
use ProfanityFilter\ProfanityFilter;
use ProfanityFilter\ProfanityLevel;

$filter = new ProfanityFilter();
$text = "You little piece of shit!";
echo $filter->clean($text); // You little piece of ****!
```

### With a specific language

```php
$filter = new ProfanityFilter(ProfanityLevel::HIGH, 'fr');
```

### With a custom blacklist

```php
$filter = new ProfanityFilter(
    ProfanityLevel::HIGH,
    'fr',
    __DIR__ . '/my-custom-blacklist.json'
);
```

```json
{
    "low": ["wordlow"],
    "medium": ["wordmedium"],
    "high": ["wordhigh"]
}
```

### Add or remove words at runtime

```php
$filter->addWord('uglyword');
$filter->removeWord('otheruglyword');
```

### Check if a text contains profanity

```php
if ($filter->containsProfanity($text)) {
    echo "Inappropriate content detected!";
}
```

---

## Language support

Currently supported locales:

- `en` - English
- `fr` - Français

---

## Profanity levels

Each level includes all words from the previous level:

| Level    | Description                 |
| -------- | --------------------------- |
| LOW      | Mild profanity              |
| MEDIUM   | Default, balanced           |
| HIGH     | Agressive filtering         |

---

## License

MIT License - see [LICENSE](LICENSE.md) for details.