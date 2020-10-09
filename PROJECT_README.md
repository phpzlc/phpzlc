# project_name

## 环境要求
    php 7.3
    mysql 5.6 以上 可支持8.0版本以上
    
> php.ini   

```ini
upload_max_filesize = 100M
post_max_size = 100M
date.timezone = "Asia/Shanghai"
```

> nginx

```apacheconfig
client_max_body_size     100M;
proxy_connect_timeout    900s;
proxy_read_timeout       900s;
proxy_send_timeout       900s;
```

## 部署

```shell script
//数据库配置
touch .env.local
vim .env.local
  APP_ENV=prod  #此项目根据开发环境决定，开发者可以不配
  DATABASE_URL=mysql://root:root@127.0.0.1:3306/portal_skeleton?serverVersion=5.6  # 数据库配置

//文件夹权限
mkdir var
sudo chmod -R 777 var/

//创建数据库
php bin/console doctrine:database:create
php bin/console doctrine:schema:create

//安装资源
php bin/console assets:install  
```

