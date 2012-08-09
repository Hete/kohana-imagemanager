This is an image manager for Kohana.

It stores images a similar way git does : it uses the sha1 as name.

It is designed to work closely to models so I have backed it in database.

To store an image :

    ImageManager::instance()->store('/path/to/an/image', '<table_name>', '<primary_key>');

To store multiple images :

    ImageManager::instance()->store_files($_FILES['<name attribute>'], '<table_name>', '<primary_key>');

To retreive images associated to a model :

    $images = ImageManager::instance()->retreive('<table_name>', '<primary_key>');

    foreach($images->find_all() as $image) {
        echo $image->path();
    }

To delete an image, get its model and delete it or delete it if you have its hash. 

For security purpose, you may not delete an image by its hash if it is associated with an existing model. I will probably add a force boolean to do so.

ImageManager::instance()->delete('<hash>');

As there is no way to use foreign keys and nothing so far to remove files from the hard drive, I have work on my shoulders !

Things to do :
- Upload helper integration for validation in the storing process (size, file type, etc);
- Generic design to store any kind of files;
- Model relationship