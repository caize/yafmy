<?php

use Db\Driver;

class NewsModel extends Driver{

    protected $_table = "yf_news";
    protected $_table_s = "yf_diyflag";

    /**
     * 用户登录判断
     */
    public function newsCount($field,$alias)
    {

        if($field){
            $sql = "SELECT COUNT(1) AS {$alias} FROM `{$this->_table}` WHERE ( {$field} ) LIMIT 1";
            $counts = $this->_db->query($sql);
            if(!empty($counts)){
                return $counts[0];
            }else{
                return false;
            }
        }

    }

    //获取文章列表
    public function newsList($sWhere=null)
    {
        $aColumns = array('news_title','news_titleshort','news_columnid','news_flag','news_open','news_key','news_source','news_hits','news_time','n_id');
        $o_news = new Table($this->_table,$aColumns,'n_id');
        $list = $o_news ->complex($sWhere);
        return  $list;

    }

    //获取文章的属性
    public function attribute(){

        $wheres = array();
        $new_attr = $this->_db->select($this->_table_s,$wheres);
        if(is_array($new_attr) && !empty($new_attr)){
            return $new_attr;
        }
        return false;
    }







}
