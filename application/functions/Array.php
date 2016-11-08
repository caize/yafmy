<?php
/**
 * 处理数据相关函数
 */


/**
 *  说明:二维数组去重
 *  @param    array2D    要处理二维数组
 *  @param    stkeep     是否保留一级数组键值(默认不保留)
 *  @param    ndformat   是否保留二级数组键值(默认保留)
 *  @return   output     返回去重后的数组
 */
function unique_arr($array2D, $stkeep = false, $ndformat = true) {
    if($stkeep){    //一级数组键可以为非数字
        $stArr = array_keys($array2D);
    }

    if($ndformat){   //二级数组键必须相同
        $ndArr = array_keys(end($array2D));
    }

    foreach ($array2D as $v){  //降维
        $v = join(',', $v);
        $temp[] = $v;
    }

    $temp = array_unique($temp);
    foreach ($temp as $k => $v){  //数组重新组合
        if($stkeep){
            $k = $stArr[$k];
        }

        if($ndformat){
            $tempArr = explode(",",$v);
            foreach($tempArr as $ndkey => $ndval){
                $output[$k][$ndArr[$ndkey]] = $ndval;
            }
        }else{
            $output[$k] = explode(",",$v);
        }
    }
    return $output;
}

/**
 *
 * 将二维数组转换成一维数组
 * @param array $array 待转换的二维数组
 * @param string $glue 需要转换的键  如id
 */
function swapDoubleToSingle($array, $glue){
    $tmp = array();
    if($array) {
        foreach($array as $v) {
            $tmp[] = $v[$glue];
        }
    }

    return $tmp;
}

/**
 *
 * 将二维数组转换成对应格式的二维数组
 * @param array $array 待转换的二维数组
 * @param string $glue 需要转换的键,如id
 */
function swapDoubleToDouble($array, $glue){
    $tmp = array();
    if($array) {
        foreach($array as $v) {
            $tmp[$v[$glue]] = $v;
        }
    }
    return $tmp;
}