<?php
/**
 * 管理后台控制器
 * 
 * @author NHXuan <hnnhx2007@163.com>
 * @copyright 2015-11
 */
class IndexController extends Yaf\Controller_Abstract{
	
	
	//管理首页
	public function indexAction(){
		echo '网站首页';

		return false;
	}
	
	
	//文章列表
	public function listAction(){
	
		
		$PostM = new PostsModel();
		$page  = isset($_GET['page']) ? intval($_GET['page']):1;
		
        $search = isset($_GET['search']) && trim($_GET['search'])?trim($_GET['search']):'';
        
        $where = "post_type = 'post' and post_status = 'publish'";
        
        if($search){
            $where.=" and post_title like '%{$search}%' ";
        }
        

        $data = $PostM->listinfo($where,'ID desc',$page,10);
	  
	    if(isset($data['data']) && count($data['data']) > 0){
	        $this->assign(array('data' => $data['data']))
	             ->assign(array('pages' => $data['page']))
	             ->assign(array('postModel'=>$PostM));  
	    }
	
	}
	
	
    /**
     * 
     * 发布&更新文章
     * 
     */
	public function postAction(){
		
		$PostM = new PostsModel();    
		
		if(isset($_POST) && $_POST){

			$action         =  isset($_POST['action']) && trim($_POST['action'])?trim($_POST['action']):show_msg('缺少动作');
			$post_id        =  $_POST['post_id']?intval($_POST['post_id']):0;
			$post_title     =  isset($_POST['post_title'])?$_POST['post_title']:date('Y-m-d H:i:s',time());
            $post_cat       =  isset($_POST['post_cat']) && count($_POST['post_cat']) > 0?$_POST['post_cat']:show_msg('没有选择栏目');
			$post_content   =  $_POST['post_content']?$_POST['post_content']:'';              


			if($action == 'edit'){
		    
				if(isset($post_id)){

				    	$data = array(
				    	
				    	          'post_author' => $_SESSION['USER_NAME'],  
				    	          'post_date'  => date('Y-m-d H:i:s',SYS_TIME),
				    	          'post_title' => $post_title,
				    		      'post_content' => $post_content,
				    	          'post_status'  => 'publish',
				                  'post_type'    => 'post',
				    	);
				    	
				    	if($PostM->update($data,array('ID'=>$post_id))){
				    	 
		    		        //文章栏目关联表
		    		        $termM  = new TermRelationModel();
                           
		    		        //栏目附表
		                    $termTax  = new TermTaxonomyModel();


		                    //先所有栏目统计数-1
		                    $r = $termM->select(array('object_id' => $post_id));
			                foreach ($r as $cat){
			                
			                   if(!$termTax->update(array('count'=>'-=1'),array('term_id'=>$cat['term_taxonomy_id']))){
		                           show_msg('更新栏目数量失败!');
		                       }
		                       
			                }

		    		        
		    		        //先删掉所有与该文章关联的数据
				    	    if(!$termM->delete(array('object_id' => $post_id))){
			                        
			                         show_msg('出现异常,修改失败');
			                }
			                
			           
			                     
		                    foreach ($post_cat as $key => $catid){
		                     	
			                    $termdata = array(
			                                     'object_id' => $post_id,
			                                     'term_taxonomy_id' => $catid 
			                    );

			                     
			                    //采用replace into的方式添加数据
			                    if(!$termM->insert($termdata,'',true)){
			                     	
			                         show_msg('修改失败!');
			                    }
			                     
			                    //所有栏目+1
		                        if(!$termTax->update(array('count'=>'+=1'),array('term_id'=>$catid))){
		                           show_msg('更新栏目数量失败!');
		                        }
		                        
		                    }
		    
		                
		                    show_msg('修改成功!',"/".ADMIN_NAME."/index/post/id/".trim($post_id));
				    		  
				    	}else{
				    	
				    		 show_msg('修改失败!');   

				    	}
				
				}else{
				 
					show_msg('参数错误,缺少id');
					
				}
			
		 
		    //添加文章
		    }elseif($action == 'add'){   
		   
		    
		    	$data = array(
		    	
		    	          'post_author' => $_SESSION['USER_NAME'],  
		    	          'post_date'  => date('Y-m-d H:i:s',SYS_TIME),
		    	          'post_title' => $post_title,
		    		      'post_content' => $post_content,
		    	          'post_status'  => 'publish',
		                  'post_type'    => 'post',
		    	);
		    	 	
		    	$insertd = $PostM->insert($data,true);
		    	
		    	//添加关联栏目
		    	if($insertd){
		    
                     $termM    = new TermRelationModel();
                     $termTax  = new TermTaxonomyModel();
                     foreach ($post_cat as $catid){
                     	
	                     $termdata = array(
	                                     'object_id' => $insertd,
	                                     'term_taxonomy_id' => $catid 
	                     );
	                     
	                     
	                    
	                     //采用replace into的方式添加栏目关联
	                     if(!$termM->insert($termdata,'',true)){
	                     	 $PostM->delete(array('ID'=>$insertd));
	                         show_msg('添加失败!');
	                     }
	                     
	                     
	                     //修改栏目统计文章的个数
                         if(!$termTax->update(array('count'=>'+=1'),array('term_id'=>$catid))){
	                           show_msg('更新栏目数量失败!');
	                     }
	            
                     }
                     
                     show_msg('添加成功!',"/".ADMIN_NAME."/index/post/id/{$insertd}");
		    	
		    	}else{
		    		
		    	    show_msg('添加失败!');
		    	
		    	}
		    }
		}
			
		
		
		$id = intval($this->getRequest()->getParam("id", 0));	
		
		if($id){
			
			
			
			$data = $PostM->get_one(array('ID'=>$id,'post_type'=>'post','post_status'=>'publish'));
			
			if($data){
				 
				 $catdata = getCategory($id);
				 $arrcat  = array();
				 
				 //遍历所属栏目
				 foreach ($catdata as $val){
				 	$arrcat[] = $val['term_id']; 
				 }
				 
			     $this->assign(array('r' => $data))
			          ->assign(array('postModel'=>$PostM))
			          ->assign(array('arrcat'=>$arrcat));
			}

		}
		
		
	    $this->assign(array('id' => $id));
	}
	
	
	/**
	 * 
	 * 删除文章
	 */
	public function delAction(){
			
	    $id = intval($this->getRequest()->getParam("id", 0));	

		if($id){
			
			$PostM   = new PostsModel();
			$termM   = new TermRelationModel();   
			$termTax = new TermTaxonomyModel();
			
			$data = $PostM->get_one(array('ID'=>$id));
			
			if($data){
				 
				
			    //减少所属栏目统计的文章数量
                 $arrterm_id = $termM->select(array('object_id' => $id));
                 
                 if($arrterm_id){
	                 foreach ($arrterm_id as $r){
	                       if(!$termTax->update(array('count'=>'-=1'),array('term_id'=>$r['term_taxonomy_id']))){
	                           show_msg('更新栏目数量失败!');
	                       }
	                 }
                 }
                 
                 //删除文章表
				 if(!$PostM->delete(array('ID'=>$id))){
				 	   show_msg('出现异常,删除失败');
				 }
				
                 
				 //删除文章栏目关联
	    	     if(!$termM->delete(array('object_id' => $id))){       
                         show_msg('出现异常,修改失败');
                 }
                 
                 show_msg('删除成功','/'.ADMIN_NAME.'/index/list');
               
			}else{
			 
				show_msg('文章不存在');
			  
			}

		}else{
			
			show_msg('参数错误');
		
		} 
	}

	
	/**
	 * 
	 * 页面管理
     *  
	 */
	public function pageAction(){
	
	
	    
		$PostM = new PostsModel();
		$page  = isset($_GET['page']) ? intval($_GET['page']):1;
		
        $search = isset($_GET['search']) && trim($_GET['search'])?trim($_GET['search']):'';
        
        $where = "post_type = 'page' and post_status = 'publish'";
        
        if($search){
            $where.=" and post_title like '%{$search}%' ";
        }
        

        $data = $PostM->listinfo($where,'post_date desc',$page,10);
	  
	    	    
	    if(isset($data['data']) && count($data['data']) > 0){
	        $this->assign(array('data' => $data['data']))
	             ->assign(array('pages' => $data['page']))
	             ->assign(array('postModel'=>$PostM));  
	    }
		
	
	}
	
	
    /**
     * 
     * 发布&更新页面
     * 
     */
	public function postPageAction(){
		
		$PostM = new PostsModel();    
		
		if(isset($_POST) && $_POST){

			$action         =  isset($_POST['action']) && trim($_POST['action'])?trim($_POST['action']):show_msg('缺少动作');
			$post_id        =  $_POST['post_id']?$_POST['post_id']:0;
			$post_title     =  isset($_POST['post_title'])?$_POST['post_title']:date('Y-m-d H:i:s',time());
       		$post_content   =  $_POST['post_content']?$_POST['post_content']:'';              


			if($action == 'edit'){
		    
				if(isset($post_id)){

				    	$data = array(
				    	
				    	          'post_author' => 'admin',  // (:第一版先默认一个用户
				    	          'post_date'  => date('Y-m-d H:i:s',SYS_TIME),
				    	          'post_title' => $post_title,
				    		      'post_content' => $post_content,
				    	          'post_status'  => 'publish',
				                  'post_type'    => 'page',
				    	);
				    	
				    	if($PostM->update($data,array('ID'=>$post_id))){

		                    show_msg('修改成功!');
				    		  
				    	}else{
				    	
				    		 show_msg('修改失败!');   

				    	}
				
				}else{
				 
					show_msg('参数错误,缺少id');
					
				}
			
		 
		    //添加文章
		    }elseif($action == 'add'){   
		   
		    
		    	$data = array(
		    	
		    	          'post_author' => 'admin',  // (:第一版先默认一个用户
		    	          'post_date'  => date('Y-m-d H:i:s',SYS_TIME),
		    	          'post_title' => $post_title,
		    		      'post_content' => $post_content,
		    	          'post_status'  => 'publish',
		                  'post_type'    => 'page',
		    	);
		    	 	
		    	$insertid = $PostM->insert($data,true);
		    	
		    	//添加关联栏目
		    	if($insertid){
                     
                     show_msg('添加成功!',"/{ADMIN_NAME}/index/post/id/{$insertid}");
		    	
		    	}else{
		    		
		    	    show_msg('添加失败!');
		    	
		    	}
		    }
		}
			
		
		
		$id = intval($this->getRequest()->getParam("id", 0));	
		
		if($id){
			
			
			
			$data = $PostM->get_one(array('ID'=>$id,'post_type'=>'page','post_status'=>'publish'));
			
			if($data){
				 
				 $catdata = getCategory($id);
				 $arrcat  = array();
				 
				 //遍历所属栏目
				 foreach ($catdata as $val){
				 	$arrcat[] = $val['term_id']; 
				 }
				 
			     $this->assign(array('r' => $data))
			          ->assign(array('postModel'=>$PostM))
			          ->assign(array('arrcat'=>$arrcat));
			}

		}
		
		
	    $this->assign(array('id' => $id));
	}
	
	
	/**
	 * 
	 * 分类管理
	 * 
	 */
	public function categoryAction(){
		
		 $CatM     = new CategoryModel(); 
		 $data     = $CatM->getCategory();
         $pagesize = 10;  //每页显示5条             
		 $number   = count($data);
		 $page     = isset($_GET['page']) ? intval($_GET['page']):1;
		 $offset   = $pagesize*($page-1);
		 $pages    = pages($number, $page, $pagesize);
		 
         $data     = $CatM->getCategory('','',"LIMIT $offset, $pagesize");
         
	     $this->assign(array('data' => $data))->assign(array('pages' => $pages));
	}
	

