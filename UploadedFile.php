<?php

namespace impresja\impresja;

use impresja\impresja\File;

class UploadedFile extends File
{
    public $tmp_name;
    public $originalName;
    public $originalExtension;

    function __construct($file)
    {
        if (is_uploaded_file($file['tmp_name']) and !$file['error']) {
            parent::__construct($file['tmp_name']);
            $this->tmp_name = $file['tmp_name'];
            $original = pathinfo($file['name']);
            $this->originalName = $file['name'];
            $this->originalShortname = $original['filename'];
            $this->originalExtension = $original['extension'];
        } else {
            switch ($file['error']) {
                case 0: //no error; possible file attack!
                    $this->error = "Nieznany błąd.";
                    break;
                case 1: //uploaded file exceeds the upload_max_filesize directive in php.ini
                    $this->error = "Plik, który próbujesz wysłać jest za duży.";
                    break;
                case 2: //uploaded file exceeds the MAX_FILE_SIZE directive - html form
                    $this->error = "Plik, który próbujesz wysłać jest za duży.";
                    break;
                case 3: //uploaded file was only partially uploaded
                    $this->error = "Plik został wysłany tylko w części.";
                    break;
                case 4: //no file was uploaded
                    $this->error = "Żaden plik nie został przesłany.";
                    break;
                default: //a default error, just in case!  :)
                    $this->error = "Nieznany błąd.";
                    break;
            }
        }
    }
    function save($name)
    {
        return move_uploaded_file($this->tmp_name, $name);
    }
}
