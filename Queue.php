<?php

/**
 * Created by PhpStorm.
 * User: 武德安
 * Date: 2017/9/5 0005
 * Time: 15:26
 * Name: 队列类
 */
class Queue
{
    private    $_queue = [];
    protected  $cache  = null;
    protected  $queuecachename;

    /**
     * 构造方法
     * Queue constructor.
     * @param $queuename
     */
    public function __construct($queuename ) {
        $this->cache = & Cache::instance();
        $this->queuecachename = 'queue_'.$queuename;
        $result = $this->cache->get($this->queuecachename);
        if(is_array($result)) {
            $this->_queue = $result;
        }
    }

    /**
     * 将一个单元放入队列末尾
     * @param $value
     * @return $this
     */
    public function enQueue($value) {
        $this->_queue[]=$value;
        $this->cache->set($this->queuecachename,$this->_queue);
        return $this;
    }

    /**
     * 将队列开头的一个或多个单元移除
     * @param int $num
     * @return array
     */
    public function sliceQueue($num = 1) {
        if(count($this->_queue)<$num) {
            $num = count($this->_queue);
        }
        $output = array_slice($this->_queue,0,$num);
        $this->cache->set($this->queuecachename,$this->_queue);
        return $output;
    }

    /**
     * 将队列开头的单元移出队列
     * @return mixed
     */
    public function deQueue() {
        $entry = array_shift($this->_queue);
        $this->cache->set($this->queuecachename,$this->_queue);
        return $entry;
    }

    /**
     * 获取队列的长度
     * @return int
     */
    public function size() {
        return count($this->_queue);
    }

    /**
     * 获取队列中的第一个
     * @return mixed
     */
    public function peek() {
        return $this->_queue[0];
    }

    /**
     * 返回队列中的一个或者多个单元
     * @param $num
     * @return array
     */
    public function peeks($num){
        if(count($this->_queue)<$num) {
            $num = count($this->_queue);
        }
        return array_slice($this->_queue,0,$num);
    }

    /**
     *  销毁队列
     */
    public function destroy() {
        $this->cache->remove($this->queuecachename);
    }

}