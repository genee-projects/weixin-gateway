<?php

namespace Gini\Module;

class WeixinGateway
{
    public static function setup()
    {
        $file  = APP_PATH.'/'.DATA_DIR.'/config/clients.json';
        $customizedClients  = (array)json_decode(file_get_contents($file), true);
        $conf = \Gini\Config::export();
        $conf['app']['clients'] = array_merge((array)$conf['app']['clients'], $customizedClients);
        \Gini\Config::import($conf);
    }
}