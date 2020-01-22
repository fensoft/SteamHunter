<?php
function filter($in, $out) {
  $processed_keys = array();
  foreach ($out as $key => $val)
    array_push($processed_keys, $val->apiname);
  $res = array();
  foreach ($in as $key => $val)
    if (!in_array($val->apiname, $processed_keys))
      array_push($res, $val);
  return $res;
}

function scan($ach) {
  $res = array();
  $odd = false;
  
  $whitelist = array();
  $structure = json_decode(file_get_contents("scan/218620.json"));
  if (json_last_error()) {
    die("JSON:" . json_last_error_msg());
  }
  foreach ($structure as $json) {
    if ($json->type == "timed_order") {
      $before = $after = null;
      if (isset($json->after)) {
        $ts = strptime($json->after, "%d/%m/%Y %H:%M:%S");
        $after = intval(mktime($ts['tm_hour'], $ts['tm_min'], $ts['tm_sec'], $ts['tm_mon'], $ts['tm_mday'], ($ts['tm_year'] + 1900)));
      }
      if (isset($json->before)) {
        $ts = strptime($json->before, "%d/%m/%Y %H:%M:%S");
        $before = intval(mktime($ts['tm_hour'], $ts['tm_min'], $ts['tm_sec'], $ts['tm_mon'], $ts['tm_mday'], ($ts['tm_year'] + 1900)));
      }
      $content = array_filter($ach, function($item) use ($json) { return in_array($item->apiname, $json->content); });
      $prev = false;
      $list = array();
      foreach ($content as $key => $val) {
        if ($prev && $val->unlocktime != 0 && $prev->unlocktime + intval($json->time) > $val->unlocktime && ($after == null || $val->unlocktime > $after) && ($before == null || $val->unlocktime < $before)) {
          $list[$content[$key]->apiname] = $list[$prev->apiname] = true;
        }
        $prev = $val;
      }
      $done = false;
      foreach ($content as $val)
        if (in_array($val->apiname, array_keys($list))) {
          $val->class = $json->class . '-' . ($odd ? 'odd' : 'even');
          $res[$val->apiname] = $val;
          $done = true;
        }
      if ($done)
        $odd = !$odd;
    } else if ($json->type == "whitelist_dupe") {
      foreach ($json->content as $val) {
        foreach ($json->content as $val2) {
          if (!isset($whitelist[$val]))
            $whitelist[$val] = array();
          if ($val != $val2 && isset($whitelist[$val]) && !in_array($val2, $whitelist[$val]))
            array_push($whitelist[$val], $val2);
        }
      }
    }
  }

  $dupes = array();
  foreach (array_group_by(filter($ach, $res), function($item) { return $item->unlocktime; }) as $key => $vals) {
    if ($key != 0 && count($vals) > 1) {
      $dup = array();
      $list = array();
      foreach ($vals as $key2 => $val)
        array_push($list, $val->apiname);
      foreach ($vals as $key2 => $val) {
        if (isset($whitelist[$val->apiname])) {
          foreach ($whitelist[$val->apiname] as $zeval) {
            if (($key = array_search($zeval, $list)) !== false)
              unset($list[$key]);
          }
        }
      }
      if (count($list) > 1) {
        $odd = !$odd;
        foreach ($vals as $key2 => $val) {
          $code = true;
          if (in_array($val->apiname, $list)) {
            array_push($dup, $val->apiname);
          }
        }
        $code = true;
        foreach ($vals as $key2 => $val) {
          if (in_array($val->apiname, $list)) {
            $val->class = 'cheat-warning-' . ($odd ? 'odd' : 'even');
            if ($code) {
              $val->code = json_encode(array("owner" => $_REQUEST["user"], "type" => "whitelist_dupe", "content" => $dup));
              $code = false;
            }
            $res[$val->apiname] = $val;
          }
        }
      }
      
      if (count($dup) > 1)
        array_push($dupes, array("owner" => $_REQUEST["user"], "type" => "whitelist_dupe", "content" => $dup));
    }
  }

  foreach (filter($ach, $res) as $key => $val) {
    if ($val->unlocktime != 0) {
      $val->class = 'cheat-ok';
      $res[$val->apiname] = $val;
    } else {
      $res[$val->apiname] = $val;
    }
  }
  return $res;
}
