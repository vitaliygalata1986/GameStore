<?php

defined('ABSPATH') || exit;

spl_autoload_register(function ($class) {
    $prefix = 'Vitos\\DeepSeek\\';
    $base_dir = DEEPSEEK_PLUGIN_DIR;

    // не наш namespace
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    // например: Admin\DeepSeek_Admin
    $relative = substr($class, strlen($prefix));
    $parts = explode('\\', $relative);

    $class_name = array_pop($parts);                 // DeepSeek_Admin
    $subdir = strtolower(implode('/', $parts));  // admin / includes

    $file = 'class-' . strtolower(str_replace('_', '-', $class_name)) . '.php';

    $path = rtrim($base_dir, '/') . '/' . ($subdir ? $subdir . '/' : '') . $file;

    if (file_exists($path)) {
        require_once $path;
    }
});
