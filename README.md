# Profanity Filter

A small, dependency-free PHP library to detect and censor profanity in text.

```php
use ProfanityFilter\ProfanityFilter;

$filter = new ProfanityFilter();

echo $filter->clean('This sh1t is funny'); // This **** is funny
```

## Features

- Detects and censors profanity, and tells you which words were found
- Matches **whole words only**: `class` is never flagged because it contains `ass`
- Case-insensitive (`Shit`, `SHIT`, `sHiT`)
- Accent-insensitive while your text keeps its accents
- Catches common evasions: leetspeak (`sh1t`, `$hit`), stretched letters (`shiiiit`) and punctuation inside a word (`s.h.i.t`)
- Full or partial censoring (`****` or `s**t`)
- Keeps your text intact: spaces, tabs and line breaks are preserved exactly as written
- One word list per language, plus your own words on top

## Requirements

- PHP 8.3 or higher
- The `mbstring` extension

## Installation

```bash
composer require devtrope/profanity-filter
```

## Usage

### Censor a text

```php
$filter = new ProfanityFilter();

$filter->clean('This shit is funny');        // This **** is funny
$filter->clean('This shit is funny', '#');   // This #### is funny
```

Every character of a profane word is replaced by the replacement string (`*` by default). The rest of the text is returned untouched.

Punctuation stuck to a profane word is masked with it: `Shit,` becomes `*****`.

### Partial censoring

Pass `partial: true` to keep the first and the last letter of each censored word:

```php
$filter->clean('This shit is funny as fuck', partial: true); // This s**t is funny as f**k
```
Words of one or two letters are always fully masked, because there is nothing left to hide in the middle.

### Check a text

```php
$filter->containsProfanity('This shit is funny'); // true
$filter->containsProfanity('This is a class');    // false
```

### Get the details

`getMatches()` returns one `Detection` object per profane word found, in the order they appear in the text.

```php
$matches = $filter->getMatches('This sh1t is funny as fuck');

foreach ($matches as $match) {
    echo "{$match->original} => {$match->profanity} (word #{$match->position})\n";
}

// sh1t => shit (word #1)
// fuck => fuck (word #5)
```

| Property    | Description                                                         |
|-------------|---------------------------------------------------------------------|
| `original`  | The word exactly as it was written in the text (`sh1t`)             |
| `profanity` | The entry of the word list that matched (`shit`)                    |
| `position`  | Position of the word in the text, starting at `0`                   |

This is useful to tell users why a message was refused, to feed a moderation queue, or to apply your own rules (for example, reject a message after a certain number of detections).

### Choose a language

Pass a locale to the constructor. The default is `en`.

```php
$filter = new ProfanityFilter('fr');

$filter->clean('Quel connard'); // Quel *******
```

Available locales: `en`, `fr`, `es`, `de`, `it`, `pt`.

A `MissingBlacklistFileException` is thrown if no word list exists for the given locale, and an `InvalidBlacklistException` if a list is not valid JSON or is not a list of words.

On a multilingual site, create one filter per language and reuse it, rather than mixing every language in one list: a harmless word in one language is sometimes a profanity in another.

### Add or remove words

```php
$filter->addWords('troll');
$filter->addWords(['troll', 'moldu']);

$filter->removeWords('cul');
$filter->removeWords(['cul', 'con']);
```

Words must be written in lowercase, as a single word (no spaces). Removing a word is also how you handle a false positive for your own audience.

Removing a word is also how you handle a false positive for your own audience.

## What gets detected
 
| Text                | Detected | Why                                                |
|---------------------|----------|----------------------------------------------------|
| `Shit`, `SHIT`      | yes      | case-insensitive                                   |
| `enculé`, `ENCULÉ`  | yes      | accents are ignored (`fr` list)                    |
| `scheiße`           | yes      | `ß` is read as `ss` (`de` list)                    |
| `sh1t`, `$hit`      | yes      | leetspeak                                          |
| `shiiiiit`          | yes      | stretched letters                                  |
| `asss`              | yes      | stretched letters, genuine double letters are kept |
| `s.h.i.t`           | yes      | punctuation inside the word                        |
| `shit!`, `(shit)`   | yes      | punctuation around the word                        |
| `class`, `assume`   | no       | the profanity is only part of another word         |
| `455`, `4.5.5`      | no       | numbers are not converted to letters               |

## Limitations

This library is a word filter, not a moderation system. Keep these limits in mind:

- **Compound words** (common in German, for example) are not split, so a profanity glued to another word is not detected.
- **Letters separated by spaces** (`s h i t`) are not detected.
- **Look-alike Unicode characters** (a Cyrillic `а` instead of a Latin `a`) are not detected.
- **Accent handling covers the supported languages**. Other alphabets are left as they are.
- **Context is ignored.** The filter cannot tell an insult from a quotation, and a word can be harmless in one language and offensive in another.
- The provided word lists are a starting point. They are not exhaustive and do not replace human review.

## Word lists

Lists are stored in `data/blacklist.{locale}.json`. A list is a flat JSON array of words:

```json
[
  "shit",
  "fuck",
  "asshole"
]
```

Rules for the entries:

- lowercase, one word per entry, no spaces
- letters only, without accents: the text is stripped of its accents before the comparison
- no letter repeated three times in a row
- no duplicates

Contributions for new languages or missing words are welcome: add or edit the file and the matching tests, then open a pull request.

## Upgrading

### From 0.1.x

Nothing breaks, `clean()` gets an optional `partial` argument, and the filter now catches more cases, so a text that passed before may now be flagged.

### From 0.0.x

This version is not backward compatible with the 0.0.x releases. The main differences:

- The constructor only takes a locale. The severity levels and the custom blacklist path are gone: word lists are now a single flat list per language.
- `addWord()` and `removeWord()` became `addWords()` and `removeWords()`, and accept a string or an array.
- `clean()` and `containsProfanity()` expect a `string` (no `null`).

## Testing

```bash
composer install
vendor/bin/phpunit tests
```

## License

Released under the [MIT License](LICENSE.md).