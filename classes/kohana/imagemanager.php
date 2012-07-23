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

	private function __construct($config = 'default') {

		$this->_config = Kohana::$config->load("imagemanager.$config");

		// Initial tests
		if (!is_writable($this->_config['base_path'])) {
			throw new Kohana_Exception("Image folder (:path) folder not writable.", array(":path" => $this->_config['base_path']));
		}
	}

	//////////////////////
	// Storage functions

	/**
	 * Store an image data on hard drive and database.
	 * @param $image_path Path to the image to store.
	 * @param $update Tells if we have to update the 
	 * @param $parent_table Table to which the image is associated.
	 * @param $parent_id Id to which the image is associated.
	 * @throws Image_Manager_Invalid_Hash_Exception if hash from file and hash from image_data do not match.
	 * @return the corresponding ORM model for this image.
	 */
	public function store($image_path, $parent_table = null, $parent_id = null) {

		$hash = sha1_file($image_path);

		$filename = $this->filepath_to_hash($hash);

		if (!$this->image_exists($hash)) {

			// On déplace l'image
			if (!move_uploaded_file($image_path, $filename)) {
				throw new Kohana_Exception("Image copy from $image_path to $filename has failed !");
			}

			// Test de validité
			if (sha1_file($filename) !== $hash) {
				unlink($filename);
				throw new Kohana_Exception("Hash calculated from store parameter and file do not match.");
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
	 * Store images from the $_FILES['<html name attribute>'] variable
	 */
	public function store_files($files, $parent_table = null, $parent_id = null) {

		foreach ($files["tmp_name"] as $filepath) {
			if ($filepath === "")
				continue;

			// $filepath = $files["tmp_name"][$key];
			ImageManager::instance()->store($filepath, false, $parent_table, $parent_id);
			unset($filepath);
		}
	}

	/////////////////////
	// Retreive functions

	/**
	 * Get a fetchable array of images that belong to a model.
	 * @param type $parent_table
	 * @param type $parent_id
	 */
	public function retreive($parent_table = null, $parent_id = null) {
		return ORM::factory('image')
				->where('parent_table', '=', $parent_table)
				->and_where('parent_id', '=', $parent_id);
	}

	/**
	 * Get a fetchable array of images that belong to a model.
	 * @param type $parent_table
	 * @param type $parent_id
	 */
	public function retreive_from_model($model) {

		return retreive($model->_table_name, $model->pk());
	}

	//////////////////
	// Delete functions

	/**
	 * Delete an unreferenced ($parent_id and $parent_table must be null) image corresponding to the $hash.
	 * Only works if the image is not referenced in the database or if $force is true.
	 */
	public function delete($hash) {
		if (!$this->image_exists($hash)) {
			throw new Kohana_Exception(":hash do not exists in images folder !", array(":hash" => $hash));
		}

		// Test de référencement
		foreach (ORM::factory('image', array('hash' => $hash))->find_all() as $image) {

			if (!ORM::factory($image->_table_name, $image->pk())->find()) {
				$image->delete();
			}
		}

		if (ORM::factory('image', array('hash' => $hash))->find_all()->count_all() < 1) {
			// Aucuns modèles ne référence cette image, elle peut être détruite.
		}
	}

	////////////////////////////
	// Utilities



	public function hash_to_filepath($hash) {
		return $this->_config['base_path'] . "/$hash";
	}

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

}

?>
