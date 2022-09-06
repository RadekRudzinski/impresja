<?php

namespace impresja\impresja;

class File
{
    public string $name = '';
    public string $path = '';
    public string $extension = '';
    public string $shortname = '';
    public string $full_path = '';
    public ?string $type = null;
    public ?string $error = null;

    function __construct(string $name)
    {
        if (file_exists($name) && !is_dir($name)) {
            $this->full_path = $name;
            $path_parts = pathinfo($name);
            $this->path = $path_parts['dirname'];
            $this->name = $path_parts['basename'];
            $this->shortname = $path_parts['filename'];
            if (isset($path_parts['extension'])) {
                $this->extension = $path_parts['extension'];
                $this->type = mime_content_type($name);
            }
        }
    }
    public function fileSize()
    {
        return fileSize($this->full_path);
    }

    public function unlink()
    {
        if (file_exists($this->full_path) && !is_dir($this->full_path)) {
            unlink($this->full_path);
            return true;
        }
        return false;
    }

    public function link()
    {
        return $this->path . $this->name;
    }
}
