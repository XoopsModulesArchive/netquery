<?php if (!defined('BB2_CORE')) die("I said no cheating!");
function bb2_insert_head()
{
    global $bb2_javascript;
    echo $bb2_javascript;
}
function bb2_read_settings()
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
    $bb_running = true;
    $bb_enabled = $moduleConfig['bb_enabled'];
    $bb_retention = $moduleConfig['bb_retention'];
    $bb_visible = $moduleConfig['bb_visible'];
    $bb_display_stats = $moduleConfig['bb_display_stats'];
    $bb_strict = $moduleConfig['bb_strict'];
    $bb_verbose = $moduleConfig['bb_verbose'];
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
function bb2_insert_stats($force = false)
{
    global $xoopsDB;
    $settings = bb2_read_settings();
    if ($force || $settings['display_stats']) {
        $query = "SELECT COUNT(*) FROM ".$xoopsDB->prefix('netquery_spamblocker')." WHERE bb_key NOT LIKE '00000000' ";
        if ($result = $xoopsDB->query($query))
        {
            list($count) = $xoopsDB->fetchrow($result);
            echo sprintf('<a href="http://www.bad-behavior.ioerror.us/">%1$s</a> %2$s <strong>%3$s</strong> %4$s %5$s %6$s</p>', 'Bad Behavior', 'has blocked', $count, 'access attempts in the last', $settings['log_retain'], 'days.');
        }
    }
}
function bb2_key_response($key)
{
    $response = array('response' => 0, 'explanation' => '', 'log' => '');
    include_once(BB2_CORE . '/responses.inc.php');
    if (is_callable('bb2_get_response')) $response = bb2_get_response($key);
    return $response;
}
function bb2_db_date()
{
    return gmdate('Y-m-d H:i:s');
}
function bb2_email()
{
    global $xoopsConfig;
    return $xoopsConfig['adminmail'];
}
function bb2_relative_path()
{
    return '/';
}
function bb2_db_escape($string)
{
    return addslashes($string);
}
function bb2_db_query($query)
{
    global $xoopsDB;
    if (!$result = $xoopsDB->queryF($query)) {
        return false;
    }
    return $result;
}
function bb2_db_num_rows($result)
{
    global $xoopsDB;
    return $xoopsDB->getRowsNum($result);
}
//
// Original functions.
//
if (!function_exists('stripos'))
{
    function stripos($haystack,$needle,$offset = 0)
    {
        return(strpos(strtolower($haystack),strtolower($needle),$offset));
    }
}
if (!function_exists('str_split'))
{
    function str_split($string, $split_length=1)
    {
        if ($split_length < 1) {
            return false;
        }

        for ($pos=0, $chunks = array(); $pos < strlen($string); $pos+=$split_length) {
            $chunks[] = substr($string, $pos, $split_length);
        }
        return $chunks;
    }
}
function uc_all($string)
{
    $temp = preg_split('/(\W)/', str_replace("_", "-", $string), -1, PREG_SPLIT_DELIM_CAPTURE);
    foreach ($temp as $key=>$word) {
        $temp[$key] = ucfirst(strtolower($word));
    }
    return join ('', $temp);
}
function match_cidr($addr, $cidr)
{
    $output = false;

    if (is_array($cidr)) {
        foreach ($cidr as $cidrlet) {
            if (match_cidr($addr, $cidrlet)) {
                $output = true;
            }
        }
    } else {
        @list($ip, $mask) = explode('/', $cidr);
        if (!$mask) $mask = 32;
        $mask = pow(2,32) - pow(2, (32 - $mask));
        $output = ((ip2long($addr) & $mask) == (ip2long($ip) & $mask));
    }
    return $output;
}
function bb2_load_headers()
{
    if (!is_callable('getallheaders')) {
        $headers = array();
        foreach($_SERVER as $name => $value)
            if(substr($name, 0, 5) == 'HTTP_')
                $headers[substr($name, 5)] = $value;
    } else {
        $headers = getallheaders();
    }
    return $headers;
}
?>