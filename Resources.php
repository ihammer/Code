<?php

/**
 * Created by PhpStorm.
 * User: 武德安
 * Date: 2017/9/5 0005
 * Time: 15:05
 * Name: 资源类
 */
class Resources
{

    /**获取客户端ip
     * @return string
     */
    protected function getClientIp()
    {
        if (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } else if (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } else if (getenv('REMOTE_ADDR')) {
            $ip = getenv('REMOTE_ADDR');
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    /***富文本编辑器标签过滤（htmlpurifier插件）**/
    function removeXSS($data)
    {
        require_once './HtmlPurifier/HTMLPurifier.auto.php';
        $_clean_xss_config = HTMLPurifier_Config::createDefault();
        $_clean_xss_config->set('Core.Encoding', 'UTF-8');
        // 设置保留的标签
        $_clean_xss_config->set('HTML.Allowed','div,b,strong,i,em,a[href|title],ul,ol,li,p[style],br,span[style],img[width|height|alt|src]');
        $_clean_xss_config->set('CSS.AllowedProperties', 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align');
        $_clean_xss_config->set('HTML.TargetBlank', TRUE);
        $_clean_xss_obj = new HTMLPurifier($_clean_xss_config);
        // 执行过滤
        return $_clean_xss_obj->purify($data);
    }

    /**
     * 登录原地址跳转过滤函数
     * redirect('home/login?from='__SELF__,0,'正在跳转'); 通过I('get.from')获取原地址,但是如果from被恶意手动输入外部网址,则登录就会跳转到外部网站,所以需要做下面的地址过滤 from=后面的url地址
     * @param  [type] $url [description]
     * @return [type]      [description]
     */
    function gt($url){
        // 如果存在http://开头
        if(preg_match("/^http:\/\//i", $url)){
            // 白名单
            if(preg_match("/^http:\/\/(localhost)|(www.baidu.com)/i")){
                return $url;
            }else{
                return 'home/index/index';
            }
        }else{
            return $url;
        }
    }

    /**************onethink加解密码方法********************
    使用demo 加密cookie
    setcookie('user',think_encrypt(serialize($user_login,C('KEY')),time()+3600,"/"))
     */

    /**
     * 系统加密方法
     * @param string $data 要加密的字符串
     * @param string $key  加密密钥
     * @param int $expire  过期时间 单位 秒
     * @return string
     * @author 武德安
     */
    function think_encrypt($data, $key = '', $expire = 0) {
        $key  = md5(empty($key) ? C('DATA_AUTH_KEY') : $key);
        $data = base64_encode($data);
        $x    = 0;
        $len  = strlen($data);
        $l    = strlen($key);
        $char = '';

        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) $x = 0;
            $char .= substr($key, $x, 1);
            $x++;
        }

        $str = sprintf('%010d', $expire ? $expire + time():0);

        for ($i = 0; $i < $len; $i++) {
            $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1)))%256);
        }
        return str_replace(array('+','/','='),array('-','_',''),base64_encode($str));
    }

    /**
     * 系统解密方法
     * @param  string $data 要解密的字符串 （必须是think_encrypt方法加密的字符串）
     * @param  string $key  加密密钥
     * @return string
     * @author 武德安
     */
    function think_decrypt($data, $key = ''){
        $key    = md5(empty($key) ? C('DATA_AUTH_KEY') : $key);
        $data   = str_replace(array('-','_'),array('+','/'),$data);
        $mod4   = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        $data   = base64_decode($data);
        $expire = substr($data,0,10);
        $data   = substr($data,10);

        if($expire > 0 && $expire < time()) {
            return '';
        }
        $x      = 0;
        $len    = strlen($data);
        $l      = strlen($key);
        $char   = $str = '';

        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) $x = 0;
            $char .= substr($key, $x, 1);
            $x++;
        }

        for ($i = 0; $i < $len; $i++) {
            if (ord(substr($data, $i, 1))<ord(substr($char, $i, 1))) {
                $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
            }else{
                $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
            }
        }
        return base64_decode($str);
    }


    //二分查找
    function bin_sch($array, $low, $high, $k)
    {
        if ($low <= $high) {
            $mid = intval(($low + $high) / 2);
            if ($array[$mid] == $k) {
                return $mid;
            } elseif ($k < $array[$mid]) {
                return bin_sch($array, $low, $mid - 1, $k);
            } else {
                return bin_sch($array, $mid + 1, $high, $k);
            }
        }
        return -1;
    }

    //顺序查找（数组里查找某个元素）
    function seq_sch($array, $n, $k)
    {
        $array[$n] = $k;
        for ($i = 0; $i < $n; $i++) {
            if ($array[$i] == $k) {
                break;
            }
        }
        if ($i < $n) {
            return $i;
        } else {
            return -1;
        }
    }

    //线性表的删除（数组中实现）
    function delete_array_element($array, $i)
    {
        $len = count($array);
        for ($j = $i; $j < $len; $j++) {
            $array[$j] = $array[$j + 1];
        }
        array_pop($array);
        return $array;
    }