	/**
	 * 
	 * 添加&编辑分类
	 * 
	 */
	public function postCategoryAction(){

		$termM   = new TermsModel();
		$termTax = new TermTaxonomyModel();
		
		if(isset($_POST) && $_POST){

			$action      =  isset($_POST['action']) && trim($_POST['action'])?trim($_POST['action']):show_msg('缺少动作');
			$cat_id      =  $_POST['cat_id']?$_POST['cat_id']:0;
			$name        =  isset($_POST['name'])?$_POST['name']:show_msg('请填写分类名称');      
       		$description =  isset($_POST['description'])?$_POST['description']:'';              

       		
			if($action == 'edit'){
		    
				if(isset($cat_id)){

					    $data = array(
					                  
					         'name'=>$name
					    );
				    
					    
					    //修改主表
				    	if($termM->update($data,array('term_id'=>$cat_id))){
                                  
				    		   
				    		  $data_2 = array(
				    		                
				    		       'description'=>$description
				    		  );
				    		  
				    		  
				    		  if($termTax->update($data_2,array('term_id'=>$cat_id))){
				    		  
				    		        show_msg('修改成功!');   
				    		  
				    		  }else{
				    	
				    		        show_msg('修改失败!');   
				              }
				    		  
				    	}else{
				    	
				    		 show_msg('修改失败!');   

				    	}
				
				}else{
				 
					show_msg('参数错误,缺少id');
					
				}
			
		 
		    //添加栏目
		    }elseif($action == 'add'){   
		   
		         
		         $data = array(
					    'name'=>$name,
		                'slug'=>seo_friendly_url($name),
				 );
		    	 					 
		    	$insertd = $termM->insert($data,true);
		    	
		    	//添加关联附表
		    	if($insertd){
                     
		    		 
		    		  $data_2 = array(
		    		       'taxonomy' => 'category',
		    		       'term_id' => $insertd,         
		    		       'description'=>$description
		    		  );
		    		  
		    		  if($termTax->insert($data_2)){
		    		  	
		    		  	   show_msg('添加分类成功!');
		    		  
		    		  }else{
		    		   
		    		  	   show_msg('添加成功!'); 
		    		  
		    		  }
				    		  
		    	}else{
		    		
		    	    show_msg('添加失败!');
		    	
		    	}
		    }
		}
		
		
		$id = intval($this->getRequest()->getParam("id", 0));
					
		if($id){

			$data = $termM->get_one(array('term_id'=>$id));
			
			if($data){
				 
				 $data2 = $termTax->get_one(array('term_id'=>$data['term_id']));
			
				 if($data2){
				   $r = array_merge($data,$data2);
				 }
			     $this->assign(array('r' => $r));
			}

		}
		
		
	    $this->assign(array('id' => $id));
	    
	
	}
	
