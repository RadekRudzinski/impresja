<?php

namespace impresja\impresja;

class Session
{
    protected const FLASH_KEY = 'flash_messages';
    public function __construct()
    {
        session_start();
        $flashMesseges = $_SESSION[self::FLASH_KEY] ?? [];
        foreach ($flashMesseges as &$flashMessage) {
            $flashMessage['remove'] = true;
        }
        $_SESSION[self::FLASH_KEY] = $flashMesseges;
    }

    public function setFlash($key, $message)
    {
        $_SESSION[self::FLASH_KEY][$key] = [
            'remove' => false,
            'value' => $message
        ];
    }

    public function getFlash($key)
    {
        return $_SESSION[self::FLASH_KEY][$key]['value'] ?? false;
    }


    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public function get($key)
    {
        return $_SESSION[$key] ?? false;
    }

    public function remove($key)
    {
        unset($_SESSION[$key]);
    }

    public function __destruct()
    {
        $flashMesseges = $_SESSION[self::FLASH_KEY];

        foreach ($flashMesseges as $key => $flashMessage) {
            if ($flashMessage['remove']) {
                unset($flashMesseges[$key]);
            }
        }
        $_SESSION[self::FLASH_KEY] = $flashMesseges;
    }
}
