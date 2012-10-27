<?php

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
     * 
     */
    public function delete() {
        unlink($this->filepath());
        parent::delete();
    }

    public function file_exists() {
        return ImageManager::instance()->image_exists($this->hash);
    }

    /**
     * Returns the path to this image.
     */
    public function filepath() {
        return ImageManager::instance()->hash_to_filepath($this->hash);
    }

}

?>
