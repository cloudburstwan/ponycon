<?php
header("Location: https://discord.com/oauth2/authorize?client_id=" . json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/private/oauth.json"), true)["id"] . "&response_type=code&redirect_uri=https%3A%2F%2Fadmin.ponycon.info%2Fauth%2Fcallback.php&scope=identify+guilds+guilds.members.read");
die();