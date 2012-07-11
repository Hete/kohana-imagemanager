<?php 

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class ImageManager_Invalid_Hash_Exception extends Kohana_Exception {
    
    
}
 
class ImageManager_Image_Not_Found_Exception extends Kohana_Exception {
    
    
}
 
class ImageManager_Cannot_Move_Image_Exception extends Kohana_Exception {
    
    
}

abstract class Kohana_ImageManager {



    /**
     *
     */
    protected static $_instance;
    
    protected $_config;
    
    public static function instance() {
        return ImageManager::$_instance ? ImageManager::$_instance : ImageManager::$_instance = new ImageManager();
    }

    private function __construct() {
       
            $this->_config = Kohana::$config->load('imagemanager.default');
        
    }

    
    
    

    /**
     * Store an image data on hard drive and database.
     * @param $image_path Path to the image to store.
     * @param $parent_table Table to which the image is associated.
     * @param $parent_id Id to which the image is associated.
     * @throws Image_Manager_Invalid_Hash_Exception if hash from file and hash from image_data do not match.
     * @return the corresponding ORM model for this image.
     */
    public function store($image_path, $parent_table = null, $parent_id = null) {              
        
        $hash = sha1_file($image_path);
        
        $filename = $this->filename_from_hash($hash);     

        if ( ! $this->image_exists($hash)) {
        
        	// On déplace l'image
            if ( ! move_uploaded_file($image_path, $filename)) {
        		throw new Image_Manager_Cannot_Move_Image_Exception("Image copy from $image_path to $filename has failed !");
        	} 
        	
        	// Test de validité
        	if(sha1_file($filename) !== $hash) {
            	unlink($filename);
            	throw new Image_Manager_Invalid_Hash_Exception("Hash calculated from store parameter and file do not match.");
        	}        
        
        }    
        
        

        $image = ORM::factory('image');
        $image->hash = $hash;
        $image->parent_id = $parent_id;
        $image->parent_table = $parent_table;        
        $image->save();       
        
        return $hash;
        
    }
    
    
    /**
     * Returns an image object on its sha1 hash.
     * To retreive images based on their db id, use models.
     * @param type $hash
     * @return an image object or null in case of failure.
     */
    public function retreive($hash) {
        $filename = $this->filename_from_hash($hash);
        if (file_exists($filename)) {
            return $filename;            
        }
        throw new Image_Manager_Image_Not_Found_Exception("Image with name $filename was not found in the image folder.");
    }
    
    
    /**
     * Store images from the $_FILES['$name'] variable
     */    
    public function store_from_files_variable($files, $parent_table = null, $parent_id = null) {
    // die(print_r($files));
    	foreach ($files["tmp_name"] as $filepath) { 
    		if($filepath === "") continue;     			
    		
    		// $filepath = $files["tmp_name"][$key];
        	ImageManager::instance()->store($filepath, $parent_table, $parent_id);
        	unset($filepath);    			
		}		
    }    
    
    
    /**
     * 
     */
    public function image_exists($hash) {    	
        return file_exists($this->filename_from_hash($hash));
    }
    
    public function filename_from_hash($hash) {
    	return $this->_config['base_path'] . "/$hash";
    }

    



}

?>