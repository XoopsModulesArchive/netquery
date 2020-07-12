<?php
// ------------------------------------------------------------------------- //
// Original Author : Richard Virtue - http://virtech.org/
// Licence Type : Public GNU/GPL
// ------------------------------------------------------------------------- //
include '../../mainfile.php';
if (file_exists("language/".$xoopsConfig['language']."/main.php"))
{
  include_once "language/".$xoopsConfig['language']."/main.php";
}
else
{
  include_once "language/english/main.php";
}
include_once('include/nqTimer.class.php');
include_once('include/nqSniff.class.php');
if (!function_exists('checkdnsrr'))
{
  function checkdnsrr($host, $type = '')
  {
    global $digexec_local;
    if(!empty($host))
    {
      if($type == '') $type = "MX";
      $output = '';
      $k = '';
      $line = '';
      @exec("$digexec_local -type=$type $host", $output);
      $pattern = "/^$host/i";
      while(list($k, $line) = each($output))
      {
        if(preg_match($pattern, $line)) return true;
      }
      return false;
    }
  }
}
if (!function_exists('getmxrr'))
{
  function getmxrr($hostname, &$mxhosts)
  {
    global $digexec_local;
    if (!is_array($mxhosts)) $mxhosts = array();
    if (!empty($hostname ))
    {
      $output = '';
      $ret = '';
      $k = '';
      $line = '';
      @exec("$digexec_local -type=MX $hostname", $output, $ret);
      while (list($k, $line) = each($output))
      {
        if (preg_match("/^$hostname\tMX preference = ([0-9]+), mail exchanger = (.*)$/", $line, $parts))
        {
          $mxhosts[$parts[1]]=$parts[2];
        }
      }
      if (count($mxhosts))
      {
        reset($mxhosts);
        ksort($mxhosts);
        $i = 0;
        while (list($pref,$host) = each($mxhosts))
        {
          $mxhosts2[$i] = $host;
          $i++;
        }
        $mxhosts = $mxhosts2;
        return true;
      }
      else
      {
        return false;
      }
    }
  }
}
function sanitizeSysString($string, $min = '', $max = '')
{
  $pattern = '/(;|\||`|>|<|&|^|"|'."\n|\r|'".'|{|}|[|]|\)|\()/i';
  $string = preg_replace($pattern, '', $string);
  $string = preg_replace('/\$/', '\\\$', $string);
  $len = strlen($string);
  if((($min != '') && ($len < $min)) || (($max != '') && ($len > $max))) return false;
  return $string;
}
function getgeoip($args)
{
    global $xoopsDB;
    extract($args);
    if (!isset($ip))
    {
      if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
      {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
      }
      elseif (isset($_SERVER['HTTP_CLIENT_IP']))
      {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
      }
      else
      {
        $ip = $_SERVER['REMOTE_ADDR'];
      }
    }
    $ipnum = sprintf("%u", ip2long($ip));
    $sql = "SELECT cc, cn, lat, lon FROM ".$xoopsDB->prefix("netquery_geoip")." NATURAL JOIN ".$xoopsDB->prefix("netquery_geocc")." WHERE ".$ipnum." BETWEEN start AND end";
    $getgeoip = $xoopsDB->query($sql);
    $geoip = $xoopsDB->fetchArray($getgeoip);
    $geoflag = "images/geoflags/".$geoip['cc'].".gif";
    if (!file_exists($geoflag)) $geoflag = "images/geoflags/blank.gif";
    if (!file_exists($geoflag)) $geoflag = "";
    $geoip['geoflag'] = $geoflag;
    return $geoip;
}
function getcountries($args)
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
function whois($args)
{
    extract($args);
    $msg = '';
    $readbuf = '';
    $nextServer = '';
    if (! $sock = @fsockopen($whois_server, 43, $errnum, $error, 10))
    {
        unset($sock);
        $msg .= "Cannot connect to ".$whois_server." (".$error.")";
    }
    else
    {
        fputs($sock, $target."\r\n");
        while (!feof($sock))
        {
            $readbuf .= fgets($sock, 10240);
        }
        @fclose($sock);
    }
    if (!preg_match("/Whois Server:/i", $readbuf))
    {
        $pattern = "/$whois_unfound/i";
        if (! empty($whois_unfound) && preg_match($pattern, $readbuf))
        {
            $msg .= "<span class=\"nq-red\">NOT FOUND</span>: No match for $target<br />";
        }
    }
    else
    {
        $readbuf = explode("\n", $readbuf);
        for ($i=0; $i<sizeof($readbuf); $i++)
        {
            if (preg_match("/Whois Server:/i", $readbuf[$i])) $readbuf = $readbuf[$i];
        }
        $nextServer = substr($readbuf, 17, (strlen($readbuf)-17));
        $nextServer = str_replace("1:Whois Server:", "", trim(rtrim($nextServer)));
        $readbuf = "";
        if (! $sock = @fsockopen($nextServer, 43, $errnum, $error, 10))
        {
            unset($sock);
            $msg .= "Cannot connect to ".$nextServer." (".$error.")";
        }
        else
        {
            fputs($sock, $target."\r\n");
            while (!feof($sock))
            {
                $readbuf .= fgets($sock, 10240);
            }
            @fclose($sock);
        }
    }
    $msg .= nl2br($readbuf);
    return $msg;
}
function whoisip($args)
{
    extract($args);
    $msg = '';
    $readbuf = '';
    $nextServer = '';
    $whois_server = "whois.arin.net";
    if (!$target = gethostbyname($target))
    {
        $msg .= "IP Whois requires an IP address.";
    }
    else
    {
        if (! $sock = @fsockopen($whois_server, 43, $errnum, $error, 10))
        {
            unset($sock);
            $msg .= "Cannot connect to ".$whois_server." (".$error.")";
        }
        else
        {
            fputs($sock, $target."\r\n");
            while (!feof($sock))
            {
                $readbuf .= fgets($sock, 10240);
            }
            @fclose($sock);
        }
        if (preg_match("/whois.apnic.net/i", $readbuf)) $nextServer = "whois.apnic.net";
        else if (preg_match("/whois.ripe.net/i", $readbuf)) $nextServer = "whois.ripe.net";
        else if (preg_match("/whois.lacnic.net/i", $readbuf)) $nextServer = "whois.lacnic.net";
        else if (preg_match("/whois.registro.br/i", $readbuf)) $nextServer = "whois.registro.br";
        else if (preg_match("/whois.afrinic.net/i", $readbuf)) $nextServer = "whois.afrinic.net";
        if ($nextServer)
        {
            $readbuf = "";
            if (! $sock = @fsockopen($nextServer, 43, $errnum, $error, 10))
            {
                unset($sock);
                $msg .= "Cannot connect to ".$nextServer." (".$error.")";
            }
            else
            {
                fputs($sock, $target."\r\n");
                while (!feof($sock))
                {
                    $readbuf .= fgets($sock, 10240);
                }
                @fclose($sock);
            }
        }
        $readbuf = str_replace(" ", "&nbsp;", $readbuf);
        $msg .= nl2br($readbuf);
    }
    return $msg;
}
function validemail($args)
{
    global $query_email_server;
    extract($args);
    $msg = '';
    list ($username,$domain) = explode("@",$target,2);
    if (checkdnsrr($domain.'.', 'MX') ) $addmsg = "<br />DNS Record Check: MX record returned OK.";
    else if (checkdnsrr($domain.'.', 'A') ) $addmsg = "<br />DNS Record Check: A record returned OK.";
    else if (checkdnsrr($domain.'.', 'CNAME') ) $addmsg = "<br />DNS Record Check: CNAME record returned OK.";
    else $addmsg = "<br />DNS Record Check: DNS record not returned.)";
    $msg .= $addmsg;
    if ($query_email_server)
    {
        if (getmxrr($domain, $mxhost))
        {
            $address = $mxhost[0];
        }
        else
        {
            $address = $domain;
        }
        $addmsg = "<br />MX Server Address Check: Address accepted by ".$address;
        if (!$sock = @fsockopen($address, 25, $errnum, $error, 10))
        {
            unset($sock);
            $addmsg = "<br />MX Server Address Check: Cannot connect to ".$address." (".$error.")";
        }
        else
        {
            if (preg_match("/^220/", $out = fgets($sock, 1024)))
            {
                fputs ($sock, "HELO ".$_SERVER['HTTP_HOST']."\r\n");
                $out = fgets ( $sock, 1024 );
                fputs ($sock, "MAIL FROM: <{$target}>\r\n");
                $from = fgets ( $sock, 1024 );
                fputs ($sock, "RCPT TO: <{$target}>\r\n");
                $to = fgets ($sock, 1024);
                fputs ($sock, "QUIT\r\n");
                fclose($sock);
                if (!preg_match("/^250/", $from) || !preg_match("/^250/", $to ))
                {
                    $addmsg = "<br />MX Server Address Check: Address rejected by ".$address;
                }
            }
            else
            {
                $addmsg = "<br />MX Server Address Check: No response from ".$address;
            }
        }
        $msg .= $addmsg;
    }
    return $msg;
}
function bb2_settings()
{
    global $xoopsDB, $xoopsModuleConfig;
    $bb_running = (defined('BB2_VERSION')) ? true : false;
    $bb_retention = $xoopsModuleConfig['bb_retention'];
    $bb_enabled = $xoopsModuleConfig['bb_enabled'];
    $bb_visible = $xoopsModuleConfig['bb_visible'];
    $bb_display_stats = $xoopsModuleConfig['bb_display_stats'];
    $bb_strict = $xoopsModuleConfig['bb_strict'];
    $bb_verbose = $xoopsModuleConfig['bb_verbose'];
    $settings = array('log_table' => $xoopsDB->prefix("netquery_spamblocker"),
                      'log_retain' => $bb_retention,
                      'enabled' => $bb_enabled,
                      'running' => $bb_running,
                      'visible' => $bb_visible,
                      'display_stats' => $bb_display_stats,
                      'strict' => $bb_strict,
                      'verbose' => $bb_verbose );
    return $settings;
}
function bb2_stats()
{
    global $xoopsDB;
    $query = "SELECT COUNT(*) FROM ".$xoopsDB->prefix('netquery_spamblocker')." WHERE bb_key NOT LIKE '00000000' ";
    $result = $xoopsDB->query($query);
    list($bbstats) = $xoopsDB->fetchrow($result);
    return $bbstats;
}
$timer = new nqTimer();
$timer->start('main');
$client = new nqSniff();
$geoip = getgeoip(array('ip' => $client->property('ip')));
foreach ($xoopsModuleConfig as $configkey => $configvar)
{
    $$configkey = $configvar;
}
$xoops_module_header = '<link rel="stylesheet" type="text/css" href="'.XOOPS_URL.'/modules/netquery/styles/'.$stylesheet.'" />';
$buttondir = ((list($testdir) = preg_split('/[._-]/', $stylesheet)) && (!empty($testdir)) && (file_exists(XOOPS_ROOT_PATH.'/modules/netquery/images/'.$testdir))) ? XOOPS_URL.'/modules/netquery/images/'.$testdir : XOOPS_URL.'/modules/netquery/images/wlbuttons';
if ($getlinks = $xoopsDB->query("SELECT * FROM ".$xoopsDB->prefix("netquery_whois")." ORDER BY whois_tld"))
{
    $links = array();
    while ($links[] = $xoopsDB->fetchArray($getlinks))
    {
    }
}
else
{
    echo "Could not retrieve whois lookup links from the database.";
}
if ($getlgrouters = $xoopsDB->query("SELECT * FROM ".$xoopsDB->prefix("netquery_lgrouter")." WHERE router != 'default' ORDER BY router_id"))
{
    $lgrouters = array();
    while ($lgrouters[] = $xoopsDB->fetchArray($getlgrouters))
    {
    }
}
else
{
    echo "Could not retrieve looking glass routers settings from the database.";
}
if ($getlgdefault = $xoopsDB->query("SELECT * FROM ".$xoopsDB->prefix("netquery_lgrouter")." WHERE router = 'default'"))
{
    $lgdefault = $xoopsDB->fetchArray($getlgdefault);
}
else
{
    echo "Could not retrieve looking glass default settings from the database.";
}
$results   = '';
$hlpfile="docs/manual.html";
$winsys = (DIRECTORY_SEPARATOR == '\\');
$maxpoptions = array(4, 5, 6, 7, 8, 9, 10);
$maxtoptions = array(10, 20, 30, 40, 50, 60);
$httpoptions = array('HEAD', 'GET');
$digoptions = array();
  $digoptions[] = array('name' => 'ANY', 'value' => 'ANY');
  $digoptions[] = array('name' => 'Mail eXchanger', 'value' => 'MX');
  $digoptions[] = array('name' => 'Start Of Authority', 'value' => 'SOA');
  $digoptions[] = array('name' => 'Name Servers', 'value' => 'NS');
