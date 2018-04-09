<?php
/**
 * Follow the conventions:
 * 1. Configuration file should be named config.php
 * 2. Configuration file should be located in the root of project
 */
return [
    'database.connectionParams' => [
        'url' => 'sqlite:///'.__DIR__.'/db.sqlite',
    ],
    'template.path' => __DIR__.'/src/Resources/views',
//    'template.cache' => __DIR__.'/var/cache/twig',
    'template.cache' => false,
    'users' => [
        // username => md5(pass)
        'admin' => '202cb962ac59075b964b07152d234b70',
    ],
    'image' => [
        'path' => __DIR__.'/public/images',
        'thumbs' => [
            'simple' => [
                'widht' => 320,
                'height' => 240,
                'crop' => false,
            ],
        ],
    ],
];
