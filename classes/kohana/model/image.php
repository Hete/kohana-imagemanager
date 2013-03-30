<?php

defined('SYSPATH') or die('No direct script access.');

/**
 * Model for image.
 * 
 * @package ImageManager
 * @category Models
 * @author Hète.ca Team
 * @copyright (c) 2013, Hète.ca Inc.
 */
class Kohana_Model_Image extends ORM {

    /**
     * 
     * @param array $file
     * @param integer $max_width
     * @param integer $max_height
     * @param boolean $exact
     * @param string $max_size
     * @return Validation
     */
    public static function get_image_file_validation(array $file, $max_width = NULL, $max_height = NULL, $exact = FALSE, $max_size = NULL) {

        if ($max_size === NULL) {
            $max_size = ImageManager::instance()->config("max_size");
        }

        return Validation::factory($file)
                        ->rule("name", "not_empty")
                        ->rule("tmp_name", "not_empty")
                        ->rule("error", "not_empty")
                        ->rule("size", "not_empty")
                        ->rule("name", "Upload::not_empty", array(":file"))
                        ->rule("name", "Upload::image", array(":file", $max_width, $max_height, $exact))
                        ->rule("name", "Upload::size", array(":file", $max_size))
                        ->bind(":file", $file);
    }

    /**
     * Delete the image and its model.
     */
    public function delete() {
        // Unlink only if the file exists
        if ($this->exists()) {
            unlink($this->path());
        }
        return parent::delete();
    }

    /**
     * Tells if the image exists in the filepath.
     * @return type
     */
    public function exists() {
        return ImageManager::instance()->image_exists($this->hash);
    }

    /**
     * Get the image path
     * @param type $fallback
     * @return type
     */
    public function path($fallback = NULL) {
        if ($fallback === NULL) {
            $fallback = ImageManager::instance()->config("fallback_image");
        }
        return $this->file_exists() ? ImageManager::instance()->hash_to_filepath($this->hash) : $fallback;
    }

    public function file_exists() {
        return $this->exists();
    }

    /**
     * Returns the path to this image.   
     */
    public function filepath($fallback = NULL) {
        return $this->path($fallback);
    }

    public function rules() {
        return array(
            "hash" => array(
                array("not_empty"),
                array("alpha_numeric"),
                array("exact_length", array(":value", 40))
            ),
        );
    }

}

?>
