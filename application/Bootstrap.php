<?php
/**
 * @name Bootstrap
 * @author root
 * @desc 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * @see http://www.php.net/manual/en/class.yaf-bootstrap-abstract.php
 * 这些方法, 都接受一个参数:Yaf\Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */
class Bootstrap extends Yaf\Bootstrap_Abstract{
	protected $config;
    public function _initConfig() {
		//把配置保存起来
		$this->config = $arrConfig = Yaf\Application::app()->getConfig();
		Yaf\Registry::set('config', $arrConfig);
	}

	public function _initSession(Yaf\Dispatcher $dispatcher) {
		Yaf\Session::getInstance()->start();
	}
	
	public function _initError(Yaf\Dispatcher $dispatcher) {
		if ($this->config->application->debug)
		{
			define('DEBUG_MODE', false);
			ini_set('display_errors', 'On');
		}
		else
		{
			define('DEBUG_MODE', false);
			ini_set('display_errors', 'Off');
		}
	}

	public function _initPlugin(Yaf\Dispatcher $dispatcher) {
		//注册一个插件
		$objSamplePlugin = new SamplePlugin();
		$dispatcher->registerPlugin($objSamplePlugin);
	}

	//使用默认路由
	/*
	public function _initRoute(Yaf\Dispatcher $dispatcher) {

		//在这里注册自己的路由协议,默认使用简单路由
		$router = $dispatcher->getInstance()->getRouter();
		$router ->addConfig(Yaf\Registry::get('config')->routes);
		
	}

	public function _initRoute(Yaf_Dispatcher $dispatcher) {
		//在这里注册自己的路由协议,默认使用简单路由
		$router = $dispatcher->getInstance()->getRouter();
		// 指定 3 个变量名 在 URL 参数名
		$route = new Yaf_Route_Simple("m", "c", "a");
		$router->addRoute("Simple", $route);
		//对于如下请求: "http://domain.com/index.php?c=index&a=test
	}
	*/


	//使用正则路由Yaf_Route_Regex
	public function _initRoutes(Yaf\Dispatcher $dispatcher) {
		//在这里注册自己的路由协议,默认使用简单路由
		//通过派遣器得到默认的路由器(默认路由器是:Yaf_Router;默认路由协议是:Yaf_Rout_Static)

		$router = $dispatcher->getInstance()->getRouter();
		$routeFile= APPLICATION_PATH . "/conf/routes.php";
		$arrRoute =require_once($routeFile); //注意Yaf\Loader::import($path); 返回Boolean true/false
		if(is_array($arrRoute)){
			foreach($arrRoute as $key => $val){
				$router->addRoute($key, $val);
			}
		}

	}



	//包含函数库
	public function _initFunc(Yaf\Dispatcher $dispatcher)
	{
		//加载函数
		if( is_dir($this->config->application->directory.'/functions/') ){
			$auto_funcs = glob($this->config->application->directory.'/functions/*.php');
			if(!empty($auto_funcs) && is_array($auto_funcs)) {
				foreach($auto_funcs as $func_path)
				{
					if(file_exists($func_path))
					{
						Yaf\Loader::import($func_path);
					}
				}
			}

		}
	}


	//加载惯例配置文件
	public function _initConvention(Yaf\Dispatcher $dispatcher){

		$conf= APPLICATION_PATH . "/conf/convention.php";
		if(is_file($conf)){
			if(function_exists('C')){
				C(load_config($conf)); //利用thinkPHP加载惯例配置文件的思路
			}else{
				//TODO
			}
		}
	}

	//定义系统常量
	public function _initSys(Yaf\Dispatcher $dispatcher){
		// 系统信息
		if (version_compare(PHP_VERSION, '5.4.0', '<')) {
		ini_set('magic_quotes_runtime', 0);
		define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc() ? true : false);
		} else {
			define('MAGIC_QUOTES_GPC', false);
		}

		define('IS_AJAX', ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || !empty($_POST[C('VAR_AJAX_SUBMIT')]) || !empty($_GET[C('VAR_AJAX_SUBMIT')])) ? true : false);
		define('MODULE_NAME','index');
		define('ACTION_NAME','index');
		define('CONTROLLER_NAME','index');

		define('IS_CGI', (0 === strpos(PHP_SAPI, 'cgi') || false !== strpos(PHP_SAPI, 'fcgi')) ? 1 : 0);
		if (!defined('_PHP_FILE_')) {
			if (IS_CGI) {
				//CGI/FASTCGI模式下
				$_temp = explode('.php', $_SERVER['PHP_SELF']);
				define('PHP_FILE', rtrim(str_replace($_SERVER['HTTP_HOST'], '', $_temp[0] . '.php'), '/'));
			} else {
				define('PHP_FILE', rtrim($_SERVER['SCRIPT_NAME'], '/'));
			}
		}
		define('APPLICATION', strip_tags(PHP_FILE));

	}

	// 加载框架底层语言包
	public function _initLang(Yaf\Dispatcher $dispatcher){

		if(function_exists('C')){
			$lang = $this->config->application->directory . '/lang/' . strtolower(C('DEFAULT_LANG')) . '.php';
			if(file_exists($lang)){
				if(function_exists("L")){
					L(require_once $lang);
				}
			}
		}
	}


	//视图
	public function _initView(Yaf\Dispatcher $dispatcher){
		//在这里注册自己的view控制器，例如smarty,firekylin
		$dispatcher->getInstance()->returnResponse(FALSE);//关闭自动响应
		//Yaf\Dispatcher::getInstance()->autoRender(FALSE); // 关闭自动加载模板
	}
}
