<?php

namespace Gini\Controller\CGI;

use \Gini\Controller\CGI;

class Index extends CGI {

    protected function getWechatId() {
        $conf = (array) \Gini\Config::get('wechat');
        $app = new \Wechat\App($conf['app_id'], $conf['app_secret']);
        $openId = $app->getOAuth()->getOpenId();
        $wxUserInfo = $app->getUserInfo($openId);
        return $wxUserInfo['unionid'];
    }

    public function __index() {
        $unionId = $this->getWechatId();
        $form = $this->form();
        if ($form['wx-token'] && $form['wx-redirect']) {
            $token = $form['wx-token'];
            \Gini\Cache::of('wechat')->set('unionid['.$token.']', $unionId, 20);
            $this->redirect($form['wx-redirect'], ['wx-token' => $token]);
        }
    }

}