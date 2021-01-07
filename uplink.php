<?php
require_once 'common.php';

$user = VerifyAuthorization();

$link = $_POST['link'];
$expiry = $_POST['expiry'];

$contentType = "text/plain";
$extension = 'txt';

$result = NewItem($user["Username"], $extension, $contentType, $expiry);
$file = fopen("files/" . $result["Id"], "w") or die("Unable to open file!");
fwrite($file, $link);

echo $user["BaseUrl"] . $result["Id"] . '/' . $result["Password"];