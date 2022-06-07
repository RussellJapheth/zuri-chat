<?php

require_once __DIR__ . '/vendor/autoload.php';

function twig()
{
    static $twig = null;

    if ($twig === null) {
        /**
         * Setup the Twig environment and pass in options
         *
         * @var object
         */
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/views');

        $twig = new \Twig\Environment(
            $loader,
            [
                'debug' => true,
                'cache' => false
            ]
        );

        /**
         * Add the debug extension to twig
         */
        $twig->addExtension(new \Twig\Extension\DebugExtension());
    }

    return $twig;
}

define('DATABASE_FILE', __DIR__ . '/data.json');

if (!file_exists(DATABASE_FILE)) {
    $data = new stdClass();
    $data->chats = [];
    file_put_contents(DATABASE_FILE, json_encode($data));
}
