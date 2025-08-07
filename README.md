# ProfanityFilter

A lightweight and customizable PHP package for detecting and censoring profanity in text.  
Includes multiple sensitivity levels, support for custom word lists, and is PSR-12 and PHPStan compatible.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![Static Analysis: PHPStan](https://img.shields.io/badge/static%20analysis-phpstan-blue)](https://phpstan.org)
[![Code Style: PSR-12](https://img.shields.io/badge/code%20style-PSR--12-lightgrey.svg)](https://www.php-fig.org/psr/psr-12/)

---

## Features

- Detects offensive words in strings
- Supports multiple filtering levels: `low`, `medium`, `high`
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

## Basic Usage

```php
use ProfanityFilter\ProfanityFilter;
use ProfanityFilter\ProfanityLevel;

$filter = new ProfanityFilter(ProfanityLevel::MEDIUM);

$text = "You little piece of shit!";
$isProfane = $filter->containsProfanity($text); // true

$cleaned = $filter->clean($text); // You little piece of ****!
```

---

## Customization

### Load a custom JSON blacklist

You can provide your own JSON file with a structure like this:

```json
{
    "low": ["wordlow"],
    "medium": ["wordmedium"],
    "high": ["wordhigh"]
}
```

```php
$filter = new ProfanityFilter(ProfanityLevel::HIGH, __DIR__ . '/my_blacklist.json');
```

### Add or remove words at runtime

```php
$filter->addWord('uglyword');
$filter->removeWord('otheruglyword');
```

---

## 📄 License

MIT License - see [LICENSE](LICENSE.md) for details.