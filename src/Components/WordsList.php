<?php

declare(strict_types=1);

namespace Devtrope\ProfanityFilter\Components;

use Devtrope\ProfanityFilter\Exceptions\InvalidBlacklistException;
use Devtrope\ProfanityFilter\Exceptions\LocaleException;
use Devtrope\ProfanityFilter\Exceptions\MissingBlacklistFileException;

final class WordsList
{
    /**
     * @param string|list<string> $locales
     * @throws LocaleException
     */
    public function __construct(private string|array $locales)
    {
        if (false === \is_array($locales)) {
            $this->locales = [$locales];
        }

        /**
         * @var list<string> $locales
         */
        $locales = $this->locales;
        foreach ($locales as $locale) {
            if (false === ctype_alpha($locale)) {
                throw new LocaleException("Unsupported locale");
            }
        }
    }

    /**
     * @throws MissingBlacklistFileException
     * @throws InvalidBlacklistException
     * @return list<string>
     */
    public function getAll(): array
    {
        $words = [];

        /**
         * @var list<string> $locales
         */
        $locales = $this->locales;
        foreach ($locales as $locale) {
            $blacklist = dirname(__DIR__, 2) . "/data/blacklist.{$locale}.json";
            if (false === is_file($blacklist)) {
                throw new MissingBlacklistFileException(
                    "No blacklist file for the locale \"{$locale}\". You can create
                    your own blacklist file and open a pull request to add it"
                );
            }
            
            $json = file_get_contents($blacklist);
            if (false === $json || false === json_validate($json)) {
                throw new InvalidBlacklistException(
                    "The \"{$locale}\" blacklist file is not valid JSON"
                );
            }
    
            $currentWords = json_decode($json, true);
            if (false === \is_array($currentWords) || false === array_is_list($currentWords)) {
                throw new InvalidBlacklistException(
                    "The \"{$locale}\" blacklist file must be a list of words"
                );
            }
    
            foreach ($currentWords as $word) {
                if (false === \is_string($word)) {
                    throw new InvalidBlacklistException(
                        "The \"{$locale}\" blacklist file must only contain strings"
                    );
                }
            }

            $words = array_merge($words, $currentWords);
        }

        return $words;
    }
}
