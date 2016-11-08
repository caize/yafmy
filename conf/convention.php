<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

/**
 * 惯例配置文件
 * 该文件请不要修改，如果要覆盖惯例配置的值，可在应用配置文件中设定和惯例不符的配置项
 * 配置名称大小写任意，系统会统一转换成小写
 * 所有配置参数都可以在生效前动态改变
 */

return array(

    /* Cookie设置 */
    'COOKIE_EXPIRE'          => 0, // Cookie有效期
    'COOKIE_DOMAIN'          => '', // Cookie有效域名
    'COOKIE_PATH'            => '/', // Cookie路径
    'COOKIE_PREFIX'          => '', // Cookie前缀 避免冲突
    'COOKIE_SECURE'          => false, // Cookie安全传输
    'COOKIE_HTTPONLY'        => '', // Cookie httponly设置

    /* SESSION设置 */
    'SESSION_AUTO_START'     => false, // 是否自动开启Session
    'SESSION_OPTIONS'        => array(), // session 配置数组 支持type name id path expire domain 等参数
    'SESSION_TYPE'           => '', // session hander类型 默认无需设置 除非扩展了session hander驱动
    'SESSION_PREFIX'         => 'yaf_', // session 前缀


    'SESSION_USER_KEY'               => 'aid', // session 前缀
    'SESSION_ADMIN_WEAK_PWD'         => 'admin_weak_pwd', //登录用户
    'SESSION_ADMIN_USERNAME'         => 'admin_username', //登录用户
    'SESSION_ADMIN_REALNAME'         => 'admin_realname', //登录用户
    'SESSION_ADMIN_AVATAR'           => 'admin_avatar', //登录用户
    'SESSION_ADMIN_LAST_CHANGE_PWD_TIME'         => 'admin_last_change_pwd_time', //登录用户


    //'VAR_SESSION_ID'      =>  'session_id',     //sessionID的提交变量

    /* 默认设定 */
    'DEFAULT_AJAX_RETURN'    => 'JSON', // 默认AJAX 数据返回格式,可选JSON XML ...
    'DEFAULT_LANG'           => 'zh-cn', // 默认语言
    'DB_PREFIX'              => 'yf_',



    /* 模板引擎设置 */
    'TMPL_ACTION_ERROR'      => 'error/dispatch_jump.tpl', // 默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS'    => 'error/dispatch_jump.tpl', // 默认成功跳转对应的模板文件

    /*AJAX*/
    'VAR_AJAX_SUBMIT'        => 'ajax', // 默认的AJAX提交变量

    /*URL参数*/
    'VAR_MODULE'             => 'm', // 默认模块获取变量
    'VAR_CONTROLLER'         => 'c', // 默认控制器获取变量
    'VAR_ACTION'             => 'a', // 默认操作获取变量


    /* URL设置 */
    'URL_PATHINFO_DEPR'      => '/', // PATHINFO模式下，各参数之间的分割符号
    'URL_CASE_INSENSITIVE'   => false, // 默true 表示URL不区分大小写 false则表示区分大小写
    'MULTI_MODULE'           => true, // 是否允许多模块 如果为false 则必须设置 DEFAULT_MODULE
    'URL_MODEL'              => 0, // 0 (普通模式); 1 (PATHINFO 模式);
    'URL_HTML_SUFFIX'        => 'html', // URL伪静态后缀设置,只有 1 默认时才生效

    'APP_SUB_DOMAIN_DEPLOY'  => false, // 是否开启子域名部署
    'APP_SUB_DOMAIN_RULES'   => array(), // 子域名部署规则

);
