<?php

namespace impresja\impresja;

class Grid
{
    public const UP = '<i class="fa-solid fa-square-caret-up"></i>';
    public const DOWN = '<i class="fa-solid fa-square-caret-down"></i>';

    public const STRING = 'string';
    public const BOOL = 'bool';
    public const SELECT = 'select';
    public static function Active($value)
    {
        if ($value) {
            return '<i class="fa-solid fa-square-check active"></i>';
        }
        return '<i class="fa-solid fa-square-check inactive"></i>';
    }

    public static function Currency($value, $currency = 'zł')
    {
        if ($value) {
            return number_format($value, 2, ',', ' ') . " " . $currency;
        }
        return '0,00 ' . $currency;
    }

    public static function Icon($file)
    {
        $path_parts = pathinfo($file);
        $path_parts['extension'] = mb_strtolower($path_parts['extension']);
        if ($path_parts['extension'] == 'pdf') {
            return "<i class='fa-solid fa-file-pdf'></i>";
        } elseif ($path_parts['extension'] == 'txt') {
            return "<i class='fa-solid fa-file-lines'></i>";
        } elseif ($path_parts['extension'] == 'doc') {
            return "<i class='fa-solid fa-file-word'></i>";
        } elseif ($path_parts['extension'] == 'xls') {
            return "<i class='fa-solid fa-file-excel'></i>";
        } elseif ($path_parts['extension'] == 'gif' or $path_parts['extension'] == 'jpg' or $path_parts['extension'] == 'png') {
            return "<i class='fa-solid fa-file-image'></i>";
        } else {
            return "<i class='fa-solid fa-file'></i>";
        }
    }
}
