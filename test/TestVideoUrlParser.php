<?php
/**
 * Created by PhpStorm.
 * User: 秦豪
 * Date: 2020/5/29
 * Time: 14:17
 */

use collectVideo\VideoUrlParser;

require_once '../vendor/autoload.php';

$url='https://v.douyin.com/nxrF2Y';
$data=VideoUrlParser::getUrlData($url);
 echo 'haode ';
var_dump($data);
echo 'niub ';