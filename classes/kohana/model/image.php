<?php

defined('SYSPATH') or die('No direct script access.');

/**
 * Model for image.
 * 
 * @package ImageManager
 * @category Model
 * @author Hète.ca Team
 * @copyright (c) 2013, Hète.ca Inc.
 */
class Kohana_Model_Image extends ORM {

    /**
     * Retrieve the Image object associated to this model to apply transformations.
     * The image object is defined in the image module.
     * DO NOT OVERRIDE THE ORIGINAL IMAGE !
     */
    public function image() {
        return Image::factory($this->filepath());
    }

    /**
     * Delete the image and its model.
     */
    public function delete() {
        unlink($this->filepath());
        parent::delete();
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
                // sha1 matches its file
                array("equals", array(":value", sha1_file($this->filepath())))
            ),
        );
    }

}

?>
