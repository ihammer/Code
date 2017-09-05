<?php

/**
 * Created by PhpStorm.
 * User: 武德安
 * Date: 2017/9/5 0005
 * Time: 15:03
 */
class String_
{
    /**
     * @param $url
     * @return string
     * @name 短网址
     */
    function urlShort($url){
        $url= crc32($url);
        $result= sprintf("%u", $url);
        $sUrl= '';
        while($result>0){
            $s= $result%62;
            if($s>35){
                $s= chr($s+61);
            } elseif($s>9 && $s<=35){
                $s= chr($s+ 55);
            }
            $sUrl.= $s;
            $result= floor($result/62);
        }
        return $sUrl;
    }

    /**
     * @param $str
     * @return string
     * @name 编码判断
     */
    function getcode($str) {
        $s1 = iconv('utf-8', 'gbk', $str);
        $s0 = iconv('gbk', 'utf-8', $s1);

        if ( $s0 == $str ) {
            return 'utf-8';
        } else {
            return 'gbk';
        }
    }

    /**

     * 字符串截取，支持中文和其他编码

     * @param [string] $str  [字符串]

     * @param integer $start [起始位置]

     * @param integer $length [截取长度]

     * @param string $charset [字符串编码]

     * @param boolean $suffix [是否有省略号]

     * @return [type]   [description]

     */

    function msubstr($str, $start=0, $length=15, $charset="utf-8", $suffix=true) {
        if(function_exists("mb_substr")) {
            return mb_substr($str, $start, $length, $charset);
        } elseif(function_exists('iconv_substr')) {
            return iconv_substr($str,$start,$length,$charset);
        }
        $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("",array_slice($match[0], $start, $length));
        if($suffix) {
            return $slice."…";
        }
        return $slice;
        /**
         * 这里的关键，$charset="utf-8"，对中文支持是很重要的！不然出现一些？号，就要挨批了！
         * 使用方法如下：
         * echo msubstr($str, $start=0, $length=15, $charset="utf-8", $suffix=true)
         * */
    }

}