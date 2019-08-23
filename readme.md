# 欢迎使用JwtBaseApi

![LOGO](https://test1-1256003521.cos.ap-guangzhou.myqcloud.com/static/JwtBaseApi.png)

[![Php Version](https://img.shields.io/badge/php-%3E=7.2-brightgreen.svg?maxAge=2592000)](https://secure.php.net/)
[![Laravel Version](https://img.shields.io/badge/laravel-%3E=5.7-brightgreen.svg?maxAge=2592000)](https://laravel.com/)

## 项目简介

- 一套Laravel基于JWT的基础Api🍺

## 安装教程

- 克隆代码库`git clone https://github.com/zhukangs/JwtBaseApi.git` 
- 进入项目 ` cd JwtBaseApi`  ，复制一份配置文件 `cp .env.example .env` ，并填写数据库相关配置
- 然后执行命令 `composer install` 安装 laravel 框架，依赖库
- 生成密钥 `php artisan key:generate`
- 生成数据表以及部分初始数据 `php artisan migrate --seed` 
- 配置域名(按laravel项目正常配置即可,解析到public目录)
- 如发现权限相关问题 执行 chown -R 用户名:用户组 项目目录
- 测试反问：`http://xxx.test`，是否出现laravel初始页面



## 基础文档

地址：<http://apidoc.zam9.com/web/#/5?page_id=88>

访问密码：123456



作者 [@zhukang][1]
2019 年 8月 23日    