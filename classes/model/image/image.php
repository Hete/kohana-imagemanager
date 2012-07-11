<?php

abstract class Model_Image_Image extends ORM {    



    /**
     * Retrieve the Image object associated to this model.
     * The image object is defined in the image module.
     * DO NOT OVERRIDE THE ORIGINAL IMAGE !
     */
    public function image() {        
        return Image::factory($this->filepath());
    }
    
    /**
     * Returns the path to this image.
     */
    public function filepath() {
    	return ImageManager::instance()->retreive($this->hash);
    }
    
    }
?>