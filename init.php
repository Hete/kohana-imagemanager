<?php

// Initial tests
if (!is_writable(DOCROOT . Kohana::$config->load("imagemanager.base_path"))) {
    throw new Kohana_Exception("Image folder :path folder not writable.", array(":path" => DOCROOT . Kohana::$config->load("imagemanager.base_path")));
}
?>
