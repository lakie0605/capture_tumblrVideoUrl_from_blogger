<?php
//获取博主名
$nameFile = fopen('names.txt', 'r') or die("can't open nameFile");
$nameData = fread($nameFile, filesize('names.txt'));
$arrName = explode(',', $nameData);
fclose($nameFile);

foreach ($arrName as $blogger) {
    //分解博主信息，获取名称和爬取次数
    $arrData = explode(':', str_replace(PHP_EOL, '', $blogger));
    $name = $arrData[0];
    if ($arrData[1]) {
        $num = $arrData[1] > 0 ? $arrData[1] : 10;
    } else {
        $num = 10;
    }
    //设置video保存文件名
    $videoFlieName = $name . '_video.txt';
    //删除之前爬取的url
    unlink($videoFlieName);
    //循环爬取指定页数
    $urlData_700 = $urls_700 = null;
    for ($i = 1; $i < $num; $i++) {
        $page_url = "https://$name.tumblr.com/page/$i";
        //700开始
        $ch_700 = curl_init();
        curl_setopt($ch_700, CURLOPT_URL, $page_url);
        curl_setopt($ch_700, CURLOPT_RETURNTRANSFER, 1);
        $output_700 = curl_exec($ch_700);
        //匹配700_url
        $pattern_700 = "/https:\/\/w+.[a-zA-Z]+.com\/[a-zA-Z]+\/$name\/\d+\/700/";
        preg_match_all($pattern_700, $output_700, $urlData_700);
        //无内容跳出
        if ($i > 2 && count($urlData_700[0]) == 0) {
            break;
        }
        foreach ($urlData_700[0] as $value) {
            $urls_700[] = $value;
        }
        curl_close($ch_700);
    }
    //循环获取到的700url
    if (count($urls_700) > 0) {
        foreach ($urls_700 as $url_700) {
            //爬取video地址
            $ch_video = curl_init();
            curl_setopt($ch_video, CURLOPT_URL, $url_700);
            curl_setopt($ch_video, CURLOPT_RETURNTRANSFER, 1);
            $output_video = curl_exec($ch_video);
            //匹配video_url
            $pattern_video = "/https:\/\/$name.tumblr.com\/video_file\/t:[0-9a-zA-Z]+\/[0-9]+\/[a-zA-Z]+_[0-9a-zA-Z]+\/?4?8?0?/";
            preg_match_all($pattern_video, $output_video, $urlData_video);
            //文件存储
            $videoFile = fopen($videoFlieName, 'a+') or die("can't open $videoFlieName");
            foreach ($urlData_video[0] as $value) {
                $value = $value . "\r\n";
                fwrite($videoFile, $value);
            }
            fclose($videoFile);
            curl_close($ch_video);
        }
    }
}
echo 'succeed';
