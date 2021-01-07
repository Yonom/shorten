<?php
require_once "medoo.php";
require_once "random.php";

// CONFIG_START
$database = new medoo([
    'database_type' => 'mysql',
    'server' => 'localhost',
    'charset' => 'utf8',
    'database_name' => 'DATABASE_NAME',
    'username' => 'DATABASE_USER',
    'password' => 'DATABASE_PASSWORD'
]);

$users = array(
    'USER_1_NAME' => array(
        "Key" => 'USER_1_PASSWORD',
        "BaseUrl" => 'https://YOUR_DOMAIN/'
    ),
    'USER_2_NAME' => array(
        "Key" => 'USER_2_PASSWORD',
        "BaseUrl" => 'https://YOUR_DOMAIN/'
    )
);
// CONFIG_END

function IsValidBaseUrl($item)
{
    global $users;
    return 'https://' . $_SERVER['HTTP_HOST'] . '/' == $users[$item["Owner"]]["BaseUrl"];
}


function VerifyAuthorization() {
    global $users;

    if($_SERVER['REQUEST_METHOD'] != 'POST' ||
        !isset($_POST['name']) ||
        !isset($_POST['key']))
        die("INVALID_REQUEST");

    // Make sure the user is authorized
    $username = $_POST['name'];
    $userkey = $_POST['key'];
    if ($userkey != $users[$username]["Key"]) {
        die("AUTHENTICATION_FAILED");
    } else {
        return array(
            "Username" => $username,
            "BaseUrl" => $users[$username]["BaseUrl"]);
    }
}

function RenewItem($owner, $id, $newPassword, $expiry)
{
    global $database;

    $query =  [
        "AND" => [
            'Id' => $id,
            'Owner' => $owner
        ]
    ];

    $data = $database->update('links', [
        'Password' => $newPassword,
        'Expires' => date('Y-m-d H:i:s', strtotime("+7 days")),
        'NextExpiry' => $expiry
    ], $query);

    if ($data == 0) {
        die("ACCESS_DENIED: That link was not created by you.");
    }

    return array(
        "Id" => $id,
        "Password" => $newPassword
    );
}

function NewItem($owner, $extension, $contentType, $expiry) {
    global $database;

    // Generate a unique ID
    do {
        $id = strtolower(GenerateRandomWord());
        $query = ['Id' => $id];
        $res = $database->get('links', ['Expires'], $query);
        if (!$res) break;
        if (HasExpired($res)) {
            $database->delete('links', $query);
            break;
        }
    } while (true);

    $password = strtolower(GenerateRandomWord());

    $database->insert('links', [
        'Id' => $id,
        'Owner' => $owner,
        'Password' => $password,
        'Extension' => $extension,
        'ContentType' => $contentType,
        'Expires' => date('Y-m-d H:i:s', strtotime("+7 days")),
        'NextExpiry' => $expiry
    ]);

    return array(
        "Id" => $id,
        "Password" => $password
    );
}

function UpdateExpiry($id, $item)
{
    global $database;
    if (HasExpired($item)) return true;

    $newExpiryTime = strtotime("+" . $item['NextExpiry']);
    $newExpiry = date('Y-m-d H:i:s', strtotime("+" . $item['NextExpiry']));
    $rows = $database->update("links", [
        'Expires' => $newExpiry
    ], [
        "AND" => [
            'Id' => $id,
            'Expires' => $item['Expires']
        ]
    ]);
    return !$rows && $newExpiryTime <= time();
}

function HasExpired($item)
{
    return strtotime($item["Expires"]) <= time();
}

function GenerateRandomWord()
{
    $random = new \PHP\Random(false);
    $words = GetWords('wordlist');
    return $words[$random->int(0, count($words) - 1)];
}

function GetWords($name)
{
    return explode(',', file_get_contents($name . '.txt'));
}