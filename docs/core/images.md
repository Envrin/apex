
# Image Handling

Apex provides a *core/images* library that allows you to easily store and manage images, generate thumbnails, display images, and more.  All methods within this class are 
static allowing them to be easily accessed anywhere within the software.  Below explains all methods within this class.


### Overview

Every image has three attributes associated with it:

* **type** -- The type or category of image, and can be anything you wish to identify the subset of images (eg. user_avatar, product, blog_post, etc.).
* **record_id** -- A unique ID# used to identify the specific image within the type / category (eg. user ID#, product ID#, etc.)
* **size** -- Defaults to "full" when a new image is added, but can be anything you wish upon generating thumbnails (eg. thumb, small, tiny, etc.)


Throughout the below methods, you will notice all three variables being referenced quite often.  When you want to display an image, you simply link 
the `src` of the image to:

`/image/TYPE/RECORD_ID/SIZE.jpg`

For example, to display the thumbnail of the image of type "user_vatar" and ID# 591, you would use:

`<img src="/image/user_avatar/591/thumb">`

Then simply use the below methods to add images to the database, and generate thumbnails for them.  This allows for the easy handling and displaying of images.


### `array = images::upload(string $form_field, string $type, [string $record_id = 0], [int $is_default = 0])`

**Description:** Obtains an uploaded file from the previous form, and adds it to the database.

**Parameters**

Variable | Type | Description
------------- |------------- |------------- 
`$form_field` | string | The name of the file form field on the previous form.
`$type` | string | The type of image, and can be anything you wish (eg. user_avatar, product, etc.)
`$record_id` | string | The unique ID# of the record to reference this image of this type.
`$is_default` | int | A 1/0 defining whether or not this is the default / blank image.  When someone views an image of this type, and it does not exist for the specified record ID#, the default image for that type will be displayed.

**Return Value**

If the upload fails, false will be returned.  Otherwise if successful, an array is returned, giving various details of the newly uploaded image as shown below.

Variable | Type | Description
------------- |------------- |------------- 
`$filename` | string | The filename of the image.
`$mime_type` | string | The MIME type of the image
`$width` | int | Width of the image in pixels.
`$height` | int | Height of the image in pixels.
`$contents` | string | Binary string containing the image contents.

**Example**

~~~php
namespace apex;

use apex\core\forms;
use apex\core\images;

// Upload image from form field 'product_image'
$product_id = 51;
$image = images::upload('product_image', 'product', $product_id);
~~~


### `int = images::add(string $filename, string $contents, string $type, [string $record_id = 0], [string $size = 'full'], [int $is_default = 0])`

**Description:** Assumes you already have the contents of the image instead of it being uploaded from the previous form, and allows you to add an image to the database.  Returns the unique ID# of the newly added image.

**Parameters**

Variable | Type | Description
------------- |------------- |------------- 
`$filename` | string | The filename of the image.
`$contents` | string | The contents of the image in binary format.
`$type` | string | The type / subset of image, and can be anything you wish (eg. user_avatar, product, etc.).
`$record_id` | string | The unique ID# of the image within the type / subset.
`$size` | string | defaults to "full", but can be anything you wish in case of thumbnails (eg. thumb, medisum, small, tiny, etc.)
`$is_default` | int | A 1/0, defaults to 0, and if 1 and no image exists with the specified record ID# when displaying the image, the default image will be displayed.

**Example**

~~~php
namespace apex;

use apex\core\images;

// Get contents of image
$image_contents = file_get_contents("./some_image.jpg");

// Add image
$image_id = images::add('some_page.jpg', $image_contents, 'my_category', 42);
~~~


### `add_thumbnail(string $type, string $record_id, string $size, int $thumb_width, int $thumb_height)`

**Description:** Generate a thumbnail for an existing image within the database.  Call this method after you've uploaded / added an image using one of the above methods, and want to generate a thumbnail for it.  Returns the unique ID# of the newly generated thumbnail /image in the database.

**Parameters**

Variable | type | Description
------------- |------------- |------------- 
`$type` | string | The type / subset of the image to generate a thumbnail for, and needs to be the same type as the image was created with.
`$record_id` | string | The record ID# of the image to generate a thumbnail for, and needs to be the same record ID# the image was added with
`$size` | string | The new size of the image, and can be anything you wish (eg. thumb, medium, small, tiny, etc.)
`$thumb_width` | int | The width in peixels to size the thumbnail to.
`$thumb_height` | int | The height in pixels to size the new thumbnail to.

**Example**

~~~php
namespace apex;

use apex\core\images;


// Upload image with type 'product', and ID# 473
$product_id = 473
$image = images::upload('product_image', 'product', $product_id);

// Generate a 80x80 thumbnail of newly uploadd image
$thumb_id = images::add_thumbnail('product', $product_id, 'thumb', 80, 80);
~~~

In the above example, you can then display the full sized image with the HTML tag:

`<img src="/image/product/473/full">`

And you can display the thumbnail of that image with:

`<img src="/image/product/473/thumb">`


### `array = images::get(string $type, [string $record_id = 0], [string $size = 'full'])`

**Description:** Retrives an image from the database, including its filename, size, and contents.  Generally only used within the library itself, and should never have ben to executed within your code for the most part.

**Parameters**

Variable | Type | Description
------------- |------------- |------------- 
`Type` | String | The type / subset of the image which is was added under (eg. user_avatar, product, etc.)
`$record_id` | string | The ID# of the image within the type / subset to retrieve, and is the ID# used when adding the image.
`$size` | string | The size of the image to retrieve, defaults to "full"

**Return Value**

If no image is found that matches the type and record ID#, false is returned.  Otherwise, an array is returned as shown below.

Variable | Type | Description
------------- |------------- |------------- 
`$filename` | string | The filename of the image
`$mime_type` | string | The MIME type of the image
`$width` | int | The width of the image in pixels
`$height` | int | The height of the image in pixels
`$contents` | string | The contents of the image in binary format

**Example**

~~~php
namespace apex;

use apex\core\images;

// Get image of type 'user_avatar' and ID# 591
$userid = 591;
list($filename, $mime_type, $width, $height, $contents) = images::get('user_avatar', $userid);

// Display image
header("Content-type: $mime_type);
echo $contents;
~~~



