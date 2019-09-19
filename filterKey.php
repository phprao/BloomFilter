<?php
/**
 * ----------------------------------------------------------
 * date: 2019/9/19 17:05
 * ----------------------------------------------------------
 * author: Raoxiaoya
 * ----------------------------------------------------------
 * describe:
 * ----------------------------------------------------------
 */
require 'BloomFilterAbstract.php';

class filterKey extends BloomFilterAbstract
{
    /**
     * 指定一个过滤器名称
     * @var string
     */
    protected $bucket = 'filterKey_01';

    protected $hashFunction = array('BKDRHash', 'SDBMHash', 'JSHash');

    public function __construct($config)
    {
        parent::__construct($config);
    }
}

$f  = new filterKey(['127.0.0.1', 6379, '123456']);
$k1 = 'aaaaaaa';
$k2 = 'bbbbbbb';

$f->add($k1);

var_dump($f->exists($k1));// true

var_dump($f->exists($k2));// false
