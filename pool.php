<?php

if (isset($_GET['id'])) {
    $id = md5($_GET['id']);
    $data = file_get_contents('./logs/' . $id . '.txt');
    print nl2br($data);
}

function isValidMd5($md5 = '')
{
    return strlen($md5) == 32 && ctype_xdigit($md5);
}

?>
