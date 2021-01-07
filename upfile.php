<?php
require_once 'MimeType.php';
require_once 'common.php';

$user = VerifyAuthorization();

$file = $_FILES['file'];
$expiry = $_POST['expiry'];

if ($file['error']) die('Upload Error: ' . $file['error']);

$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$contentType = mime_type($extension);

if ($extension == "txt") {
    $text = file_get_contents($_FILES['file']['tmp_name']);
    // If it's a link
    if (filter_var($text, FILTER_VALIDATE_URL)) {
        $contentType = "text/x.url";

        // If it's our link, renew
        if (preg_match('(^' . preg_quote($user["BaseUrl"]) .'([a-zA-Z]+)(\/([^\?\.]*?))?(\.[a-zA-Z0-9.-]*)?$)', $text, $matches)) {
            $id = strtolower($matches[1]);
            $password = isset($matches[3]) ?  strtolower($matches[3]) : '';
            $linkExtension = isset($matches[4]) ? $matches[4] : '';

            $result = RenewItem($user["Username"], $id, $password, $expiry);
        }
    }
}

// If it wasn't our link
if (empty($result)){
    $result = NewItem($user["Username"], $extension, $contentType, $expiry);
    rename($file['tmp_name'], "files/" . $result["Id"]);
    chmod("files/" . $result["Id"], 0644);
}

echo $user["BaseUrl"] . $result["Id"];
if ($result["Password"]) echo '/' . $result["Password"];
if (!empty($linkExtension))  echo $linkExtension;