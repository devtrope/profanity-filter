<?php

namespace ProfanityFilter\Configuration;

class FilterConfig
{
    private FilterLevel $level = FilterLevel::MEDIUM;
    private string $language = 'en';

    public function setLevel(FilterLevel $level): self
    {
        $this->level = $level;
        return $this;
    }

    public function level(): FilterLevel
    {
        return $this->level;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;
        return $this;
    }

    public function language(): string
    {
        return $this->language;
    }
}
