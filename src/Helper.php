<?php
/**
 * Created by PhpStorm.
 * User: kali
 * Date: 2019/12/5
 * Time: 17:27
 */

namespace collectVideo;



class Helper
{
    /**
     * 获取重定向url最终的真实地址
     * @param $url
     * @return mixed
     */
    public static function getRealUrl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
            'accept-language: zh-CN,zh;q=0.9,en;q=0.8',
            'cache-control: no-cache',
            'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36',
            'cookie: _ga=GA1.2.654087223.' . time()
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        // 不需要页面内容
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        // 不直接输出
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // 返回最后的Location
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_exec($ch);
        $info = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        return $info;
    }

    public static function parseQuery($url)
    {
        $params = explode('?', $url);
        $url = $params[0];
        if (!isset($params[1])) {
            $query = [];
        } else {
            $_query = explode('&', $params[1]);
            $query = [];
            foreach ($_query as $item) {
                $p = explode('=', $item);
                $query[$p[0]] = $p[1];
            }
        }
        array_unshift($query, $url);
        return $query;
    }

    public static function curl($url, &$content_type = '', $timeout = 120, $referer = false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
            'accept-language: zh-CN,zh;q=0.9,en;q=0.8',
            'cache-control: no-cache',
            'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36',
            'cookie: _ga=GA1.2.654087223.' . time(),
            'Connection: keep-alive',
            'Pragma: no-cache',
            'Upgrade-Insecure-Requests: 1'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 5);

        if ($referer) {
            curl_setopt($ch, CURLOPT_REFERER, $referer);
        }
        $output = curl_exec($ch);

        $cur_content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        if ($content_type == 'image') {
            if (strpos($cur_content_type, $content_type) === false) {
                $output = false;
            }
        }
        $content_type = $cur_content_type;
        curl_close($ch);
        return $output;
    }
}