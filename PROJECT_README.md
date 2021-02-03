# project_name

## 环境要求

    php 7.3
    mysql 5.7 以上 可支持8.0版本以上
    
> php.ini   

```ini
upload_max_filesize = 1024M
post_max_size = 1024M
date.timezone = "Asia/Shanghai"
```

> nginx

```apacheconfig
client_max_body_size     1024M;
proxy_connect_timeout    9000s;
proxy_read_timeout       9000s;
proxy_send_timeout       9000s;
```

> mysql

```mysql.cnf
MySql 关闭 ONLY_FULL_GROUP_BY 参照链接 https://www.cnblogs.com/shoose/p/13259186.html
mysql5.7 及以上
[mysqld]
sql_mode ='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'
mysql8.0 及以上
[mysqld]
sql_mode ='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'
```

## 部署

```shell script
//数据库配置
touch .env.local
vim .env.local
APP_ENV=prod  #此项目根据开发环境决定，开发者可以不配
DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7  # 数据库配置

//文件夹权限
mkdir var
sudo chmod -R 777 var/

//创建数据库
php bin/console doctrine:database:create
php bin/console doctrine:schema:create

//内置数据
php bin/console doctrine:fixtures:load  --append

//安装资源
php bin/console assets:install  
```

