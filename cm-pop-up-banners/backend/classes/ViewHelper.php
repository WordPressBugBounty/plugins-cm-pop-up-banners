<?php

namespace com\cminds\popupfly;

class ViewHelper {

    public static function load($filename, $data = array()) {
        $_a12ec803b2ce49e4a541068d495ab523 = $data;
        unset($data);
        ob_start();
        extract($_a12ec803b2ce49e4a541068d495ab523, EXTR_SKIP);
        include CMPOPFLY_PLUGIN_DIR . $filename;
        return ob_get_clean();
    }

}
