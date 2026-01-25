<?php

function uuidv4($data) {
    assert(strlen($data) == 16);

    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

require_once $_SERVER['DOCUMENT_ROOT'] . "/private/session.php"; global $list;

$data = $_POST;
$id = $data["id"];
$logo = json_decode($data["logo"] ?? "null", true) ?? null;

unset($data["id"]);
unset($data["logo"]);

if ($id === "" || isset($data["_duplicate"])) {
    $oldLogoLocation = $_SERVER['DOCUMENT_ROOT'] . "/assets/events/" . $id . ".png";
    $id = uuidv4(openssl_random_pseudo_bytes(16));
    if (isset($data["_duplicate"])) {
        // Copies the existing logo, if it exists.
        copy($oldLogoLocation, $_SERVER['DOCUMENT_ROOT'] . "/assets/events/" . $id . ".png");
    }
}

$socials = [];
foreach ($data["socials"] as $name => $social) {
    if (trim($social["url"]) !== "") {
        $socials[$name] = [
                "url" => $social["url"],
                "live" => isset($social["live"])
        ];
    }
}
$data["socials"] = $socials;

$data["date"]["start"] = strtotime($data["date"]["start"] . ":00+00:00");
$data["date"]["end"] = strtotime($data["date"]["end"] . ":00+00:00");

$breaks = [];

if (isset($data["break"])) {
    if (count($data["break"]) === 0) {
        unset($data["break"]);
    } else {
        foreach ($data["break"] as $index => $break) {
            $breaks[] = [
                "start" => strtotime($break["start"] . ":00+00:00"),
                "end" => strtotime($break["end"] . ":00+00:00")
            ];
        }

        $data["break"] = $breaks;
    }
}

if (isset($data["streaming"]["enabled"])) $data["streaming"]["enabled"] = true;
else $data["streaming"]["enabled"] = false;
if (trim($data["streaming"]["stream"]) === "") unset($data["streaming"]["stream"]);
if (trim($data["streaming"]["ponyTown"]) === "none") unset($data["streaming"]["ponyTown"]);

if (trim($data["website"]) === "") unset($data["website"]);
if (trim($data["summary"]) === "") unset($data["summary"]);

// TODO: Modify location handling to support current setup
$location = [
        'name' => $data["location"]["name"] ?? null,
        'openstreetmap' => null,
        'googlemaps' => null,
        'applemaps' => null
];

if (trim($data["location"]["openstreetmap"]) === "") {
    unset($location["openstreetmap"]);
} else {
    $location["openstreetmap"] = [
        "name" => $data["location"]["openstreetmap"],
        "url" => $data["location_url"]["openstreetmap"] ?? null
    ];
}

/* if (trim($data["location"]["googlemaps"]) === "") {
    unset($location["googlemaps"]);
} else {
    $location["googlemaps"] = [
        "name" => $data["location"]["googlemaps"],
        "url" => $data["location_url"]["googlemaps"] ?? null
    ];
}

if (trim($data["location"]["applemaps"]) === "") {
    unset($location["applemaps"]);
} else {
    $location["applemaps"] = [
        "name" => $data["location"]["applemaps"],
        "url" => $data["location_url"]["applemaps"] ?? null
    ];
}*/

if (isset($location["name"]) && trim($location["name"]) != "") {
    $data["location"] = $location;
} else {
    unset($data["location"]);
}

if (isset($data["show_times"])) $data["show_times"] = true;
else $data["show_times"] = false;

if (isset($data["hidden"])) $data["hidden"] = true;
else $data["hidden"] = false;

if (in_array($id, array_keys($list))) unset($list[$id]);
if (!isset($data["_delete"])) $list[$id] = $data;

if (isset($logo)) {
    if (str_starts_with($logo["type"], "image/")) {
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/assets/events/" . $id . ".png", base64_decode($logo["data"]));
    }
}

file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/data/events.bak", file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/data/events.xml"));
file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/data/events.xml", export_event_list());

$shortenedId = "";
foreach( explode("-", $id) as $section) {
    $shortenedId = $shortenedId . $section[0];
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js"></script>
    <title>Admin portal</title>
</head>
<body>
    <div class="container">
        <br>
        <p>The operation completed successfully.</p>
        <p>Link to share:</p>
        <input type="text" id="share-box-link" disabled class="form-control" value="https://ponycon.info/<?= $shortenedId ?>">
        <a class="btn btn-primary" href="/">Home</a><br/><br/>
        <p>Funky debug shit</p>
        <?php var_dump($data); ?>
    </div>
</body>
</html>
