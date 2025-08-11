<?php

namespace ProfanityFilter\Configuration;

class FilterConfig
{
    private FilterLevel $level = FilterLevel::MEDIUM;

    public function setLevel(FilterLevel $level): self
    {
        $this->level = $level;
        return $this;
    }

    public function level(): FilterLevel
    {
        return $this->level;
    }
}
