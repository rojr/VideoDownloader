<?php
if (isset($_GET['id']) && isValidMd5($_GET['id'])) {
    $id = $_GET['id'];

    $amount = 0;

    $name = '';

    if (file_exists('./dl/' . $id . '.zip')) {
        serveFile('./dl/' . $id . '.zip');
    }

    foreach (scandir('./dl/' . $id) as $dir) {
        if (!is_dir('./dl/' . $id . '/' . $dir)) {
            $amount++;
            $name = $dir;
        }
    }

    if ($amount > 0) {
        if ($amount == 1) {
            serveFile('./dl/' . $id . '/' . $name);
        } else {
            exec('cd dl; zip -9 -r ' . $id . '.zip ' . $id);
            serveFile('./dl/' . $id . '.zip');
        }
    } else {
        print 'Files seem to be deleted... sorry!';
    }

}

function isValidMd5($md5 = '')
{
    return strlen($md5) == 32 && ctype_xdigit($md5);
}

function serveFile($file = '')
{
    if (file_exists($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }
}

?>
