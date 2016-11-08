<?php
//路由规则
return  array(
    //后台登陆，验证码
    "login"=>new Yaf\Route\Regex(
        '#^/login-([\w-]+).html$#',
        array(
            'module' => 'index',
            'controller' => 'login',
            'action' => 'index'
        ),
        array(
            1 => 'name'
        )
    ),

    //系统管理控制器
    "sys"=>new Yaf\Route\Regex(
        '#^/sys-([\w-]+).html$#',
        array(
            'module' => 'index',
            'controller' => 'sys',
            'action' => 'index'
        ),
        array(
            1 => 'name'
        )
    ),
    //文章控制器
    "news"=>new Yaf\Route\Regex(
        '#^/news-([\w-]+).html$#',
        array(
            'module' => 'index',
            'controller' => 'news',
            'action' => 'index'
        ),
        array(
            1 => 'name'
        )
    ),

    //网站首页
    "home"=>new Yaf\Route\Regex(
        '#^/home-([\w-]+).html$#',
        array(
            'module' => 'home',
            'controller' => 'index',
            'action' => 'index'
        ),
        array(
            1 => 'name'
        )
    ),

    //查看文章内容路由
    "content" => new Yaf\Route\Regex(
        '#^content/([a-zA-Z-_0-9]+).html$#',
        array(
            'controller' => 'content',
            'action' => 'action'
        ),
        array('1' => 'ident')
    ),
    "category" => new Yaf\Route\Regex(
        '#^category/(/[a-zA-Z-_0-9]+/[a-zA-Z-_0-9]+/)$#',
        array(
            'controller' => 'category',
            'action' => 'subcat'
        ),
        array('1' => 'ident')
    )

);//注意分号
