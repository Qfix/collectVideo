<?php
namespace collectVideo;
use storage\Storage;
use VideoUrlParser\Exception;

/**
 * Created by PhpStorm.
 * User: 秦豪
 * Date: 2020/5/29
 * Time: 14:15
 */

abstract class VideoUrlParser
{
    //匹配成功
    const ERR_CODE = 0;
    //不支持的来源
    const ERR_NOSOURCE = 203;
    //未获取到数据
    const ERR_NODATA = 204;

    private static $drivers = [];
    private static $web_names = [];

    /**
     * 返回结果集
     * @var array
     */
    protected $_result = [
        'title' => '', //视频标题
        'info' => '', //视频介绍
        'cover_pic' => '', //封面图片
        'source_url' => '', //视频真实url
    ];
    /**
     * 页面链接地址
     * @var string
     */
    private $url;

    protected function setUrl($url)
    {
        $this->url = $url;
    }

    protected function getUrl()
    {
        return $this->url;
    }

    protected function setResult($result)
    {
        if (!empty($result)) {
            foreach ($result as $k => $val) {
                $this->_result[$k] = $val;
            }
        }
    }

    protected function getResult()
    {
        return $this->_result;
    }

    private static function getInstance($url)
    {
        self::$drivers = self::$web_names = [];
        $drivers = include __DIR__ . '/drivers.php';
        if (is_array($drivers)) {
            foreach ($drivers as $driver) {
                if (class_exists($driver) && is_subclass_of($driver, self::class)) {
                    /**
                     * @var VideoUrlParser $class
                     */
                    $class = new $driver();
                    self::$web_names[] = $class->web_name;
                    self::$drivers[] = $class;
                }
            }
        }
        /**
         * @var VideoUrlParser $class
         */
        foreach (self::$drivers as $class) {
            $domain_regex = '/((' . str_replace('.', '\.', implode(')|(', $class->domain)) . '))/';
            if (preg_match($domain_regex, parse_url($url, PHP_URL_HOST))) {
                $class->setUrl($url);
                return $class;
            }
        }
        throw new Exception(sprintf('目前支持的视频链接（%s），其他来源暂不支持', implode('、', self::$web_names)), self::ERR_NOSOURCE);

    }

    protected abstract function exec();

    public static function getUrlData($url)
    {
        $data = ['result' => null];
        $begin_time = microtime(true);
        try {
            $driver = self::getInstance(trim($url));
            $driver->exec();
            $result = $driver->getResult();
            $data['result'] = $result;
            $data['result']['url'] = $driver->url;
            $data['code'] = self::ERR_CODE; //成功
            $data['web_names'] = self::$web_names;
            $data['errmsg'] = 'ok';
            unset($driver);
        } catch (Exception $e) {
            $data['code'] = $e->getCode();
            $data['web_names'] = self::$web_names;
            $data['errmsg'] = $e->getMessage();
        }
        $data['execTime'] = round(microtime(true) - $begin_time, 3);
        return $data;
    }
}