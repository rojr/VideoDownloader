<?php
include_once('./simple_html_dom.php');
ini_set('max_execution_time', 0);
ini_set('memory_limit', '2058M');

$res = new stdClass();
if (isset($_REQUEST['url']) && isset($_REQUEST['i'])) {
    $url = str_replace('\/', '', $_REQUEST['url']);
    if (preg_match('/(http(s|):\/\/)(www\.|)((((youtube|vimeo)\.com)|(youtu\.be)|(soundcloud.com))\/(([0-9]+)|(watch.+)|([A-z]|[0-9]|\/|\-)+))/', $url)) {
        $id = md5($_REQUEST['i']);
        $path = './dl/' . $id;

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        if (!is_dir('logs/')) {
            mkdir('logs/', 0777, true);
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
    } else if (preg_match('/(artstation\/).+/', $url)) {
        $user = str_replace('artstation/', '', $url);
        $aj = json_decode(getData("https://www.artstation.com/users/{$user}/projects.json", $url));

        $id = md5($_REQUEST['i']);
        $path = './dl/' . $id;

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        if (!is_dir('logs/')) {
            mkdir('logs/', 0777, true);
        }
        chmod($path, 0777);

        $file = fopen('logs/' . $id . '.txt', 'x');
        foreach ($aj->data as $datum) {
            fwrite($file, '<b>New Project: ' . $datum->title . '</b>' . "\n");
            fwrite($file, 'Pulling item data' . "\n");
            $projectData = json_decode(getData("https://www.artstation.com/projects/{$datum->slug}.json", 'www.google.com'));
            foreach ($projectData->assets as $asset) {
                if (isset($asset->image_url) && $asset->image_url) {
                    try {
                        fwrite($file, 'Downloading image...' . "\n");
                        $image = file_get_contents($asset->image_url);

                        fwrite($file, 'Downloaded' . "\n");
                        $title = str_replace(['{', '}', '/', '"', ' '], '_', $datum->title);
                        $imagePath = "dl/{$id}/{$title}_{$asset->id}";
                        file_put_contents($imagePath, $image);
                        fwrite($file, 'Saving to disk.' . "\n");

                        if (file_exists($imagePath)) {
                            $exifType = exif_imagetype($imagePath);
                            $extention = '';
                            if ($exifType == IMAGETYPE_JPEG) {
                                $extention = '.jpg';
                            } else if ($exifType == IMAGETYPE_PNG) {
                                $extention = '.png';
                            } else if ($exifType == IMAGETYPE_BMP) {
                                $extention = '.bmp';
                            } else if ($exifType == IMAGETYPE_GIF) {
                                $extention = '.gif';
                            }

                            if ($extention) {
                                rename($imagePath, $imagePath . $extention);
                                fwrite($file, 'Done...' . "\n");
                            }
                        }
                    } catch (Exception $exception) {
                    }
                }
            }
        }

        $res->url = '/link.php?id=' . $id;
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
