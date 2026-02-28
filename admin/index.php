<?php require_once $_SERVER['DOCUMENT_ROOT'] . "/private/session.php"; global $list; ?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Admin - PonyCon Countdown</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="shortcut icon" href="logo.svg" type="image/svg">
</head>
<body>
    <?php if (isset($_GET["i"]) || isset($_GET["n"]) || isset ($_GET["d"])): ?>
    <?php

    $event = null;

    if (isset($_GET["i"])) {
        if (in_array($_GET["i"], array_keys($list))) {
            $event = $list[$_GET["i"]];
        } else {
            die("Not found, <a href='/'>home</a>.</body></html>");
        }
    }

    ?>
    <div class="container">
        <br>
        <h1><?= isset($_GET["i"]) ? $event["name"] : "Create new" ?> <a href="/" class="btn btn-outline-primary" style="float: right;">Home</a></h1>

        <form action="/update.php" method="post">
            <input type="hidden" name="id" required value="<?= $_GET["i"] ?? "" ?>">
            <input type="hidden" name="logo" id="logo-value" required value="">

            <table style="width: 100%;">
                <tr>
                    <td style="width: 15%; font-weight: bold; text-align: right; padding-right: 10px;">
                        <div style="height: 100%; display: flex; align-items: center; justify-content: right;">
                            Type:
                        </div>
                    </td>
                    <td>
                        <select name="type" class="form-select" required>
                            <option value="convention" <?= isset($_GET["i"]) ? ($event["type"] === "convention" ? "selected" : "") : "" ?>>Convention</option>
                            <option value="concert" <?= isset($_GET["i"]) ? ($event["type"] === "concert" ? "selected" : "") : "" ?>>Concert</option>
                            <option value="album" <?= isset($_GET["i"]) ? ($event["type"] === "album" ? "selected" : "") : "" ?>>Album release</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><hr></td>
                </tr>
                <tr>
                    <td style="width: 15%; font-weight: bold; text-align: right; padding-right: 10px;">
                        <div style="height: 100%; display: flex; align-items: center; justify-content: right;">
                            Name:
                        </div>
                    </td>
                    <td>
                        <input class="form-control" name="name" required type="text" placeholder="Name" value="<?= isset($_GET["i"]) ? $event["name"] : "" ?>">
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><hr></td>
                </tr>
                <tr>
                    <td style="width: 15%; font-weight: bold; text-align: right; padding-right: 10px;">
                        <div style="height: 100%; display: flex; align-items: center; justify-content: right;">
                            Location:
                        </div>
                    </td>
                    <td>
                        <input class="form-control" name="location[name]" id="location" type="text" placeholder="Location Display Name" value="<?= isset($_GET["i"]) && isset($event["location"]) ? $event["location"]["name"] : "" ?>">
                        <table style="width:100%;">
                            <tr>
                                <td style="text-align: right; padding-right: 10px; width: 15%;">OpenStreetMap:</td>
                                <td><input class="form-control" name="location[openstreetmap]" id="location-openstreetmap" type="text" placeholder="Location Name" value="<?= isset($_GET["i"]) && isset($event["location"]["openstreetmap"]) ? $event["location"]["openstreetmap"]["name"] : "" ?>"></td>
                                <input name="location_url[openstreetmap]" id="location-openstreetmap-url" type="hidden" value="<?= isset($_GET["i"]) && isset($event["location"]["openstreetmap"]) ? $event["location"]["openstreetmap"]["url"] : "" ?>">
                                <td>
                                    <label>
                                        <button type="button" class="btn btn-outline-secondary" onclick="refreshMap('openstreetmap')">Refresh</button>
                                    </label>
                                </td>
                            </tr>
                            <!--<tr>
                                <td style="text-align: right; padding-right: 10px; width: 15%;">Google Maps:</td>
                                <td><input class="form-control" name="location[googlemaps]" id="location-googlemaps" type="text" placeholder="Location Name" value="<?= isset($_GET["i"]) && isset($event["location"]["googlemaps"]) ? $event["location"]["googlemaps"]["name"] : "" ?>"></td>
                                <input name="location_url[googlemaps]" id="location-googlemaps-url" type="hidden" value="<?= isset($_GET["i"]) && isset($event["location"]["googlemaps"]) ? $event["location"]["googlemaps"]["url"] : "" ?>">
                                <td>
                                    <label>
                                        <input type="radio" class="form-check-input" name="location-preview" value="googlemaps"  id="location-googlemaps-preview">
                                        Preview
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align: right; padding-right: 10px; width: 15%;">Apple Maps:</td>
                                <td><input class="form-control" name="location[applemaps]" id="location-applemaps" type="text" placeholder="Location Name" value="<?= isset($_GET["i"]) && isset($event["location"]["applemaps"]) ? $event["location"]["applemaps"]["name"] : "" ?>"></td>
                                <input name="location_url[applemaps]" id="location-applemaps-url" type="hidden" value="<?= isset($_GET["i"]) && isset($event["location"]["applemaps"]) ? $event["location"]["applemaps"]["url"] : "" ?>">
                                <td>
                                    <label>
                                        <input type="radio" class="form-check-input" name="location-preview" value="applemaps"  id="location-applemaps-preview">
                                        Preview
                                    </label>
                                </td>
                            </tr>-->
                        </table>
                        <input id="location-url" type="hidden" value="<?= isset($_GET["i"]) && isset($event["location"]["openstreetmap"]) ? $event["location"]["openstreetmap"]["url"] : "" ?>">
                        <br>
                        <p><b>Detected:</b> <span id="map-detected">-</span> (<code id="map-detected-id">-</code>)</p>
                        <iframe style="border: 1px solid black;" id="map" src="https://www.openstreetmap.org/export/embed.html"></iframe>
                        <!-- ?bbox=1.8757578%2C47.8132802%2C1.9487114%2C47.9335581 [2, 0, 1, 3] -->
                        <script>
                            // TODO: Modify how this behaves
                            document.getElementById("location-openstreetmap").addEventListener("change", refreshMap("openstreetmap"))
                            document.getElementById("location-googlemaps").addEventListener("change", refreshMap("googlemaps"))
                            document.getElementById("location-applemaps").addEventListener("change", refreshMap("applemaps"))

                            async function refreshMap(type) {
                                if (type === "openstreetmap") {
                                    try {
                                        let data = await (await fetch("https://nominatim.openstreetmap.org/search?q=" + encodeURIComponent(document.getElementById("location-openstreetmap").value) + "&format=json")).json();
                                        console.log(data, data[0]);
                                        let first = data[0];

                                        document.getElementById("map-detected").innerText = first['display_name'];
                                        document.getElementById("map-detected-id").innerText = first['osm_id'];
                                        document.getElementById("location-openstreetmap-url").value = "https://openstreetmap.org/" + first['osm_type'] + "/" + first['osm_id'];

                                        console.log(first['boundingbox'][2] + "," + first['boundingbox'][0] + "," + first['boundingbox'][3] + "," + first['boundingbox'][1]);

                                        document.getElementById("map").src = "https://www.openstreetmap.org/export/embed.html?bbox=" + encodeURIComponent(first['boundingbox'][2] + "," + first['boundingbox'][0] + "," + first['boundingbox'][3] + "," + first['boundingbox'][1])
                                    } catch (e) {
                                        document.getElementById("map-detected").innerText = "-";
                                        document.getElementById("map").src = "https://www.openstreetmap.org/export/embed.html";
                                    }
                                } else if (type === "googlemaps") {
                                    // TODO: Support Google Maps embed.
                                    // Google query format: https://google.com/maps/?q=Test
                                    // Use Google Maps Embed API for embed.
                                } else if (type === "applemaps") {
                                    // TODO: Support Apple Maps embed.
                                    // Apple query format: https://maps.apple.com/?q=Test
                                    // Use the search API to find the location and then use MapKit JS to embed.
                                }
                            }

                            if (document.getElementById("location").value.trim() !== "") {
                                refreshMap();
                            }
                        </script>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><hr></td>
                </tr>
                <tr>
                    <td style="width: 15%; font-weight: bold; text-align: right; padding-right: 10px;">
                        <div style="height: 100%; display: flex; align-items: center; justify-content: right;">
                            Summary:
                        </div>
                    </td>
                    <td>
                        <textarea class="form-control" name="summary" placeholder="Summary"><?= isset($_GET["i"]) ? $event["summary"] : "" ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><hr></td>
                </tr>
                <tr>
                    <td style="width: 15%; font-weight: bold; text-align: right; padding-right: 10px;">
                        <div style="height: 100%; display: flex; align-items: center; justify-content: right;">
                            Logo:
                        </div>
                    </td>
                    <td>
                        <input type="file" id="logo" onchange="updateLogo();"><br>
                        <img id="logo-preview" style="border: 1px solid black; height: 96px; max-width: 100%; margin-top: 20px;">
                        <script>
                            let defaultLogo = "<?= isset($_GET['i']) && file_exists($_SERVER['DOCUMENT_ROOT'] . "/assets/events/" . $_GET['i'] . ".png") ? "https://ponycon.info/assets/events/" . $_GET['i'] . ".png" : "https://ponycon.info/assets/img/placeholder.png" ?>";

                            function updateLogo() {
                                if (!document.getElementById("logo").files[0]) {
                                    document.getElementById("logo-preview").src = defaultLogo;
                                    return
                                }

                                let fr = new FileReader();
                                fr.readAsDataURL(document.getElementById("logo").files[0]);

                                fr.addEventListener("load", function () {
                                    let file = {
                                        type: document.getElementById("logo").files[0].type,
                                        data: this.result.split(",")[1]
                                    }

                                    document.getElementById("logo-preview").src = this.result;
                                    document.getElementById("logo-value").value = JSON.stringify(file);
                                });
                            }

                            document.getElementById("logo").value = "";
                            document.getElementById("logo-preview").src = defaultLogo;
                        </script>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><hr></td>
                </tr>
                <tr>
                    <td style="width: 15%; font-weight: bold; text-align: right; padding-right: 10px;">
                        <div style="height: 100%; display: flex; align-items: center; justify-content: right;">
                            Access:
                        </div>
                    </td>
                    <td>
                        <label>
                            <input type="checkbox" class="form-check-input" name="irl" <?= isset($_GET["i"]) && $event["irl"] ? "checked" : "" ?>>
                            IRL
                        </label>
                        <label>
                            <input type="checkbox" class="form-check-input" name="online" <?= isset($_GET["i"]) && $event["online"] ? "checked" : "" ?>>
                            Online
                        </label>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><hr></td>
                </tr>
                <tr>
                    <td style="width: 15%; font-weight: bold; text-align: right; padding-right: 10px;">
                        <div style="height: 100%; display: flex; align-items: center; justify-content: right;">
                            Dates:
                        </div>
                    </td>
                    <td>
                        <p>
                            <label>
                                Start: <span id="time-start-warning" data-bs-toggle="tooltip" data-bs-html="true" title="<b>Unusual start time</b><br>This event starts on an unusual day. Is this date correct?" class="tooltip-nohelp" style="position: relative; top:-2px; cursor: pointer; display: none;"><span><img src="warning.svg" alt="Warning"/></span></span>
                                <input class="form-control" type="datetime-local" name="date[start]" id="date1" required value="<?= isset($_GET["i"]) ? substr(date("c", $event["date"]["start"]), 0, 16) : "" ?>">
                            </label>
                            <label>
                                End: <span id="time-end-warning" data-bs-toggle="tooltip" data-bs-html="true" title="<b>Unusual end time</b><br>This event ends on an unusual day. Is this date correct?" class="tooltip-nohelp" style="position: relative; top:-2px; cursor: pointer; display: none;"><span><img src="warning.svg" alt="Warning"/></span></span>
                                <input class="form-control" type="datetime-local" name="date[end]" id="date2" required value="<?= isset($_GET["i"]) ? substr(date("c", $event["date"]["end"]), 0, 16) : "" ?>">
                            </label>
                            <label>
                                <input type="checkbox" class="form-check-input" name="show_times" <?= isset($_GET["i"]) && isset($event["show_times"]) && $event["show_times"] ? "checked" : "" ?>>
                                Show times
                            </label>
                        </p>
                        Starts <span id="time-start">-</span>, for <span id="time-duration">-</span><span id="time-duration-warning" data-bs-toggle="tooltip" data-bs-html="true" title="<b>Long event time</b><br>This event lasts longer than usual. Are the dates correct?" class="tooltip-nohelp" style="position: relative; top:-2px; cursor: pointer; display: none;"><span><img src="warning.svg" alt="Warning"/></span></span>
                        <script>
                            function timeAgo(time) {
                                if (!isNaN(parseInt(time))) {
                                    time = new Date(time).getTime();
                                }

                                let periods = ["second", "minute", "hour", "day", "week", "month", "year", "ages"];

                                let lengths = ["60", "60", "24", "7", "4.35", "12", "100"];

                                let now = new Date().getTime();

                                let difference = Math.abs(Math.round((now - time) / 1000));
                                let period;

                                if (difference <= 10 && difference >= 0) {
                                    return "now";
                                }

                                let j;

                                for (j = 0; difference >= lengths[j] && j < lengths.length - 1; j++) {
                                    difference /= lengths[j];
                                }

                                difference = Math.round(difference);

                                period = periods[j];

                                if(time - now < 0) {
                                    return `${difference} ${period}${difference > 1 ? "s" : ""} ago`;
                                } else {
                                    return `in ${difference} ${period}${difference > 1 ? "s" : ""}`;
                                }
                            }

                            function duration(start, end) {
                                if (!isNaN(parseInt(start))) {
                                    start = new Date(start).getTime();
                                }

                                if (!isNaN(parseInt(end))) {
                                    end = new Date(end).getTime();
                                }

                                let periods = ["second", "minute", "hour", "day", "week", "month", "year", "ages"];

                                let lengths = ["60", "60", "24", "7", "4.35", "12", "100"];

                                let difference = Math.abs(Math.round((end - start) / 1000));
                                let period;

                                if (difference <= 10 && difference >= 0) {
                                    return "-";
                                }

                                let j;

                                for (j = 0; difference >= lengths[j] && j < lengths.length - 1; j++) {
                                    difference /= lengths[j];
                                }

                                difference = Math.round(difference);

                                period = periods[j];

                                if (periods.indexOf(period) >= 4) {
                                    document.getElementById("time-duration-warning").style = "position: relative; top:-2px; cursor: pointer;";
                                } else {
                                    document.getElementById("time-duration-warning").style = "position: relative; top:-2px; cursor: pointer; display: none;";
                                }

                                return `${difference} ${period}${difference > 1 ? "s" : ""}`;
                            }

                            function updateDate() {
                                if (document.getElementById("date1").value !== "") {
                                    if (![5, 6, 0].includes(new Date(new Date(document.getElementById("date1").value)).getDay())) {
                                        document.getElementById("time-start-warning").style = "position: relative; top:-2px; cursor: pointer;";
                                    } else {
                                        document.getElementById("time-start-warning").style = "position: relative; top:-2px; cursor: pointer; display: none;";
                                    }
                                }

                                if (document.getElementById("date2").value !== "") {
                                    if (![6, 0, 1].includes(new Date(new Date(document.getElementById("date2").value)).getDay())) {
                                        document.getElementById("time-end-warning").style = "position: relative; top:-2px; cursor: pointer;";
                                    } else {
                                        document.getElementById("time-end-warning").style = "position: relative; top:-2px; cursor: pointer; display: none;";
                                    }
                                }

                                if (document.getElementById("date1").value === "" || document.getElementById("date2").value === "") {
                                    document.getElementById("time-start").innerText = "";
                                    document.getElementById("time-duration").innerText = "";
                                    return
                                }

                                document.getElementById("time-start").innerText = timeAgo(new Date(document.getElementById("date1").value));
                                document.getElementById("time-duration").innerText = duration(new Date(document.getElementById("date2").value), new Date(document.getElementById("date1").value));
                            }

                            document.getElementById("date1").onchange = document.getElementById("date2").onchange = updateDate;
                            updateDate();
                        </script>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><hr></td>
                </tr>
                <tr>
                    <td style="width: 15%; font-weight: bold; text-align: right; padding-right: 10px;">
                        <div style="height: 100%; display: flex; align-items: center; justify-content: right;">
                            Breaks:
                        </div>
                    </td>
                    <td>
                        <table style="width:100%;" id="breaksContainer">
                            <?php if(isset($_GET["i"])): ?>
                            <?php
                            if(isset($event["break"])):
                                $breaks = $event["break"];
                                uasort($breaks, function ($a, $b) {
                                    return $a["start"] - $b["start"];
                                });
                                foreach ($breaks as $index => $break):
                                    ?>
                                    <tr id="break<?= $index ?>">
                                        <td>
                                            <label>
                                                Start:
                                                <input class="form-control" type="datetime-local" name="break[<?= $index ?>][start]" id="break<?= $index ?>-date1" required value="<?= substr(date("c", $break["start"]), 0, 16) ?>">
                                            </label>
                                            <label>
                                                End:
                                                <input class="form-control" type="datetime-local" name="break[<?= $index ?>][end]" id="break<?= $index ?>-date2" required value="<?= substr(date("c", $break["end"]), 0, 16) ?>">
                                            </label>
                                            Starts <span id="break<?= $index ?>-start">-</span>, for <span id="break<?= $index ?>-duration">-</span>
                                            <button type="button" class="btn btn-outline-danger" id="break<?= $index ?>-delete" onclick="deleteBreak(this)" style="float: right;">Delete</button>
                                        </td>
                                    </tr>
                                    <tr id="break<?= $index ?>-sep">
                                        <td colspan="2"><hr></td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            <?php endif; ?>
                            <script>
                                let breakCount = <?= isset($breaks) ? count($breaks) : 0 ?>;

                                // script to handle deleting of breaks
                                function deleteBreak(element) {
                                    let breakId = element.id.split("-")[0];

                                    let breakEntry = document.getElementById(breakId);
                                    let breakEntrySep = document.getElementById(breakId + "-sep");

                                    breakEntry.parentNode.removeChild(breakEntry);
                                    breakEntrySep.parentNode.removeChild(breakEntrySep);
                                }

                                // script to handle the start and duration info for each one
                                function updateBreakDates(id) {
                                    if (document.getElementById(id + "-date1").value === "" || document.getElementById(id + "-date2").value === "") {
                                        document.getElementById(id + "-start").innerText = "";
                                        document.getElementById(id + "-duration").innerText = "";
                                        return;
                                    }

                                    document.getElementById(id + "-start").innerText = timeAgo(new Date(document.getElementById(id + "-date1").value));
                                    document.getElementById(id + "-duration").innerText = duration(new Date(document.getElementById(id + "-date2").value), new Date(document.getElementById(id + "-date1").value));
                                }

                                function updateBreakTimes() {
                                    document.getElementById("breaksContainer").lastChild.childNodes.forEach(child => {
                                        if (/^break(\d+)$/g.test(child.id)) {
                                            updateBreakDates(child.id);

                                            document.getElementById(`${child.id}-date1`).onchange = () => updateBreakDates(child.id);
                                            document.getElementById(`${child.id}-date2`).onchange = () => updateBreakDates(child.id);
                                        }
                                    });
                                }

                                updateBreakTimes();

                                // script to handle creating breaks
                                function createBreak() {
                                    let elementsToAdd = `
                                    <tr id="break${breakCount}">
                                        <td>
                                            <label>
                                                Start:
                                                <input class="form-control" type="datetime-local" name="break[${breakCount}][start]" id="break${breakCount}-date1" required>
                                            </label>
                                            <label>
                                                End:
                                                <input class="form-control" type="datetime-local" name="break[${breakCount}][end]" id="break${breakCount}-date2" required>
                                            </label>
                                            Starts <span id="break${breakCount}-start">-</span>, for <span id="break${breakCount}-duration">-</span>
                                            <button type="button" class="btn btn-outline-danger" id="break${breakCount}-delete" onclick="deleteBreak(this)" style="float: right;">Delete</button>
                                        </td>
                                    </tr>
                                    <tr id="break${breakCount}-sep">
                                        <td colspace="2"><hr></td>
                                    </tr>`;

                                    breakCount++;

                                    document.getElementById("breakAddButton").insertAdjacentHTML('beforebegin', elementsToAdd);
                                    updateBreakTimes();
                                }
                            </script>
                            <tr id="breakAddButton">
                                <td>
                                    <button type="button" class="btn btn-outline-secondary" onclick="createBreak()" style="width:100%;">Add new break</button>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><hr></td>
                </tr>
                <tr>
                    <td style="width: 15%; font-weight: bold; text-align: right; padding-right: 10px;">
                        <div style="height: 100%; display: flex; align-items: center; justify-content: right;">
                            Website:
                        </div>
                    </td>
                    <td>
                        <input class="form-control" type="url" name="website" placeholder="Website" value="<?= isset($_GET["i"]) ? $event["website"] : "" ?>">
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><hr></td>
                </tr>
                <tr>
                    <td style="width: 15%; font-weight: bold; text-align: right; padding-right: 10px;">
                        <div style="height: 100%; display: flex; align-items: center; justify-content: right;">
                            Streaming:
                        </div>
                    </td>
                    <td>
                        <table style="width:100%;">
                            <tr>
                                <td style="text-align: right; padding-right: 10px; width: 15%;">Streaming:</td>
                                <td>
                                    <label>
                                        <input type="checkbox" class="form-check-input" name="streaming[enabled]" <?= isset($_GET["i"]) && $event["streaming"]["enabled"] ? "checked" : "" ?>>
                                        Enabled
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align: right; padding-right: 10px; width: 15%;">Stream URL:</td>
                                <td><input class="form-control" type="text" name="streaming[stream]" value="<?= isset($_GET["i"]) ? (isset($event["streaming"]["stream"]) ? $event["streaming"]["stream"] : "") : "" ?>"></td>
                            </tr>
                            <tr>
                                <td style="text-align: right; padding-right: 10px; width: 15%;">PonyTown:</td>
                                <td>
                                    <select name="streaming[ponyTown]" required class="form-select">
                                        <option value="none" <?= isset($_GET["i"]) ? (isset($event["streaming"]["ponyTown"]) ? "" : "selected") : "" ?>>[None]</option>
                                        <option value="main" <?= isset($_GET["i"]) ? (isset($event["streaming"]["ponyTown"]) && $event["streaming"]["ponyTown"] === "main" ? "selected" : "") : "" ?>>event.pony.town</option>
                                        <option value="blue" <?= isset($_GET["i"]) ? (isset($event["streaming"]["ponyTown"]) && $event["streaming"]["ponyTown"] === "blue" ? "selected" : "") : "" ?>>eventblue.pony.town</option>
                                        <option value="green" <?= isset($_GET["i"]) ? (isset($event["streaming"]["ponyTown"]) && $event["streaming"]["ponyTown"] === "green" ? "selected" : "") : "" ?>>eventgreen.pony.town</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><hr></td>
                </tr>
                <tr>
                    <td style="width: 15%; font-weight: bold; text-align: right; padding-right: 10px;">
                        <div style="height: 100%; display: flex; align-items: center; justify-content: right;">
                            Social media:
                        </div>
                    </td>
                    <td>
                        <table style="width:100%;">
                            <tr>
                                <td style="text-align: right; padding-right: 10px; width: 15%;">Discord:</td>
                                <td><input class="form-control" type="text" name="socials[discord][url]" value="<?= isset($_GET["i"]) ? (isset($event["socials"]["discord"]) ? $event["socials"]["discord"]["url"] : "") : "" ?>"></td>
                                <td>
                                    <label>
                                        <input type="checkbox" class="form-check-input" name="socials[discord][live]" <?= isset($_GET["i"]) && isset($event["socials"]["discord"]) && $event["socials"]["discord"]["live"] ? "checked" : "" ?>>
                                        Live
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align: right; padding-right: 10px; width: 15%;">Mastodon:</td>
                                <td><input class="form-control" type="text" name="socials[mastodon][url]" value="<?= isset($_GET["i"]) ? (isset($event["socials"]["mastodon"]) ? $event["socials"]["mastodon"]["url"] : "") : "" ?>"></td>
                                <td>
                                    <label>
                                        <input type="checkbox" class="form-check-input" name="socials[mastodon][live]" <?= isset($_GET["i"]) && isset($event["socials"]["mastodon"]) && $event["socials"]["mastodon"]["live"] ? "checked" : "" ?>>
                                        Live
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align: right; padding-right: 10px; width: 15%;">Twitter:</td>
                                <td><input class="form-control" type="text" name="socials[twitter][url]" value="<?= isset($_GET["i"]) ? (isset($event["socials"]["twitter"]) ? $event["socials"]["twitter"]["url"] : "") : "" ?>"></td>
                                <td>
                                    <label>
                                        <input type="checkbox" class="form-check-input" name="socials[twitter][live]" <?= isset($_GET["i"]) && isset($event["socials"]["twitter"]) && $event["socials"]["twitter"]["live"] ? "checked" : "" ?>>
                                        Live
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align: right; padding-right: 10px; width: 15%;">YouTube:</td>
                                <td><input class="form-control" type="text" name="socials[youtube][url]" value="<?= isset($_GET["i"]) ? (isset($event["socials"]["youtube"]) ? $event["socials"]["youtube"]["url"] : "") : "" ?>"></td>
                                <td>
                                    <label>
                                        <input type="checkbox" class="form-check-input" name="socials[youtube][live]" <?= isset($_GET["i"]) && isset($event["socials"]["youtube"]) && $event["socials"]["youtube"]["live"] ? "checked" : "" ?>>
                                        Live
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align: right; padding-right: 10px; width: 15%;">Twitch:</td>
                                <td><input class="form-control" type="text" name="socials[twitch][url]" value="<?= isset($_GET["i"]) ? (isset($event["socials"]["twitch"]) ? $event["socials"]["twitch"]["url"] : "") : "" ?>"></td>
                                <td>
                                    <label>
                                        <input type="checkbox" class="form-check-input" name="socials[twitch][live]" <?= isset($_GET["i"]) && isset($event["socials"]["twitch"]) && $event["socials"]["twitch"]["live"] ? "checked" : "" ?>>
                                        Live
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align: right; padding-right: 10px; width: 15%;">Facebook:</td>
                                <td><input class="form-control" type="text" name="socials[facebook][url]" value="<?= isset($_GET["i"]) ? (isset($event["socials"]["facebook"]) ? $event["socials"]["facebook"]["url"] : "") : "" ?>"></td>
                                <td>
                                    <label>
                                        <input type="checkbox" class="form-check-input" name="socials[facebook][live]" <?= isset($_GET["i"]) && isset($event["socials"]["facebook"]) && $event["socials"]["facebook"]["live"] ? "checked" : "" ?>>
                                        Live
                                    </label>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <br>
            <p style="text-align: center;">
                <label>
                    <input type="checkbox" class="form-check-input" name="hidden" <?= isset($_GET["i"]) && isset($event["hidden"]) && $event["hidden"] ? "checked" : "" ?>>
                    Hidden
                </label>
                <span style="margin-left: 5px; padding-left: 10px; border-left: 1px solid rgba(0, 0, 0, .25);">
                <label>
                    <input type="checkbox" class="form-check-input" name="_duplicate" <?= !isset($_GET["i"]) ? "disabled" : "" ?>>
                    Duplicate
                </label>
                <label>
                    <input type="checkbox" class="form-check-input" name="_delete" <?= !isset($_GET["i"]) ? "disabled" : "" ?>>
                    Delete
                </label>
                </span>
                <span style="margin-left: 5px; padding-left: 10px; border-left: 1px solid rgba(0, 0, 0, .25);">
                    <input type="submit" value="Update" class="btn btn-primary">
                </span>
            </p>
        </form>
    </div>
    <?php else: ?>
    <div class="container">
        <br>
        <h1>Admin portal</h1>

        <div class="list-group">
            <?php $olist = $list; uasort($olist, function ($a, $b) {
                return $a["date"]["start"] - $b["date"]["start"];
            }); foreach ($olist as $id => $item): ?>
                <a class="list-group-item list-group-item-action" href="/?i=<?= $id ?>"><?= $item["name"] ?></a>
            <?php endforeach; ?>
        </div>

        <br>
        <a href="/?n" class="btn btn-primary">Create new</a>
    </div>
    <?php endif; ?>
    <script>
        let tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        let tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    </script>
</body>
</html>