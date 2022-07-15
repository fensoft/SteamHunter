<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config.php';

function array_group_by(array $arr, callable $key_selector) {
  $result = array();
  foreach ($arr as $i) {
    $key = call_user_func($key_selector, $i);
    $result[$key][] = $i;
  }
  return $result;
}

function getCachedContents($url, $time = 30, $minsize = 1) {
  $success = false;
  $result = apcu_fetch($url, $success);
  if (!$success || $result == "" || strlen($result) < $minsize) {
    $opts = array('http' => array('ignore_errors' => true));
    $context = stream_context_create($opts);
    $result = file_get_contents($url, false, $context);
    if ($result != "")
      apcu_store($url, $result, $time);
    //echo "<!--" . $url . "=" . strlen($result) . $result . "-->";
  } else {
    //echo "<!--" . $url . "= cachehit" . strlen($result) . $result . "-->\n";
  }
  return $result;
}

function getAppId($appid) {
  $found = false;
  $games = json_decode(getCachedContents(STEAMENDPOINT . "/ISteamApps/GetAppList/v2", 3600, 1024), true);
  $games = $games["applist"]["apps"];
  if (!is_numeric($appid)) {
    foreach ($games as $key => $game) {
      if (strtolower($appid) == strtolower($game["name"])) {
        $appid = $game["appid"];
        $found = true;
      }
    }
  }
  return $appid;
}

function getSteamId($user) {
  if (is_numeric($user)) {
    $userid = $user;
  } else {
    $vanity = json_decode(getCachedContents(STEAMENDPOINT . "/ISteamUser/ResolveVanityURL/v0001/?key=" . KEY . "&vanityurl=" . $user));
    $userid = $vanity->response->steamid;
  }
  return $userid;
}

function getSteamApiAchievements($userid, $appid, $language = "english") {
  if (isset($_REQUEST["snapshot"])) {
    return json_decode(gzdecode(file_get_contents("history/$appid/$userid/" . $_REQUEST["snapshot"] . "." . $language)));
  } else {
    $content = getCachedContents(STEAMENDPOINT . "/ISteamUserStats/GetPlayerAchievements/v1?key=" . KEY . "&steamid=" . $userid . "&appid=" . $appid . "&l=" . $language);
    header("X-FenAchievements: " . $_SERVER["REQUEST_TIME"]);
    $folder = "history/$appid/$userid";
    if (!is_dir($folder))
      mkdir($folder, 0777, TRUE);
    file_put_contents($folder . "/" . $_SERVER["REQUEST_TIME"] . "." . $language, gzencode($content, 9));
    return json_decode($content);
  }
}

function getSteamApiStats($userid, $appid) {
  return json_decode(getCachedContents(STEAMENDPOINT . "/ISteamUserStats/GetUserStatsForGame/v0002/?key=" . KEY . "&steamid=" . $userid . "&appid=" . $appid));
}

function getSteamApiPlaytime($userid, $appid) {
  $data = json_decode(getCachedContents(STEAMENDPOINT . "/IPlayerService/GetOwnedGames/v1/?key=" . KEY . "&steamid=" . $userid));
  foreach ($data->response->games as $val) {
    if ($val->appid == $appid)
      return $val->playtime_forever;
  }
  return 0;
}

function getSteamApiUserInfo($userid, $appid) {
  $a = getSteamApiAchievements($userid, $appid);
  $s = getSteamApiStats($userid, $appid);
  $result = array("steamid" => intval($userid));
  $result["achievements"] = array();
  foreach ($a->playerstats->achievements as $key => $value) {
    $result["achievements"][$value->apiname] = $value->unlocktime;
  }
  $result["stats"] = array();
  foreach ($s->playerstats->stats as $key => $value) {
    $result["stats"][$value->name] = $value->value;
  }
  return $result;
}

function getSteamProfile($userid) {
  return json_decode(getCachedContents(STEAMENDPOINT . "/ISteamUser/GetPlayerSummaries/v0002/?key=" . KEY . "&steamids=" . $userid))->response->players[0];
}

