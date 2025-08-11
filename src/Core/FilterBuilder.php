<?php

namespace ProfanityFilter\Core;

use ProfanityFilter\Configuration\FilterLevel;
use ProfanityFilter\ProfanityFilter;

class FilterBuilder
{
    private array $config = [];

    public function level(FilterLevel $level): self
    {
        $this->config['level'] = $level;
        return $this;
    }

    public function build(): ProfanityFilter
    {
        $this->config['language'] = 'fr';
        $this->config['customBlacklistPath'] = null;
        return new ProfanityFilter(
            $this->config
        );
    }
}
