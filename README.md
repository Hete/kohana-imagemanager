This is an image manager for Kohana.

It stores images a similar way git does : it uses the sha1 as name.

It is designed to work closely to models so I have backed it in database.

To store an image :

    ImageManager::instance()->store('/path/to/an/image');

To store multiple images using $_FILES variable (REMEMBER TO NAME THE FIELD WITH [] IN THE HTML FORM !) :

    <input type="file" name="images[]" />

    ImageManager::instance()->store_files('<name attribute>');

Eventually, 

    ImageManager::instance()->store_files($this->request->post("<whatever name you gave the input>"));

To retreive images associated to a model :

    $model->add("images", $random_image_model);   


    foreach($model->images->find_all() as $image) {
        echo $image->path();
    }

To delete an image, get its model and delete it or delete it if you have its hash. 

For security purpose, you may not delete an image by its hash if it is associated with an existing model. I will probably add a force boolean to do so.

    ImageManager::instance()->delete('<hash>');

Build your own model-relationship to protect your images with foreign keys.