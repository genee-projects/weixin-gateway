# Weixin Gateway

1. configure `raw/config/cache.yml`:

```yaml
wechat:
  driver: redis
  options:
    servers:
      default:
        host: 172.17.0.1
        port: 6379
```

2. configure `raw/config/wechat.yml`:

```yaml
app_id: APPID
app_secret: APPSECRET
```
