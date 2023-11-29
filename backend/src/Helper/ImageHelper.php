<?php


namespace App\Helper;


use Creonit\MediaBundle\Model\File;
use Creonit\MediaBundle\Model\Gallery;
use Creonit\MediaBundle\Model\GalleryItem;
use Creonit\MediaBundle\Model\Image;

class ImageHelper
{
    public static function makeImage(File $file): Image
    {
        $image = new Image();
        $image->setFile($file)->save();

        return $image;
    }

    public static function makeGallery(): Gallery
    {
        return new Gallery();
    }

    public static function makeGalleryItem(): GalleryItem
    {
        return new GalleryItem();
    }
}
