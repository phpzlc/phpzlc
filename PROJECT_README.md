# project_name

## 环境要求

    php 8.2
    mysql 5.7 以上 可支持8.0版本以上
    
> php.ini   

```ini
date.timezone = "Asia/Shanghai"
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
//项目配置
touch .env.local
vim .env.local
APP_ENV=prod  #生产环境配置
DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7  # 数据库配置

//文件夹权限
mkdir var
sudo chmod -R 777 var/

//创建数据库
php bin/console doctrine:database:create
php bin/console doctrine:schema:create

//安装资源
php bin/console assets:install  

//内置数据(APP_ENV=dev环境下可执行)
php bin/console doctrine:fixtures:load  --append

//生成文档(非必须)
php bin/console phpzlc:generate:document
```

