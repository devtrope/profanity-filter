# CHANGELOG for 0.2.x

This changelog references the relevant changes done in 0.2 minor versions.

To get the diff between two versions, go to [https://github.com/devtrope/profanity-filter/compare/v0.1.2...v0.2.0](https://github.com/devtrope/profanity-filter/compare/v0.1.2...v0.2.0)

## 0.2.1 - 2026-10-06

### Fixes
- Profane words with apostrophes are detected

## 0.2.0 - 2026-10-05

### Improvements
- Accent normalization
- Partial censoring with the `partial` argument of `clean()`
- `clean()` and `containsProfanity()` detect more cases than in 0.1.x

### Fixes
- Normalize the words given to `addWords()` and `removeWords()`
- Ignore empty words in `addWords`
- Detect stretched words that have double letters
- Keep only unaccented entries in the word lists