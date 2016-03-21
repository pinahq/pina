<?php

namespace Pina\Modules\Language;

use Pina\TableDataLoader;

class StringDataLoader extends TableDataLoader
{

    function __construct()
    {
        parent::__construct();
        $this->fields = array(
            'language_code',
            'string_key',
            'string_value',
            'module_key'
        );
        $this->table = 'cody_string';
        $this->file .= __DIR__.'/data/strings.csv';
    }

}
