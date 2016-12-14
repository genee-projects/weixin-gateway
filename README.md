# Weixin Gateway

### 如何调用
1. 获取当前登录微信用户
    
    ```php
    protected function redirectToGateway() {
        $gatewayUrl = \Gini\Config::get('wechat.gateway')['url'];
        $token = md5(mt_rand());
        $this->redirect($gatewayUrl, [
            'wx-redirect' => URL($_SERVER['REQUEST_URI']),
            'wx-token' => $token
        ]);
    }

    protected function getWechatId() {
        $unionId = $_SESSION['wechat-gateway.unionid'];
        if (!$unionId) {
            $token = $_GET['wx-token'];
            if (!$token) {
                // 如果没有微信号而且没有token, 应该跳转到微信网关
                $this->redirectToGateway();
            }

			$conf = \Gini\Config::get('wechat.gateway');
            $rpc = new \Gini\RPC($conf['api_url']);
            $unionId = $rpc->Wechat->getUnionId($token);
            if ($unionId) {
                $_SESSION['wechat-gateway.unionid'] = $unionId;
            } else {
                $this->redirectToGateway();
            }
        }
        return $unionId;
    }
    ```
2. 获取JS-SDK调用权限

	```php
	<?php
		$conf = \Gini\Config::get('wechat.gateway');
		$rpc = new \Gini\RPC($conf['api_url']);
		$rpc->Wechat->authorize($conf['client_id'], $conf['client_secret']);
		$signPackage = $rpc->Wechat->getJSSignPackage(URL());
	?>
	<script>
	require(['weixin'], function(wx) {
		var config = <?= J($signPackage) ?>;
        conf.jsApiList = ['scanQRCode'];
        wx.config(conf);
        wx.ready(function() {
            wx.scanQRCode({
                needResult: 1
                ,scanType: ["qrCode"]
                ,success: function(res) {
                    var result = res.resultStr;
                    window.location.href = result;
                }
            });
        });
        wx.error(function(res) {
            alert(JSON.stringify(res));
        });
	});
	</script>
    ```

3. 获取当前登录微信用户
    ```php
    <?php
    $conf = \Gini\Config::get('wechat.gateway');
    $rpc = new \Gini\RPC($conf['api_url']);
    $rpc->Wechat->authorize($conf['client_id'], $conf['client_secret']);
    $rpc->Wechat->sendTemplateMessage('OPENID', 'TEMPLATEID', [
        'url' => 'path/to/your/url',
        'topcolor' => '#FF0000',
        'user' => [
            'value' => '张三',
            'color' => '#173177',
        ]
    ]);
    ?>
    ```    


### 如何安装
1. 配置到 gini 环境
2. 修改 `raw/config/cache.yml` 设置Gateway需要的缓冲服务器, 用于对申请的token进行临时保存:

	```yaml
	wechat:
	  driver: redis
	  options:
	    servers:
	      default:
	        host: 172.17.0.1
	        port: 6379
	```

2. 修改 `raw/config/wechat.yml` 设置gateway对应的公众号授权:

	```yaml
	app_id: APPID
	app_secret: APPSECRET
	```

3. 修改 `raw/config/app.yml` 设置gateway授权的客户端id和secret:

	```yaml
	clients:
	  CLIENT_ID: CLIENT_SECRET
	```
