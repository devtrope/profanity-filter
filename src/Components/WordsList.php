<?php

declare(strict_types=1);

namespace ProfanityFilter\Components;

use ProfanityFilter\Exceptions\InvalidBlacklistException;
use ProfanityFilter\Exceptions\LocaleException;
use ProfanityFilter\Exceptions\MissingBlacklistFileException;

final class WordsList
{
    /**
     * @param string $locale
     * @throws LocaleException
     */
    public function __construct(private readonly string $locale)
    {
        if (false === ctype_alpha($locale)) {
            throw new LocaleException("Unsupported locale");
        }
    }

    /**
     * @throws MissingBlacklistFileException
     * @throws InvalidBlacklistException
     * @return String[]
     */
    public function getAll(): array
    {
        $blacklist = dirname(__DIR__, 2) . "/data/blacklist.{$this->locale}.json";
        if (false === is_file($blacklist)) {
            throw new MissingBlacklistFileException(
                "No blacklist file for the locale \"{$this->locale}\". You can create
                your own blacklist file and open a pull request to add it"
            );
        }
        
        $json = file_get_contents($blacklist);
        if (false === $json || false === json_validate($json)) {
            throw new InvalidBlacklistException(
                "The \"{$this->locale}\" blacklist file is not valid JSON"
            );
        }

        $words = json_decode($json, true);
        if (false === \is_array($words) || false === array_is_list($words)) {
            throw new InvalidBlacklistException(
                "The \"{$this->locale}\" blacklist file must be a list of words"
            );
        }

        foreach ($words as $word) {
            if (false === \is_string($word)) {
                throw new InvalidBlacklistException(
                    "The \"{$this->locale}\" blacklist file must only contain strings"
                );
            }
        }

        return $words;
    }
}
