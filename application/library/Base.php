<?php
/**
 * 控制器基类
 *
 */

class Base extends Yaf\Controller_Abstract {

    //验证是否登录
    public function init(){

        $aid = C('SESSION_USER_KEY');
        if (!session($aid)) {
            $this->redirect('/login-login.html');
            return true;
        }
    }

    /**
     * 模板变量赋值
     * @access protected
     * @param mixed $name 要显示的模板变量
     * @param mixed $value 变量的值
     * @return $this
     */
    protected function assign($name,$value)
    {
        $this->getView()->assign($name,$value);
        return $this;

    }

    /**
     * 模板显示 调用内置的模板引擎显示方法，
     * @access protected
     * @param string $path 指定要调用的模板文件
     * @return $this
     */
    protected function display($path)
    {
        $this->getView()->display($path);
        return $this;
    }




    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param string $message 错误信息
     * @param string $jumpUrl 页面跳转地址
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @return void
     */
    protected function error($message = '', $jumpUrl = '', $ajax = false)
    {
        $this->dispatchJump($message, 0, $jumpUrl, $ajax);
    }

    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param string $message 提示信息
     * @param string $jumpUrl 页面跳转地址
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @return void
     */
    protected function success($message = '', $jumpUrl = '', $ajax = false)
    {
        $this->dispatchJump($message, 1, $jumpUrl, $ajax);
    }


    /**
     * 默认跳转操作 支持错误导向和正确跳转
     * 调用模板显示 默认为public目录下面的success页面
     * 提示页面为可配置 支持模板标签
     * @param string $message 提示信息
     * @param Boolean $status 状态
     * @param string $jumpUrl 页面跳转地址
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @access private
     * @return void
     */
    private function dispatchJump($message, $status = 1, $jumpUrl = '', $ajax = false)
    {
        if (true === $ajax || IS_AJAX) {
            // AJAX提交
            $data           = is_array($ajax) ? $ajax : array();
            $data['info']   = $message;
            $data['status'] = $status;
            $data['url']    = $jumpUrl;
            $this->ajaxReturn($data);
        }
        if (is_int($ajax)) {
            $this->assign('waitSecond', $ajax);
        }

        if (!empty($jumpUrl)) {
            $this->assign('jumpUrl', $jumpUrl);
        }

        // 提示标题
        $this->assign('msgTitle', $status ? L('_OPERATION_SUCCESS_') : L('_OPERATION_FAIL_'));

        $this->assign('status', $status); // 状态

        //成功
        if ($status) {
            //发送成功信息
            $this->assign('message', $message); // 提示信息
            // 成功操作后默认停留1秒
            if (!isset($this->waitSecond)) {
                $this->assign('waitSecond', '1');
            }

            // 默认操作成功自动返回操作前页面
            if (!isset($this->jumpUrl)) {
                $this->assign("jumpUrl", $_SERVER["HTTP_REFERER"]);
            }

            $this->display(C('TMPL_ACTION_SUCCESS'));

            //失败
        } else {
            $this->assign('error', $message); // 提示信息
            //发生错误时候默认停留3秒
            if (!isset($this->waitSecond)) {
                $this->assign('waitSecond', '3');
            }

            // 默认发生错误的话自动返回上页
            if (!isset($this->jumpUrl)) {
                $this->assign('jumpUrl', "javascript:history.back(-1);");
            }

            $this->display(C('TMPL_ACTION_ERROR'));
            // 中止执行  避免出错后继续执行
            exit;
        }
    }


    /**
     * Ajax方式返回数据到客户端
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type AJAX返回数据格式
     * @param int $json_option 传递给json_encode的option参数
     * @return void
     */
    protected function ajaxReturn($data, $type = '', $json_option = 0)
    {
        if (empty($type)) {
            $type = C('DEFAULT_AJAX_RETURN');
        }

        switch (strtoupper($type)) {
            case 'JSON':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($data, $json_option));
            case 'XML':
                // 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                exit(xml_encode($data));
            case 'JSONP':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                $handler = isset($_GET[C('VAR_JSONP_HANDLER')]) ? $_GET[C('VAR_JSONP_HANDLER')] : C('DEFAULT_JSONP_HANDLER');
                exit($handler . '(' . json_encode($data, $json_option) . ');');
            case 'EVAL':
                // 返回可执行的js脚本
                header('Content-Type:text/html; charset=utf-8');
                exit($data);
            default:
                // 用于扩展其他返回格式数据
                Hook::listen('ajax_return', $data);
        }
    }
}
