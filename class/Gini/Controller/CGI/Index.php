<?php

namespace Gini\Controller\CGI;

use \Gini\Controller\CGI;

class Index extends CGI {

    protected function getWechatUserInfo() {
        $conf = (array) \Gini\Config::get('wechat');
        $app = new \Wechat\App($conf['app_id'], $conf['app_secret']);
        $openId = $app->getOAuth()->getOpenId();
        $wxUserInfo = $app->getUserInfo($openId);
        return $wxUserInfo;
    }

    public function __index() {
        $userInfo = $this->getWechatUserInfo();
        $form = $this->form();
        if ($userInfo['openid'] && $form['wx-token'] && $form['wx-redirect']) {
            $token = $form['wx-token'];
            \Gini\Cache::of('wechat')->set('wx-user['.$token.']', $userInfo, 300);
            $this->redirect($form['wx-redirect'], ['wx-token' => $token]);
        }
    }

}