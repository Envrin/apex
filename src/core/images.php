<?php
declare(strict_types = 1);

namespace apex\core;

use apex\DB;
use apex\registry;
use apex\log;
use apex\debug;
use apex\core\forms;
use apex\core\io;

/**
* Class to handle image storage and manipulation.
*/
class images
{

/**
* Add a new image to the database
*     @param string $filename The filename of the image
*     @param string $contents The contents of the image
*     @param string $type The type of image (eg. user, product, etc.)
*     @param int $recordstring Optional record ID to retrieve the image later
*     @param int $is_default Boolean (1/0) that defines whether or not the image is default for this type (eg. default user avatar)
*     @return int The ID# of the new image
*/
public static function add(string $filename, string $contents, string $type, string $record_id = '', string $size = 'full', int $is_default = 0):int
{

    // Debug
    debug::add(4, fmsg("Starting to add image with type: {1}, record_id: {2}, size: {3}", $type, $record_id, $size), __FILE__, __LINE__);

    // Save image to filesystem
    $tmpfile = tempnam(sys_get_temp_dir(), 'apex');
    if (file_exists($tmpfile)) { @unlink($tmpfile); }
    file_put_contents($tmpfile, $contents);

    // Get image dimensions
    if (!@list($width, $height, $mime_type, $attr) = getimagesize($tmpfile)) {
        return false;
    }

    // Delete existing image, if exists
    DB::query("DELETE FROM images WHERE type = %s AND record_id = %s", $type, $record_id);

    // Add to DB
    DB::insert('images', array(
        'type' => $type, 
        'record_id' => $record_id, 
        'is_default' => $is_default, 
        'size' => $size, 
        'width' => $width, 
        'height' => $height, 
        'mime_type' => $mime_type, 
        'filename' => $filename)
    );
    $image_id = DB::insert_id();

    // Add to contents
    DB::insert('images_contents', array(
        'id' => $image_id, 
        'contents' => $contents)
    );

    // Debug
    debug::add(3, fmsg("Added new image to database, type: {1}, record_id: {2}", $type, $record_id), __FILE__, __LINE__);

    // Return
    return $image_id;

}

/**
* Upload a new image
*     @param string $form_field The name of the form field of the uploaded image.
*     @param string $type The type of image (eg. user, product, etc.)
*     @param string $record_id Optional record ID# of the image to retrieve it later.
*     @param int $is_default Whether or not this is the default image for this type.
*     @return int The ID# of the image
*/
public static function upload(string $form_field, string $type, string $record_id = '', int $is_default = 0) 
{

    // Debug
    debug::add(4, fmsg("Starting to upload / add new image of type: {1}, record_id: {2},from form field: {3}", $type, $record_id, $form_field), __FILE__, __LINE__);

    // Get the uploaded file
    if (!list($filename, $mime_type, $contents) = forms::get_uploaded_file($form_field)) { 
        return false;
    }

    // Add the file
    $image_id = self::add($filename, $contents, $type, $record_id, $is_default);

    // Return
    return $image_id;

}

/**
* Retrive image from the database
*     @param string $type The type of image (eg. user, product, etc.)
*     @param string $record_id The record ID# of the image.
*     @param string $size The size of the image
*/
public static function get(string $type, string $record_id = '', string $size = 'full')
{

    // Check database
    if (!$row = DB::get_row("SELECT * FROM images WHERE type = %s AND record_id = %s AND size = %s", $type, $record_id, $size)) { 
        return false;
    }

    // Get contents
    $contents = DB::get_field("SELECT contents FROM images_contents WHERE id = %i", $row['id']);

    // Return
    return array($row['filename'], $row['mime_type'], $row['width'], $row['height'], $contents);

}

/**
* Add a thumbnail
*/
public static function add_thumbnail(string $image_type, string $record_id, string $size, int $thumb_width, int $thumb_height) 
{

    // Get contents of existing image
    if (!list($filename, $type, $width, $height, $contents) = self::get($image_type, $record_id, 'full')) { 
        return false;
    }

    // Save tmp file
    $tmpfile = tempnam(sys_get_temp_dir(), 'apex');
    if (file_exists($tmpfile)) { @unlink($tmpfile); }
    file_put_contents($tmpfile, $contents);

    // Initialize image
    if ($type == IMAGETYPE_GIF) { 
        @$source = imagecreatefromgif($tmpfile);
        $ext = 'gif';
    } elseif ($type == IMAGETYPE_JPEG) { 
        @$source = imagecreatefromjpeg($tmpfile);
        $ext = 'jpg';
    } elseif ($type == IMAGETYPE_PNG) { 
        @$source = imagecreatefrompng($tmpfile);
        $ext = 'png';
    } else { return false; }

    // Get ratios
    $ratio_x = sprintf("%.2f", ($width / $thumb_width));
    $ratio_y = sprintf("%.2f", ($height / $thumb_height));

    // Resize image, if needed
    if ($ratio_x != $ratio_y) { 
        if ($ratio_x > $ratio_y) { 
            $new_width = $width;
            $new_height = ($height - sprintf("%.2f", ($height * ($ratio_x - $ratio_y)) / 100));
        } elseif ($ratio_y > $ratio_x) { 
            $new_height = $height;
            $new_width = ($width - sprintf("%.2f", ($width * ($ratio_y - $ratio_x)) / 100));
        }
        $new_width = (int) $new_width;
        $new_height = (int) $new_height;

        // Resize
        imagecopy($source, $source, 0, 0, 0, 0, (int) $new_width, (int) $new_height);
        $width = $new_width;
        $height = $new_height;
    }

    // Create thumbnail
    $thumb_source = imagecreatetruecolor($thumb_width, $thumb_height);
    imagecopyresized($thumb_source, $source, 0, 0, 0, 0, (int) $thumb_width, (int) $thumb_height, (int) $width, (int) $height);

    // Get thumb filename
    $thumb_filename = tempnam(sys_get_temp_dir(), 'apex');

    // Save thumbnail
    if ($type == IMAGETYPE_GIF) {
        imagegif($thumb_source, $thumb_filename);
    } elseif ($type == IMAGETYPE_JPEG) {
        imagejpeg($thumb_source, $thumb_filename);
    } elseif ($type == IMAGETYPE_PNG) {
        imagepng($thumb_source, $thumb_filename);
    } else { return false; }

    // Return file
    $thumb_contents = file_get_contents($thumb_filename);
    @unlink($thumb_filename);
    @unlink($tmpfile);

    // Free memory
    imagedestroy($source);
    imagedestroy($thumb_source);

    // Insert thumbnail to db
    $thumb_id = self::add($filename, $thumb_contents, $image_type, $record_id, $size);

    // Debug
    debug::add(4, fmsg("Created thumbnail for image of type: {1}, record_id: {2} of size: {3}", $type, $record_id, $size), __FILE__, __LINE__);

    // Return
    return $thumb_id;

}

/**
* Display image
*/
public static function display(string $type, string $record_id = '', string $size = 'full')
{

    // Get image
    if (!list($filename, $mime_type, $width, $height, $contents) = self::get($type, $record_id, $size)) { 
        registry::set_content_type('text/plain');
        registry::set_response('No image exists here');
        return;
    }

    // Set response variables
    registry::set_content_type($mime_type);
    registry::set_response($contents);

}


}

