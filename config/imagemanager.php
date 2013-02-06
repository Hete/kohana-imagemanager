<?php

return array(
    'default' => array(
        'image_table' => 'images', // You should have an images table in your database
        'base_path' => DOCROOT . 'images', // Base path where images are stored.
        'hash_function' => 'sha1', // You may select a custon hash function   
        "fallback_image" => NULL, // Fallback uri if image is not found
        'max_size' => '1M',
    ),
    'custom' => null,
);
?>