	/**
	 * 
	 * 删除分类
	 * 
	 */
	public function delCategoryAction(){
	
	
	    $id = intval($this->getRequest()->getParam("id", 0));	

		if($id){
			
			$termM   = new TermsModel();
			$termTax = new TermTaxonomyModel();
		    $data = $termM->get_one(array('term_id'=>$id));
		    if($data){
				 

		    	 $r = $termTax->get_one(array('term_id'=>$id));
		    	
		    	 //判断该分类下是否存在文章
		    	 if($r['count'] > 0){
		    	      show_msg('请删除该分类下的文章后才可以删除栏目.');
		    	 }
		    	
		    	 //删除主表
				 if(!$termM->delete(array('term_id'=>$id))){
				 	   show_msg('出现异常,删除失败');
				 }

			     //删除附表
	    	     if(!$termTax->delete(array('term_id'=>$id))){
                        
                         show_msg('出现异常,修改失败');

                 }
                 
                 show_msg('删除成功');
                 
			}else{
			 
				show_msg('分类不存在');
			}

		}else{
			
			show_msg('参数错误');
		
		} 
	
	}
	
	
	/**
	 * 
	 *  修改密码
	 * 
	 */
	public function passwordAction(){
	
		if($_POST){
		
		     $password   =  isset($_POST['password'])?$_POST['password']:show_msg('请输入密码');   
		     $password2  =  isset($_POST['password2'])?$_POST['password2']:show_msg('请重复输入密码');     

		     if($password !== $password2){
		     	 show_msg('两次密码输入不一致');
		     }

		     $user = new UsersModel();
		     
		     $Yaf_hasher  = new Util_PasswordHash(8, TRUE);
		     $newpassword = $Yaf_hasher->HashPassword($password);

		     if($user->update(array('user_pass'=>$newpassword),array('ID'=>$_SESSION['USER_ID']))){
		     	 show_msg('修改成功');
		     }else{
		         show_msg('修改失败');
		     }
		     
		}
	
	}
	
	
	/**
	 * 
	 * 系统设置
	 * :) 这是我偷懒的节奏,实在太忙了,真心没有时间写了,等下个版本再完善吧,阿门
	 */
	public function systemAction(){
		
		if($_POST){
			 $config = "<?php \n return ".var_export($_POST,true).";?>";
			 if(@file_put_contents(APPLICATION_PATH.'/conf/system.php',$config)){
			      show_msg('修改成功');
			 }else{
			      show_msg('修改失败,请检查目录权限.');
			 }
	    }
	
	}
	

	
	/**
	 *
	 *  主题管理
	 *
	 */
	
	public function templateAction(){
	
	    $tempconfig = load_config('template');
		$temp_list = array();
		foreach($tempconfig as $key => $val){
		
		    $temp_path  = APPLICATION_PATH.'/template/'.$val['Name']; 

			if(file_exists($temp_path.'/config.php')){
			 
 				$temp_list[$key] = require $temp_path.'/config.php';		
				$temp_list[$key]['dirname'] = $val['Name'];
			}
			
		}


		$this->assign(array('data' => $temp_list));
	 
	}
	

}
