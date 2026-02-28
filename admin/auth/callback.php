<?php

header("Content-Type: text/plain");

if (!isset($_GET['code'])) {
    die();
}

$appdata = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/private/oauth.json"), true);

$crl = curl_init('https://discord.com/api/oauth2/token');
curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($crl, CURLINFO_HEADER_OUT, true);
curl_setopt($crl, CURLOPT_POST, true);
curl_setopt($crl, CURLOPT_HTTPHEADER, [
    "Authorization: Basic " . base64_encode($appdata["id"] . ":" . $appdata["secret"]),
    "Content-Type: application/x-www-form-urlencoded",
    "Accept: application/json"
]);
curl_setopt($crl, CURLOPT_POSTFIELDS, "grant_type=authorization_code&redirect_uri=" . urlencode("https://" . $_SERVER['HTTP_HOST'] . "/auth/callback.php") . "&code=" . $_GET['code']);

$result = curl_exec($crl);
$app = json_decode($result, true);

curl_close($crl);

if (isset($app["access_token"])) {
    $crlUsr = curl_init('https://discord.com/api/v10/users/@me');
    curl_setopt($crlUsr, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($crlUsr, CURLINFO_HEADER_OUT, true);
    curl_setopt($crlUsr, CURLOPT_HTTPHEADER, [
        "Authorization: " . $app["token_type"] . " " . $app["access_token"],
        "Accept: application/json"
    ]);

    $resultUsr = curl_exec($crlUsr);
    $user = json_decode($resultUsr, true);

    $crlMem = curl_init("https://discord.com/api/v10/users/@me/guilds/1279891341509394505/member");
    curl_setopt($crlMem, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($crlMem, CURLINFO_HEADER_OUT, true);
    curl_setopt($crlMem, CURLOPT_HTTPHEADER, [
        "Authorization: " . $app["token_type"] . " " . $app["access_token"],
        "Accept: application/json"
    ]);

    $resultMem = curl_exec($crlMem);
    $member = json_decode($resultMem, true);

    if (!in_array($appdata["allowedRole"], $member["roles"])) {
        header("Location: https://ponycon.info/");
        die();
    }

    if (!file_exists($_SERVER['DOCUMENT_ROOT'] . "/private/tokens")) mkdir($_SERVER['DOCUMENT_ROOT'] . "/private/tokens");

    $token = bin2hex(random_bytes(32));
    file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/private/tokens/" . $token, json_encode($user));
    header("Set-Cookie: PCIA_SESSION_TOKEN=" . $token . "; SameSite=None; Path=/; Secure; HttpOnly; Expires=" . date("r", time() + (86400 * 730)));

    header("Location: /");
    die();
} else {
    print("no access token.");
}