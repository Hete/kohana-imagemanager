<?php


return array(
             
    'default' => array(
        
            'image_table'  => 'images', // You should have an images table in your database
            'base_path'    => 'images', // Base path where images are stored.
            'hash_function' => 'sha1', // You may select a custon hash function   
        
            'max_size' => '1M',
            
        
    ),
    'custom' => null, 
);


?>