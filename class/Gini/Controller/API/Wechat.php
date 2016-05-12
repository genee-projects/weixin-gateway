<?php

namespace Gini\Controller\API;

use \Gini\Controller\API;

class Wechat extends API {

    public function actionGetUnionId($token) {
        return \Gini\Cache::of('wechat')->get('unionid['.$token.']');
    }

}