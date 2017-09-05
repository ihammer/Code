<?php

/**
 * Created by PhpStorm.
 * User: 武德安
 * Date: 2017/9/5 0005
 * Time: 15:14
 * Name: 数据库处理类
 */
class DataBase
{
/**
 * 数据模型类
 * @ Xiaofeng modified 20170827_v1.0
 */
//$m = new Model();
//var_dump($m -> find("stu","id"));
//var_dump($m->ins("stu", "name", "zhu"));
//var_dump($m -> upd("stu","name","dujianing","id","1"));
//var_dump($m -> del("stu","name","li"));


    protected $link;

    protected $dbHost = ''; //主机名
    protected $dbUser = '';  //数据库用户名
    protected $dbPwd = ''; //数据库密码
    protected $dbName = '';  //数据库名
    public $tableName = '';  //表名
    protected $fields = array();  //表的字段名
    protected $priKey; //表的主键名(唯一字段)
    protected $where = '';   //where条件组成的字段where(' id>1 and …')
    protected $group = '';   //group by 条件组成的字段
    protected $having = '';   //having 条件组成的字段
    protected $order = '';   //order 条件组成的字段

//构造函数，初始化数据库连接

    public function __construct($tableName,$table_pre=null) {

        $this->dbHost = $GLOBALS["db_settings"]["con_db_host"];
        $this->dbUser = $GLOBALS["db_settings"]["con_db_id"];
        $this->dbPwd = $GLOBALS["db_settings"]["con_db_pass"];
        $this->dbName = $GLOBALS["db_settings"]["con_db_name"];

        if(null!=$table_pre && strlen($table_pre)>0)
        {
            $tempTableName =  $table_pre. $tableName;

        }
        else
        {
            $tempTableName = $GLOBALS["db_settings"]["tablepre"] . $tableName;

        }
        $this->tableName = $tempTableName;



        $this->link = mysqli_connect($this->dbHost, $this->dbUser, $this->dbPwd, $this->dbName) or die("数据库连接失败");
        mysqli_set_charset($this->link, "utf8");

        if(null!=$tableName)
        {
            $this->getFields();  //获取表的所有字段
        }
    }




    /*
     * 查询指定字段
     * $vaue返回指定值
     */

    public function getField($sql) {

        $this->log($sql);
        $res = mysqli_query($this->link, $sql);
        $arr = mysqli_fetch_array($res,MYSQLI_ASSOC);
        return $arr;
    }



    public function getFieldAll($sql)
    {
        $this->log($sql);

        $re=array();
        $res = mysqli_query($this->link, $sql);
        if(null!=$res){
            while($k=mysqli_fetch_array($res,MYSQLI_ASSOC))
            {
                $re[]=$k;
            }
        }
        return $re;
    }










    //查找 1.表名 2.条件 3.值 如果不添加条件或者值，就全部查询
    public function find($table = "", $key = "", $value = "")
    {

        $sql_fields=array ();

        foreach ($this->fields as $v)
        {
            $temp_v=$this->str_prefix($v,1,'`');
            $sql_fields[]=$this->str_suffix($temp_v,1,'`');
        }



        $fields = implode(',', $sql_fields);  //组装$this->fields成这样id,name,sex,age,email

        //$fields = implode(',', $this->fields);
        if (!$key || !$value) {
            $sql = "select {$fields} from {$table}";
        } else {
            $sql = "select {$fields} from {$table} where {$key} = '{$value}'";
        }
        $res = mysqli_query($this->link, $sql);
        $arr = mysqli_fetch_all($res, MYSQLI_ASSOC);
        mysqli_free_result($res);
        return $arr;
    }



    /**
     * 通过主键查找一条记录
     * @param $pk int 主键 如$id
     * @return 成功返回查询记录 是一维数组$record 失败返回false
     */
    public function findByKey($pk)
    {

        $sql_fields=array ();

        foreach ($this->fields as $v)
        {
            $temp_v=$this->str_prefix($v,1,'`');
            $sql_fields[]=$this->str_suffix($temp_v,1,'`');
        }



        $fields = implode(',', $sql_fields);  //组装$this->fields成这样id,name,sex,age,email

        //$fields = implode(',', $this->fields);
        $sql = "select {$fields} from {$this->tableName} where {$this->priKey}= " . intval($pk);

        $result = mysqli_query($this->link,$sql);
        if ($result && mysqli_affected_rows($this->link) == 1)
        {
            return $record = mysqli_fetch_assoc($result);
        }
        else
        {
            return null;
        }
    }



