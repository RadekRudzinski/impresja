<?php

namespace impresja\impresja\models;


class MenuItemModel
{
    public string $icon = '';
    public string $title = '';
    public string $url = '';
    public const  HOME = '<i class="fa-solid fa-house"></i>';
    public const  USER = '<i class="fa-solid fa-user"></i>';
    public const  ADD = '<i class="fa-solid fa-file-circle-plus"></i>';
    public const  BACK = '<i class="fa-solid fa-arrow-left"></i>';
    public const  SAVE = '<i class="fa-solid fa-floppy-disk"></i>';

    public function __construct($icon, $title, $url)
    {
        $this->icon = $icon;
        $this->title = $title;
        $this->url = $url;
    }

    public function __toString()
    {
        return "<div class='main_menu_item'><a href='$this->url'>$this->icon $this->title</a></div>";
    }
}
