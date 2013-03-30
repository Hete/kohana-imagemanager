<?php

// Initial tests
if (!is_writable(Kohana::$config->load("imagemanager.base_path"))) {
    throw new Kohana_Exception("Image folder :path folder not writable.", array(":path" => Kohana::$config->load("imagemanager.base_path")));
}
?>