$lgrequests = array();
  $lgrequests[] = array('request' => 'IPv4 OSPF neighborship', 'command' => 'show ip ospf neighbor', 'handler' => 'ospfd', 'argc' => '0');
  $lgrequests[] = array('request' => 'IPv4 BGP neighborship', 'command' => 'show ip bgp summary', 'handler' => 'bgpd', 'argc' => '0');
  $lgrequests[] = array('request' => 'IPv4 OSPF RT', 'command' => 'show ip ospf route', 'handler' => 'ospfd', 'argc' => '0');
  $lgrequests[] = array('request' => 'IPv4 BGP RR to...', 'command' => 'show ip bgp', 'handler' => 'bgpd', 'argc' => '1');
  $lgrequests[] = array('request' => 'IPv4 any RR to...', 'command' => 'show ip route', 'handler' => 'zebra', 'argc' => '1');
  $lgrequests[] = array('request' => 'Interface info on...', 'command' => 'show interface', 'handler' => 'zebra', 'argc' => '1');
  $lgrequests[] = array('request' => 'IPv6 OSPF neighborship', 'command' => 'show ipv6 ospf neighbor', 'handler' => 'ospf6d', 'argc' => '0');
  $lgrequests[] = array('request' => 'IPv6 BGP neighborship', 'command' => 'show ipv6 bgp summary', 'handler' => 'ripngd', 'argc' => '0');
  $lgrequests[] = array('request' => 'IPv6 OSPF RT', 'command' => 'show ipv6 ospf route', 'handler' => 'ospf6d', 'argc' => '0');
  $lgrequests[] = array('request' => 'IPv6 BGP route to...', 'command' => 'show ipv6 bgp', 'handler' => 'ripngd', 'argc' => '1');
  $lgrequests[] = array('request' => 'IPv6 any route to...', 'command' => 'show ipv6 route', 'handler' => 'zebra', 'argc' => '1');
