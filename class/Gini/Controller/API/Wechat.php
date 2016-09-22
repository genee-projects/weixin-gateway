<?php

namespace Gini\Controller\API;

use \Gini\Controller\API;

class Wechat extends API {

    public function actionAuthorize($clientId, $clientSecret) {
        $clients = (array) \Gini\Config::get('app.clients');
        if (isset($clients[$clientId]) && $clients[$clientId] == $clientSecret) {
            $_SESSION['app.client_id'] = $clientId;
            return session_id();
        }
        return false;
    }

    private function isAuthorized() {
        return isset($_SESSION['app.client_id']);
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

}