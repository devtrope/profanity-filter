<?php

namespace ProfanityFilter\Core;

use ProfanityFilter\Configuration\FilterConfig;
use ProfanityFilter\Configuration\FilterLevel;
use ProfanityFilter\ProfanityFilter;

class FilterBuilder
{
    private FilterConfig $config;

    public function __construct()
    {
        $this->config = new FilterConfig();
    }
    
    public function level(FilterLevel $level): self
    {
        $this->config->setLevel($level);
        return $this;
    }

    public function build(): ProfanityFilter
    {
        return new ProfanityFilter(
            $this->config
        );
    }
}
