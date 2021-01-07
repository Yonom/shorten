<?php
require_once 'common.php';

// Don't cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$id = strtolower($_GET['id']);
$pass = strtolower($_GET['pass']);

$query = ['Id' => $id];
$item = $database->get('links', ['Password', 'Extension', 'ContentType', 'Expires', 'NextExpiry', 'Owner'], $query);
if (!$item || $item['Password'] != $pass || UpdateExpiry($id, $item)) {
    die("EXPIRED_LINK");
}

if (!IsValidBaseUrl($item)) {
    die("EXPIRED_LINK");
}

$content = file_get_contents(__DIR__ . '/files/' . $id);
if (!$content){
    die("EXPIRED_LINK");
}

$contentType = $item['ContentType'];
if ($contentType == 'text/x.url') {
    header('Location: ' . $content, true, 302);
}
else {
    if ($contentType != 'application/x.unknown'){
        header("Content-type: $contentType");
    } else {
        header("Content-type: ");
    }

    header('Content-disposition: inline; filename=' . $pass . '.' . $item['Extension']);
    echo $content;
}