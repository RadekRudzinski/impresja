<?php

namespace impresja\impresja;

use impresja\impresja\File;

class ImageFile extends File
{
    public $isImage = true;
    public $image;

    function __construct($name)
    {
        parent::__construct($name);
        $data = @getimagesize($name);
        if (!$data) {
            $this->isImage = false;
            $this->error = "Przesłany plik nie jest poprawnym plikiem graficznym";
        } else {
            $this->mime = $data['mime'];
            $this->image = imagecreatefromstring(file_get_contents($name));
        }
    }

    function resize($width, $height, $nazwa)
    {
        if ($this->image) {
            $X = ImageSX($this->image);
            $Y = ImageSY($this->image);
            if (!$height and $width)
                $height = round(($Y * $width) / $X);
            elseif (!$width and $height)
                $width = round(($X * $height) / $Y);
            elseif ($width and $height) {
                $newHeight = round(($Y * $width) / $X);
                if ($newHeight > $height) {
                    $width = round(($X * $height) / $Y);
                } else $height = $newHeight;
            }
            if ($width >= $X or $height >= $Y) {
                $width = $X;
                $height = $Y;
            }
            $imgTN = ImageCreateTrueColor($width, $height);
            imagealphablending($imgTN, false);
            imagesavealpha($imgTN, true);
            $background = imagecolorallocate($imgTN, 255, 255, 255);
            imagefill($imgTN, 0, 0, $background);
            ImageCopyResampled($imgTN, $this->image, 0, 0, 0, 0, $width, $height, $X, $Y);
            if ($this->mime == 'image/jpeg') ImageJPEG($imgTN, $nazwa);
            if ($this->mime == 'image/gif') ImageGIF($imgTN, $nazwa);
            if ($this->mime == 'image/png') ImagePNG($imgTN, $nazwa);
            ImageDestroy($imgTN);
            return true;
        }
        return false;
    }
}
