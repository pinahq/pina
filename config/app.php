<?php

return array(
    'path' => __DIR__.'/../app',
    'charset' => 'utf-8',
    'timezone' => 'Europe/Moscow',
    'apps' => array(
        'backend' => 'admin',
        'api' => 'api',
    ),
    'uploads' => __DIR__.'/../public/uploads',
    'templater' => array(
        'cache' => __DIR__.'/../var/cache',
        'compiled' => __DIR__.'/../var/compiled',
    )
);