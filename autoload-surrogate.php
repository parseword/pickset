<?php

/**
 * If you don't have or can't use Composer, require_once this file instead.
 */
$classes = [
    'parseword\pickset\DatabaseConnection' => 'DatabaseConnection.php',
    'parseword\pickset\DateUtils'          => 'DateUtils.php',
    'parseword\pickset\Logger'             => 'Logger.php',
    'parseword\pickset\TextUtils'          => 'TextUtils.php',
];

spl_autoload_register(function ($class) use ($classes) {
    if (!empty($classes[$class]) && file_exists(__DIR__ . '/src/' . $classes[$class])) {
        require_once __DIR__ . '/src/' . $classes[$class];
    }
});
