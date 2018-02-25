<?php

namespace Gini\Controller\API;

use \Gini\Controller\API;

class Wechat extends API {

    public function actionAuthorize($clientId, $clientSecret) {
        $clients = (array) \Gini\Config::get('app.clients');
        $admin_clients = (array) \Gini\Config::get('app.admin_clients');
        if (isset($clients[$clientId]) && $clients[$clientId] == $clientSecret) {
            $_SESSION['app.client_id'] = $clientId;
            if (in_array($clientId, $admin_clients)) {
                $_SESSION['app.admin_client_id'] = $clientId;
            }
            return session_id();
        }
        return false;
    }

    private function isAuthorized() {
        return isset($_SESSION['app.client_id']);
    }

    private function isAdmin() {
        return isset($_SESSION['app.admin_client_id']);
    }

    public function actionGetUnionId($token) {
        $userInfo = \Gini\Cache::of('wechat')->get('wx-user['.$token.']');
        return $userInfo['unionid'];
    }

    public function actionGetUserInfo($token) {
        $userInfo = \Gini\Cache::of('wechat')->get('wx-user['.$token.']');
        return $userInfo;
    }

    public function actionGetAccessToken() {
        if (!$this->isAuthorized()) return false;

        $conf = \Gini\Config::get('wechat');
        $app = new \Wechat\App($conf['app_id'], $conf['app_secret']);
        return $app->getAccessToken();
    }

    public function actionGetTicket($type) {
        if (!$this->isAuthorized()) return false;

        $conf = \Gini\Config::get('wechat');
        $app = new \Wechat\App($conf['app_id'], $conf['app_secret']);
        $js = new \Wechat\JS($app);
        return $js->getTicket($type);
    }

    public function actionGetJSSignPackage($url) {
        if (!$this->isAuthorized()) return false;

        $conf = \Gini\Config::get('wechat');
        $app = new \Wechat\App($conf['app_id'], $conf['app_secret']);
        $js = new \Wechat\JS($app);
        return $js->getSignPackage($url);
    }

    // 发送模板消息
    public function actionSendTemplateMessage($openId, $templateId, $data) {
        $conf = \Gini\Config::get('wechat');
        $app = new \Wechat\App($conf['app_id'], $conf['app_secret']);
        return $app->sendTemplateMessage($openId, $templateId, $data);
    }

    public function actionRegisterClient($clientId, $clientSecret) {
        if (!$this->isAuthorized() || !$this->isAdmin()) return false;
        $confs     = \Gini\Config::Get('app');
        $env       = $_SERVER['GINI_ENV'];
        $base_path = APP_PATH.'/'.RAW_DIR.'/config/';
        $file      = $base_path.'@'.$env.'/app.yml';
        if (!file_exists($file)) {
            $file = $base_path.'app.yml';
        }
        if (array_key_exists($clientId, (array)$confs['clients'])) {
            return false;
        }
        $confs['clients'][$clientId] = $clientSecret;
        $yaml_content                = yaml_emit($confs);
        file_put_contents($file, $yaml_content);
        \Gini\App\Cache::setup($env);
        $new_confs = \Gini\Config::Get('app');
        if ($new_confs == $confs) {
            return true;
        }
        else {
            return false;
        }
    }

    public function actionUnregisterClient($clientId, $clientSecret) {
        if (!$this->isAuthorized() || !$this->isAdmin()) return false;
        $confs     = \Gini\Config::Get('app');
        $env       = $_SERVER['GINI_ENV'];
        $base_path = APP_PATH.'/'.RAW_DIR.'/config/';
        $file      = $base_path.'@'.$env.'/app.yml';
        if (!file_exists($file)) {
            $file = $base_path.'app.yml';
        }
        if (!array_key_exists($clientId, (array)$confs['clients'])
            || $confs['clients'][$clientId] != $clientSecret) {
            return false;
        }
        unset($confs['clients'][$clientId]);
        $yaml_content = yaml_emit($confs);
        file_put_contents($file, $yaml_content);
        \Gini\App\Cache::setup($env);
        $new_confs = \Gini\Config::Get('app');
        if ($new_confs == $confs) {
            return true;
        }
        else {
            return false;
        }
    }
}