$wiexample = 'example';
$j = 1;
while ($j <= $whois_max_limit)
{
  $dom = "domain_".$j;
  $tld = "whois_tld_".$j;
  $domain[$j]    = (isset($_POST[$dom])) ? $_POST[$dom] : $wiexample;
  $whois_tld[$j] = (isset($_POST[$tld])) ? $_POST[$tld] : $whois_default;
  $wiexample = '';
  $j++;
}
$maxp      = (isset($_POST['maxp'])) ? $_POST['maxp'] : '4';
$maxt      = (isset($_POST['maxt'])) ? $_POST['maxt'] : '30';
$host      = (isset($_POST['host'])) ? $_POST['host'] : $_SERVER['REMOTE_ADDR'];
$email     = (isset($_POST['email'])) ? $_POST['email'] : 'someone@'.gethostbyaddr($_SERVER['REMOTE_ADDR']);
$server    = (isset($_POST['server'])) ? $_POST['server'] : 'None';
$portnum   = (isset($_POST['portnum'])) ? $_POST['portnum'] : '80';
$httpurl   = (isset($_POST['httpurl'])) ? $_POST['httpurl'] : 'http://'.$_SERVER['SERVER_NAME']."/";
$httpreq   = (isset($_POST['httpreq'])) ? $_POST['httpreq'] : 'HEAD';
$request   = (isset($_POST['request'])) ? $_POST['request'] : '1';
$lgparam   = (isset($_POST['lgparam'])) ? $_POST['lgparam'] : '';
$digparam  = (isset($_POST['digparam'])) ? $_POST['digparam'] : 'ANY';
$router    = (isset($_POST['router'])) ? $_POST['router'] : 'ATT Public';
$querytype = (isset($_REQUEST['querytype'])) ? $_REQUEST['querytype'] : 'none';
$formtype  = (isset($_REQUEST['formtype'])) ? $_REQUEST['formtype'] : $querytype;
if ($formtype == 'none' || $formtype == 'countries') {$formtype = $querytype_default;}
if (isset($_REQUEST['b1_x'])) {$formtype = 'whois'; $querytype = 'none';}
if (isset($_REQUEST['b2_x'])) {$formtype = 'whoisip'; $querytype = 'none';}
if (isset($_REQUEST['b3_x'])) {$formtype = 'lookup'; $querytype = 'none';}
if (isset($_REQUEST['b4_x'])) {$formtype = 'dig'; $querytype = 'none';}
if (isset($_REQUEST['b5_x'])) {$formtype = 'port'; $querytype = 'none';}
if (isset($_REQUEST['b6_x'])) {$formtype = 'http'; $querytype = 'none';}
if (isset($_REQUEST['b7_x'])) {$formtype = 'ping'; $querytype = 'none';}
if (isset($_REQUEST['b8_x'])) {$formtype = 'pingrem'; $querytype = 'none';}
if (isset($_REQUEST['b9_x'])) {$formtype = 'trace'; $querytype = 'none';}
if (isset($_REQUEST['b10_x'])) {$formtype = 'tracerem'; $querytype = 'none';}
if (isset($_REQUEST['b11_x'])) {$formtype = 'lgquery'; $querytype = 'none';}
if (isset($_REQUEST['b12_x'])) {$formtype = 'email'; $querytype = 'none';}
$b1class   = ($formtype == 'whois') ? 'inset' : 'outset';
$b2class   = ($formtype == 'whoisip') ? 'inset' : 'outset';
$b3class   = ($formtype == 'lookup') ? 'inset' : 'outset';
$b4class   = ($formtype == 'dig') ? 'inset' : 'outset';
$b5class   = ($formtype == 'port') ? 'inset' : 'outset';
$b6class   = ($formtype == 'http') ? 'inset' : 'outset';
$b7class   = ($formtype == 'ping') ? 'inset' : 'outset';
$b8class   = ($formtype == 'pingrem') ? 'inset' : 'outset';
$b9class   = ($formtype == 'trace') ? 'inset' : 'outset';
$b10class  = ($formtype == 'tracerem') ? 'inset' : 'outset';
$b11class  = ($formtype == 'lgquery') ? 'inset' : 'outset';
$b12class  = ($formtype == 'email') ? 'inset' : 'outset';
switch(strtolower($querytype))
{
  case "whois":
      $msg = ('<table class="nqoutput">');
      $j = 1;
      while ($j <= $whois_max_limit && !empty($domain[$j]))
      {
          $getlink = $xoopsDB->query("SELECT * FROM ".$xoopsDB->prefix("netquery_whois")." WHERE whois_tld = '$whois_tld[$j]'");
          $link = $xoopsDB->fetchArray($getlink);
          $whois_server = $link['whois_server'];
          $whois_prefix = $link['whois_prefix'];
          $whois_suffix = $link['whois_suffix'];
          $whois_unfound = $link['whois_unfound'];
          $target = $domain[$j].'.'.$whois_tld[$j];
          if (! empty($whois_prefix)) $target = $whois_prefix.' '.$target;
          if (! empty($whois_suffix)) $target = $target.' '.$whois_suffix;
          $msg .= ('<tr><th>'._NQ_WHOIS_ALT.' '._NQ_RESULT.' '.$j.' [<a href="index.php?formtype=whois">'._NQ_CLEAR.'</a>]:</th></tr><tr><td>');
          $msg .= whois(array('target' => $target, 'whois_server' => $whois_server, 'whois_unfound' => $whois_unfound));
          $msg .= '</td></tr>';
          $j++;
      }
      $msg .= '</table><hr />';
      $results .= $msg;
      break;
  case "whoisip":
      $target = $host;
      $msg = ('<table class="nqoutput">');
      $msg .= ('<tr><th>'._NQ_WHOISIP_ALT.' '._NQ_RESULT.' [<a href="index.php?formtype=whoisip">'._NQ_CLEAR.'</a>]:</th></tr><tr><td>');
      $msg .= whoisip(array('target' => $target));
      $msg .= '</td></tr></table><hr />';
      $results .= $msg;
      break;
  case "lookup":
      $target = $host;
      $msg = ('<table class="nqoutput">');
      $msg .= ('<tr><th>'._NQ_LOOKUP_ALT.' '._NQ_RESULT.' [<a href="index.php?formtype=lookup">'._NQ_CLEAR.'</a>]:</th></tr><tr><td>');
      $msg .= $target.' resolved to ';
      if (preg_match("/[a-zA-Z]/", $target))
      {
          $ipaddr = gethostbyname($target);
          $geoipc = getgeoip(array('ip' => $ipaddr));
          $msg .= $ipaddr." [".$geoipc['cn']."]";
      }
      else
      {
          $geoipc = getgeoip(array('ip' => $target));
          $ipname = gethostbyaddr($target);
          $msg .= $ipname." [".$geoipc['cn']."]";
      }
      if (!empty($geoipc['geoflag'])) $msg .= " <img class=\"geoflag\" src=\"".$geoipc['geoflag']."\" />";
      $msg .= '</td></tr></table><hr />';
      $results .= $msg;
      break;
  case "dig":
      $target = sanitizeSysString($host);
      $msg = ('<table class="nqoutput">');
      $msg .= ('<tr><th>'._NQ_DIG_ALT.' '._NQ_RESULT.' [<a href="index.php?formtype=dig">'._NQ_CLEAR.'</a>]:</th></tr><tr><td>');
      if (preg_match("/[a-zA-Z]/", $target))
          $ntarget = gethostbyname($target);
      else
          $ntarget = gethostbyaddr($target);
      if (! preg_match("/[a-zA-Z]/", $target) && !preg_match("/[a-zA-Z]/", $ntarget))
      {
          $msg .= 'DNS query (Dig) requires a hostname.';
      }
      else
      {
          if (! preg_match("/[a-zA-Z]/", $target) ) $target = $ntarget;
          if ($winsys)
          {
              if (@exec("$digexec_local -type=$digparam $target", $output, $ret))
                  while (list($k, $line) = each($output))
                  {
                    $msg .= $line.'<br />';
                  }
              else
                  $msg .= "The <i>nslookup</i> command is not working on your system.";
          }
          else
          {
              if (! $msg .= trim(nl2br(`$digexec_local $digparam '$target'`)))
                  $msg .= "The <i>dig</i> command is not working on your system.";
          }
      }
      $msg .= '</td></tr></table><hr />';
      $results .= $msg;
      break;
  case "email":
      $target = $email;
      $msg = ('<table class="nqoutput">');
      $msg .= ('<tr><th>'._NQ_EMAIL_ALT.' '._NQ_RESULT.' [<a href="index.php?formtype=email">'._NQ_CLEAR.'</a>]:</th></tr><tr><td>');
      if ((preg_match('/(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)/', $target)) || (preg_match('/^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,3}|[0-9]{1,3})(\]?)$/',$target)))
      {
          $addmsg = "Format Check: Correct format.";
          $msg .= $addmsg;
          if (!$winsys || $dns_dig_enabled)
          {
              $msg .= validemail(array('target' => $target));
          }
      }
      else
      {
        $addmsg = "Format check: Incorrect format.";
        $msg .= $addmsg;
      }
      $msg .= '</td></tr></table><hr />';
      $results .= $msg;
      break;
  case "port":
      $target = $server;
      $tport = $portnum;
      if ($getportdata = $xoopsDB->query("SELECT * FROM ".$xoopsDB->prefix("netquery_ports")." WHERE flag < 99 AND port = $tport")) {
          $portdata = array();
          while ( $portdata[] = $xoopsDB->fetchArray($getportdata) )
          {
          }
      }
      else
      {
          echo "Could not retrieve port lookup data from the database.";
      }
      $msg = ('<table class="nqoutput">');
      $msg .= ('<tr><th colspan="3">'._NQ_PORT_ALT.' '.$tport.' '._NQ_RESULT.' [<a href="javascript:NQpopup(\'http://isc.sans.org/port_details.php?port='.$tport.'\');">'._NQ_DETAIL.'</a>]');
      if ($user_submissions) $msg .= (' [<a href="submit.php?portnum='.$tport.'">'._NQ_SUBMIT.'</a>]');
      $msg .= (' [<a href="index.php?formtype=port">'._NQ_CLEAR.'</a>]:<br />');
      if (!empty($target) && $target != 'None')
      {
          if (! $sock = @fsockopen($target, $tport, $errnum, $error, 10))
          {
              $msg .= 'Port '.$tport.' does not appear to be open.';
          }
          else
          {
              $msg .= 'Port '.$tport.' is open and accepting connections.';
              @fclose($sock);
          }
      }
      else
      {
          $msg .= "No host specified for port check.";
      }
      $msg .= '</th></tr>';
      $msg .= '<tr><th>Protocol</th><th>Service/Exploit</th><th>Notes (Click to Search)</th></tr>';
      foreach($portdata as $portdatum)
      {
        if (!empty($portdatum['protocol']))
        {
          $getflagdata = $xoopsDB->query("SELECT * FROM ".$xoopsDB->prefix("netquery_flags")." WHERE flagnum = ".$portdatum['flag']);
          $flagdata = $xoopsDB->fetchArray($getflagdata);
          $notes = '<span class="nq-'.$flagdata['fontclr'].'">['.$flagdata['keyword'].']</span> <a href="javascript:NQpopup(\''.$flagdata['lookup_1'].$portdatum['comment'].'\');">'.$portdatum['comment'].'</a>';
          $msg .= '<tr><td>'.$portdatum['protocol'].'</td><td>'.$portdatum['service'].'</td><td>'.$notes.'</td></tr>';
        }
      }
      $msg .= '</table><hr />';
      $results .= $msg;
      break;
  case "http":
      $readbuf = '';
      $url_Complete = parse_url($httpurl);
      $url_Scheme   = (!empty ($url_Complete["scheme"])) ? $url_Complete["scheme"] : "http";
      $url_Host     = (!empty ($url_Complete["host"])) ? $url_Complete["host"] : "localhost";
      $url_Port     = (!empty ($url_Complete["port"])) ? $url_Complete["port"] : "80";
      $url_User     = (!empty ($url_Complete["user"])) ? $url_Complete["user"] : "";
      $url_Pass     = (!empty ($url_Complete["pass"])) ? $url_Complete["pass"] : "";
      $url_Path     = (!empty ($url_Complete["path"])) ? $url_Complete["path"] : "/";
      $url_Query    = (!empty ($url_Complete["query"])) ? ":".$url_Complete["query"] : "";
      $url_Fragment = (!empty ($url_Complete["fragment"])) ? $url_Complete["fragment"] : "";
      $url_HostPort = ($url_Port != 80) ? $url_Host.":".$url_Port : $url_Host;
      $url_Long     = $url_Scheme . "://" . $url_Host;
      $url_Req      = $url_Path . $url_Query;
      $fp_Send      = $httpreq . " $url_Req HTTP/1.0\n";
      $fp_Send     .= "Host: $url_Host\n";
      $fp_Send     .= "User-Agent: Netquery/1.2 PHP/" . phpversion() . "\n";
      $target = $url_Host;
      $msg = ('<table class="nqoutput">');
      $msg .= ('<tr><th>'._NQ_HTTP_ALT.' '._NQ_RESULT.' [<a href="index.php?formtype=http">'._NQ_CLEAR.'</a>]:</th></tr><tr><td><pre>');
      if (! $sock = @fsockopen($url_Host, $url_Port, $errnum, $error, 10))
      {
          unset($sock);
          $msg .= "Cannot connect to host: ".$url_Host." port: ".$url_Port." (".$error.")";
      }
      else
      {
          fputs($sock, "$fp_Send\n");
          while (!feof($sock))
          {
              $readbuf .= fgets($sock, 10240);
          }
          @fclose($sock);
          $msg .= htmlspecialchars($readbuf);
      }
      $msg .= '</pre></td></tr></table><hr />';
      $results .= $msg;
      break;
  case "ping":
      $png = '';
      $target = sanitizeSysString($host);
      $tpoints = $maxp;
      $msg = ('<table class="nqoutput">');
      $msg .= ('<tr><th>'._NQ_PING_ALT.' '._NQ_RESULT.' [<a href="index.php?formtype=ping">'._NQ_CLEAR.'</a>]:</th></tr><tr><td>');
      if ($winsys) {$PN=$pingexec_local.' -n '.$tpoints.' '.$target;}
      else {$PN=$pingexec_local.' -c '.$tpoints.' '.$target;}
      exec($PN, $response, $rval);
      for ($i = 0; $i < count($response); $i++)
      {
          $png .= $response[$i].'<br />';
      }
      if (! $msg .= trim(nl2br($png)))
      {
          $msg .= 'Ping failed. You may need to configure your server permissions.';
      }
      $msg .= '</td></tr></table><hr />';
      $results .= $msg;
      break;
  case "pingrem":
      $target = $host;
      break;
  case "trace":
      $rt = '';
      $target = sanitizeSysString($host);
      $tpoints = $maxt;
      $msg = ('<table class="nqoutput">');
      $msg .= ('<tr><th>'._NQ_TRACE_ALT.' '._NQ_RESULT.' [<a href="index.php?formtype=trace">'._NQ_CLEAR.'</a>]:</th></tr><tr><td>');
      if ($winsys) {$TR=$traceexec_local.' -h '.$tpoints.' '.$target;}
      else {$TR=$traceexec_local.' -m '.$tpoints.' '.$target;}
      exec($TR, $response, $rval);
      for ($i = 0; $i < count($response); $i++)
      {
          $rt .= $response[$i].'<br />';
      }
      if (! $msg .= trim(nl2br($rt)))
      {
          $msg .= 'Traceroute failed. You may need to configure your server permissions.';
      }
      $msg .= '</td></tr></table><hr />';
      $results .= $msg;
      break;
  case "tracerem":
      $target = $host;
      break;
  case "lgquery":
      $target = $router;
      $lgrequest = $lgrequests[$request];
      if ($getlgrouter = $xoopsDB->query("SELECT * FROM ".$xoopsDB->prefix("netquery_lgrouter")." WHERE router = '".$router."'"))
      {
          $lgrouter = $xoopsDB->fetchArray($getlgrouter);
      }
      else
      {
           echo "Could not retrieve looking glass router settings from the database.";
      }
      $readbuf = '';
      $lgaddress  = $lgrouter['address'];
      $lgport     = ($lgrouter[$lgrequest['handler'] . '_port'] > 0) ? $lgrouter[$lgrequest['handler'] . '_port'] : $lgdefault[$lgrequest['handler'] . '_port'];
      $lgcommand  = $lgrequest['command'] . (!empty ($lgparam) ? (" " . htmlentities(substr($lgparam,0,50))) : "");
      $lghandler  = (($lgrouter[$lgrequest['handler']]) && ($lgdefault[$lgrequest['handler']]));
      $lgargc     = ((($lgrouter['use_argc']) && ($lgdefault['use_argc'])) || (!$lgrequest['argc']));
      $lgusername = (!empty ($lgrouter['username'])) ? $lgrouter['username'] : $lgdefault['username'];
      if (!empty ($lgrouter[$lgrequest['handler'] . '_password']))
      {
          $lgpassword = $lgrouter[$lgrequest['handler'] . '_password'];
      }
      else if (!empty ($lgdefault[$lgrequest['handler'] . '_password']))
      {
          $lgpassword = $lgdefault[$lgrequest['handler'] . '_password'];
      }
      else if (!empty ($lgrouter['password']))
      {
          $lgpassword = $lgrouter['password'];
      }
      else
      {
          $lgpassword = $lgdefault['password'];
      }
      $msg = ('<table class="nqoutput">');
      $msg .= ('<tr><th>'._NQ_LGQUERY_ALT.' '._NQ_RESULT.' [<a href="index.php?formtype=lgquery">'._NQ_CLEAR.'</a>]:</th></tr><tr><td>');
      if (!$lghandler)
      {
          $msg .= 'This '.$lgrequest['request'].' request is not permitted for '.$lgrouter['router'].':'.$lgport.' by administrator.';
      }
      else if (!$lgargc)
      {
          $msg .= 'Full table view is not permitted on this router.';
      }
      else if (!$sock = @fsockopen($lgaddress, $lgport, $errnum, $error, 10))
      {
          unset($sock);
          $msg .= "Cannot connect to router ".$lgaddress.":".$lgport." (".$error.")";
      }
      else
      {
          socket_set_timeout ($sock, 5);
          if (!empty ($lgusername)) fputs ($sock, "{$lgusername}\n");
          if (!empty ($lgpassword))
              fputs ($sock, "{$lgpassword}\nterminal length 0\n{$lgcommand}\n");
          else
              fputs ($sock, "terminal length 0\n{$lgcommand}\n");
          if (empty ($lgparam) && $lgargc > 0) sleep (2);
          fputs ($sock, "quit\n");
          while (!feof ($sock))
          {
              $readbuf .= fgets ($sock, 256);
          }
          $start = strpos ($readbuf, $lgcommand);
          $len = strpos ($readbuf, "quit") - $start;
          while ($readbuf[$start + $len] != "\n")
          {
              $len--;
          }
          $msg .= nl2br(substr($readbuf, $start, $len));
          @fclose ($sock);
      }
      $msg .= '</td></tr></table><hr />';
      $results .= $msg;
      break;
  case "countries":
      $target = 'Top Countries';
      $countries = getcountries(array('numitems' => $topcountries_limit));
      $msg = ('<table class="nqoutput">');
      $msg .= ('<tr><th colspan="6">'._NQ_COUNTRIES.' '._NQ_RESULT.' [<a href="index.php">'._NQ_CLEAR.'</a>]:</th></tr>');
      $msg .= "<tr><th>Code</th><th>Country</th><th>Flag</th><th>Latitude</th><th>Longitude</th><th>Users</th></tr>\n";
      foreach ($countries as $country)
      {
        if (!empty ($country['cn']))
        {
          $msg .= "<tr><td>".$country['cc']."</td><td>\n";
          if ($mapping_site == 1)
            $msg .= "<a href=\"javascript:NQpopup('http://www.mapquest.com/maps/map.adp?latlongtype=decimal&amp;latitude=".$country['lat']."&amp;longitude=".$country['lon']."&amp;zoom=0');\">".$country['cn']."</a>\n";
          else if ($mapping_site == 2)
            $msg .= "<a href=\"javascript:NQpopup('http://www.multimap.com/map/browse.cgi?lat=".$country['lat']."&amp;lon=".$country['lon']."&amp;scale=40000000&amp;icon=x');\">".$country['cn']."</a>\n";
          else
            $msg .= $country['cn']."\n";
          $msg .= "</td><td>\n";
          if (!empty($country['geoflag'])) $msg .= "<img class=\"geoflag\" src=\"".$country['geoflag']."\" alt=\"\" />\n";
          $msg .= "</td><td>";
          $msg .= $country['lat']."\n";
          $msg .= "</td><td>";
          $msg .= $country['lon']."\n";
          $msg .= "</td><td>";
          $msg .= $country['users']."\n";
          $msg .= "</td></tr>";
        }
      }
      $msg .= "</table><hr />\n";
      $results .= $msg;
      break;
  case "none":
  default:
      $target = "";
      break;
}
if ($querytype != 'none')
{
    $sql = "UPDATE ".$xoopsDB->prefix("netquery_geocc")." SET users = users + 1 WHERE cc = '".$geoip['cc']."'";
    $xoopsDB->queryF($sql);
}
include_once XOOPS_ROOT_PATH."/header.php";
$xoopsTpl->assign('xoops_module_header', $xoops_module_header);
echo "<script type=\"text/javascript\" src=\"".XOOPS_URL."/modules/netquery/include/nqUtility.js\"></script>\n";
echo "<div class=\"nq-center\">\n";
echo "<h3>"._NQ_NAME."</h3>\n";
echo "<form class=\"nquser\" action=\"index.php\" method=\"post\">\n";
echo "<fieldset>\n";
echo "<legend>";
echo _NQ_OPTIONS;
echo "</legend>\n";
echo "<div class=\"gobuttons\">\n";
if (!empty($geoip['cn']))
{
    echo "<a href=\"index.php?querytype=countries\"><img class=\"gobuttonup\" src=\"".$buttondir."/btn_earth.gif\" alt=\""._NQ_COUNTRIES."\" /></a>";
}
echo "<br /></div>\n";
echo "<div class=\"nqbuttons\">\n";
if ($whois_enabled) {echo "<input name=\"b1\" id=\"b1\" type=\"image\" src=\"".$buttondir."/btn_whois.gif\" class=\"".$b1class."\" alt=\""._NQ_WHOIS_ALT."\" />";}
if ($whoisip_enabled) {echo "<input name=\"b2\" id=\"b2\" type=\"image\" src=\"".$buttondir."/btn_whoisip.gif\" class=\"".$b2class."\" alt=\""._NQ_WHOISIP_ALT."\" />";}
if ($dns_lookup_enabled) {echo "<input name=\"b3\" id=\"b3\" type=\"image\" src=\"".$buttondir."/btn_dns.gif\" class=\"".$b3class."\" alt=\""._NQ_LOOKUP_ALT."\" />";}
if ($dns_dig_enabled) {echo "<input name=\"b4\" id=\"b4\" type=\"image\" src=\"".$buttondir."/btn_dig.gif\" class=\"".$b4class."\" alt=\""._NQ_DIG_ALT."\" />";}
if ($email_check_enabled) {echo "<input name=\"b12\" id=\"b12\" type=\"image\" src=\"".$buttondir."/btn_email.gif\" class=\"".$b12class."\" alt=\""._NQ_EMAIL_ALT."\" />";}
if ($port_check_enabled) {echo "<input name=\"b5\" id=\"b5\" type=\"image\" src=\"".$buttondir."/btn_port.gif\" class=\"".$b5class."\" alt=\""._NQ_PORT_ALT."\" />";}
if ($http_req_enabled) {echo "<input name=\"b6\" id=\"b6\" type=\"image\" src=\"".$buttondir."/btn_http.gif\" class=\"".$b6class."\" alt=\""._NQ_HTTP_ALT."\" />";}
if ($ping_enabled) {echo "<input name=\"b7\" id=\"b7\" type=\"image\" src=\"".$buttondir."/btn_ping.gif\" class=\"".$b7class."\" alt=\""._NQ_PING_ALT."\" />";}
if ($ping_remote_enabled) {echo "<input name=\"b8\" id=\"b8\" type=\"image\" src=\"".$buttondir."/btn_pingrem.gif\" class=\"".$b8class."\" alt=\""._NQ_PINGREM_ALT."\" />";}
if ($trace_enabled) {echo "<input name=\"b9\" id=\"b9\" type=\"image\" src=\"".$buttondir."/btn_trace.gif\" class=\"".$b9class."\" alt=\""._NQ_TRACE_ALT."\" />";}
if ($trace_remote_enabled) {echo "<input name=\"b10\" id=\"b10\" type=\"image\" src=\"".$buttondir."/btn_tracerem.gif\" class=\"".$b10class."\" alt=\""._NQ_TRACEREM_ALT."\" />";}
if ($looking_glass_enabled) {echo "<input name=\"b11\" id=\"b11\" type=\"image\" src=\"".$buttondir."/btn_lgquery.gif\" class=\"".$b11class."\" alt=\""._NQ_LGQUERY_ALT."\" />";}
echo "</div>\n";
echo "</fieldset>\n";
switch(strtolower($formtype))
{
  case "whois":
  default:
    echo "<fieldset>\n";
    echo "<legend>\n";
    echo _NQ_WHOIS_ALT;
    echo "</legend>\n";
    echo "<div class=\"gobuttons\">\n";
    echo "<a href=\"javascript:NQpopup('".$hlpfile."#whois','console','400','600');\"><img class=\"gobuttonup\" src=\"".$buttondir."/btn_help.gif\" alt=\""._NQ_MANBUTTON_ALT."\" /></a>\n";
    echo "<input class=\"gobuttonup\" type=\"image\" src=\"".$buttondir."/btn_go.gif\" alt=\""._NQ_GOBUTTON_ALT."\" />\n";
    echo "<br /></div>\n";
    echo "<div class=\"nqinput\">\n";
    echo "<input type=\"hidden\" name=\"querytype\" id=\"querytype\" value=\"whois\" />\n";
    echo "<label class=\"nq-column11\" for=\"domain_1\">"._NQ_WHOIS_NAME."</label>\n";
    echo "<span class=\"nq-spacer\">&nbsp;</span>\n";
    echo "<label class=\"nq-column2\" for=\"whois_tld_1\">"._NQ_WHOIS_TLD."</label><br />\n";
        $j = 1;
        while ($j <= $whois_max_limit)
        {
            echo "<div class=\"nqinput\">\n";
            echo "<input class=\"nq-column11\" type=\"text\" name=\"domain_".$j."\" id=\"domain_".$j."\" value=\"$domain[$j]\" size=\"32\" maxlength=\"100\" onfocus=\"this.value=''\" />";
            echo "<span class=\"nq-spacer\">.</span>\n";
            echo "<select class=\"nq-column2\" name=\"whois_tld_".$j."\" id=\"whois_tld_".$j."\">\n";
            foreach($links as $link)
            {
                if (!empty($link['whois_tld']))
                {
                    if ($link['whois_tld'] == $whois_tld[$j])
                    {
                        echo "<option selected=\"selected\" value=\"".$link['whois_tld']."\">".$link['whois_tld']."</option>\n";
                    }
                    else
                    {
                        echo "<option value=\"".$link['whois_tld']."\">".$link['whois_tld']."</option>\n";
                    }
                }
            }
            echo "</select>\n";
            echo "</div>\n";
            $j++;
        }
    echo "</div>\n";
    echo "</fieldset>\n";
    break;
  case "whoisip":
    echo "<fieldset>\n";
    echo "<legend>\n";
    echo _NQ_WHOISIP_ALT;
    echo "</legend>\n";
    echo "<div class=\"gobuttons\">\n";
    echo "<a href=\"javascript:NQpopup('".$hlpfile."#whoisip','console','400','600');\"><img class=\"gobuttonup\" src=\"".$buttondir."/btn_help.gif\" alt=\""._NQ_MANBUTTON_ALT."\" /></a>\n";
    echo "<input class=\"gobuttonup\" type=\"image\" src=\"".$buttondir."/btn_go.gif\" alt=\""._NQ_GOBUTTON_ALT."\" />\n";
    echo "<br /></div>\n";
    echo "<div class=\"nqinput\">\n";
    echo "<input type=\"hidden\" name=\"querytype\" id=\"querytype\" value=\"whoisip\" />\n";
    echo "<label class=\"nq-column11\" for=\"host\">"._NQ_WHOISIP."</label>\n";
    echo "<br /><input class=\"nq-column11\" type=\"text\" name=\"host\" id=\"host\" value=\"$host\" size=\"32\" maxlength=\"100\" />\n";
    echo "</div>\n";
    echo "</fieldset>\n";
    break;
  case "lookup":
    echo "<fieldset>\n";
    echo "<legend>\n";
    echo _NQ_LOOKUP_ALT;
    echo "</legend>\n";
    echo "<div class=\"gobuttons\">\n";
    echo "<a href=\"javascript:NQpopup('".$hlpfile."#dnslookup','console','400','600');\"><img class=\"gobuttonup\" src=\"".$buttondir."/btn_help.gif\" alt=\""._NQ_MANBUTTON_ALT."\" /></a>\n";
    echo "<input class=\"gobuttonup\" type=\"image\" src=\"".$buttondir."/btn_go.gif\" alt=\""._NQ_GOBUTTON_ALT."\" />\n";
    echo "<br /></div>\n";
    echo "<div class=\"nqinput\">\n";
    echo "<input type=\"hidden\" name=\"querytype\" id=\"querytype\" value=\"lookup\" />\n";
    echo "<label class=\"nq-column11\" for=\"host\">"._NQ_LOOKUP."</label>\n";
    echo "<br /><input class=\"nq-column11\" type=\"text\" name=\"host\" id=\"host\" value=\"$host\" size=\"32\" maxlength=\"100\" />\n";
    echo "</div>\n";
    echo "</fieldset>\n";
    break;
  case "dig":
    echo "<fieldset>\n";
    echo "<legend>\n";
    echo _NQ_DIG_ALT;
    echo "</legend>\n";
    echo "<div class=\"gobuttons\">\n";
    echo "<a href=\"javascript:NQpopup('".$hlpfile."#dnsdig','console','400','600');\"><img class=\"gobuttonup\" src=\"".$buttondir."/btn_help.gif\" alt=\""._NQ_MANBUTTON_ALT."\" /></a>\n";
    echo "<input class=\"gobuttonup\" type=\"image\" src=\"".$buttondir."/btn_go.gif\" alt=\""._NQ_GOBUTTON_ALT."\" />\n";
    echo "<br /></div>\n";
    echo "<div class=\"nqinput\">\n";
    echo "<input type=\"hidden\" name=\"querytype\" id=\"querytype\" value=\"dig\" />\n";
    echo "<label class=\"nq-column11\" for=\"host\">"._NQ_DIG."</label>\n";
    echo "<span class=\"nq-spacer\">&nbsp;</span>\n";
    echo "<label class=\"nq-column2\" for=\"digparam\">"._NQ_DIGPARAM."</label>\n";
    echo "<br /><input class=\"nq-column11\" type=\"text\" name=\"host\" id=\"host\" value=\"$host\" size=\"32\" maxlength=\"100\" />\n";
    echo "<span class=\"nq-spacer\">&nbsp;</span>\n";
    echo "<select class=\"nq-column2\" name=\"digparam\" id=\"digparam\">\n";
    foreach($digoptions as $digoption)
    {
        if (!empty($digoption['value']))
        {
            if ($digoption['value'] == $digparam)
            {
                echo "<option selected=\"selected\" value=\"".$digoption['value']."\">".$digoption['name']."</option>\n";
            }
            else
            {
                echo "<option value=\"".$digoption['value']."\">".$digoption['name']."</option>\n";
            }
        }
    }
    echo "</select>\n";
    echo "</div>\n";
    echo "</fieldset>\n";
    break;
  case "email":
    echo "<fieldset>\n";
    echo "<legend>\n";
    echo _NQ_EMAIL_ALT;
    echo "</legend>\n";
    echo "<div class=\"gobuttons\">\n";
    echo "<a href=\"javascript:NQpopup('".$hlpfile."#email','console','400','600');\"><img class=\"gobuttonup\" src=\"".$buttondir."/btn_help.gif\" alt=\""._NQ_MANBUTTON_ALT."\" /></a>\n";
    echo "<input class=\"gobuttonup\" type=\"image\" src=\"".$buttondir."/btn_go.gif\" alt=\""._NQ_GOBUTTON_ALT."\" />\n";
    echo "<br /></div>\n";
    echo "<div class=\"nqinput\">\n";
    echo "<input type=\"hidden\" name=\"querytype\" id=\"querytype\" value=\"email\" />\n";
    echo "<label class=\"nq-column11\" for=\"email\">"._NQ_EMAIL."</label>\n";
    echo "<br /><input class=\"nq-column11\" type=\"text\" name=\"email\" id=\"email\" value=\"$email\" size=\"32\" maxlength=\"100\" />\n";
    echo "</div>\n";
    echo "</fieldset>\n";
    break;
  case "port":
    echo "<fieldset>\n";
    echo "<legend>\n";
    echo _NQ_PORT_ALT;
    echo "</legend>\n";
    echo "<div class=\"gobuttons\">\n";
    echo "<a href=\"javascript:NQpopup('".$hlpfile."#port','console','400','600');\"><img class=\"gobuttonup\" src=\"".$buttondir."/btn_help.gif\" alt=\""._NQ_MANBUTTON_ALT."\" /></a>\n";
    echo "<input class=\"gobuttonup\" type=\"image\" src=\"".$buttondir."/btn_go.gif\" alt=\""._NQ_GOBUTTON_ALT."\" />\n";
    echo "<br /></div>\n";
    echo "<div class=\"nqinput\">\n";
    echo "<input type=\"hidden\" name=\"querytype\" id=\"querytype\" value=\"port\" />\n";
    echo "<label class=\"nq-column11\" for=\"server\">"._NQ_SERVER."</label>\n";
    echo "<span class=\"nq-spacer\">&nbsp;</span>\n";
    echo "<label class=\"nq-column2\" for=\"portnum\">"._NQ_PORTNUM."</label>\n";
    echo "<br /><input class=\"nq-column11\" type=\"text\" name=\"server\" id=\"server\" value=\"$server\" size=\"32\" maxlength=\"100\" />\n";
    echo "<span class=\"nq-spacer\">&nbsp;</span>\n";
    echo "<input class=\"nq-column2\" type=\"text\" name=\"portnum\" id=\"portnum\" value=\"$portnum\" size=\"3\" maxlength=\"10\" />\n";
    echo "</div>\n";
    echo "</fieldset>\n";
    break;
  case "http":
    echo "<fieldset>\n";
    echo "<legend>\n";
    echo _NQ_HTTP_ALT;
    echo "</legend>\n";
    echo "<div class=\"gobuttons\">\n";
    echo "<a href=\"javascript:NQpopup('".$hlpfile."#httpreq','console','400','600');\"><img class=\"gobuttonup\" src=\"".$buttondir."/btn_help.gif\" alt=\""._NQ_MANBUTTON_ALT."\" /></a>\n";
    echo "<input class=\"gobuttonup\" type=\"image\" src=\"".$buttondir."/btn_go.gif\" alt=\""._NQ_GOBUTTON_ALT."\" />\n";
    echo "<br /></div>\n";
    echo "<div class=\"nqinput\">\n";
    echo "<input type=\"hidden\" name=\"querytype\" id=\"querytype\" value=\"http\" />\n";
    echo "<label class=\"nq-column11\" for=\"httpurl\">"._NQ_HTTPURL."</label>\n";
    echo "<span class=\"nq-spacer\">&nbsp;</span>\n";
    echo "<label class=\"nq-column2\" for=\"httpreq\">"._NQ_HTTPREQ."</label>\n";
    echo "<br /><input class=\"nq-column11\" type=\"text\" name=\"httpurl\" id=\"httpurl\" value=\"$httpurl\" size=\"32\" maxlength=\"100\" />\n";
    echo "<span class=\"nq-spacer\">&nbsp;</span>\n";
    echo "<select class=\"nq-column2\" name=\"httpreq\" id=\"httpreq\">\n";
    foreach($httpoptions as $httpoption)
    {
        if (!empty($httpoption))
        {
            if ($httpoption == $httpreq)
            {
                echo "<option selected=\"selected\" value=\"".$httpoption."\">".$httpoption."</option>\n";
            }
            else
            {
                echo "<option value=\"".$httpoption."\">".$httpoption."</option>\n";
            }
        }
    }
    echo "</select>\n";
    echo "</div>\n";
    echo "</fieldset>\n";
    break;
  case "ping":
    echo "<fieldset>\n";
    echo "<legend>\n";
    echo _NQ_PING_ALT;
    echo "</legend>\n";
    echo "<div class=\"gobuttons\">\n";
    echo "<a href=\"javascript:NQpopup('".$hlpfile."#ping','console','400','600');\"><img class=\"gobuttonup\" src=\"".$buttondir."/btn_help.gif\" alt=\""._NQ_MANBUTTON_ALT."\" /></a>\n";
    echo "<input class=\"gobuttonup\" type=\"image\" src=\"".$buttondir."/btn_go.gif\" alt=\""._NQ_GOBUTTON_ALT."\" />\n";
    echo "<br /></div>\n";
    echo "<div class=\"nqinput\">\n";
    echo "<input type=\"hidden\" name=\"querytype\" id=\"querytype\" value=\"ping\" />\n";
    echo "<label class=\"nq-column11\" for=\"host\">"._NQ_PING."</label>\n";
    echo "<span class=\"nq-spacer\">&nbsp;</span>\n";
    echo "<label class=\"nq-column2\" for=\"maxp\">"._NQ_COUNT."</label>\n";
    echo "<br /><input class=\"nq-column11\" type=\"text\" name=\"host\" id=\"host\" value=\"$host\" size=\"32\" maxlength=\"50\" />\n";
    echo "<span class=\"nq-spacer\">&nbsp;</span>\n";
    echo "<select class=\"nq-column2\" name=\"maxp\" id=\"maxp\">\n";
    foreach($maxpoptions as $maxpoption)
    {
        if (!empty($maxpoption))
        {
            if ($maxpoption == $maxp)
            {
                echo "<option selected=\"selected\" value=\"".$maxpoption."\">".$maxpoption."</option>\n";
            }
            else
            {
                echo "<option value=\"".$maxpoption."\">".$maxpoption."</option>\n";
            }
        }
    }
    echo "</select>\n";
    echo "</div>\n";
    echo "</fieldset>\n";
    break;
  case "pingrem":
    echo "<fieldset>\n";
    echo "<legend>\n";
    echo _NQ_PINGREM_ALT;
    echo "</legend>\n";
    echo "<div class=\"gobuttons\">\n";
    echo "<a href=\"javascript:NQpopup('".$hlpfile."#ping','console','400','600');\"><img class=\"gobuttonup\" src=\"".$buttondir."/btn_help.gif\" alt=\""._NQ_MANBUTTON_ALT."\" /></a>\n";
    echo "<input class=\"gobuttonup\" type=\"image\" src=\"".$buttondir."/btn_go.gif\" alt=\""._NQ_GOBUTTON_ALT."\" onclick=\"NQremote('".$pingexec_remote."','".$pingexec_remote_t."',host.value);\" />";
    echo "<br /></div>\n";
    echo "<div class=\"nqinput\">\n";
    echo "<input type=\"hidden\" name=\"querytype\" id=\"querytype\" value=\"pingrem\" />\n";
    echo "<label class=\"nq-column11\" for=\"host\">"._NQ_PINGREM."</label>\n";
    echo "<br /><input class=\"nq-column11\" type=\"text\" name=\"host\" id=\"host\" value=\"$host\" size=\"32\" maxlength=\"100\" />\n";
    echo "</div>\n";
    echo "</fieldset>\n";
    break;
  case "trace":
    echo "<fieldset>\n";
    echo "<legend>\n";
    echo _NQ_TRACE_ALT;
    echo "</legend>\n";
    echo "<div class=\"gobuttons\">\n";
    echo "<a href=\"javascript:NQpopup('".$hlpfile."#traceroute','console','400','600');\"><img class=\"gobuttonup\" src=\"".$buttondir."/btn_help.gif\" alt=\""._NQ_MANBUTTON_ALT."\" /></a>\n";
    echo "<input class=\"gobuttonup\" type=\"image\" src=\"".$buttondir."/btn_go.gif\" alt=\""._NQ_GOBUTTON_ALT."\" />\n";
    echo "<br /></div>\n";
    echo "<div class=\"nqinput\">\n";
    echo "<input type=\"hidden\" name=\"querytype\" id=\"querytype\" value=\"trace\" />\n";
    echo "<label class=\"nq-column11\" for=\"host\">"._NQ_TRACE."</label>\n";
    echo "<span class=\"nq-spacer\">&nbsp;</span>\n";
    echo "<label class=\"nq-column2\" for=\"maxt\">"._NQ_COUNT."</label>\n";
    echo "<br /><input class=\"nq-column11\" type=\"text\" name=\"host\" id=\"host\" value=\"$host\" size=\"32\" maxlength=\"100\" />\n";
    echo "<span class=\"nq-spacer\">&nbsp;</span>\n";
    echo "<select class=\"nq-column2\" name=\"maxt\" id=\"maxt\">\n";
    foreach($maxtoptions as $maxtoption)
    {
        if (!empty($maxtoption))
        {
            if ($maxtoption == $maxt)
            {
                echo "<option selected=\"selected\" value=\"".$maxtoption."\">".$maxtoption."</option>\n";
            }
            else
            {
                echo "<option value=\"".$maxtoption."\">".$maxtoption."</option>\n";
            }
        }
    }
    echo "</select>\n";
    echo "</div>\n";
    echo "</fieldset>\n";
    break;
  case "tracerem":
    echo "<fieldset>\n";
    echo "<legend>\n";
    echo _NQ_TRACEREM_ALT;
    echo "</legend>\n";
    echo "<div class=\"gobuttons\">\n";
    echo "<a href=\"javascript:NQpopup('".$hlpfile."#traceroute','console','400','600');\"><img class=\"gobuttonup\" src=\"".$buttondir."/btn_help.gif\" alt=\""._NQ_MANBUTTON_ALT."\" /></a>\n";
    echo "<input class=\"gobuttonup\" type=\"image\" src=\"".$buttondir."/btn_go.gif\" alt=\""._NQ_GOBUTTON_ALT."\" onclick=\"NQremote('".$traceexec_remote."','".$traceexec_remote_t."',host.value);\" />";
    echo "<br /></div>\n";
    echo "<div class=\"nqinput\">\n";
    echo "<input type=\"hidden\" name=\"querytype\" id=\"querytype\" value=\"tracerem\" />\n";
    echo "<label class=\"nq-column11\" for=\"host\">"._NQ_TRACEREM."</label>\n";
    echo "<br /><input class=\"nq-column11\" type=\"text\" name=\"host\" id=\"host\" value=\"$host\" size=\"32\" maxlength=\"100\" />\n";
    echo "</div>\n";
    echo "</fieldset>\n";
    break;
  case "lgquery":
    echo "<fieldset>\n";
    echo "<legend>\n";
    echo _NQ_LGQUERY_ALT;
    echo "</legend>\n";
    echo "<div class=\"gobuttons\">\n";
    echo "<a href=\"javascript:NQpopup('".$hlpfile."#lg','console','400','600');\"><img class=\"gobuttonup\" src=\"".$buttondir."/btn_help.gif\" alt=\""._NQ_MANBUTTON_ALT."\" /></a>\n";
    echo "<input class=\"gobuttonup\" type=\"image\" src=\"".$buttondir."/btn_go.gif\" alt=\""._NQ_GOBUTTON_ALT."\" />\n";
    echo "<br /></div>\n";
    echo "<div class=\"nqinput\">\n";
    echo "<input type=\"hidden\" name=\"querytype\" id=\"querytype\" value=\"lgquery\" />\n";
    echo "<label class=\"nq-column12\" for=\"request\">"._NQ_LGREQUEST."</label>\n";
    echo "<span class=\"nq-spacer\">&nbsp;</span>\n";
    echo "<label class=\"nq-column13\" for=\"lgparam\">"._NQ_LGPARAM."</label>\n";
    echo "<span class=\"nq-spacer\">&nbsp;</span>\n";
    echo "<label class=\"nq-column2\" for=\"router\">"._NQ_LGROUTER."</label>\n";
    echo "<br /><select class=\"nq-column12\" name=\"request\" id=\"request\">\n";
    foreach($lgrequests as $key => $req)
    {
        if (!empty($req['request']))
        {
            if ($lgdefault[$req['handler']] && (! ( ($req['argc']) && (!$lgdefault['use_argc']) ) ) )
            {
                if ($key == $request)
                {
                    echo "<option selected=\"selected\" value=\"".$key."\">".$req['request']."</option>\n";
                }
                else
                {
                    echo "<option value=\"".$key."\">".$req['request']."</option>\n";
                }
            }
        }
    }
    echo "</select>\n";
    echo "<span class=\"nq-spacer\">&nbsp;</span>\n";
    echo "<input class=\"nq-column13\" type=\"text\" name=\"lgparam\" id=\"lgparam\" value=\"$lgparam\" size=\"8\" maxlength=\"100\" />\n";
    echo "<span class=\"nq-spacer\">&nbsp;</span>\n";
    echo "<select class=\"nq-column2\" name=\"router\" id=\"router\">\n";
    foreach($lgrouters as $rtr)
    {
        if (!empty($rtr['router']))
        {
            if ($rtr['router'] == $router)
            {
                echo "<option selected=\"selected\" value=\"".$rtr['router']."\">".$rtr['router']."</option>\n";
            }
            else
            {
                echo "<option value=\"".$rtr['router']."\">".$rtr['router']."</option>\n";
            }
        }
    }
    echo "</select>\n";
    echo "</div>\n";
    echo "</fieldset>\n";
    break;
}
echo "</form>\n";
if (!empty ($results))
{
    echo "<div class=\"nqresults\">\n";
    echo $results;
    echo "</div>\n";
}
if ($bb_visible)
{
    echo "<p>\n";
    $bbsettings = bb2_settings();
    $bbstats = bb2_stats();
    if ($bbsettings['running'])
    {
        echo _NQ_SPAMBLOCKER_RUNNING;
            if ($bbsettings['verbose']) echo " - "._NQ_SPAMBLOCKER_VERBOSE;
            if ($bbsettings['enabled'])
            {
                echo " - "._NQ_SPAMBLOCKER_SCREENING;
                if ($bbsettings['strict']) echo " - "._NQ_SPAMBLOCKER_STRICT;
            }
    }
    else
    {
        echo _NQ_SPAMBLOCKER_NOTRUNNING;
    }
    echo "<br />"._NQ_SPAMBLOCKER_BLOCKED." <strong>".$bbstats."</strong> "._NQ_SPAMBLOCKER_ATTEMPTS." ".$bbsettings['log_retain']." "._NQ_SPAMBLOCKER_LOGDAYS.".";
    echo "</p>\n";
}
if ($clientinfo_enabled)
{
    echo "<p>\n";
    echo _NQ_CLIENTINFO_ALT." - \n";
    if (!empty($geoip['cn']))
    {
        if (!empty($geoip['geoflag'])) echo "<img class=\"geoflag\" src=\"".$geoip['geoflag']."\" alt=\"\" />\n";
        if ($mapping_site == 1)
            echo "<a href=\"javascript:NQpopup('http://www.mapquest.com/maps/map.adp?latlongtype=decimal&amp;latitude=".$geoip['lat']."&amp;longitude=".$geoip['lon']."&amp;zoom=0');\">".$geoip['cn']."</a> - \n";
        else if ($mapping_site == 2)
            echo "<a href=\"javascript:NQpopup('http://www.multimap.com/map/browse.cgi?lat=".$geoip['lat']."&amp;lon=".$geoip['lon']."&amp;scale=40000000&amp;icon=x');\">".$geoip['cn']."</a> - \n";
        else
            echo $geoip['cn']."\n";
    }
    echo _NQ_CLIENTIP.": ".$client->property('ip')." - "._NQ_CLIENTOS.": ".$client->property('platform')." ".$client->property('os')." - "._NQ_CLIENTBROWSER.": ".$client->property('browser')." ".$client->property('version')."<br />\n";
    echo "</p>\n";
}
$timer->stop('main');
if ($exec_timer_enabled)
{
    echo "<p>\n";
    echo _NQ_EXECTIME.": ".$timer->get_current('main')."\n";
    echo "</p>\n";
}
echo "</div>\n";
include_once XOOPS_ROOT_PATH."/footer.php";
?>