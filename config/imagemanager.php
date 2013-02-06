<?php

defined('SYSPATH') or die('No direct access allowed.');

return array(
    'default' => array(
        'image_table' => 'images', // You should have an images table in your database
        'base_path' => 'images', // Base path where images are stored.
        "fallback_image" => NULL, // Fallback uri if image is not found
        'max_size' => '1M',
    ),
    'custom' => null,
);
?>