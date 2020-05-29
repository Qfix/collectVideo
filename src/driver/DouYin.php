<?php
/**
 * Created by PhpStorm.
 * User: kali
 * Date: 2019/12/5
 * Time: 17:09
 */

namespace collectVideo\driver;


use collectVideo\VideoUrlParser;
use Exception;

class DouYin extends VideoUrlParser
{
    protected $domain = [
        'v.douyin.com',
    ];
    protected $web_name = '抖音';

    protected function exec()
    {
        //$source_url = 'https://v.douyin.com/nxrF2Y';
        $source_url = $this->getUrl();
        try {
            $return_data = $this->getData($source_url);
            if (empty($return_data['source_url'])) {
                throw new Exception('获取无水印抖音视频失败');
            }
            if ($this->is_request_source) {
                $return_data ['media_source'] = $this->curl($return_data['source_url']);
            }
            $this->setResult($return_data);
        } catch (\Exception $e) {
            $this->setResult($e->getMessage());
        }
    }


    public function curl($url, $timeout = 120)
    {
        $header = [
            'user-agent: Mozilla/5.0 (Linux; U; Android 8.1.0; zh-cn; BLA-AL00 Build/HUAWEIBLA-AL00) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/57.0.2987.132 MQQBrowser/8.9 Mobile Safari/537.36',
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 5);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    protected function getData($url)
    {
        $content = $this->curl($url);
        preg_match_all("/itemId: \"([0-9]+)\"|dytk: \"(.*)\"/", $content, $res, PREG_SET_ORDER);

        if (!$res[0][1] || !$res[1][2]) {
            die("数据异常");
        }
        $itemId = $res[0][1];
        $dytk = $res[1][2];
        $api = "https://www.iesdouyin.com/web/api/v2/aweme/iteminfo/?item_ids={$itemId}&dytk={$dytk}";
        $json = $this->curl($api);
        $arr = json_decode($json);
        $videoinfo = $arr->item_list[0]->video;
        $video_url = str_replace('playwm', 'play', $videoinfo->play_addr->url_list[0]);
        return [
            'title' => $arr->item_list[0]->desc,
            'cover_pic' => $videoinfo->origin_cover->url_list[0],
            'source_url' => $video_url,
        ];

    }
}