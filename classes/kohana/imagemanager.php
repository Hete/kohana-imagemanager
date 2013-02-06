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
     *
     */
    protected static $_instance;

    /**
     * Configuration
     * @var array
     */
    protected $_config;

    /**
     * 
     * @return ImageManager
     */
    public static function instance() {
        return ImageManager::$_instance ? ImageManager::$_instance : ImageManager::$_instance = new ImageManager();
    }

    private function __construct($config = 'default') {

        $this->_config = Kohana::$config->load("imagemanager.$config");
    }

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
    public function store(array $file, $name = "image") {
        // Validation     

        $validate = Validation::factory($file)
                ->rule($name, "Upload::not_empty", array($file))
                ->rule($name, "Upload::image", array($file))
                ->rule($name, "Upload::size", array($file, $this->config("max_size")));

        if (!$validate->check()) {
            throw new Validation_Exception($validate);
        }

        $tmp_name = $file['tmp_name'];

        $hash = sha1_file($tmp_name);

        $filename = $this->hash_to_filepath($hash);

        $image = ORM::factory('image', array("hash" => $hash));

        if ($image->loaded()) {
            return $image;
        }

        $image->hash = $hash;

        if (!Upload::save($file, $hash, $this->config("base_path"))) {
            // Corrupted download!
            throw new Kohana_Exception("Image copy from $tmp_name to $filename has failed !");
        }

        // Test de validité ultime
        if (sha1_file($filename) !== $hash) {
            unlink($filename);
            throw new Kohana_Exception("Hash calculated from store parameter and file do not match.");
        }

        $image->save();


        return $image;
    }

    /**
     * Store images from the $_FILES['<html name attribute>'] variable
     * @throw ORM_Validation_Exception
     * @return Model_Image fetchable image model.
     */
    public function store_files($name) {

        // On retire les fichiers vides
        // Validations
        $file_count = count($_FILES[$name]['name']);

        // On construit un array qu'on valide avec la classe Upload
        $images = ORM::factory("image");

        $images->where_open();

        $validation_exception = NULL;

        for ($i = 0; $i < $file_count; $i++) {

            $file = array();

            foreach ($_FILES[$name] as $key => $field) {
                $file[$key] = $field[$i];
            }

            try {
                if (Upload::not_empty($file)) {
                    $images->or_where("id", "=", ImageManager::instance()->store($file, $name)->pk());
                } else {
                    $images->or_where("id", "=", NULL);
                }
            } catch (Validation_Exception $ove) {
                if ($validation_exception === NULL) {
                    $validation_exception = $ove;
                } else {
                    $validation_exception->merge($ove);
                }
            }
        }

        $images->where_close();

        if ($validation_exception instanceof Validation_Exception) {
            throw $validation_exception;
        }

        return $file_count > 0 ? $images->find_all() : FALSE;
    }

    /////////////////////
    // Retreive functions

    /**
     * Store a single file.
     * @param type $name
     * @return Model_Image
     */
    public function store_file($name) {

        return $this->store($_FILES[$name], $name);
    }

    //////////////////
    // Delete functions

    /**
     * Delete an unreferenced ($parent_id and $parent_table must be null) image corresponding to the $hash.
     * Only works if the image is not referenced in the database or if $force is true.
     */
    public function delete($hash) {
        if (!$this->image_exists($hash)) {
            Log::instance()->add(Log::CRITICAL, ":hash do not exists in images folder !", array(":hash" => $hash));
        }
        ORM::factory('image', array('hash' => $hash))->delete();
    }

    ////////////////////////////
    // Utilities



    public function hash_to_filepath($hash) {
        return $this->config("base_path") . DIRECTORY_SEPARATOR . "$hash";
    }

    /**
     * Lookup the database and the files to see if the image exists.
     */
    public function image_exists($hash) {
        $path = $this->hash_to_filepath($hash);
        return is_file($path);
    }

}

?>
