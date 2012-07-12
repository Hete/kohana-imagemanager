<?php 

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

	// Initial tests
	if( ! is_writable($this->_config['base_path'])) {
		throw new Kohana_Exception("Image folder (:path) folder not writable.", array(":path" => $this->_config['base_path']));
	}

        

	// We need ORM, database and image modules to work correctly
        

        // Let's check the integrety of the database.
        $this->check_integrity();
    }    

    /**
     * Store an image data on hard drive and database.
     * @param $image_path Path to the image to store.
     * @param $parent_table Table to which the image is associated.
     * @param $parent_id Id to which the image is associated.
     * @throws Image_Manager_Invalid_Hash_Exception if hash from file and hash from image_data do not match.
     * @return the corresponding ORM model for this image.
     */
    public function store($image_path, $update = true, $parent_table = null, $parent_id = null) {              
        
        $hash = sha1_file($image_path);
        
        $filename = $this->filename_from_hash($hash);     

        if ( ! $this->image_exists($hash)) {
        
        	// On déplace l'image
            if ( ! move_uploaded_file($image_path, $filename)) {
        	throw new Kohana_Exception("Image copy from $image_path to $filename has failed !");
            } 
        	
            // Test de validité
            if(sha1_file($filename) !== $hash) {
                unlink($filename);
                throw new Kohana_Exception("Hash calculated from store parameter and file do not match.");
            }        
        
        }      

        if($update) $this->_update_checksum(); 
        

        $image = ORM::factory('image');
        $image->hash = $hash;
        $image->parent_id = $parent_id;
        $image->parent_table = $parent_table;        
        $image->save();       
        
        return $hash;
        
    }
 
    
    
    /**
     * Store images from the $_FILES['<html name attribute>'] variable
     */  
    public function store_files($files, $parent_table = null, $parent_id = null) {
	// die(print_r($files));
    	foreach ($files["tmp_name"] as $filepath) { 
    		if($filepath === "") continue;     			
    		
    		// $filepath = $files["tmp_name"][$key];
        	ImageManager::instance()->store($filepath, false, $parent_table, $parent_id);
        	unset($filepath);    			
		}
	$this->_update_checksum();
    }
    
      /**
       * @deprecated
       */
    public function store_from_files_variable($files, $parent_table = null, $parent_id = null) {
        $this->store_files($files, $parent_table, $parent_id);
    		
    }  

    private function _update_checksum() {} 

    private function _current_checksum() {}

    /**
     * Check the integrity.
     * If a row in the database is missing, it adds it with both $parent_id and $parent_name setted to null.
     * If a file is missing, it throws an exception.
     * Compares the checksum with the older checksum.
     * @return an associative array with state informations.
     * 'num_of_files'
     * 'num_of_rows'
     * 'checksum' sha1 of
     */
    public function check_integrity() {
        if($this->_current_checksum() !== ($new_checksum = $this->_update_checksum())) {
            throw new Kohana_Exception("Images checksum has changed. Please verify the integrity manually.");
        }




	return array(
            'checksum' => $new_checksum
        )
    } 



    /**
     * Garebage collector.
     * Finds unreferenced files in the images folder and compress to save space.
     * @param $delete Delete instead of compressing.
     */
    public function gc($delete = false) {} 
    
    
    /**
     * Lookup the database and the files to see if the image exists.
     */
    public function image_exists($hash) {    	
        return file_exists($this->filename_from_hash($hash)) and 
            ORM::factory('image')
                ->where('hash', '=', $hash)
                ->find_all()
                ->count_all() > 0;
    }
    
    public function filename_from_hash($hash) {
    	return $this->_config['base_path'] . "/$hash";
    }

    



}

?>
