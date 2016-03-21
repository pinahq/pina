<?php

return array(
    'default' => array(
        'warning' => array(
            'file' => array(__DIR__.'/../var/log/pina.log', 10),
        ),
    ),
    'mysql' => array(
        'warning' => array(
            'file' => array(__DIR__.'/../var/log/mysql.log', 10),
        )
    )
);