//冒泡排序（数组排序）
    function bubble_sort($array)
    {
        $count = count($array);
        if ($count <= 0) return false;
        for ($i = 0; $i < $count; $i++) {
            for ($j = $count - 1; $j > $i; $j--) {
                if ($array[$j] < $array[$j - 1]) {
                    $tmp = $array[$j];
                    $array[$j] = $array[$j - 1];
                    $array[$j - 1] = $tmp;
                }
            }
        }
        return $array;
    }


//快速排序（数组排序）
    function quick_sort($array)
    {
        if (count($array) <= 1) return $array;
        $key = $array[0];
        $left_arr = array();
        $right_arr = array();
        for ($i = 1; $i < count($array); $i++) {
            if ($array[$i] <= $key)
                $left_arr[] = $array[$i];
            else
                $right_arr[] = $array[$i];
        }

        $left_arr = quick_sort($left_arr);
        $right_arr = quick_sort($right_arr);
        return array_merge($left_arr, array($key), $right_arr);
    }

//获得文件属性 $file是文件路径如$_SERVER['SCRIPT_FILENAME'],$flag文件的某个属性
    function getFileAttr($file, $flag)
    {
        if (!file_exists($file)) {
            return false;
        }
        switch ($flag) {
            case 'dir':
                if (is_file($file))
                    return dirname($file);
                return realpath($file);
                break;
            case 'name':
                if (is_file($file))
                    return basename($file);
                return '-';
                break;
            case 'size':
                if (is_file($file))
                    return filesize($file);
                return '-';
                break;
            case 'perms':
                return substr(sprintf('%o', fileperms($file)), -4);;
                break;
            case 'ower':
                return fileowner($file);
                break;
            case 'owername':
                $ownerInfo = posix_getpwuid(fileowner($file));
                return isset($ownerInfo['name']) ? $ownerInfo['name'] : false;
                break;
            case 'groupname':
                $ownerInfo = posix_getpwuid(filegroup($file));
                return isset($ownerInfo['name']) ? $ownerInfo['name'] : false;
                break;
            case 'ctime':
                return filectime($file);
                break;
            case 'mtime':
                return filemtime($file);
                break;
            case 'atime':
                return fileatime($file);
                break;
            case 'suffix':
                if (is_file($file))
                    return substr($file, strrpos($file, '.') + 1);
                return '-';
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * 整理json格式字符串数据
     * @param string $json json格式字符串数据
     * @param bool|false $assoc
     * @param int $depth
     * @param int $options
     * @return mixed
     */
    public function json_clean_decode($json, $assoc = false, $depth = 512, $options = 0)
    {
        $json = str_replace(array("\n", "\r"), "", $json);
        $json = preg_replace("#(/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/)|([\s\t](//).*)#", '', $json);
        $json = preg_replace('/([{,]+)(\s*)([^"]+?)\s*:/', '$1"$3":', $json);
        $json = preg_replace('/(,)\s*}$/', '}', $json);
        if (version_compare(phpversion(), '5.4.0', '>=')) {
            $json = json_decode($json, $assoc, $depth, $options);
        } elseif (version_compare(phpversion(), '5.3.0', '>=')) {
            $json = json_decode($json, $assoc, $depth);
        } else {
            $json = json_decode($json, $assoc);
        }
        return $json;
    }


    /**
     * 判断$strJson是否是一个有效的json格式字符串
     * @param $strJson
     * @return bool
     */
    public function isValidJson($strJson)
    {
        json_decode($strJson);
        return (json_last_error() === JSON_ERROR_NONE);
    }


    /**
     * 去掉字符串中的斜线(单斜线和双斜线)
     * @param string $string
     * @return string
     */
    public static function removeslashes($string = '')
    {
        $string = implode("", explode("\\", $string));
        return stripslashes(trim($string));
    }
//去除数组中的单斜线
    function stripslashes_deep($value)
    {
        $value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
        return $value;
    }
//入库添加斜线 防sql注入
    function add_slashes_recursive( $variable )
    {
        if ( is_string( $variable ) )
            return addslashes( $variable ) ;

        elseif ( is_array( $variable ) )
            foreach( $variable as $i => $value )
                $variable[ $i ] = add_slashes_recursive( $value ) ;

        return $variable ;
    }

//页面显示时去掉数据库中数据的斜线
    function strip_slashes_recursive( $variable )
    {
        if ( is_string( $variable ) )
            return stripslashes( $variable ) ;
        if ( is_array( $variable ) )
            foreach( $variable as $i => $value )
                $variable[ $i ] = strip_slashes_recursive( $value ) ;

        return $variable ;
    }

}
//PHP实现双端队列
class Deque
{
    public $queue = array();

    /**（尾部）入队  **/
    public function addLast($value)
    {
        return array_push($this->queue,$value);
    }
    /**（尾部）出队**/
    public function removeLast()
    {
        return array_pop($this->queue);
    }
    /**（头部）入队**/
    public function addFirst($value)
    {
        return array_unshift($this->queue,$value);
    }
    /**（头部）出队**/
    public function removeFirst()
    {
        return array_shift($this->queue);
    }
    /**清空队列**/
    public function makeEmpty()
    {
        unset($this->queue);
    }

    /**获取列头**/
    public function getFirst()
    {
        return reset($this->queue);
    }

    /** 获取列尾 **/
    public function getLast()
    {
        return end($this->queue);
    }

    /** 获取长度 **/
    public function getLength()
    {
        return count($this->queue);
    }

}