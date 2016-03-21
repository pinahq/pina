<?php

return array(
    'default' => !empty($_COOKIE['language'])?$_COOKIE['language']:'ru',
    /*
    'en' => array(
        'sign_out' => 'Sign out',
    ),
    'ru' => array(
        'sign_out' => 'Выход',
    ),
     */
    'table' => '\Pina\Modules\Language\StringGateway',
);