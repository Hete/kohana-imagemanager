This is an image manager for Kohana.

It stores images a similar way git does : it uses the sha1 as name.

It is designed to work closely to models so I have backed it in database.

To store an image :
ImageManager::instance()->store('/path/to/an/image', 'event', '<id>');

To store multiple images :
ImageManager::instance()->store_from_files_variable($_FILES['<name attribute>'], 'event', '<id>');

To retreive an image (only returns the path) :
ImageManager::instance()->retreive('<image hash>');

By setting the the images() method, you can retreive matching images :

class Model_Event {

    public images() {
        return ORM::factory('image')
            ->where('parent_id', '=', $this->id)
            ->and_where('parent_table', '=', 'event');
    }

}

To delete an image, get its model and delete it.

As there is no way to use foreign keys and nothing so far to remove files from the hard drive, I have work on my shoulders !

Things to do :
- Image deletion ;
- Upload helper integration for validation in the storing process ;