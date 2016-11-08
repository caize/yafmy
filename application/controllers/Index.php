<?php
/**
 * @name IndexController
 * @author root
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */

class IndexController extends Base {

	/** 
     * 默认动作
     * Yaf支持直接把Yaf_Request_Abstract::getParam()得到的同名参数作为Action的形参
     * 对于如下的例子, 当访问http://yourhost/demo/index/index/index/name/root 的时候, 你就会发现不同
     */
	public function indexAction()
	{

		$this->display("index/index.phtml");
		Yaf\Dispatcher::getInstance()->autoRender(FALSE);
	}

	public function showAction(){
		echo 1111;
	}

}
