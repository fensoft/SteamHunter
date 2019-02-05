<?php
require __DIR__ . '/vendor/autoload.php';

# see https://steamcommunity.com/dev/apikey
define("KEY", "4C04CA35153EE9660DB27D8D87A2FA0C");

function getSteamApiAchievements($user, $appid, $language) {
  if (is_numeric($user)) {
    $userid = $user;
  } else {
    $vanity = json_decode(file_get_contents("http://api.steampowered.com/ISteamUser/ResolveVanityURL/v0001/?key=" . KEY . "&vanityurl=" . $user));
    $userid = $vanity->response->steamid;
  }
  return json_decode(file_get_contents("http://api.steampowered.com/ISteamUserStats/GetPlayerAchievements/v1?key=" . KEY . "&steamid=" . $userid . "&appid=" . $appid . "&l=" . $language));
}

function getSteamCommonAchievements($filter, $users, $appid, $language = "english", $min = -1, $max = -1) {
  $finalach = array();
  foreach ($users as $user) {
    $ach = getSteamApiAchievements($user, $appid, $language);
    foreach ($ach->playerstats->achievements as $key => $value) {
      if (!isset($finalach[$key]))
        $finalach[$key] = 0;
      if ($value->achieved != "0")
        $finalach[$key] += 1;
    }
  }
  $final = array();
  foreach ($finalach as $key => $value) {
    if ($min != -1 && $min > $value)
      continue;
    if ($max != -1 && $max < $value)
      continue;
    if (count($filter) != 0 && !in_array($ach->playerstats->achievements[$key]->apiname, $filter))
      continue;
    $final[$key] = $ach->playerstats->achievements[$key];
    $final[$key]->total = $value;
  }
  return $final;
}

$smarty = new Smarty();
$smarty->assign("request", $_REQUEST);
$smarty->display("header.tpl");
if (!isset($_REQUEST["user1"])) {
  $smarty->display("form.tpl");
} else {
  $filter = array();
  if ($_REQUEST["filter"]) {
    $filter = explode(" ", $_REQUEST["filter"]);
  }
  if ($_REQUEST["filters"]) {
    $filter = explode(" ", $_REQUEST["filters"]);
  }
  $i = 1;
  $users = array();
  while (isset($_REQUEST["user" . $i]) && $_REQUEST["user" . $i] != "") {
    array_push($users, $_REQUEST["user" . $i]);
    $i += 1;
  }
  $appid = $_REQUEST["appid"];
  if (!is_numeric($appid)) {
    $games = json_decode(file_get_contents("http://api.steampowered.com/ISteamApps/GetAppList/v2"), true);
    $games = $games["applist"]["apps"];
    foreach ($games as $key => $game) {
      if (strtolower($appid) == strtolower($game["name"]))
        $appid = $game["appid"];
    }
  }
  $results = getSteamCommonAchievements($filter, $users, $appid, $_REQUEST["language"], isset($_REQUEST["min"]) ? $_REQUEST["min"] : -1, isset($_REQUEST["max"]) ? $_REQUEST["max"] : -1);
  $smarty->assign("results", $results);
  $smarty->display("results.tpl");
}
$smarty->display("footer.tpl");
