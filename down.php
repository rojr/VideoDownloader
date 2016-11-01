<?php
$res = new stdClass();
if (isset($_POST['url']) && isset($_POST['i'])) {
    $url = str_replace('\/', '', $_POST['url']);
    if (preg_match('/(http(s|):\/\/)(www\.|)((((youtube|vimeo)\.com)|(youtu\.be)|(soundcloud.com))\/(([0-9]+)|(watch.+)|([A-z]|[0-9]|\/|\-)+))/', $url)) {
        $id = md5($_POST['i']);
        $path = './dl/' . $id;

        if (!is_dir($path)) {
            mkdir($path, 0777);
        }

        if (!is_dir($path . './logs/')) {
            mkdir($path . './logs/', 0777);
        }
        
        chmod($path, 0777);

        exec('youtube-dl -i -x --audio-format "m4a" -o \'./dl/' . escapeshellarg($id) . '/%(title)s.%(ext)s\' ' . escapeshellarg($url) . ' > logs/' . escapeshellarg($id) . '.txt');
        $res->url = '/link.php?id=' . $id;

    } else {
        $res->error = 'This is not a valid url... try again.';
    }
} else {
    $res->error = 'No URL or ID, you trying to do something malicious?';
}
print json_encode($res);
?>
