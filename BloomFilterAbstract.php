<?php
/**
 * ----------------------------------------------------------
 * date: 2019/9/19 16:55
 * ----------------------------------------------------------
 * author: Raoxiaoya
 * ----------------------------------------------------------
 * describe: 布隆过滤器抽象类
 * ----------------------------------------------------------
 */

require 'BloomFilterHash.php';

/**
 * 使用redis实现的布隆过滤器
 */
class BloomFilterAbstract
{
    /**
     * 需要使用一个方法来定义bucket的名字
     */
    protected $bucket;
    protected $hashFunction;
    protected $redis;
    protected $hash;

    public function __construct($config)
    {
        if (!$this->bucket || !$this->hashFunction) {
            throw new Exception("需要定义bucket和hashFunction", 1);
        }
        $this->hash = new BloomFilterHash;
        $this->connect(...$config);
    }

    public function connect($host = null, $port = null, $auth = null)
    {
        if (!extension_loaded('redis')) {
            throw new \Exception('redis扩展不存在');
        }
        try {
            $redis  = new \Redis();
            $result = $redis->connect($host, $port);

            if ($result == false) {
                throw new \Exception('redis连接失败');
            } else {
                if (!$redis->auth($auth)) {
                    throw new \Exception('redis密码错误');
                } else {
                    $this->redis = $redis;
                }
            }
        } catch (\Exception $e) {
            throw new \Exception('redis服务异常');
        }
    }

    /**
     * 添加到集合中
     */
    public function add($string)
    {
        $pipe = $this->redis->multi();
        foreach ($this->hashFunction as $function) {
            $hash = $this->hash->$function($string);
            $pipe->setBit($this->bucket, $hash, 1);
        }
        return $pipe->exec();
    }

    /**
     * 查询是否存在, 存在的一定会存在, 不存在有一定几率会误判
     */
    public function exists($string)
    {
        $pipe = $this->redis->multi();
        $len  = strlen($string);
        foreach ($this->hashFunction as $function) {
            $hash = $this->hash->$function($string, $len);
            $pipe = $pipe->getBit($this->bucket, $hash);
        }
        $res = $pipe->exec();
        foreach ($res as $bit) {
            if ($bit == 0) {
                return false;
            }
        }
        return true;
    }
}