    //增加 1.表名 2.需要插入的字段 3.值1
    //public function ins($table = "", $zd = "name,score", $value = "")
    //{
    //	$arr = explode(",", $value);
    //	$str = "";
    //	foreach ($arr as $k => $v)
    //	{
    //		$str .= "'" . $v . "'" . ",";
    //	}
    //	$str = rtrim($str, ",");
    //	$sql = "insert into {$table}({$zd})values({$str})";
    //
    //	$res = mysqli_query($this->link, $sql);
    //	return mysqli_insert_id($this->link);
    //}

    /**
     * 增加一条记录
     * @param $data array 提交的form表单中的数组
     * @return 成功返回id 失败返回false
     */
    public function insert($data = array()) {
        $keys = "";
        $values = "";


        foreach ($data as $k => $v) {
            if (in_array($k, $this->fields)) {
                $keys .= $k . ',';
                if (get_magic_quotes_gpc()) {
                    $v = stripcslashes($v);
                }
                $v = mysqli_real_escape_string($this->link, $v); //防SQL注入，做一个安全转义
                $values .= "'{$v}'" . ",";
            }
        }
        $keys = rtrim($keys, ',');
        $values = rtrim($values, ',');



        $sql = "insert into {$this->tableName} ($keys) values($values)";

        $this->log($sql);

        $bool = mysqli_query($this->link, $sql);
        if ($bool) {
            return mysqli_insert_id($this->link);
        } else {
            return 0;
        }
    }





    private function log($content)
    {
        $path = $_SERVER['DOCUMENT_ROOT']."/log/";
        if (!is_dir($path)){
            mkdir($path,0777);  // 创建文件夹test,并给777的权限（所有权限）
        }
        $content .="\r\n";  // 写入的内容
        $file = $path."mysqli_log.txt";    // 写入的文件
        file_put_contents($file,$content,FILE_APPEND);  // 最简单的快速的以追加的方式写入写入
    }



    /**
     * 获取当前操作表的所有字段
     */
    public function getFields() {

        $sql = "desc {$this->tableName}";
        $result = mysqli_query($this->link, $sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $fields[] = $row['Field']; //所有字段
            if ($row['Key'] == 'PRI') {
                $this->priKey = $row['Field']; //确定表的主键
            }
        }

        $this->fields = $fields;
    }

    /**
     * 查询记录列表
     * @param
     * @return 成功返回查询记录 是二维数组$records 失败返回false
     */
    public function select() {


        $sql_fields=array ();

        foreach ($this->fields as $v)
        {
            $temp_v=$this->str_prefix($v,1,'`');
            $sql_fields[]=$this->str_suffix($temp_v,1,'`');
        }



        $fields = implode(',', $sql_fields);  //组装$this->fields成这样id,name,sex,age,email


        //$fields = implode(',', $this->fields);  //组装$this->fields成这样id,name,sex,age,email




        $sql = "select {$fields} from {$this->tableName} {$this->where} {$this->group} {$this->having} {$this->order} ";


        $this->log($sql);

        $result = mysqli_query($this->link, $sql);
        if ($result && mysqli_affected_rows($this->link) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $records[] = $row;
            }
            $this->total = mysqli_affected_rows($this->link);
            return $records;
        } else {
            return false;
        }
    }

    /**
     * 前加
     * @param $str
     * @param int $n
     * @param string $char
     * @return string
     */
    private function str_prefix($str, $n=1, $char=" "){
        for ($x=0;$x<$n;$x++){$str = $char.$str;}
        return $str;
    }

    /**
     * 后加
     * @param $str
     * @param int $n
     * @param string $char
     * @return string
     */
    private function str_suffix($str, $n=1, $char=" "){
        for ($x=0;$x<$n;$x++){$str = $str.$char;}
        return $str;
    }





    public function selectOne() {

        $sql_fields=array ();

        foreach ($this->fields as $v)
        {
            $temp_v=$this->str_prefix($v,1,'`');
            $sql_fields[]=$this->str_suffix($temp_v,1,'`');
        }



        $fields = implode(',', $sql_fields);  //组装$this->fields成这样id,name,sex,age,email
        $sql = "select {$fields} from {$this->tableName} {$this->where} {$this->group} {$this->having} {$this->order} ";


        $this->log($sql);
        $result = mysqli_query($this->link, $sql);
        if ($result && mysqli_affected_rows($this->link) > 0) {
            $row = mysqli_fetch_assoc($result);
            $records = $row;

            //$this->total = mysql_affected_rows($this->link);
            return $records;
        } else {
            return false;
        }
    }



    /**
     * 删除一条记录
     * @param $id int  记录的id
     * @return 成功返回id 失败返回false
     */
    public function delete($id)
    {
        if($id>0)
        {
            $sql = "delete from {$this->tableName} where {$this->priKey}=" . intval($id);
            $this->log($sql);
            $bool = mysqli_query($this->link, $sql);
            if ($bool)
            {
                return $id;
            }
            else
            {
                return false;
            }
        }
    }


