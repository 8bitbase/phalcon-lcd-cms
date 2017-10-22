<?php

/**
 * ImageHelper
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Helpers;

class ImageHelper
{

    /**
     * @method upload_first
     * @auth: ledung
     * @param  string       $file
     * @param  string       $path
     * @param  array       $sizes
     * @return boo | string
     */
    public static function upload_first($file, $path, $sizes = null)
    {
        if (is_array($file) && isset($file[0])) {
            $file = $file[0];
        }
        if ($file->getSize() > 2000000) {
            return false;
        }

        $fileName = time() . '-' . $file->getName();
        $pathFile = $path . $fileName;

        if (!is_dir($path)) {
            mkdir($path, 0777);
        }

        if ($file->moveTo($pathFile)) {
            if ($sizes != null) {
                foreach ($sizes as $key => $item) {
                    $method = 'crop';
                    $newPathName = $path . $key . '-' . $fileName;
                    $width = $item['width'];
                    $height = $item['height'];
                    ImageHelper::resize_image($method, $pathFile, $newPathName, $width, $height);
                }
            }

            return $fileName;
        }
    }

    /**
     * @method upload
     * @auth: ledung
     * @param  string $files
     * @param  string $path
     * @param  array $sizes
     * @return array
     */
    public static function upload($files, $path, $sizes)
    {
        // Init
        $return = array(
            'status' => true,
            'error'  => null,
        );
        $fileUploaded = array();

        foreach ($files as $file) {
            $result = ImageHelper::upload_first($file, $path, $sizes);
            if ($result == false) {
                foreach ($fileUploaded as $fileName) {
                    ImageHelper::delete($fileName, $path, $sizes);
                }

                $return['status'] = false;
                $return['error']  = 'Error upload file: ' . $file->getName();
                return $return;
            }
            $fileUploaded[] = $result;
        }
    }

    /**
     * @method delete
     * @auth: ledung
     * @param  string $file
     * @param  string $path
     * @param  array $sizes
     * @return null
     */
    public static function delete($file, $path, $sizes = null)
    {
        if (is_array($file[0])) {
            $file = $file[0];
            $fileName = $file->getName();
        } else {
            $fileName = $file;
        }

        $deleted = unlink($path . $fileName);

        if ($sizes != null && $deleted) {
            foreach ($sizes as $key => $item) {
                $newPath = $path . $key . '-' . $fileName;
                unlink($newPath);
            }
        }
    }

    /**
     * @method get
     * @auth: ledung
     * @param  string $link
     * @param  strong $file
     * @param  string $size
     * @return string
     */
    public static function get($link = null, $file = null, $size = null)
    {
        if ($link == 'article' || $link == null) {
            $link = '/upload/articles/';
        }
        if (!empty($file) && is_string($link) && is_string($file) && is_string($size)) {
            return $link . $size . '-' . $file;
        } elseif(!empty($file) && is_string($link) && is_string($file)) {
            return $link . $file;
        }
    }

    /**
     * @method resize_image
     * @auth: ledung
     * @param  string       $method: 3 value is 'force', 'max', 'crop'
     * @param  string       $image_loc
     * @param  string       $new_loc
     * @param  string       $width
     * @param  string       $height
     * @return boo
     */
    public static function resize_image($method, $image_loc, $new_loc, $width, $height)
    {
        if (!is_array(@$GLOBALS['errors'])) {$GLOBALS['errors'] = array();}

        if (!in_array($method, array('force', 'max', 'crop'))) {
            $GLOBALS['errors'][] = 'Invalid method selected.';
        }

        if (!$image_loc) {
            $GLOBALS['errors'][] = 'No source image location specified.';
        } else {
            if ((substr(strtolower($image_loc), 0, 7) == 'http://') || (substr(strtolower($image_loc), 0, 7) == 'https://')) {
                /*don't check to see if file exists since it's not local*/
            } elseif (!file_exists($image_loc)) {
                $GLOBALS['errors'][] = 'Image source file does not exist.';
            }
            $extension = strtolower(substr($image_loc, strrpos($image_loc, '.')));
            if (!in_array($extension, array('.jpg', '.jpeg', '.png', '.gif', '.bmp'))) {
                $GLOBALS['errors'][] = 'Invalid source file extension!';
            }
        }

        if (!$new_loc) {
            $GLOBALS['errors'][] = 'No destination image location specified.';
        } else {
            $new_extension = strtolower(substr($new_loc, strrpos($new_loc, '.')));
            if (!in_array($new_extension, array('.jpg', '.jpeg', '.png', '.gif', '.bmp'))) {
                $GLOBALS['errors'][] = 'Invalid destination file extension!';
            }
        }

        $width = abs(intval($width));
        if (!$width) {
            $GLOBALS['errors'][] = 'No width specified!';
        }

        $height = abs(intval($height));
        if (!$height) {
            $GLOBALS['errors'][] = 'No height specified!';
        }

        if (count($GLOBALS['errors']) > 0) {

            return false;
        }

        if (in_array($extension, array('.jpg', '.jpeg'))) {
            $image = @imagecreatefromjpeg($image_loc);
        } elseif ($extension == '.png') {
            $image = @imagecreatefrompng($image_loc);
        } elseif (
            $extension == '.gif') {
            $image = @imagecreatefromgif($image_loc);
        } elseif ($extension == '.bmp') {
            $image = @imagecreatefromwbmp($image_loc);
        }

        if (!$image) {
            $GLOBALS['errors'][] = 'Image could not be generated!';
        } else {
            $current_width  = imagesx($image);
            $current_height = imagesy($image);
            if ((!$current_width) || (!$current_height)) {
                $GLOBALS['errors'][] = 'Generated image has invalid dimensions!';}
        }
        if (count($GLOBALS['errors']) > 0) {
            @imagedestroy($image);

            return false;
        }

        if ($method == 'force') {
            $new_image = ImageHelper::resize_image_force($image, $width, $height);
        } elseif ($method == 'max') {
            $new_image = ImageHelper::resize_image_max($image, $width, $height);
        } elseif ($method == 'crop') {
            $new_image = ImageHelper::resize_image_crop($image, $width, $height);
        }

        if ((!$new_image) && (count($GLOBALS['errors'] == 0))) {
            $GLOBALS['errors'][] = 'New image could not be generated!';
        }
        if (count($GLOBALS['errors']) > 0) {
            @imagedestroy($image);

            return false;
        }

        $save_error = false;
        if (in_array($extension, array('.jpg', '.jpeg'))) {
            imagejpeg($new_image, $new_loc) or ($save_error = true);
        } elseif ($extension == '.png') {
            imagepng($new_image, $new_loc) or ($save_error = true);
        } elseif ($extension == '.gif') {
            imagegif($new_image, $new_loc) or ($save_error = true);
        } elseif ($extension == '.bmp') {
            imagewbmp($new_image, $new_loc) or ($save_error = true);
        }
        if ($save_error) {
            $GLOBALS['errors'][] = 'New image could not be saved!';
        }
        if (count($GLOBALS['errors']) > 0) {
            @imagedestroy($image);
            @imagedestroy($new_image);

            return false;
        }

        @imagedestroy($image);
        @imagedestroy($new_image);

        return true;
    }

    public static function resize_image_crop($image,$width,$height) {
        $w = @imagesx($image); //current width
        $h = @imagesy($image); //current height
        if ((!$w) || (!$h)) { $GLOBALS['errors'][] = 'Image couldn\'t be resized because it wasn\'t a valid image.'; return false; }
        if (($w == $width) && ($h == $height)) { return $image; } //no resizing needed
     
        //try max width first...
        $ratio = $width / $w;
        $new_w = $width;
        $new_h = $h * $ratio;
     
        //if that created an image smaller than what we wanted, try the other way
        if ($new_h < $height) {
            $ratio = $height / $h;
            $new_h = $height;
            $new_w = $w * $ratio;
        }
     
        $image2 = imagecreatetruecolor ($new_w, $new_h);
        imagecopyresampled($image2,$image, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
     
        //check to see if cropping needs to happen
        if (($new_h != $height) || ($new_w != $width)) {
            $image3 = imagecreatetruecolor ($width, $height);
            if ($new_h > $height) { //crop vertically
                $extra = $new_h - $height;
                $x = 0; //source x
                $y = round($extra / 2); //source y
                imagecopyresampled($image3,$image2, 0, 0, $x, $y, $width, $height, $width, $height);
            } else {
                $extra = $new_w - $width;
                $x = round($extra / 2); //source x
                $y = 0; //source y
                imagecopyresampled($image3,$image2, 0, 0, $x, $y, $width, $height, $width, $height);
            }
            imagedestroy($image2);
            return $image3;
        } else {
            return $image2;
        }
    }

    public static function resize_image_max($image,$max_width,$max_height) {
        $w = imagesx($image); //current width
        $h = imagesy($image); //current height
        if ((!$w) || (!$h)) { $GLOBALS['errors'][] = 'Image couldn\'t be resized because it wasn\'t a valid image.'; return false; }
     
        if (($w <= $max_width) && ($h <= $max_height)) { return $image; } //no resizing needed
     
        //try max width first...
        $ratio = $max_width / $w;
        $new_w = $max_width;
        $new_h = $h * $ratio;
     
        //if that didn't work
        if ($new_h > $max_height) {
            $ratio = $max_height / $h;
            $new_h = $max_height;
            $new_w = $w * $ratio;
        }
     
        $new_image = imagecreatetruecolor ($new_w, $new_h);
        imagecopyresampled($new_image,$image, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
        return $new_image;
    }

    public static function resize_image_force($image,$width,$height) {
        $w = @imagesx($image); //current width
        $h = @imagesy($image); //current height
        if ((!$w) || (!$h)) { $GLOBALS['errors'][] = 'Image couldn\'t be resized because it wasn\'t a valid image.'; return false; }
        if (($w == $width) && ($h == $height)) { return $image; } //no resizing needed
     
        $image2 = imagecreatetruecolor ($width, $height);
        imagecopyresampled($image2,$image, 0, 0, 0, 0, $width, $height, $w, $h);
     
        return $image2;
    }
}
