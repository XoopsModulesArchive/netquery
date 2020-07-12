<?php
function b_netquery_quick_show()
{
  global $xoopsDB;
  if (!is_object($GLOBALS['xoopsModule']) || $GLOBALS['xoopsModule']->getVar('dirname') != "netquery") {
    $modhandler = &xoops_gethandler('module');
    $NQModule = &$modhandler->getByDirname("netquery");
    $config_handler = &xoops_gethandler('config');
    $moduleConfig = &$config_handler->getConfigsByCat(0,$NQModule->getVar('mid'));
  } else {
    $moduleConfig =& $GLOBALS['xoopsModuleConfig'];
  }
  if (!defined("BB2_CWD")) define('BB2_CWD', XOOPS_ROOT_PATH.'/modules/netquery');
  require_once(BB2_CWD . "/include/spamblocker/version.inc.php");
  require_once(BB2_CWD . "/include/spamblocker/core.inc.php");
  include_once XOOPS_ROOT_PATH."/modules/netquery/include/nqSniff.class.php";
  $buttondir = ((list($testdir) = preg_split('/[._-]/', $moduleConfig['stylesheet'])) && (!empty($testdir)) && (file_exists(XOOPS_ROOT_PATH.'/modules/netquery/images/'.$testdir))) ? XOOPS_URL.'/modules/netquery/images/'.$testdir : XOOPS_URL.'/modules/netquery/images/wlbuttons';
  $bbsettings = getbbsettingsB();
  $client = new nqSniff();
  $geoip = getgeoipB(array('ip' => $client->property('ip')));
  if (countedB() != 'yes')
  {
    $bbstart = bb2_start($bbsettings);
    if ($_SESSION['NQcounted'] != 'yes' || $bbsettings['display_stats'] == 'pagehits')
    {
      $sql = "UPDATE ".$xoopsDB->prefix("netquery_geocc")." SET users = users + 1 WHERE cc = '".$geoip['cc']."'";
      $xoopsDB->queryF($sql);
      $_SESSION['NQcounted'] = 'yes';
    }
  }
  $block = array();
  $block['stylesheet'] = $moduleConfig['stylesheet'];
  $block['buttondir'] = $buttondir;
  $block['buttonstyle'] = 'color:#000000;background-color:#80A0B0;width:21px;height:22px;border:0;padding:0;margin:0 0 0 2px;vertical-align:top;';
  $block['geoflagstyle'] = 'height:20px;width:32px;border:0;padding:0;margin:2px 2px 0px 0px;vertical-align:middle;';
  $block['log_table'] = $bbsettings['log_table'];
  $block['log_retain'] = $bbsettings['log_retain'];
  $block['enabled'] = $bbsettings['enabled'];
  $block['running'] = $bbsettings['running'];
  $block['visible'] = $bbsettings['visible'];
  $block['display_stats'] = $bbsettings['display_stats'];
  $block['verbose'] = $bbsettings['verbose'];
  $block['strict'] = $bbsettings['strict'];
  $block['bbstats'] = getbbstatsB();
  $block['platform'] = $client->property('platform');
  $block['os'] = $client->property('os');
  $block['long_name'] = $client->property('long_name');
  $block['version'] = $client->property('version');
  $block['ua'] = $client->property('ua');
  $block['geocc'] = $geoip['cc'];
  $block['geocn'] = $geoip['cn'];
  $block['geolat'] = $geoip['lat'];
  $block['geolon'] = $geoip['lon'];
  $block['geoflag'] = $geoip['geoflag'];
  $block['mapping_site'] = $moduleConfig['mapping_site'];
  $block['topcountries_limit'] = $moduleConfig['topcountries_limit'];
  $block['countries'] = getcountriesB(array('numitems' => $block['topcountries_limit']));
  $block['whois_default'] = $moduleConfig['whois_default'];
  $block['links'] = getlinksB();
  $block['host'] = $_SERVER['REMOTE_ADDR'];
  $block['email'] = 'someone@'.gethostbyaddr($_SERVER['REMOTE_ADDR']);
  $block['httpurl'] = 'http://'.$_SERVER['SERVER_NAME'];
  $block['lang_running'] = _NQ_BLOCKER_RUNNING;
  $block['lang_screening'] = _NQ_BLOCKER_SCREENING;
  $block['lang_notrunning'] = _NQ_BLOCKER_NOTRUNNING;
  $block['lang_verbose'] = _NQ_BLOCKER_VERBOSE;
  $block['lang_strict'] = _NQ_BLOCKER_STRICT;
  $block['lang_blocked'] = _NQ_BLOCKER_BLOCKED;
  $block['lang_attempts'] = _NQ_BLOCKER_ATTEMPTS;
  $block['lang_logdays'] = _NQ_BLOCKER_LOGDAYS;
  $block['lang_clientip'] = _NQ_CLIENTIP;
  $block['lang_clientos'] = _NQ_CLIENTOS;
  $block['lang_clientbrowser'] = _NQ_CLIENTBROWSER;
  $block['lang_clientua'] = _NQ_CLIENTUA;
  $block['lang_whois'] = _NQ_WHOIS_DOMAIN;
  $block['lang_whoisip'] = _NQ_WHOISIP_IPAS;
  $block['lang_lookup'] = _NQ_LOOKUP_HOST;
  $block['lang_dig'] = _NQ_DIG_HOST;
  $block['lang_email'] = _NQ_EMAIL_ADDR;
  $block['lang_port'] = _NQ_PORTNUM;
  $block['lang_http'] = _NQ_HTTPURL;
  $block['lang_ping'] = _NQ_PING_HOST;
  $block['lang_trace'] = _NQ_TRACE_HOST;
  $block['lang_gobutton'] = _NQ_GOBUTTON_ALT;
  return $block;
}
function getbbstatsB()
{
  global $xoopsDB;
  $query = "SELECT COUNT(*) FROM ".$xoopsDB->prefix('netquery_spamblocker')." WHERE bb_key NOT LIKE '00000000' ";
  $result = $xoopsDB->query($query);
  list($bbstats) = $xoopsDB->fetchrow($result);
  return $bbstats;
}
function getbbsettingsB()
{
  global $xoopsDB;
  if (!is_object($GLOBALS['xoopsModule']) || $GLOBALS['xoopsModule']->getVar('dirname') != "netquery") {
    $modhandler = &xoops_gethandler('module');
    $BBModule = &$modhandler->getByDirname("netquery");
    $config_handler = &xoops_gethandler('config');
    $moduleConfig = &$config_handler->getConfigsByCat(0,$BBModule->getVar('mid'));
  } else {
    $moduleConfig =& $GLOBALS['xoopsModuleConfig'];
  }
  $bb_running = (defined('BB2_VERSION')) ? true : false;
  $bb_retention = $moduleConfig['bb_retention'];
  $bb_enabled = $moduleConfig['bb_enabled'];
  $bb_visible = $moduleConfig['bb_visible'];
  $bb_display_stats = $moduleConfig['bb_display_stats'];
  $bb_strict = $moduleConfig['bb_strict'];
  $bb_verbose = $moduleConfig['bb_verbose'];
  $bb_logging = $moduleConfig['bb_logging'];
  $bb_httpbl_key = $moduleConfig['bb_httpbl_key'];
  $bb_httpbl_threat = $moduleConfig['bb_httpbl_threat'];
  $bb_httpbl_maxage = $moduleConfig['bb_httpbl_maxage'];
  $settings = array('log_table' => $xoopsDB->prefix("netquery_spamblocker"),
                    'log_retain' => $bb_retention,
                    'enabled' => $bb_enabled,
                    'running' => $bb_running,
                    'visible' => $bb_visible,
                    'display_stats' => $bb_display_stats,
                    'strict' => $bb_strict,
                    'verbose' => $bb_verbose,
                    'logging' => $bb_logging,
                    'httpbl_key' => $bb_httpbl_key,
                    'httpbl_threat' => $bb_httpbl_threat,
                    'httpbl_maxage' => $bb_httpbl_maxage );
  return $settings;
}
function getgeoipB($args)
{
  global $xoopsDB;
  extract($args);
  if (!isset($ip)) {
    if (getenv('HTTP_CLIENT_IP')) {
      $ip = getenv('HTTP_CLIENT_IP');
    } elseif ($_SERVER['REMOTE_ADDR']) {
      $ip = $_SERVER['REMOTE_ADDR'];
    } else {
      $ip = getenv('REMOTE_ADDR');
    }
  }
  $ipnum = sprintf("%u", ip2long($ip));
  $sql = "SELECT * FROM ".$xoopsDB->prefix("netquery_geoip")." NATURAL JOIN ".$xoopsDB->prefix("netquery_geocc")." WHERE ".$ipnum." BETWEEN start AND end";
  $result = $xoopsDB->query($sql);
  $geoip = $xoopsDB->fetchArray($result);
  $flagfile = XOOPS_ROOT_PATH."/modules/netquery/images/geoflags/".$geoip['cc'].".gif";
  $flagdefault = XOOPS_ROOT_PATH."/modules/netquery/images/geoflags/blank.gif";
  if (file_exists($flagfile)) {$geoflag = XOOPS_URL."/modules/netquery/images/geoflags/".$geoip['cc'].".gif";}
  elseif (file_exists($flagdefault)) {$geoflag = XOOPS_URL."/modules/netquery/images/geoflags/blank.gif";}
  else {$geoflag = "";}
  $geoip['geoflag'] = $geoflag;
  return $geoip;
}
function getcountriesB($args)
{
  global $xoopsDB;
  extract($args);
  if ((!isset($numitems)) || (!is_numeric($numitems)))
  {
    $numitems = 1000000;
  }
  $countries = array();
  $sql = "SELECT * FROM ".$xoopsDB->prefix("netquery_geocc")." WHERE users > 0 ORDER BY users DESC LIMIT ".$numitems;
  $getcountries = $xoopsDB->query($sql);
  while ($x = $xoopsDB->fetchRow($getcountries))
  {
    list($ci, $cc, $cn, $lat, $lon, $users) = $x;
    $geoflag = "images/geoflags/".$cc.".gif";
    if (!file_exists($geoflag)) $geoflag = "images/geoflags/blank.gif";
    if (!file_exists($geoflag)) $geoflag = "";
    $countries[] = array('ci'=>$ci, 'cc'=>$cc, 'cn'=>$cn, 'lat'=>$lat, 'lon'=>$lon, 'users'=>$users, 'geoflag'=>$geoflag);
  }
  return $countries;
}
function getlinksB()
{
  global $xoopsDB;
  $links = array();
  if ($getlinks = $xoopsDB->query("SELECT * FROM ".$xoopsDB->prefix("netquery_whois")." ORDER BY whois_tld"))
  {
    while ($links[] = $xoopsDB->fetchArray($getlinks))
    {
    }
  }
  return $links;
}
function &countedB()
{
  static $counted;
  if (isset($counted)) $counted = 'yes';
  else $counted = 'no';
  return $counted;
}
?>