function getSteamCommonAchievements($filter, $users, $appid, $language = "english", $min = -1, $max = -1) {
  $nogame = array();
  $finalach = array();
  foreach ($users as $user) {
    $ach = getSteamApiAchievements($user, $appid, $language);
    if ($ach == NULL) {
      array_push($nogame, getSteamProfile($user)->personaname);
      continue;
    }
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
  return array("result" => $final, "nogame" => $nogame);
}

function sanitize($dangerous_filename) {
  $dangerous_characters = array(" ", '"', "'", "&", "/", "\\", "?", "#");
  return str_replace($dangerous_characters, '_', $dangerous_filename);
}

function array_diff_r($aArray1, $aArray2) {
  $aReturn = array();

  foreach ($aArray1 as $mKey => $mValue) {
    if (array_key_exists($mKey, $aArray2)) {
      if (is_array($mValue)) {
        $aRecursiveDiff = array_diff_r($mValue, $aArray2[$mKey]);
        if (count($aRecursiveDiff)) { $aReturn[$mKey] = $aRecursiveDiff; }
      } else {
        if ($mValue != $aArray2[$mKey]) {
          $aReturn[$mKey] = $mValue;
        }
      }
    } else {
      $aReturn[$mKey] = $mValue;
    }
  }
  return $aReturn;
}

function array_blacklist($arr, $list) {
  $res = $arr;
  foreach ($list as $val) {
    unset($res[$val]);
  }
  return $res;
}

function ksortRecursive(&$array, $sort_flags = SORT_REGULAR) {
  if (!is_array($array)) return false;
  ksort($array, $sort_flags);
  foreach ($array as &$arr) {
      ksortRecursive($arr, $sort_flags);
  }
  return true;
}

function getMongoStats($steamid, $appid) {
  $data = getSteamApiUserInfo($steamid, $appid);
  $data["playtime"] = getSteamApiPlaytime(getSteamId($_REQUEST["user"]), $appid);
  $client = new MongoDB\Client("mongodb://" . MONGODB_USERNAME . ":" . MONGODB_PASSWORD . "@" . MONGODB_HOSTNAME .  ":" . MONGODB_PORT);
  $collection = $client->payday2->stats;
  $result = $collection->find(['steamid' => intVal($steamid)], ['sort' => ['time' => -1], 'limit' => 1]);
  $result->setTypeMap(array('array' => 'array', 'document' => 'array', 'root' => 'array'));
  $mongo = null;
  $mongoid = null;
  foreach ($result as $entry) {
    $mongo = $entry;
  }
  if ($mongo === null || count(array_diff_r(array_blacklist($mongo, ["_id", "first", "last"]), $data)) != 0) {
    $data["first"] = $data["last"] = time();
    $result = $collection->insertOne($data);
  } else {
    $data["first"] = $mongo["first"];
    $data["last"] = time();
    $collection->updateOne(array('_id' => $mongo["_id"]), array('$set' => array("last" => time())));
  }
  return $data;
}
if ($_REQUEST["action"] == 'php-info') {
  phpinfo();
  die();
}

if (!isset($_REQUEST['api'])) {
  $smarty = new Smarty();
  $smarty->registerPlugin("modifier", "appid", "getAppId");
  $smarty->registerPlugin("modifier", "steamid", "getSteamId");
  $smarty->assign("request", $_REQUEST);
  $smarty->display("header.tpl");
} else {
  header('Content-Type: application/json; charset=utf-8');
}
$dbh = new PDO('mysql:host=' . MYSQL_HOSTNAME . ';dbname=' . MYSQL_DATABASE, MYSQL_USER, MYSQL_PASSWORD);
$stats = array("visits" => "select count(*) as visits from stats_visits",
               "ips"    => "select count(distinct ip) as ips from stats_visits",
               "appids" => "select count(distinct appid) as appids from stats_visits",
               "users"  => "select count(distinct steamid) as users from stats_users");
foreach ($stats as $key => $query) {
  $stmt = $dbh->prepare($query);
  $stmt->execute();
  $content = $stmt->fetch();
  if (!isset($_REQUEST['api']))
    $smarty->assign($ke, $content[$key]);
}
$action = $_REQUEST["action"];

if ($action == '')
  $action = 'menu';

if ($action == 'results-scan') {
  $appid = getAppId($_REQUEST["appid"]);
  $steamid = getSteamId($_REQUEST["user"]);
  getMongoStats($steamid, $appid);

  $ach = getSteamApiAchievements($steamid, $appid, $_REQUEST["language"]);
  if ($ach->playerstats->success === false) {
    die($ach->playerstats->error);
  }
  include_once "scan/" . basename($appid) . ".php";
  $result = scan($ach->playerstats->achievements);
  if (isset($_REQUEST['api']) && $_REQUEST['api'] == 'json')
    echo json_encode($result, JSON_PRETTY_PRINT);
  else
    $smarty->assign("results", $result);
}
if ($action == 'results-scan-history') {
  $dir = "history/" . sanitize($_REQUEST["appid"]) . "/" . sanitize($_REQUEST["user"]);
  if (!is_dir($dir))
    die("No history");
  $history = array_diff(scandir($dir), array('..', '.'));
  $hist = array();
  foreach ($history as $val) {
    $split = explode(".", $val);
    array_push($hist, array("time" => $split[0], "language" => $split[1]));
  }
  $smarty->assign("history", $hist);
}
if ($action == 'results-stats') {
  $appid = getAppId($_REQUEST["appid"]);
  if (isset($_REQUEST['api']) && $_REQUEST['api'] == 'json') {
    $steamid = getSteamId($_REQUEST["user"]);
    $data = getMongoStats($steamid, $appid);
    ksortRecursive($data["achievements"]);
    ksortRecursive($data["stats"]);
    die(json_encode($data));
  } else
    $smarty->assign("results", getSteamApiStats(getSteamId($_REQUEST["user"]), $appid));
}

if ($action == 'results-common') {
  $filter = array();
  if ($_REQUEST["filter"]) {
    $filter = explode(" ", $_REQUEST["filter"]);
  }
  if ($_REQUEST["filters"]) {
    $filter = explode(" ", $_REQUEST["filters"]);
  }
  $i = 1;
  $users = array();
  $users_error = array();
  while (isset($_REQUEST["user" . $i]) && $_REQUEST["user" . $i] != "") {
    $user = getSteamId($_REQUEST["user" . $i]);
    if ($user != NULL)
      array_push($users, $user);
    else
      array_push($users_error, $_REQUEST["user" . $i]);
    $i += 1;
  }
  $appid = getAppId($_REQUEST["appid"]);
  if (!ctype_digit($appid)) {
    $action = "appid_not_found.tpl";
  } else {
    $results = getSteamCommonAchievements($filter, $users, $appid, $_REQUEST["language"], isset($_REQUEST["min"]) ? $_REQUEST["min"] : -1, isset($_REQUEST["max"]) ? $_REQUEST["max"] : -1);
    $query = $dbh->prepare("INSERT INTO `stats_visits` (`ip`, `appid`, `language`, `filters`) VALUES (:ip, :appid, :language, :filter)");
    $query->execute(array("ip" => $_SERVER['REMOTE_ADDR'], "appid" => $appid, "language" => $_REQUEST["language"], "filter" => json_encode($filter)));
    $id = $dbh->lastInsertId();
    $query = $dbh->prepare("INSERT INTO `stats_users` (`fk_stats_visits_id`, `steamid`) VALUES (:id, :steamid)");
    foreach ($users as $key => $steamid) {
      $query->execute(array("id" => $id, "steamid" => intval($steamid)));
    }
    $smarty->assign("results", $results["result"]);
    $smarty->assign("nogame", $results["nogame"]);
    $smarty->assign("users_error", $users_error);
  }
}
if (!isset($_REQUEST['api'])) {
  $smarty->display(basename($action) . ".tpl");
  $smarty->display("footer.tpl");
}
