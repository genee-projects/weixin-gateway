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
        if ($form['token'] && $form['redirect']) {
            \Gini\Cache::of('wechat')->set($form['token'], $unionId, 20);
            $this->redirect($form['redirect'], ['wx-token' => $form['token']]);
        }
    }

}