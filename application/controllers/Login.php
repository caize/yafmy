<?php
/**
 * @name IndexController
 * @author root
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 *
 * 登录控制器
 *
 */

class LoginController extends Base
{

    static public $_Instance = null;

    public function init()
    {

        if(!self::$_Instance){
            self::$_Instance = new  AdminModel();

        }

    }

    //控制器入口，根据路由配置
    public function indexAction()
    {
        $name = $this->getRequest()->getParam("name",'');
        if($name){

            switch($name){
                case "login";
                    $this->display('login/login.phtml');
                    break;
                case "verify";
                    $this->verify();
                    break;
                case "execute";
                    $this->executelogin();
                     break;
            }

        }

        Yaf\Dispatcher::getInstance()->autoRender(FALSE);
    }


    /**
     *生成验证码  也可以通过login/verify    但要定义成verifyAction
     */
    public function verify()
    {
        $aid = C('SESSION_USER_KEY');//读取配置文件
        if (session($aid)) {
            $this->redirect('index/index.phtml');
            return true;
        }
        ob_end_clean();
        $verify = new Verify (array(
            'fontSize' => 20,
            'imageH' => 42,
            'imageW' => 250,
            'length' => 5,
            'useCurve' => false,
        ));
        $verify->entry('aid');

        return false;
    }

    /**
     *用户登录验证
     */
    public function executelogin()
    {
        $ajax = $this->getRequest()->isXmlHttpRequest();
        if($ajax){
            $post =  $this->getRequest()->getPost();

            $admin_username = $post['admin_username'];
            $password = $post['admin_pwd'];

            $verify = trim($post['verify']);
            $aid = C('SESSION_USER_KEY');
            $verifyModel =new Verify ();
            $isverify =  $verifyModel->check($verify,$aid);//返回 1 验证成功  返回空则失败
            if(!$isverify){
                $this->error('验证码错误','login/login',0);
            }

            $admin= self::$_Instance->loginUsr($admin_username);

            if (!$admin||encrypt_password($password,$admin['admin_pwd_salt'])!==$admin['admin_pwd']){
                $this->error('用户名或者密码错误，重新输入','login/login',0);
            }else{
                //检查是否弱密码
                session('admin_weak_pwd', false);
                $weak_pwd_reg = array(
                    '/^[0-9]{0,6}$/',
                    '/^[a-z]{0,6}$/',
                    '/^[A-Z]{0,6}$/'
                );
                foreach ($weak_pwd_reg as $reg) {
                    if (preg_match($reg, $password)) {
                        session('admin_weak_pwd', true);
                        break;
                    }
                }
                //登录后更新数据库，登录IP，登录次数,登录时间
                $data=array(
                    'admin_last_ip'=>$admin['admin_ip'],
                    'admin_last_time'=>$admin['admin_time'],
                    'admin_ip'=>get_client_ip(0,true),
                    'admin_time'=>time(),
                );
                //dump($data);

                //更新数据处理
                $wheres = array('admin_id'=>$admin['admin_id']);
                $dparam = array('admin_hits'=> 1);
                self::$_Instance->setInc($wheres,$dparam);
                self::$_Instance->save(array('admin_username'=>$admin_username),$data);


                $admin_username = C('SESSION_ADMIN_USERNAME');
                $admin_realname = C('SESSION_ADMIN_REALNAME');
                $admin_avatar = C('SESSION_ADMIN_AVATAR');
                $admin_last_change_pwd_time = C('SESSION_ADMIN_LAST_CHANGE_PWD_TIME');

                session($aid,$admin['admin_id']);
                session($admin_username,$admin['admin_username']);
                session($admin_realname,$admin['admin_realname']);
                session($admin_avatar,$admin['admin_avatar']);
                session($admin_last_change_pwd_time, $admin ['admin_changepwd']);
                $this->success('恭喜您，登陆成功','index/index',0);
            }


        }

        return false;
    }


}