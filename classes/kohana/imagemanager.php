<?php

defined('SYSPATH') or die('No direct script access.');

/**
 * Image manager core.
 * 
 * @package ImageManager
 * @author Hète.ca Team
 * @copyright (c) 2013, Hète.ca Inc.
 */
class Kohana_ImageManager {

    /**
     * Singleton
     * 
     * @var ImageManager
     */
    protected static $_instance;

    /**
     * Configuration
     * 
     * @var array
     */
    protected $_config;

    /**
     * Get the instance of ImageManager singleton.
     * 
     * @return ImageManager
     */
    public static function instance() {
        return ImageManager::$_instance ? ImageManager::$_instance : ImageManager::$_instance = new ImageManager();
    }

    private function __construct() {
        $this->_config = Kohana::$config->load("imagemanager");
    }

    /**
     * Getter for configuration.
     * 
     * @see Arr::path     
     */
    public function config($path, $default = NULL, $delimiter = NULL) {
        return Arr::path($this->_config, $path, $default, $delimiter);
    }

    //////////////////////
    // Storage functions

    /**
     * Store an image data on hard drive and database.
     * @param array $file  
     * @throws ORM_Validation_Exception you must catch that exception.
     * @return Model_Image the corresponding ORM model for this image.
     */
    public function store(array $file, $max_width = NULL, $max_height = NULL, $exact = FALSE, $max_size = NULL) {

        $tmp_name = $file['tmp_name'];

        $hash = sha1_file($tmp_name);

        $filename = $this->hash_to_filepath($hash);

        $image = ORM::factory('image', array("hash" => $hash));

        $image->hash = $hash;

        try {
            $image->save(Model_Image::get_image_file_validation($file, $max_width, $max_height, $exact, $max_size));
        } catch (ORM_Validation_Exception $ove) {
            throw $ove;
        }

        if (!Upload::save($file, $hash, $this->config("base_path"))) {
            // Corrupted download!
            $this->delete($hash);
            throw new Kohana_Exception("Image copy from $tmp_name to $filename has failed ! Image was deleted.");
        }

        return $image;
    }

    /**
     * Store images from the $_FILES['<html name attribute>'] variable
     * @throw ORM_Validation_Exception
     * @return Model_Image fetchable image model or FALSE if $FILES[$name] was empty.
     */
    public function store_files($name, $max_width = NULL, $max_height = NULL, $exact = FALSE, $max_size = NULL) {

        $files = array();

        // Parsing $files
        foreach ($_FILES[$name] as $field => $list_of_values) {
            foreach ($list_of_values as $index => $value) {
                $files[$index][$field] = $value;
            }
        }

        // On retire les fichiers vides
        // Validations
        $file_count = count($files);

        // Unsetting empty files
        foreach ($files as $key => $values) {
            if ($values["error"] === UPLOAD_ERR_NO_FILE) {
                unset($files[$key]);
            }
        }

        // No images uploaded
        if (!Valid::not_empty($files)) {
            return ORM::factory("image", NULL);
        }

        // On construit un array qu'on valide avec la classe Upload
        $images = ORM::factory("image");

        $images->where_open();

        $validation_exception = NULL;

        foreach ($files as $file) {

            try {

                $image = ImageManager::instance()->store($file, $max_width, $max_height, $exact, $max_size);

                $images->or_where("id", "=", $image->pk());
            } catch (ORM_Validation_Exception $ove) {
                if ($validation_exception === NULL) {
                    $validation_exception = $ove;
                } else {
                    $validation_exception->merge($ove);
                }
            }
        }

        $images->where_close();

        // Throw the merged exception
        if ($validation_exception !== NULL) {
            throw $validation_exception;
        }

        return $file_count > 0 ? $images->find_all() : FALSE;
    }

    //////////////////
    // Delete functions

    /**
     * Delete an image given its hash.
     */
    public function delete($hash) {
        ORM::factory('image', array('hash' => $hash))->delete();
    }

    ////////////////////////////
    // Utilities

    /**
     * Take an hash and return its filepath.
     * 
     * @param string $hash
     * @return string
     */
    public function hash_to_filepath($hash) {
        return rtrim($this->config("base_path"), "/") . DIRECTORY_SEPARATOR . "$hash";
    }

    /**
     * Lookup in files if the specified hash exists.
     * 
     * @param string $hash
     * @return boolean wheter the file exists or not
     */
    public function exists($hash) {
        $path = $this->hash_to_filepath($hash);
        return (bool) is_file($path);
    }

    /**
     * 
     * @deprecated 
     * @param type $hash
     * @return type
     */
    public function image_exists($hash) {
        return $this->exists($hash);
    }

}

?>
