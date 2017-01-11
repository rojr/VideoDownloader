<?php
include_once('./simple_html_dom.php');

$res = new stdClass();
if (isset($_REQUEST['url']) && isset($_REQUEST['i'])) {
    $url = str_replace('\/', '', $_REQUEST['url']);
    if (preg_match('/(http(s|):\/\/)(www\.|)((((youtube|vimeo)\.com)|(youtu\.be)|(soundcloud.com))\/(([0-9]+)|(watch.+)|([A-z]|[0-9]|\/|\-)+))/', $url)) {
        $id = md5($_REQUEST['i']);
        $path = './dl/' . $id;

        if (!is_dir($path)) {
            mkdir($path, 0777);
        }

        if (!is_dir($path . './logs/')) {
            mkdir($path . './logs/', 0777);
        }

        chmod($path, 0777);

        exec('youtube-dl -i -x --audio-format mp3 -o \'./dl/' . escapeshellarg($id) . '/%(title)s.%(ext)s\' ' . escapeshellarg($url) . ' > logs/' . escapeshellarg($id) . '.txt');
        $res->url = '/link.php?id=' . $id;
    } else if (preg_match('/^((http(s|):\/\/)(www\.|)techno\-livesets\.com).+/', $url)) {

        $html = str_get_html(getData($url, $url));
        $id = $html->find('.play-me', 0);

        $target = 'https://www.techno-livesets.com/wp-admin/admin-ajax.php?action=get_media&id=' . $id->{"data-id"};

        $technoLivesetsData = json_decode(getData($target, $url));

        if ($technoLivesetsData) {
            $res->url = $technoLivesetsData[0]->mp3;
        }
    } else {
        $res->error = 'This is not a valid url... try again.';
    }
} else {
    $res->error = 'No URL or ID, you trying to do something malicious?';
}
print json_encode($res);


function getData($url, $referer)
{
    exec("
        curl '{$url}' 
        -H 'dnt: 1' -H 'accept-encoding: gzip, deflate, sdch, br' 
        -H 'x-requested-with: XMLHttpRequest' -H 'accept-language: en-GB,en-US;q=0.8,en;q=0.6' 
        -H 'user-agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.100 Safari/537.36' 
        -H 'accept: application/json, text/javascript, */*; q=0.01' 
        -H 'referer: {$referer}' 
        -H 'authority: www.techno-livesets.com' -H 'cookie: bp-activity-oldestpage=1' --compressed", $result);
    return implode('', $result);
}

?>