//析构函数
    public function __destruct() {
        if (isset($res)) {
            mysqli_free_result($res);
        }
        mysqli_close($this->link);
    }


    /**
     * 更新一条记录
     * @param $data array 提交的form表单中的数组
     * @param $id int  修改记录的id
     * @return 成功返回id 失败返回false
     */
    public function update($data = array(), $id) {
        $set_sql = "";
//循环过滤属性把数组组装成 name='张三',age='183',email='aaa@bb.com'
        foreach ($data as $k => $v) {
            if (in_array($k, $this->fields)) {
                if (get_magic_quotes_gpc()) {
                    $v = stripcslashes($v);
                }
                $v = mysqli_real_escape_string($this->link, $v); //防SQL注入，做一个安全转义
                $set_sql .= "{$k}='$v',";
            }
        }
        $set_sql = rtrim($set_sql, ',');

        $sql = "update {$this->tableName} set {$set_sql} where {$this->priKey}=" . intval($id);

        $this->log($sql);

        $bool = mysqli_query($this->link, $sql);

        if ($bool && mysqli_affected_rows($this->link) == 1) {
            return $id;
        } else {
            return false;
        }
    }



    /**
     * 更新一条记录
     * @param $data array 提交的form表单中的数组
     * @param $id int  修改记录的id
     * @return 成功返回id 失败返回false
     */
    public function updateBy($data = array(), $condition,$id) {
        $set_sql = "";

        foreach ($data as $k => $v) {
            if (in_array($k, $this->fields)) {
                if (get_magic_quotes_gpc()) {
                    $v = stripcslashes($v);
                }
                $v = mysqli_real_escape_string($this->link, $v); //防SQL注入，做一个安全转义
                $set_sql .= "{$k}='$v',";
            }
        }
        $set_sql = rtrim($set_sql, ',');

        $sql = "update {$this->tableName} set {$set_sql} where {$condition}='{$id}'" ;

        $this->log($sql);

        $bool = mysqli_query($this->link, $sql);

        if ($bool && mysqli_affected_rows($this->link) == 1) {
            return $id;
        } else {
            return false;
        }
    }





    /**
     * 统计总行数
     * @return 成功返回id 失败返回false
     */
    public function count()
    {
        return $this->total;
    }

    /**
     * where() 函数
     * @param $where 条件
     * @return 返回该对象
     */
    public function where($where = '')
    {
        if (is_string($where) && !empty($where))
        {
            $this->where = ' where ' . $where;
        }
        elseif(is_array($where) && !empty($where) )
        {
            $count=count($where);

            $str='';
            $i=1;
            foreach($where as $key=>$v)
            {

                $str .= "`".$key."` = '".$v."'";
                if($i==$count)
                {
                    continue;
                }
                if($count >1 )
                {
                    $str .=' and ';
                }
                $i++;
            }
            $this->where = ' where '.$str;
        }
        return $this;
    }

    /**
     * group() 函数
     * @param $group 条件
     * @return 返回该对象
     */
    public function group($group = '')
    {
        if (!empty($group))
        {
            $this->group = ' group by ' . $group;
        }
        return $this;
    }

    /**
     * having() 函数
     * @param $having 条件
     * @return 返回该对象
     */
    public function having($having = '')
    {
        if (!empty($having))
        {
            $this->having = ' having ' . $having;
        }
        return $this;
    }

    /**
     * order() 函数
     * @param $order 条件
     * @return 返回该对象
     */
    public function order($order = '')
    {
        if (!empty($order))
        {
            $this->order = ' order by ' . $order;
        }
        return $this;
    }

}
