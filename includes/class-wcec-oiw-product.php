<?php

class WCEC_OIW_Product
{
    protected static $_instance = null;

    public static function init()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {
       
    }


}