<?php
// ------------------------------------------------------------------------- //
// Original Author : Richard Virtue - http://virtech.org/
// Licence Type : Public GNU/GPL
// ------------------------------------------------------------------------- //
include_once '../../../include/cp_header.php';
function bb2_settings()
{
    global $xoopsDB, $xoopsModuleConfig;
    if (!defined("BB2_CWD")) define('BB2_CWD', XOOPS_ROOT_PATH.'/modules/netquery');
    require_once(BB2_CWD . "/include/spamblocker/version.inc.php");
    $bb_running = false;
    $query = "SELECT COUNT(*) FROM ".$xoopsDB->prefix('newblocks')." WHERE func_file = 'netquick.php' AND visible = '1' ";
    $result = $xoopsDB->query($query);
    list($blocks_active) = $xoopsDB->fetchrow($result);
    if ($blocks_active > 0) $bb_running = true;
    $bb_retention = $xoopsModuleConfig['bb_retention'];
    $bb_enabled = $xoopsModuleConfig['bb_enabled'];
    $bb_visible = $xoopsModuleConfig['bb_visible'];
    $bb_display_stats = $xoopsModuleConfig['bb_display_stats'];
    $bb_strict = $xoopsModuleConfig['bb_strict'];
    $bb_verbose = $xoopsModuleConfig['bb_verbose'];
    $settings = array('version' => BB2_VERSION,
                      'log_table' => $xoopsDB->prefix("netquery_spamblocker"),
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
function bb2_response($key)
{
    $response = array('response' => 0, 'explanation' => '', 'log' => '');
    if (!defined('BB2_CORE')) define('BB2_CORE', XOOPS_ROOT_PATH.'/modules/netquery/include/spamblocker');
    include_once(BB2_CORE . "/responses.inc.php");
    if (is_callable('bb2_get_response')) $response = bb2_get_response($key);
    if ($response['response'] == '200')
    {
        $response['explanation'] = 'No problem detected';
        $response['log'] = 'Request accepted';
    }
    return $response;
}
function adm_whoisip($args)
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
$styleinclude = XOOPS_ROOT_PATH.'/modules/netquery/styles/'. str_replace('.css', '.php', $xoopsModuleConfig['stylesheet']);
if ( file_exists($styleinclude) )
{
    include_once $styleinclude;
    $buttondir = ((list($testdir) = preg_split('/[._-]/', $xoopsModuleConfig['stylesheet'])) && (!empty($testdir)) && (file_exists(XOOPS_ROOT_PATH.'/modules/netquery/images/'.$testdir))) ? XOOPS_URL.'/modules/netquery/images/'.$testdir : XOOPS_URL.'/modules/netquery/images/wlbuttons';
}
else
{
    include_once "../styles/wlbuttons_xoops.php";
    $buttondir = XOOPS_URL."/modules/netquery/images/wlbuttons";
}
if ( file_exists("../language/".$xoopsConfig['language']."/admin.php") )
{
    include_once "../language/".$xoopsConfig['language']."/admin.php";
}
else
{
    include_once "../language/english/admin.php";
}
$hlpfile = '../docs/manual.html';
$op = (isset($_REQUEST['op'])) ? $_REQUEST['op'] : "main";
switch(strtolower($op)) {
  case "main":
    if ($getsubmits = $xoopsDB->query("SELECT COUNT(*) FROM ".$xoopsDB->prefix("netquery_ports")." WHERE flag = '99'"))
    {
        list($portsubmits) = $xoopsDB->fetchRow($getsubmits);
    }
    $modid = $xoopsModule->getvar('mid');
    xoops_cp_header();
    echo "<script type=\"text/javascript\" src=\"".XOOPS_URL."/modules/netquery/include/nqUtility.js\"></script>\n";
    echo "<h4>"._NQ_MOD_FORM."\n";
    echo "<a href=\"javascript:NQpopup('".$hlpfile."#admin','console','400','600');\"><img class=\"gobutton\" style=\"".$img_gobutton."\" src=\"".$buttondir."/btn_help.gif\" alt=\""._NQ_MANBUTTON_ALT."\" /></a>";
    echo "</h4>\n";
    echo "<form class=\"nqadmin\" style=\"".$form_nqadmin."\" action=\"index.php?op=updmain\" method=\"post\">\n";
    echo "<fieldset style=\"".$form_nqadmin_fieldset."\">\n";
    echo "<legend style=\"".$form_nqadmin_legend."\">\n";
    echo _NQ_OPTIONS."\n";
    echo "</legend>\n";
    echo "<span>\n";
    echo "Configuration:\n";
    echo "[<a href=\"../../system/admin.php?fct=preferences&op=showmod&mod=".$modid."\">Preferences</a>]\n";
    echo "</span><br /><br />\n";
    echo "<span>\n";
    echo "GeoIP:\n";
    if (file_exists("xogeoip.php")) echo "[<a href=\"xogeoip.php?step=1\">"._NQ_NEWGEOIP."</a>]\n";
    else echo "[<a href=\"http://www.virtech.org/tools/\" target=\"_blank\">"._NQ_GETGEOIP."</a>]\n";
    echo "| "._NQ_COUNTRIES.":\n";
    echo "[<a href=\"index.php?op=resettopcountries\" onclick=\"return confirm('"._NQ_RESET_CONFIRM."')\">"._NQ_RESET."</a>]\n";
    echo "</span><br /><br />\n";
    echo "<span>\n";
    echo _NQ_WHOIS_ALT.":\n";
    echo "[<a href=\"index.php?op=whois\">"._NQ_EDITWHOIS."</a>]\n";
    echo "</span><br /><br />\n";
    echo "<span>\n";
    echo _NQ_PORT_ALT.":\n";
    echo "[<a href=\"index.php?op=ports\">"._NQ_EDITPORTS."</a>]\n";
    if (file_exists("xoports.php")) echo "[<a href=\"xoports.php?step=1\">"._NQ_NEWPORTS."</a>]\n";
    else echo "[<a href=\"http://www.virtech.org/tools/\" target=\"_blank\">"._NQ_GETPORTS."</a>]\n";
    echo "</span><br /><br />\n";
    echo "<span>\n";
    echo _NQ_USERSUB.":\n";
    if ($portsubmits > 0) echo "[<a style=\"color:red\" href=\"index.php?op=ports&amp;pflag=99\">".$portsubmits." "._NQ_NEWSUBMITS."</a>]\n";
    else echo "["._NQ_NOSUBMITS."]\n";
    echo "[<a href=\"index.php?op=flags\">"._NQ_EDITFLAGS."</a>]\n";
    echo "</span><br /><br />\n";
    echo "<span>\n";
    echo _NQ_LGQUERY_ALT.":\n";
    echo "[<a href=\"index.php?op=lgrouters\">"._NQ_EDITLG."</a>]\n";
    echo "</span><br /><br />\n";
    echo "<span>\n";
    echo _NQ_SPAMBLOCKER_ALT.":\n";
    echo "[<a href=\"index.php?op=bblogedit\">"._NQ_EDITSPAMBLOCKER."</a>]\n";
    echo "</span><br /><br />\n";
    echo "</fieldset>\n";
    echo "</form>\n";
    xoops_cp_footer();
    break;
  case "updmain":
    if (!isset($_POST['Submit']) || $_POST['Submit'] != _NQ_SUBMIT)
    {
        redirect_header("../../../admin.php", 1);
        exit;
    }
    redirect_header("index.php?op=main", 1);
    exit;
    break;
  case "resettopcountries":
    $sql = "UPDATE ".$xoopsDB->prefix("netquery_geocc")." SET users = 0 WHERE users > 0";
    $xoopsDB->queryF($sql);
    redirect_header("index.php?op=main", 1);
    exit;
    break;
  case "whois":
    $links = array();
    if ($getlinks = $xoopsDB->query("SELECT * FROM ".$xoopsDB->prefix("netquery_whois")." ORDER BY whois_tld"))
    {
        while ( $links[] = $xoopsDB->fetchArray($getlinks) )
        {
        }
    }
    else
    {
        echo "Could not retrieve whois lookup settings from the database.";
    }
    xoops_cp_header();
    echo "<script type=\"text/javascript\" src=\"".XOOPS_URL."/modules/netquery/include/nqUtility.js\"></script>\n";
    OpenTable();
    echo "<table class=\"nqinput\" style=\"".$table_nqinput."\">\n";
    echo "<tr><th style=\"".$table_nqinput_th."\" colspan=\"2\">\n";
    echo _NQ_WHOIS_FORM."\n";
    echo "</th><th style=\"".$table_nqinput_th.";text-align:right\">\n";
    echo "<a href=\"javascript:NQpopup('".$hlpfile."#admin','console','400','600');\"><img class=\"gobutton\" style=\"".$img_gobutton."\" src=\"".$buttondir."/btn_help.gif\" alt=\""._NQ_MANBUTTON_ALT."\" /></a>";
    echo "</th></tr>\n";
    echo "<tr><th style=\"".$table_nqinput_th."\">"._NQ_WHOISTLD."</th>";
    echo "<th style=\"".$table_nqinput_th."\">"._NQ_WHOISSRV."</th>";
    echo "<th style=\"".$table_nqinput_th."\">"._NQ_OPTIONS."</th></tr>\n";
    foreach ($links as $link)
    {
        if ($link['whois_tld'])
        {
            echo "<tr><td>".$link['whois_tld']."</td><td>".$link['whois_server']."</td>\n";
            echo "<td>[<a href=\"index.php?op=modwhois&amp;whois_id=".$link['whois_id']."\">"._NQ_EDIT."</a>]\n";
            echo " - [<a href=\"index.php?op=remwhois&amp;whois_id=".$link['whois_id']."\">"._NQ_DELETE."</a>]\n";
            echo "</td></tr>\n";
        }
    }
    echo "<tr><th style=\"".$table_nqinput_th."\" colspan=\"3\">\n";
    echo "[<a style=\"".$table_nqinput_th_alink."\" onmouseover=\"this.style.textDecoration='underline';\" onmouseout=\"this.style.textDecoration='none';\" href=\"index.php?op=newwhois\">"._NQ_ADDNEW."</a>]\n";
    echo " ~ [<a style=\"".$table_nqinput_th_alink."\" onmouseover=\"this.style.textDecoration='underline';\" onmouseout=\"this.style.textDecoration='none';\" href=\"index.php?op=main\">"._NQ_RETMAIN."</a>]\n";
    echo "</th></tr>\n";
    echo "</table>\n";
    CloseTable();
    xoops_cp_footer();
    break;
  case "modwhois":
    $whois_id = $_REQUEST['whois_id'];
    if ($getlink = $xoopsDB->query("SELECT * FROM ".$xoopsDB->prefix("netquery_whois")." WHERE whois_id = '$whois_id'"))
    {
        $link = $xoopsDB->fetchArray($getlink);
    }
    else
    {
        echo "Could not retrieve whois lookup data from the database.";
    }
    xoops_cp_header();
    OpenTable();
    echo "<form action=\"index.php?op=updwhois\" method=\"post\">\n";
    echo "<table class=\"nqinput\">\n";
    echo "<tr><th colspan=\"5\">\n";
    echo _NQ_WHOIS_FORM." ~ ID#:".$link['whois_id']."\n";
    echo "<input type=\"hidden\" name=\"whois_id\" value=\"".$link['whois_id']."\" />\n";
    echo "</th></tr>\n";
    echo "<tr><td>\n";
    echo "<span class=\"nq-help\" title=\""._NQ_WHOISTLD_HELP."\"><label for=\"whois_tld\">"._NQ_WHOISTLD."</label></span>\n";
    echo "</td><td>\n";
    echo "<span class=\"nq-help\" title=\""._NQ_WHOISSRV_HELP."\"><label for=\"whois_server\">"._NQ_WHOISSRV."</label></span>\n";
    echo "</td><td>\n";
    echo "<span class=\"nq-help\" title=\""._NQ_WHOISPREFIX_HELP."\"><label for=\"whois_prefix\">"._NQ_WHOISPREFIX."</label></span>\n";
    echo "</td><td>\n";
    echo "<span class=\"nq-help\" title=\""._NQ_WHOISSUFFIX_HELP."\"><label for=\"whois_suffix\">"._NQ_WHOISSUFFIX."</label></span>\n";
    echo "</td><td>\n";
    echo "<span class=\"nq-help\" title=\""._NQ_WHOISUNFOUND_HELP."\"><label for=\"whois_unfound\">"._NQ_WHOISUNFOUND."</label></span>\n";
    echo "</td></tr>\n";
    echo "<tr><td>\n";
    echo "<input type=\"text\" name=\"whois_tld\" id=\"whois_tld\" value=\"".$link['whois_tld']."\" size=\"5\" maxlength=\"10\" />\n";
    echo "</td><td>\n";
    echo "<input type=\"text\" name=\"whois_server\" id=\"whois_server\" value=\"".$link['whois_server']."\" size=\"25\" maxlength=\"50\" />\n";
    echo "</td><td>\n";
    echo "<input type=\"text\" name=\"whois_prefix\" id=\"whois_prefix\" value=\"".$link['whois_prefix']."\" size=\"10\" maxlength=\"20\" />\n";
    echo "</td><td>\n";
    echo "<input type=\"text\" name=\"whois_suffix\" id=\"whois_suffix\" value=\"".$link['whois_suffix']."\" size=\"10\" maxlength=\"20\" />\n";
    echo "</td><td>\n";
    echo "<input type=\"text\" name=\"whois_unfound\" id=\"whois_unfound\" value=\"".$link['whois_unfound']."\" size=\"15\" maxlength=\"30\" />\n";
    echo "</td></tr>\n";
    echo "<tr><td colspan=\"5\" class=\"nq-center\">\n";
    echo "<input type=\"submit\" name=\"Submit\" id=\"Submit\" value=\""._NQ_SUBMIT."\" />\n";
    echo "&nbsp;<input type=\"submit\" name=\"Cancel\" id=\"Cancel\" value=\""._NQ_CANCEL."\" />\n";
    echo "</td></tr>\n";
    echo "</table>\n";
    echo "</form>\n";
    CloseTable();
    xoops_cp_footer();
    break;
  case "updwhois":
    if (!isset($_POST['Submit']) || $_POST['Submit'] != _NQ_SUBMIT)
    {
        redirect_header("index.php?op=whois", 1);
        exit;
    }
    $whois_id = $_POST['whois_id'];
    $whois_tld = $_POST['whois_tld'];
    if (!isset($_POST['whois_server']) || $_POST['whois_server'] == '')
    {
        $whois_server = ltrim($whois_tld, " .") . ".whois-servers.net";
        $whois_server = gethostbyname($whois_server);
        $whois_server = gethostbyaddr($whois_server);
    }
    else
    {
        $whois_server = $_POST['whois_server'];
    }
    $whois_prefix = $_POST['whois_prefix'];
    $whois_suffix = $_POST['whois_suffix'];
    $whois_unfound = $_POST['whois_unfound'];
    if ($getlink = $xoopsDB->query("SELECT * FROM ".$xoopsDB->prefix("netquery_whois")." WHERE whois_id = '$whois_id'"))
    {
        $link = $xoopsDB->fetchArray($getlink);
    }
    else
    {
        echo "Could not retrieve whois lookup data from the database.";
    }
    $sql = "UPDATE ".$xoopsDB->prefix("netquery_whois")."
        SET whois_tld    = '" . $whois_tld . "',
            whois_server = '" . $whois_server . "',
            whois_prefix = '" . $whois_prefix . "',
            whois_suffix = '" . $whois_suffix . "',
            whois_unfound = '" . $whois_unfound . "'
        WHERE whois_id = " . $whois_id;
    $xoopsDB->query($sql);
    redirect_header("index.php?op=whois", 1);
    exit;
    break;
  case "remwhois":
    $whois_id = $_REQUEST['whois_id'];
    if ($getlink = $xoopsDB->query("SELECT * FROM ".$xoopsDB->prefix("netquery_whois")." WHERE whois_id = '$whois_id'"))
    {
        $link = $xoopsDB->fetchArray($getlink);
    }
    else
    {
        echo "Could not retrieve whois lookup data from the database.";
    }
    xoops_cp_header();
    OpenTable();
    echo "<form action=\"index.php?op=delwhois\" method=\"post\">\n";
    echo "<table class=\"nqinput\">\n";
    echo "<tr><th colspan=\"2\">\n";
    echo _NQ_WHOIS_FORM." ~ ID#:".$link['whois_id']."\n";
    echo "<input type=\"hidden\" name=\"whois_id\" value=\"".$link['whois_id']."\" />\n";
    echo "</th></tr>\n";
    echo "<tr><td>\n";
    echo _NQ_WHOISTLD.": [".$link['whois_tld']."]\n";
    echo "</td><td>\n";
    echo _NQ_WHOISSRV.": [".$link['whois_server']."]\n";
    echo "</td></tr>\n";
    echo "<tr><td colspan=\"2\" style=\"text-align:center;\">\n";
    echo "<input type=\"submit\" name=\"Confirm\" id=\"Comfirm\" value=\""._NQ_CONFIRM."\" />\n";
    echo "&nbsp;<input type=\"submit\" name=\"Cancel\" id=\"Cancel\" value=\""._NQ_CANCEL."\" />\n";
    echo "</td></tr>\n";
    echo "</table>\n";
    echo "</form>\n";
    CloseTable();
    xoops_cp_footer();
    break;
  case "delwhois":
    if (!isset($_POST['Confirm']) || $_POST['Confirm'] != _NQ_CONFIRM)
    {
        redirect_header("index.php?op=whois", 1);
        exit;
    }
    $whois_id = $_POST['whois_id'];
    if ($getlink = $xoopsDB->query("SELECT * FROM ".$xoopsDB->prefix("netquery_whois")." WHERE whois_id = '$whois_id'"))
    {
        $link = $xoopsDB->fetchArray($getlink);
    }
    else
    {
        echo "Could not retrieve whois lookup data from the database.";
    }
    $sql = "DELETE FROM ".$xoopsDB->prefix("netquery_whois")." WHERE whois_id = " . $whois_id;
    $xoopsDB->query($sql);
    redirect_header("index.php?op=whois", 1);
    exit;
    break;
  case "newwhois":
    xoops_cp_header();
    OpenTable();
    echo "<form action=\"index.php?op=addwhois\" method=\"post\">\n";
    echo "<table class=\"nqinput\">\n";
    echo "<tr><th colspan=\"5\">\n";
    echo _NQ_WHOIS_FORM." ~ "._NQ_ADDNEW."\n";
    echo "</th></tr>\n";
    echo "<tr><td>\n";
    echo "<span class=\"nq-help\" title=\""._NQ_WHOISTLD_HELP."\"><label for=\"whois_tld\">"._NQ_WHOISTLD."</label></span>\n";
    echo "</td><td>\n";
    echo "<span class=\"nq-help\" title=\""._NQ_WHOISSRV_HELP."\"><label for=\"whois_server\">"._NQ_WHOISSRV."</label></span>\n";
    echo "</td><td>\n";
    echo "<span class=\"nq-help\" title=\""._NQ_WHOISPREFIX_HELP."\"><label for=\"whois_prefix\">"._NQ_WHOISPREFIX."</label></span>\n";
    echo "</td><td>\n";
    echo "<span class=\"nq-help\" title=\""._NQ_WHOISSUFFIX_HELP."\"><label for=\"whois_suffix\">"._NQ_WHOISSUFFIX."</label></span>\n";
    echo "</td><td>\n";
    echo "<span class=\"nq-help\" title=\""._NQ_WHOISUNFOUND_HELP."\"><label for=\"whois_unfound\">"._NQ_WHOISUNFOUND."</label></span>\n";
    echo "</td></tr>\n";
    echo "<tr><td>\n";
    echo "<input type=\"text\" name=\"whois_tld\" id=\"whois_tld\" value=\"\" size=\"5\" maxlength=\"10\" />\n";
    echo "</td><td>\n";
    echo "<input type=\"text\" name=\"whois_server\" id=\"whois_server\" value=\"\" size=\"25\" maxlength=\"50\" />\n";
    echo "</td><td>\n";
    echo "<input type=\"text\" name=\"whois_prefix\" id=\"whois_prefix\" value=\"\" size=\"10\" maxlength=\"20\" />\n";
    echo "</td><td>\n";
    echo "<input type=\"text\" name=\"whois_suffix\" id=\"whois_suffix\" value=\"\" size=\"10\" maxlength=\"20\" />\n";
    echo "</td><td>\n";
    echo "<input type=\"text\" name=\"whois_unfound\" id=\"whois_unfound\" value=\"\" size=\"15\" maxlength=\"30\" />\n";
    echo "</td></tr>\n";
    echo "<tr><td colspan=\"5\" style=\"text-align:center;\">\n";
    echo "<input type=\"submit\" name=\"Submit\" id=\"Submit\" value=\""._NQ_SUBMIT."\" />\n";
    echo "&nbsp;<input type=\"submit\" name=\"Cancel\" id=\"Cancel\" value=\""._NQ_CANCEL."\" />\n";
    echo "</td></tr>\n";
    echo "</table>\n";
    echo "</form>\n";
    CloseTable();
    xoops_cp_footer();
    break;
  case "addwhois":
    if (!isset($_POST['Submit']) || $_POST['Submit'] != _NQ_SUBMIT)
    {
        redirect_header("index.php?op=whois", 1);
        exit;
    }
    $nextId = $xoopsDB->genId($xoopsDB->prefix("netquery_whois")."_whois_id_seq");
    $whois_tld = $_POST['whois_tld'];
    if (!isset($_POST['whois_server']) || $_POST['whois_server'] == '')
    {
        $whois_server = ltrim($whois_tld, " .") . ".whois-servers.net";
        $whois_server = gethostbyname($whois_server);
        $whois_server = gethostbyaddr($whois_server);
    }
    else
    {
        $whois_server = $_POST['whois_server'];
    }
    $whois_prefix = $_POST['whois_prefix'];
    $whois_suffix = $_POST['whois_suffix'];
    $whois_unfound = $_POST['whois_unfound'];
    $sql = "INSERT INTO ".$xoopsDB->prefix("netquery_whois")." (
        whois_id,
        whois_tld,
        whois_server,
        whois_prefix,
        whois_suffix,
        whois_unfound)
        VALUES (
        $nextId,
        '" . $whois_tld . "',
        '" . $whois_server . "',
        '" . $whois_prefix . "',
        '" . $whois_suffix . "',
        '" . $whois_unfound . "')";
    $xoopsDB->query($sql);
    if ($nextId == 0)
    {
        $nextId = $xoopsDB->getInsertId();
    }
    redirect_header("index.php?op=whois", 1);
    exit;
    break;
  case "lgrouters":
    $routers = array();
    if ($getrouters = $xoopsDB->query("SELECT * FROM ".$xoopsDB->prefix("netquery_lgrouter")." ORDER BY router_id"))
    {
        while ( $routers[] = $xoopsDB->fetchArray($getrouters) )
        {
        }
    }
    else
    {
        echo "Could not retrieve looking glass routers from the database.";
    }
    xoops_cp_header();
    echo "<script type=\"text/javascript\" src=\"".XOOPS_URL."/modules/netquery/include/nqUtility.js\"></script>\n";
    OpenTable();
    echo "<table class=\"nqinput\" style=\"".$table_nqinput."\">\n";
    echo "<tr><th style=\"".$table_nqinput_th."\" colspan=\"2\">\n";
    echo _NQ_LG_FORM."\n";
    echo "</th><th style=\"".$table_nqinput_th.";text-align:right\">\n";
    echo "<a href=\"javascript:NQpopup('".$hlpfile."#admin','console','400','600');\"><img class=\"gobutton\" style=\"".$img_gobutton."\" src=\"".$buttondir."/btn_help.gif\" alt=\""._NQ_MANBUTTON_ALT."\" /></a>";
    echo "</th></tr>\n";
    echo "<tr><th style=\"".$table_nqinput_th."\">"._NQ_ROUTER."</th>";
    echo "<th style=\"".$table_nqinput_th."\">"._NQ_ADDRESS."</th>";
    echo "<th style=\"".$table_nqinput_th."\">"._NQ_OPTIONS."</th></tr>\n";
    foreach ($routers as $router)
    {
        if ($router['router'])
        {
            echo "<tr><td>".$router['router']."</td><td>".$router['address']."</td>\n";
            echo "<td>[<a href=\"index.php?op=modrouter&amp;router_id=".$router['router_id']."\">"._NQ_EDIT."</a>]\n";
            if ($router['router'] != 'default')
            {
                echo " - [<a href=\"index.php?op=remrouter&amp;router_id=".$router['router_id']."\">"._NQ_DELETE."</a>]\n";
            }
            echo "</td></tr>\n";
        }
    }
    echo "<tr><th style=\"".$table_nqinput_th."\" colspan=\"3\">\n";
    echo "[<a style=\"".$table_nqinput_th_alink."\" onmouseover=\"this.style.textDecoration='underline';\" onmouseout=\"this.style.textDecoration='none';\" href=\"index.php?op=newrouter\">"._NQ_ADDNEW."</a>]\n";
    echo " ~ [<a style=\"".$table_nqinput_th_alink."\" onmouseover=\"this.style.textDecoration='underline';\" onmouseout=\"this.style.textDecoration='none';\" href=\"index.php?op=main\">"._NQ_RETMAIN."</a>]\n";
    echo "</th></tr>\n";
    echo "</table>\n";
    CloseTable();
    xoops_cp_footer();
    break;
  case "modrouter":
    $router_id = $_REQUEST['router_id'];
    if ($getrouter = $xoopsDB->query("SELECT * FROM ".$xoopsDB->prefix("netquery_lgrouter")." WHERE router_id = '$router_id'"))
    {
        $router = $xoopsDB->fetchArray($getrouter);
    }
    else
    {
        echo "Could not retrieve looking glass router data from the database.";
    }
    xoops_cp_header();
    OpenTable();
    echo "<form action=\"index.php?op=updrouter\" method=\"post\">\n";
    echo "<table class=\"nqinput\">\n";
    echo "<tr><th colspan=\"3\">\n";
    echo _NQ_LG_FORM." ~ ID#:".$router['router_id']."\n";
    echo "<input type=\"hidden\" name=\"router_id\" id=\"router_id\" value=\"".$router['router_id']."\" />\n";
    echo "</th></tr>\n";
    if ($router['router'] == 'default')
    {
        echo "<tr><td colspan=\"3\">\n";
        echo "<input type=\"hidden\" name=\"router_router\" id=\"router_router\" value=\"".$router['router']."\" />\n";
        echo "<input type=\"hidden\" name=\"router_address\" id=\"router_address\" value=\"".$router['address']."\" />\n";
        echo _NQ_ROUTER_DEFAULT."\n";
    }
    else
    {
        echo "<tr><td>\n";
        echo _NQ_ROUTER_NAMEADDR."\n";
        echo "</td><td>\n";
        echo "<input type=\"text\" name=\"router_router\" id=\"router_router\" value=\"".$router['router']."\" size=\"21\" maxlength=\"100\" />\n";
        echo "</td><td>\n";
        echo "<input type=\"text\" name=\"router_address\" id=\"router_address\" value=\"".$router['address']."\" size=\"21\" maxlength=\"100\" />\n";
    }
    echo "</td></tr><tr><td>\n";
    if ($router['use_argc']) echo "<input type=\"checkbox\" name=\"router_use_argc\" id=\"router_use_argc\" value=\"1\" checked=\"checked\" />\n";
    else echo "<input type=\"checkbox\" name=\"router_use_argc\" id=\"router_use_argc\" value=\"1\" />\n";
    echo _NQ_LGFULL;
    echo "</td><td>\n";
    echo "<label for=\"router_username\">"._NQ_LGUSER."</label>\n";
    echo "<input type=\"text\" name=\"router_username\" id=\"router_username\" value=\"".$router['username']."\" size=\"10\" maxlength=\"20\" />\n";
    echo "</td><td>\n";
    echo "<label for=\"router_password\">"._NQ_LGPASS."</label>\n";
    echo "<input type=\"text\" name=\"router_password\" id=\"router_password\" value=\"".$router['password']."\" size=\"10\" maxlength=\"20\" />\n";
    echo "</td></tr><tr><td>\n";
    if ($router['zebra']) echo "<input type=\"checkbox\" name=\"router_zebra\" id=\"router_zebra\" value=\"1\" checked=\"checked\" />\n";
    else echo "<input type=\"checkbox\" name=\"router_zebra\" id=\"router_zebra\" value=\"1\" />\n";
    echo _NQ_LGUSE." zebra";
    echo "</td><td>\n";
    echo "<label for=\"router_zebra_port\">"._NQ_LGPORT."</label>\n";
    echo "<input type=\"text\" name=\"router_zebra_port\" id=\"router_zebra_port\" value=\"".$router['zebra_port']."\" size=\"10\" maxlength=\"10\" />\n";
    echo "</td><td>\n";
    echo "<label for=\"router_zebra_password\">"._NQ_LGPASS."</label>\n";
    echo "<input type=\"text\" name=\"router_zebra_password\" id=\"router_zebra_password\" value=\"".$router['zebra_password']."\" size=\"10\" maxlength=\"20\" />\n";
    echo "</td></tr><tr><td>\n";
    if ($router['ripd']) echo "<input type=\"checkbox\" name=\"router_ripd\" id=\"router_ripd\" value=\"1\" checked=\"checked\" />\n";
    else echo "<input type=\"checkbox\" name=\"router_ripd\" id=\"router_ripd\" value=\"1\" />\n";
    echo _NQ_LGUSE." ripd";
    echo "</td><td>\n";
    echo "<label for=\"router_ripd_port\">"._NQ_LGPORT."</label>\n";
    echo "<input type=\"text\" name=\"router_ripd_port\" id=\"router_ripd_port\" value=\"".$router['ripd_port']."\" size=\"10\" maxlength=\"10\" />\n";
    echo "</td><td>\n";
    echo "<label for=\"router_ripd_password\">"._NQ_LGPASS."</label>\n";
    echo "<input type=\"text\" name=\"router_ripd_password\" id=\"router_ripd_password\" value=\"".$router['ripd_password']."\" size=\"10\" maxlength=\"20\" />\n";
    echo "</td></tr><tr><td>\n";
    if ($router['ripngd']) echo "<input type=\"checkbox\" name=\"router_ripngd\" id=\"router_ripngd\" value=\"1\" checked=\"checked\" />\n";
    else echo "<input type=\"checkbox\" name=\"router_ripngd\" id=\"router_ripngd\" value=\"1\" />\n";
    echo _NQ_LGUSE." ripngd";
    echo "</td><td>\n";
    echo "<label for=\"router_ripngd_port\">"._NQ_LGPORT."</label>\n";
    echo "<input type=\"text\" name=\"router_ripngd_port\" id=\"router_ripngd_port\" value=\"".$router['ripngd_port']."\" size=\"10\" maxlength=\"10\" />\n";
    echo "</td><td>\n";
    echo "<label for=\"router_ripngd_password\">"._NQ_LGPASS."</label>\n";
    echo "<input type=\"text\" name=\"router_ripngd_password\" id=\"router_ripngd_password\" value=\"".$router['ripngd_password']."\" size=\"10\" maxlength=\"20\" />\n";
    echo "</td></tr><tr><td>\n";
    if ($router['ospfd']) echo "<input type=\"checkbox\" name=\"router_ospfd\" id=\"router_ospfd\" value=\"1\" checked=\"checked\" />\n";
    else echo "<input type=\"checkbox\" name=\"router_ospfd\" id=\"router_ospfd\" value=\"1\" />\n";
    echo _NQ_LGUSE." ospfd";
    echo "</td><td>\n";
    echo "<label for=\"router_ospfd_port\">"._NQ_LGPORT."</label>\n";
    echo "<input type=\"text\" name=\"router_ospfd_port\" id=\"router_ospfd_port\" value=\"".$router['ospfd_port']."\" size=\"10\" maxlength=\"10\" />\n";
    echo "</td><td>\n";
    echo "<label for=\"router_ospfd_password\">"._NQ_LGPASS."</label>\n";
    echo "<input type=\"text\" name=\"router_ospfd_password\" id=\"router_ospfd_password\" value=\"".$router['ospfd_password']."\" size=\"10\" maxlength=\"20\" />\n";
    echo "</td></tr><tr><td>\n";
    if ($router['bgpd']) echo "<input type=\"checkbox\" name=\"router_bgpd\" id=\"router_bgpd\" value=\"1\" checked=\"checked\" />\n";
    else echo "<input type=\"checkbox\" name=\"router_bgpd\" id=\"router_bgpd\" value=\"1\" />\n";
    echo _NQ_LGUSE." bgpd";
    echo "</td><td>\n";
    echo "<label for=\"router_bgpd_port\">"._NQ_LGPORT."</label>\n";
    echo "<input type=\"text\" name=\"router_bgpd_port\" id=\"router_bgpd_port\" value=\"".$router['bgpd_port']."\" size=\"10\" maxlength=\"10\" />\n";
    echo "</td><td>\n";
    echo "<label for=\"router_bgpd_password\">"._NQ_LGPASS."</label>\n";
    echo "<input type=\"text\" name=\"router_bgpd_password\" id=\"router_bgpd_password\" value=\"".$router['bgpd_password']."\" size=\"10\" maxlength=\"20\" />\n";
    echo "</td></tr><tr><td>\n";
    if ($router['ospf6d']) echo "<input type=\"checkbox\" name=\"router_ospf6d\" id=\"router_ospf6d\" value=\"1\" checked=\"checked\" />\n";
    else echo "<input type=\"checkbox\" name=\"router_ospf6d\" id=\"router_ospf6d\" value=\"1\" />\n";
    echo _NQ_LGUSE." ospf6d";
    echo "</td><td>\n";
    echo "<label for=\"router_ospf6d_port\">"._NQ_LGPORT."</label>\n";
    echo "<input type=\"text\" name=\"router_ospf6d_port\" id=\"router_ospf6d_port\" value=\"".$router['ospf6d_port']."\" size=\"10\" maxlength=\"10\" />\n";
    echo "</td><td>\n";
    echo "<label for=\"router_ospf6d_password\">"._NQ_LGPASS."</label>\n";
    echo "<input type=\"text\" name=\"router_ospf6d_password\" id=\"router_ospf6d_password\" value=\"".$router['ospf6d_password']."\" size=\"10\" maxlength=\"20\" />\n";
    echo "</td></tr>\n";
    echo "<tr><td colspan=\"3\" style=\"text-align:center;\">\n";
    echo "<input type=\"submit\" name=\"Submit\" id=\"Submit\" value=\""._NQ_SUBMIT."\" />\n";
    echo "&nbsp;<input type=\"submit\" name=\"Cancel\" id=\"Cancel\" value=\""._NQ_CANCEL."\" />\n";
    echo "</td></tr>\n";
    echo "</table>\n";
    echo "</form>\n";
    CloseTable();
    xoops_cp_footer();
    break;
  case "updrouter":
    if (!isset($_POST['Submit']) || $_POST['Submit'] != _NQ_SUBMIT)
    {
        redirect_header("index.php?op=lgrouters", 1);
        exit;
    }
    $router_id = $_POST['router_id'];
    $router_router = $_POST['router_router'];
    $router_address = $_POST['router_address'];
    $router_username = $_POST['router_username'];
    $router_password = $_POST['router_password'];
    $router_zebra = (isset($_POST['router_zebra'])) ? '1' : '0';
    $router_zebra_port = $_POST['router_zebra_port'];
    $router_zebra_password = $_POST['router_zebra_password'];
    $router_ripd = (isset($_POST['router_ripd'])) ? '1' : '0';
    $router_ripd_port = $_POST['router_ripd_port'];
    $router_ripd_password = $_POST['router_ripd_password'];
    $router_ripngd = (isset($_POST['router_ripngd'])) ? '1' : '0';
    $router_ripngd_port = $_POST['router_ripngd_port'];
    $router_ripngd_password = $_POST['router_ripngd_password'];
    $router_ospfd = (isset($_POST['router_ospfd'])) ? '1' : '0';
    $router_ospfd_port = $_POST['router_ospfd_port'];
    $router_ospfd_password = $_POST['router_ospfd_password'];
    $router_bgpd = (isset($_POST['router_bgpd'])) ? '1' : '0';
    $router_bgpd_port = $_POST['router_bgpd_port'];
    $router_bgpd_password = $_POST['router_bgpd_password'];
    $router_ospf6d = (isset($_POST['router_ospf6d'])) ? '1' : '0';
    $router_ospf6d_port = $_POST['router_ospf6d_port'];
    $router_ospf6d_password = $_POST['router_ospf6d_password'];
    $router_use_argc = (isset($_POST['router_use_argc'])) ? '1' : '0';
    if ($getrouter = $xoopsDB->query("SELECT * FROM ".$xoopsDB->prefix("netquery_lgrouter")." WHERE router_id = '$router_id'"))
    {
        $router = $xoopsDB->fetchArray($getrouter);
    }
    else
    {
        echo "Could not retrieve looking glass router settings from the database.";
    }
    $sql = "UPDATE ".$xoopsDB->prefix("netquery_lgrouter")."
    SET router          = '" . $router_router . "',
        address         = '" . $router_address . "',
        username        = '" . $router_username . "',
        password        = '" . $router_password . "',
        zebra           = '" . $router_zebra . "',
        zebra_port      = '" . $router_zebra_port . "',
        zebra_password  = '" . $router_zebra_password . "',
        ripd            = '" . $router_ripd . "',
        ripd_port       = '" . $router_ripd_port . "',
        ripd_password   = '" . $router_ripd_password . "',
        ripngd          = '" . $router_ripngd . "',
        ripngd_port     = '" . $router_ripngd_port . "',
        ripngd_password = '" . $router_ripngd_password . "',
        ospfd           = '" . $router_ospfd . "',
        ospfd_port      = '" . $router_ospfd_port . "',
        ospfd_password  = '" . $router_ospfd_password . "',
        bgpd            = '" . $router_bgpd . "',
        bgpd_port       = '" . $router_bgpd_port . "',
        bgpd_password   = '" . $router_bgpd_password . "',
        ospf6d          = '" . $router_ospf6d . "',
        ospf6d_port     = '" . $router_ospf6d_port . "',
        ospf6d_password = '" . $router_ospf6d_password . "',
        use_argc        = '" . $router_use_argc . "'
    WHERE router_id = " . $router_id;
    $xoopsDB->query($sql);
    redirect_header("index.php?op=lgrouters", 1);
    exit;
    break;
  case "remrouter":
    $router_id = $_REQUEST['router_id'];
    if ($getrouter = $xoopsDB->query("SELECT * FROM ".$xoopsDB->prefix("netquery_lgrouter")." WHERE router_id = '$router_id'"))
    {
        $router = $xoopsDB->fetchArray($getrouter);
    }
    else
    {
        echo "Could not retrieve looking glass router settings from the database.";
    }
    xoops_cp_header();
    OpenTable();
    echo "<form action=\"index.php?op=delrouter\" method=\"post\">\n";
    echo "<table class=\"nqinput\">\n";
    echo "<tr><th colspan=\"2\">\n";
    echo _NQ_LG_FORM." ~ ID#:".$router['router_id']."\n";
    echo "<input type=\"hidden\" name=\"router_id\" value=\"".$router['router_id']."\" />\n";
    echo "</th></tr>\n";
    echo "<tr><td>\n";
    echo _NQ_ROUTER.": [".$router['router']."]\n";
    echo "</td><td>\n";
    echo _NQ_ADDRESS.": [".$router['address']."]\n";
    echo "</td></tr>\n";
    echo "<tr><td colspan=\"3\" style=\"text-align:center\">\n";
    echo "<input type=\"submit\" name=\"Confirm\" id=\"Comfirm\" value=\""._NQ_CONFIRM."\" />\n";
    echo "&nbsp;<input type=\"submit\" name=\"Cancel\" id=\"Cancel\" value=\""._NQ_CANCEL."\" />\n";
    echo "</td></tr>\n";
    echo "</table>\n";
    echo "</form>\n";
    CloseTable();
    xoops_cp_footer();
    break;
  case "delrouter":
    if (!isset($_POST['Confirm']) || $_POST['Confirm'] != _NQ_CONFIRM)
    {
        redirect_header("index.php?op=lgrouters", 1);
        exit;
    }
    $router_id = $_POST['router_id'];
    if ($getrouter = $xoopsDB->query("SELECT * FROM ".$xoopsDB->prefix("netquery_lgrouter")." WHERE router_id = '$router_id'"))
    {
        $router = $xoopsDB->fetchArray($getrouter);
    }
    else
    {
        echo "Could not retrieve looking glass router data from the database.";
    }
    $sql = "DELETE FROM ".$xoopsDB->prefix("netquery_lgrouter")." WHERE router_id = " . $router_id;
    $xoopsDB->query($sql);
    redirect_header("index.php?op=lgrouters", 1);
    exit;
    break;
  case "newrouter":
    xoops_cp_header();
    OpenTable();
    echo "<form action=\"index.php?op=addrouter\" method=\"post\">\n";
    echo "<table class=\"nqinput\">\n";
    echo "<tr><th colspan=\"3\">\n";
    echo _NQ_LG_FORM." ~ "._NQ_ADDNEW."\n";
    echo "</th></tr>\n";
    echo "<tr><td>\n";
    echo _NQ_ROUTER_NAMEADDR."\n";
    echo "</td><td>\n";
    echo "<input type=\"text\" name=\"router_router\" id=\"router_router\" value=\"\" size=\"21\" maxlength=\"100\" />\n";
    echo "</td><td>\n";
    echo "<input type=\"text\" name=\"router_address\" id=\"router_address\" value=\"\" size=\"21\" maxlength=\"100\" />\n";
    echo "</td></tr><tr><td>\n";
    echo "<input type=\"checkbox\" name=\"router_use_argc\" id=\"router_use_argc\" value=\"1\" />\n";
    echo " Use full table</td><td>\n";
    echo "<label for=\"router_username\">"._NQ_LGUSER."</label>\n";
    echo "<input type=\"text\" name=\"router_username\" id=\"router_username\" value=\"\" size=\"10\" maxlength=\"20\" />\n";
    echo "</td><td>\n";
    echo "<label for=\"router_password\">"._NQ_LGPASS."</label>\n";
    echo "<input type=\"text\" name=\"router_password\" id=\"router_password\" value=\"\" size=\"10\" maxlength=\"20\" />\n";
    echo "</td></tr><tr><td>\n";
    echo "<input type=\"checkbox\" name=\"router_zebra\" id=\"router_zebra\" value=\"1\" />\n";
    echo _NQ_LGUSE." zebra</td><td>\n";
    echo "<label for=\"router_zebra_port\">"._NQ_LGPORT."</label>\n";
    echo "<input type=\"text\" name=\"router_zebra_port\" id=\"router_zebra_port\" value=\"\" size=\"10\" maxlength=\"10\" />\n";
    echo "</td><td>\n";
    echo "<label for=\"router_zebra_password\">"._NQ_LGPASS."</label>\n";
    echo "<input type=\"text\" name=\"router_zebra_password\" id=\"router_zebra_password\" value=\"\" size=\"10\" maxlength=\"20\" />\n";
    echo "</td></tr><tr><td>\n";
    echo "<input type=\"checkbox\" name=\"router_ripd\" id=\"router_ripd\" value=\"1\" />\n";
    echo _NQ_LGUSE." ripd</td><td>\n";
    echo "<label for=\"router_ripd_port\">"._NQ_LGPORT."</label>\n";
    echo "<input type=\"text\" name=\"router_ripd_port\" id=\"router_ripd_port\" value=\"\" size=\"10\" maxlength=\"10\" />\n";
    echo "</td><td>\n";
    echo "<label for=\"router_ripd_password\">"._NQ_LGPASS."</label>\n";
    echo "<input type=\"text\" name=\"router_ripd_password\" id=\"router_ripd_password\" value=\"\" size=\"10\" maxlength=\"20\" />\n";
    echo "</td></tr><tr><td>\n";
    echo "<input type=\"checkbox\" name=\"router_ripngd\" id=\"router_ripngd\" value=\"1\" />\n";
    echo _NQ_LGUSE." ripngd</td><td>\n";
    echo "<label for=\"router_ripngd_port\">"._NQ_LGPORT."</label>\n";
    echo "<input type=\"text\" name=\"router_ripngd_port\" id=\"router_ripngd_port\" value=\"\" size=\"10\" maxlength=\"10\" />\n";
    echo "</td><td>\n";
    echo "<label for=\"router_ripngd_password\">"._NQ_LGPASS."</label>\n";
    echo "<input type=\"text\" name=\"router_ripngd_password\" id=\"router_ripngd_password\" value=\"\" size=\"10\" maxlength=\"20\" />\n";
    echo "</td></tr><tr><td>\n";
    echo "<input type=\"checkbox\" name=\"router_ospfd\" id=\"router_ospfd\" value=\"1\" />\n";
    echo _NQ_LGUSE." ospfd</td><td>\n";
    echo "<label for=\"router_ospfd_port\">"._NQ_LGPORT."</label>\n";
    echo "<input type=\"text\" name=\"router_ospfd_port\" id=\"router_ospfd_port\" value=\"\" size=\"10\" maxlength=\"10\" />\n";
    echo "</td><td>\n";
    echo "<label for=\"router_ospfd_password\">"._NQ_LGPASS."</label>\n";
    echo "<input type=\"text\" name=\"router_ospfd_password\" id=\"router_ospfd_password\" value=\"\" size=\"10\" maxlength=\"20\" />\n";
    echo "</td></tr><tr><td>\n";
    echo "<input type=\"checkbox\" name=\"router_bgpd\" id=\"router_bgpd\" value=\"1\" />\n";
    echo _NQ_LGUSE." bgpd</td><td>\n";
    echo "<label for=\"router_bgpd_port\">"._NQ_LGPORT."</label>\n";
    echo "<input type=\"text\" name=\"router_bgpd_port\" id=\"router_bgpd_port\" value=\"\" size=\"10\" maxlength=\"10\" />\n";
    echo "</td><td>\n";
    echo "<label for=\"router_bgpd_password\">"._NQ_LGPASS."</label>\n";
    echo "<input type=\"text\" name=\"router_bgpd_password\" id=\"router_bgpd_password\" value=\"\" size=\"10\" maxlength=\"20\" />\n";
    echo "</td></tr><tr><td>\n";
    echo "<input type=\"checkbox\" name=\"router_ospf6d\" id=\"router_ospf6d\" value=\"1\" />\n";
    echo _NQ_LGUSE." ospf6d</td><td>\n";
    echo "<label for=\"router_ospf6d_port\">"._NQ_LGPORT."</label>\n";
    echo "<input type=\"text\" name=\"router_ospf6d_port\" id=\"router_ospf6d_port\" value=\"\" size=\"10\" maxlength=\"10\" />\n";
    echo "</td><td>\n";
    echo "<label for=\"router_ospf6d_password\">"._NQ_LGPASS."</label>\n";
    echo "<input type=\"text\" name=\"router_ospf6d_password\" id=\"router_ospf6d_password\" value=\"\" size=\"10\" maxlength=\"20\" />\n";
    echo "</td></tr>\n";
    echo "<tr><td colspan=\"3\" style=\"text-align:center\">\n";
    echo "<input type=\"submit\" name=\"Submit\" id=\"Submit\" value=\""._NQ_SUBMIT."\" />\n";
    echo "&nbsp;<input type=\"submit\" name=\"Cancel\" id=\"Cancel\" value=\""._NQ_CANCEL."\" />\n";
    echo "</td></tr>\n";
    echo "</table>\n";
    echo "</form>\n";
    CloseTable();
    xoops_cp_footer();
    break;
  case "addrouter":
    if (!isset($_POST['Submit']) || $_POST['Submit'] != _NQ_SUBMIT)
    {
        redirect_header("index.php?op=lgrouters", 1);
        exit;
    }
    $nextId = $xoopsDB->genId($xoopsDB->prefix("netquery_lgrouter")."_router_id_seq");
    $router_router = $_POST['router_router'];
    $router_address = $_POST['router_address'];
    $router_username = $_POST['router_username'];
    $router_password = $_POST['router_password'];
    $router_zebra = (isset($_POST['router_zebra'])) ? '1' : '0';
    $router_zebra_port = $_POST['router_zebra_port'];
    $router_zebra_password = $_POST['router_zebra_password'];
    $router_ripd = (isset($_POST['router_ripd'])) ? '1' : '0';
    $router_ripd_port = $_POST['router_ripd_port'];
    $router_ripd_password = $_POST['router_ripd_password'];
    $router_ripngd = (isset($_POST['router_ripngd'])) ? '1' : '0';
    $router_ripngd_port = $_POST['router_ripngd_port'];
    $router_ripngd_password = $_POST['router_ripngd_password'];
    $router_ospfd = (isset($_POST['router_ospfd'])) ? '1' : '0';
    $router_ospfd_port = $_POST['router_ospfd_port'];
    $router_ospfd_password = $_POST['router_ospfd_password'];
    $router_bgpd = (isset($_POST['router_bgpd'])) ? '1' : '0';
    $router_bgpd_port = $_POST['router_bgpd_port'];
    $router_bgpd_password = $_POST['router_bgpd_password'];
    $router_ospf6d = (isset($_POST['router_ospf6d'])) ? '1' : '0';
    $router_ospf6d_port = $_POST['router_ospf6d_port'];
    $router_ospf6d_password = $_POST['router_ospf6d_password'];
    $router_use_argc = (isset($_POST['router_use_argc'])) ? '1' : '0';
    $sql = "INSERT INTO ".$xoopsDB->prefix("netquery_lgrouter")." (
          router_id,
          router,
          address,
          username,
          password,
          zebra,
          zebra_port,
          zebra_password,
          ripd,
          ripd_port,
          ripd_password,
          ripngd,
          ripngd_port,
          ripngd_password,
          ospfd,
          ospfd_port,
          ospfd_password,
          bgpd,
          bgpd_port,
          bgpd_password,
          ospf6d,
          ospf6d_port,
          ospf6d_password,
          use_argc)
         VALUES ($nextId,
          '" . $router_router . "',
          '" . $router_address . "',
          '" . $router_username . "',
          '" . $router_password . "',
          '" . $router_zebra . "',
          '" . $router_zebra_port . "',
          '" . $router_zebra_password . "',
          '" . $router_ripd . "',
          '" . $router_ripd_port . "',
          '" . $router_ripd_password . "',
          '" . $router_ripngd . "',
          '" . $router_ripngd_port . "',
          '" . $router_ripngd_password . "',
          '" . $router_ospfd . "',
          '" . $router_ospfd_port . "',
          '" . $router_ospfd_password . "',
          '" . $router_bgpd . "',
          '" . $router_bgpd_port . "',
          '" . $router_bgpd_password . "',
          '" . $router_ospf6d . "',
          '" . $router_ospf6d_port . "',
          '" . $router_ospf6d_password . "',
          '" . $router_use_argc . "')";
    $xoopsDB->query($sql);
    if ($nextId == 0)
    {
    $nextId = $xoopsDB->getInsertId();
    }
    redirect_header("index.php?op=lgrouters", 1);
    exit;
    break;
  case "ports":
    $pflag = (isset($_REQUEST['pflag'])) ? $_REQUEST['pflag'] : '';
    $portnum = (isset($_REQUEST['portnum'])) ? $_REQUEST['portnum'] : 80;
    $ports = array();
    if (!empty($pflag))
    {
        if ($getports = $xoopsDB->query("SELECT * FROM ".$xoopsDB->prefix("netquery_ports")." WHERE flag = '$pflag'"))
        {
            while ( $ports[] = $xoopsDB->fetchArray($getports) )
            {
            }
        }
    }
    else
    {
        if ($getports = $xoopsDB->query("SELECT * FROM ".$xoopsDB->prefix("netquery_ports")." WHERE flag < 99 AND port = '$portnum'"))
        {
            while ( $ports[] = $xoopsDB->fetchArray($getports) )
            {
            }
        }
    }
    xoops_cp_header();
    echo "<script type=\"text/javascript\" src=\"".XOOPS_URL."/modules/netquery/include/nqUtility.js\"></script>\n";
    OpenTable();
    echo "<table class=\"nqinput\" style=\"".$table_nqinput."\">\n";
    echo "<tr><th style=\"".$table_nqinput_th."\" colspan=\"3\">\n";
    echo _NQ_PORTS_FORM."<br />"._NQ_OVERRIDE."\n";
    echo "</th><th style=\"".$table_nqinput_th.";text-align:right\">\n";
    echo "<a href=\"javascript:NQpopup('".$hlpfile."#admin','console','400','600');\"><img class=\"gobutton\" style=\"".$img_gobutton."\" src=\"".$buttondir."/btn_help.gif\" alt=\""._NQ_MANBUTTON_ALT."\" /></a>";
    echo "</th></tr>\n";
    echo "<tr><td colspan=\"4\"><br />\n";
    echo "<form action=\"index.php?op=ports\" method=\"post\">\n";
    echo "<fieldset>\n";
    echo _NQ_PORTNUM." <input type=\"text\" name=\"portnum\" id=\"portnum\" value=\"$portnum\" size=\"3\" maxlength=\"10\" tabindex=\"0\" />\n";
    echo _NQ_FLAGNUM." <input type=\"text\" name=\"pflag\" id=\"pflag\" value=\"$pflag\" size=\"3\" maxlength=\"4\" tabindex=\"0\" />\n";
    echo "<input type=\"image\" class=\"gobutton\" style=\"".$input_gobutton."\" src=\"".$buttondir."/btn_go.gif\" alt=\""._NQ_GOBUTTON_ALT."\" />\n";
    echo "</fieldset>\n";
    echo "</form>\n";
    echo "</td></tr>\n";
    echo "<tr><th style=\"".$table_nqinput_th."\">"._NQ_PORTNUM."</th>";
    echo "<th style=\"".$table_nqinput_th."\">"._NQ_PROTOCOL."</th>";
    echo "<th style=\"".$table_nqinput_th."\">"._NQ_PORTSVC."</th>";
    echo "<th style=\"".$table_nqinput_th."\">"._NQ_OPTIONS."</th></tr>\n";
    foreach ($ports as $port)
    {
        if ($port['port_id'])
        {
            echo "<tr><td>".$port['port']."</td><td>".$port['protocol']."</td><td>".$port['service']."</td>\n";
            echo "<td>[<a href=\"index.php?op=modport&amp;port_id=".$port['port_id']."&amp;pflag=".$pflag."\">"._NQ_EDIT."</a>]\n";
            echo " - [<a href=\"index.php?op=remport&amp;port_id=".$port['port_id']."&amp;pflag=".$pflag."\">"._NQ_DELETE."</a>]\n";
            echo "</td></tr>\n";
        }
    }
    echo "<tr><th style=\"".$table_nqinput_th."\"colspan=\"4\">\n";
    echo "[<a style=\"".$table_nqinput_th_alink."\" onmouseover=\"this.style.textDecoration='underline';\" onmouseout=\"this.style.textDecoration='none';\" href=\"index.php?op=newport&amp;portnum=".$portnum."\">"._NQ_ADDNEW."</a>]\n";
    echo " ~ [<a style=\"".$table_nqinput_th_alink."\" onmouseover=\"this.style.textDecoration='underline';\" onmouseout=\"this.style.textDecoration='none';\" href=\"index.php?op=main\">"._NQ_RETMAIN."</a>]\n";
    echo "</th></tr>\n";
    echo "</table>\n";
    CloseTable();
    xoops_cp_footer();
    break;
  case "modport":
    $port_id = $_REQUEST['port_id'];
    $pflag = $_REQUEST['pflag'];
    if ($getport = $xoopsDB->query("SELECT * FROM ".$xoopsDB->prefix("netquery_ports")." WHERE port_id = '$port_id'"))
    {
        $port = $xoopsDB->fetchArray($getport);
    }
    else
    {
        echo "Could not retrieve port service data from the database.";
    }
    if ($getflags = $xoopsDB->query("SELECT * FROM ".$xoopsDB->prefix("netquery_flags")." ORDER BY flagnum"))
    {
        while ( $flags[] = $xoopsDB->fetchArray($getflags) )
        {
        }
    }
    else
    {
        echo "Could not retrieve flags data from the database.";
    }
    xoops_cp_header();
    echo "<script type=\"text/javascript\" src=\"".XOOPS_URL."/modules/netquery/include/nqUtility.js\"></script>\n";
    OpenTable();
    echo "<form action=\"index.php?op=updport\" method=\"post\">\n";
    echo "<table class=\"nqinput\">\n";
    echo "<tr><th colspan=\"5\">\n";
    echo _NQ_PORTS_FORM." ~ ID#:".$port['port_id']."\n";
    echo "<input type=\"hidden\" name=\"port_id\" value=\"".$port['port_id']."\" />\n";
    echo "<input type=\"hidden\" name=\"port_port\" value=\"".$port['port']."\" />\n";
    echo "<input type=\"hidden\" name=\"pflag\" value=\"".$pflag."\" />\n";
    echo "</th></tr>\n";
    echo "<tr><td>\n";
    echo "<label for=\"port_port\">"._NQ_PORTNUM."</label>\n";
    echo "</td><td>\n";
    echo "<label for=\"port_protocol\">"._NQ_PROTOCOL."</label>\n";
    echo "</td><td>\n";
    echo "<label for=\"port_service\">"._NQ_PORTSVC."</label>\n";
    echo "</td><td>\n";
    echo "<label for=\"port_comment\">"._NQ_PORTNOTE."</label>\n";
    echo "</td><td>\n";
    echo "<label for=\"port_flag\">"._NQ_PORTFLAG."</label>\n";
    echo "</td></tr><tr><td>\n";
    echo "<span id=\"port_port\">".$port['port']."</span>\n";
    echo "</td><td>\n";
    echo "<input type=\"text\" name=\"port_protocol\" id=\"port_protocol\" value=\"".$port['protocol']."\" size=\"3\" maxlength=\"3\" />\n";
    echo "</td><td>\n";
    echo "<input type=\"text\" name=\"port_service\" id=\"port_service\" value=\"".$port['service']."\" size=\"20\" maxlength=\"35\" />\n";
    echo "</td><td>\n";
    echo "<input type=\"text\" name=\"port_comment\" id=\"port_comment\" value=\"".$port['comment']."\" size=\"25\" maxlength=\"50\" />\n";
    echo "</td><td>\n";
    echo "<select name=\"port_flag\" id=\"port_flag\">\n";
    foreach($flags as $flag)
    {
        if (!empty($flag['keyword']))
        {
            if ($flag['flagnum'] == $port['flag'])
            {
                echo "<option selected=\"selected\" value=\"".$flag['flagnum']."\">".$flag['keyword']."</option>\n";
            }
            else
            {
                echo "<option value=\"".$flag['flagnum']."\">".$flag['keyword']."</option>\n";
            }
        }
    }
    echo "</select>\n";
    echo "</td></tr>\n";
    echo "<tr><td colspan=\"5\" style=\"text-align:center;\">\n";
    echo "<input type=\"submit\" name=\"Submit\" id=\"Submit\" value=\""._NQ_SUBMIT."\" />\n";
    echo "&nbsp;<input type=\"submit\" name=\"Cancel\" id=\"Cancel\" value=\""._NQ_CANCEL."\" />\n";
    echo "&nbsp;<input type=\"button\" value=\""._NQ_PORTNUM.$port['port']." "._NQ_LIST."\" onclick=\"NQpopup('portlist.php?portnum=".$port['port']."','console','400','600');\" />\n";
    echo "</td></tr>\n";
    echo "</table>\n";
    echo "</form>\n";
    CloseTable();
    xoops_cp_footer();
    break;
  case "updport":
    $port_port = $_POST['port_port'];
    $pflag = $_POST['pflag'];
    if (!isset($_POST['Submit']) || $_POST['Submit'] != _NQ_SUBMIT)
    {
        redirect_header("index.php?op=ports&amp;portnum=".$port_port."&amp;pflag=".$pflag, 1);
        exit;
    }
    $port_id = $_POST['port_id'];
    $port_protocol = $_POST['port_protocol'];
    $port_service = $_POST['port_service'];
    $port_comment = $_POST['port_comment'];
    $port_flag = $_POST['port_flag'];
    if ($getport = $xoopsDB->query("SELECT * FROM ".$xoopsDB->prefix("netquery_ports")." WHERE port_id = '$port_id'"))
    {
        $port = $xoopsDB->fetchArray($getport);
    }
    else
    {
        echo "Could not retrieve port service data from the database.";
    }
    $sql = "UPDATE ".$xoopsDB->prefix("netquery_ports")."
        SET protocol = '" . $port_protocol . "',
            service  = '" . $port_service . "',
            comment  = '" . $port_comment . "',
            flag     = '" . $port_flag . "'
        WHERE port_id = " . $port_id;
    $xoopsDB->query($sql);
    redirect_header("index.php?op=ports&amp;portnum=".$port_port."&amp;pflag=".$pflag, 1);
    exit;
    break;
  case "remport":
    $port_id = $_REQUEST['port_id'];
    $pflag = $_REQUEST['pflag'];
    if ($getport = $xoopsDB->query("SELECT * FROM ".$xoopsDB->prefix("netquery_ports")." WHERE port_id = '$port_id'"))
    {
        $port = $xoopsDB->fetchArray($getport);
    }
    else
    {
        echo "Could not retrieve port service data from the database.";
    }
    xoops_cp_header();
    OpenTable();
    echo "<form action=\"index.php?op=delport\" method=\"post\">\n";
    echo "<table class=\"nqinput\">\n";
    echo "<tr><th colspan=\"3\">\n";
    echo _NQ_PORTS_FORM." ~ ID#:".$port['port_id']."\n";
    echo "<input type=\"hidden\" name=\"port_id\" value=\"".$port['port_id']."\" />\n";
    echo "<input type=\"hidden\" name=\"port_port\" value=\"".$port['port']."\" />\n";
    echo "<input type=\"hidden\" name=\"pflag\" value=\"".$pflag."\" />\n";
    echo "</th></tr>\n";
    echo "<tr><td>\n";
    echo _NQ_PORTNUM.": [".$port['port']."]\n";
    echo "</td><td>\n";
    echo _NQ_PROTOCOL.": [".$port['protocol']."]\n";
    echo "</td><td>\n";
    echo _NQ_PORTSVC.": [".$port['service']."]\n";
    echo "</td></tr>\n";
    echo "<tr><td colspan=\"3\" style=\"text-align:center;\">\n";
    echo "<input type=\"submit\" name=\"Confirm\" id=\"Comfirm\" value=\""._NQ_CONFIRM."\" />\n";
    echo "&nbsp;<input type=\"submit\" name=\"Cancel\" id=\"Cancel\" value=\""._NQ_CANCEL."\" />\n";
    echo "</td></tr>\n";
    echo "</table>\n";
    echo "</form>\n";
    CloseTable();
    xoops_cp_footer();
    break;
  case "delport":
    $port_port = $_POST['port_port'];
    $pflag = $_POST['pflag'];
    if (!isset($_POST['Confirm']) || $_POST['Confirm'] != _NQ_CONFIRM)
    {
        redirect_header("index.php?op=ports&amp;portnum=".$port_port."&amp;pflag=".$pflag, 1);
        exit;
    }
    $port_id = $_POST['port_id'];
    if ($getport = $xoopsDB->query("SELECT * FROM ".$xoopsDB->prefix("netquery_ports")." WHERE port_id = '$port_id'"))
    {
        $port = $xoopsDB->fetchArray($getport);
    }
    else
    {
        echo "Could not retrieve port service data from the database.";
    }
    $sql = "DELETE FROM ".$xoopsDB->prefix("netquery_ports")." WHERE port_id = " . $port_id;
    $xoopsDB->query($sql);
    redirect_header("index.php?op=ports&amp;portnum=".$port_port."&amp;pflag=".$pflag, 1);
    exit;
    break;
  case "newport":
    $portnum = (isset($_REQUEST['portnum'])) ? $_REQUEST['portnum'] : '80';
    if ($getflags = $xoopsDB->query("SELECT * FROM ".$xoopsDB->prefix("netquery_flags")." ORDER BY flagnum"))
    {
        while ( $flags[] = $xoopsDB->fetchArray($getflags) )
        {
        }
    }
    else
    {
        echo "Could not retrieve flags data from the database.";
    }
    xoops_cp_header();
    OpenTable();
    echo "<form action=\"index.php?op=addport\" method=\"post\">\n";
    echo "<table class=\"nqinput\">\n";
    echo "<tr><th colspan=\"5\">\n";
    echo _NQ_PORTS_FORM." ~ "._NQ_ADDNEW."\n";
    echo "<input type=\"hidden\" name=\"portnum\" value=\"".$portnum."\" />\n";
    echo "</th></tr>\n";
    echo "<tr><td>\n";
    echo "<label for=\"port_port\">"._NQ_PORTNUM."</label>\n";
    echo "</td><td>\n";
    echo "<label for=\"port_protocol\">"._NQ_PROTOCOL."</label>\n";
    echo "</td><td>\n";
    echo "<label for=\"port_service\">"._NQ_PORTSVC."</label>\n";
    echo "</td><td>\n";
    echo "<label for=\"port_comment\">"._NQ_PORTNOTE."</label>\n";
    echo "</td><td>\n";
    echo "<label for=\"port_flag\">"._NQ_PORTFLAG."</label>\n";
    echo "</td></tr><tr><td>\n";
    echo "<input type=\"text\" name=\"port_port\" id=\"port_port\" value=\"".$portnum."\" size=\"2\" maxlength=\"5\" />\n";
    echo "</td><td>\n";
    echo "<input type=\"text\" name=\"port_protocol\" id=\"port_protocol\" value=\"\" size=\"3\" maxlength=\"3\" />\n";
    echo "</td><td>\n";
    echo "<input type=\"text\" name=\"port_service\" id=\"port_service\" value=\"\" size=\"20\" maxlength=\"35\" />\n";
    echo "</td><td>\n";
    echo "<input type=\"text\" name=\"port_comment\" id=\"port_comment\" value=\"\" size=\"25\" maxlength=\"50\" />\n";
    echo "</td><td>\n";
    echo "<select name=\"port_flag\" id=\"port_flag\">\n";
    foreach($flags as $flag)
    {
        if (!empty($flag['keyword']))
        {
            if ($flag['flagnum'] == 0)
            {
                echo "<option selected=\"selected\" value=\"".$flag['flagnum']."\">".$flag['keyword']."</option>\n";
            }
            else
            {
                echo "<option value=\"".$flag['flagnum']."\">".$flag['keyword']."</option>\n";
            }
        }
    }
    echo "</select>\n";
    echo "</td></tr>\n";
    echo "<tr><td colspan=\"5\" style=\"text-align:center;\">\n";
    echo "<input type=\"submit\" name=\"Submit\" id=\"Submit\" value=\""._NQ_SUBMIT."\" />\n";
    echo "&nbsp;<input type=\"submit\" name=\"Cancel\" id=\"Cancel\" value=\""._NQ_CANCEL."\" />\n";
    echo "</td></tr>\n";
    echo "</table>\n";
    echo "</form>\n";
    CloseTable();
    xoops_cp_footer();
    break;
  case "addport":
    $portnum = (isset($_POST['portnum'])) ? $_POST['portnum'] : '80';
    if (!isset($_POST['Submit']) || $_POST['Submit'] != _NQ_SUBMIT)
    {
        redirect_header("index.php?op=ports&amp;portnum=".$portnum, 1);
        exit;
    }
    $nextId = $xoopsDB->genId($xoopsDB->prefix("netquery_ports")."_port_id_seq");
    $port_port = $_POST['port_port'];
    $port_protocol = $_POST['port_protocol'];
    $port_service = $_POST['port_service'];
    $port_comment = $_POST['port_comment'];
    $port_flag = $_POST['port_flag'];
    $sql = "INSERT INTO ".$xoopsDB->prefix("netquery_ports")." (
        port_id,
        port,
        protocol,
        service,
        comment,
        flag)
        VALUES (
        $nextId,
        '" . $port_port. "',
        '" . $port_protocol. "',
        '" . $port_service . "',
        '" . $port_comment . "',
        '" . $port_flag . "')";
    $xoopsDB->query($sql);
    if ($nextId == 0)
    {
        $nextId = $xoopsDB->getInsertId();
    }
    redirect_header("index.php?op=ports&amp;portnum=".$port_port, 1);
    exit;
    break;
  case "flags":
    $flags = array();
    if ($getflags = $xoopsDB->query("SELECT * FROM ".$xoopsDB->prefix("netquery_flags")." ORDER BY flagnum"))
    {
        while ( $flags[] = $xoopsDB->fetchArray($getflags) )
        {
        }
    }
    else
    {
        echo "Could not retrieve flags data from the database.";
    }
    xoops_cp_header();
    echo "<script type=\"text/javascript\" src=\"".XOOPS_URL."/modules/netquery/include/nqUtility.js\"></script>\n";
    OpenTable();
    echo "<table class=\"nqinput\" style=\"".$table_nqinput."\">\n";
    echo "<tr><th style=\"".$table_nqinput_th."\" colspan=\"3\">\n";
    echo _NQ_FLAGS_FORM."\n";
    echo "</th><th style=\"".$table_nqinput_th.";text-align:right\">\n";
    echo "<a href=\"javascript:NQpopup('".$hlpfile."#admin','console','400','600');\"><img class=\"gobutton\" style=\"".$img_gobutton."\" src=\"".$buttondir."/btn_help.gif\" alt=\""._NQ_MANBUTTON_ALT."\" /></a>";
    echo "</th></tr>\n";
    echo "<tr><th style=\"".$table_nqinput_th."\">"._NQ_FLAGNUM."</th>";
    echo "<th style=\"".$table_nqinput_th."\">"._NQ_KEYWORD."</th>";
    echo "<th style=\"".$table_nqinput_th."\">"._NQ_FONT."</th>";
    echo "<th style=\"".$table_nqinput_th."\">"._NQ_OPTIONS."</th></tr>\n";
    foreach ($flags as $flag)
    {
        if ($flag['flag_id'])
        {
            echo "<tr><td>".$flag['flagnum']."</td><td>".$flag['keyword']."</td><td style=\"color:".$flag['fontclr'].";\">".$flag['fontclr']."</td>\n";
            echo "<td>[<a href=\"index.php?op=modflag&amp;flag_id=".$flag['flag_id']."\">"._NQ_EDIT."</a>]\n";
            if ($flag['flagnum'] != 99) echo " - [<a href=\"index.php?op=remflag&amp;flag_id=".$flag['flag_id']."\">"._NQ_DELETE."</a>]\n";
            echo "</td></tr>\n";
        }
    }
    echo "<tr><th style=\"".$table_nqinput_th."\" colspan=\"4\">\n";
    echo "[<a style=\"".$table_nqinput_th_alink."\" onmouseover=\"this.style.textDecoration='underline';\" onmouseout=\"this.style.textDecoration='none';\" href=\"index.php?op=newflag\">"._NQ_ADDNEW."</a>]\n";
    echo " ~ [<a style=\"".$table_nqinput_th_alink."\" onmouseover=\"this.style.textDecoration='underline';\" onmouseout=\"this.style.textDecoration='none';\" href=\"index.php?op=main\">"._NQ_RETMAIN."</a>]\n";
    echo "</th></tr>\n";
    echo "</table>\n";
    CloseTable();
    xoops_cp_footer();
    break;
  case "modflag":
    $flag_id = $_REQUEST['flag_id'];
    if ($getflag = $xoopsDB->query("SELECT * FROM ".$xoopsDB->prefix("netquery_flags")." WHERE flag_id = '$flag_id'"))
    {
        $flag = $xoopsDB->fetchArray($getflag);
    }
    else
    {
        echo "Could not retrieve flag data from the database.";
    }
    $colors = array('black', 'blue', 'purple', 'red', 'brown', 'orange', 'yellow', 'green', 'cyan', 'violet');
    xoops_cp_header();
    OpenTable();
    echo "<form action=\"index.php?op=updflag\" method=\"post\">\n";
    echo "<table class=\"nqinput\">\n";
    echo "<tr><th colspan=\"3\">\n";
    echo _NQ_FLAGS_FORM." ~ ID#:".$flag['flagnum']."\n";
    echo "<input type=\"hidden\" name=\"flag_id\" value=\"".$flag['flag_id']."\" />\n";
    echo "</th></tr>\n";
    echo "<tr><td>\n";
    echo "<label for=\"flag_keyword\">"._NQ_KEYWORD."</label>\n";
    echo "</td><td>\n";
    echo "<label for=\"flag_fontclr\">"._NQ_FONT."</label>\n";
    echo "</td><td>\n";
    echo "<label for=\"flag_lookup_1\">"._NQ_FLAGLU1."</label>\n";
    echo "</td></tr><tr><td>\n";
    echo "<input type=\"text\" name=\"flag_keyword\" id=\"flag_keyword\" value=\"".$flag['keyword']."\" size=\"10\" maxlength=\"20\" />\n";
    echo "</td><td>\n";
    echo "<select name=\"flag_fontclr\" id=\"flag_fontclr\" tabindex=\"0\">\n";
    foreach($colors as $color)
    {
        if (!empty($color))
        {
            if ($color == $flag['fontclr'])
            {
                echo "<option selected=\"selected\" style=\"color:".$color.";\" value=\"".$color."\">".$color."</option>\n";
            }
            else
            {
                echo "<option style=\"color:".$color.";\" value=\"".$color."\">".$color."</option>\n";
            }
        }
    }
    echo "</select>\n";
    echo "</td><td>\n";
    echo "<input type=\"text\" name=\"flag_lookup_1\" id=\"flag_lookup_1\" value=\"".$flag['lookup_1']."\" size=\"30\" maxlength=\"100\" />\n";
    echo "</td></tr>\n";
    echo "<tr><td colspan=\"3\" style=\"text-align:center;\">\n";
    echo "<input type=\"submit\" name=\"Submit\" id=\"Submit\" value=\""._NQ_SUBMIT."\" />\n";
    echo "&nbsp;<input type=\"submit\" name=\"Cancel\" id=\"Cancel\" value=\""._NQ_CANCEL."\" />\n";
    echo "</td></tr>\n";
    echo "</table>\n";
    echo "</form>\n";
    CloseTable();
    xoops_cp_footer();
    break;
  case "updflag":
    if (!isset($_POST['Submit']) || $_POST['Submit'] != _NQ_SUBMIT)
    {
        redirect_header("index.php?op=flags", 1);
        exit;
    }
    $flag_id = $_POST['flag_id'];
    $flag_keyword = $_POST['flag_keyword'];
    $flag_fontclr = $_POST['flag_fontclr'];
    $flag_lookup_1 = $_POST['flag_lookup_1'];
    if ($getflag = $xoopsDB->query("SELECT * FROM ".$xoopsDB->prefix("netquery_flags")." WHERE flag_id = '$flag_id'"))
    {
        $flag = $xoopsDB->fetchArray($getflag);
    }
    else
    {
        echo "Could not retrieve flag data from the database.";
    }
    $sql = "UPDATE ".$xoopsDB->prefix("netquery_flags")."
        SET keyword  = '" . $flag_keyword . "',
            fontclr  = '" . $flag_fontclr . "',
            lookup_1 = '" . $flag_lookup_1 . "'
        WHERE flag_id = " . $flag_id;
    $xoopsDB->query($sql);
    redirect_header("index.php?op=flags", 1);
    exit;
    break;
  case "remflag":
    $flag_id = $_REQUEST['flag_id'];
    if ($getflag = $xoopsDB->query("SELECT * FROM ".$xoopsDB->prefix("netquery_flags")." WHERE flag_id = '$flag_id'"))
    {
        $flag = $xoopsDB->fetchArray($getflag);
    }
    else
    {
        echo "Could not retrieve flag data from the database.";
    }
    xoops_cp_header();
    OpenTable();
    echo "<form action=\"index.php?op=delflag\" method=\"post\">\n";
    echo "<table class=\"nqinput\">\n";
    echo "<tr><th colspan=\"2\">\n";
    echo _NQ_FLAGS_FORM." ~ ID#:".$flag['flagnum']."\n";
    echo "<input type=\"hidden\" name=\"flag_id\" value=\"".$flag['flag_id']."\" />\n";
    echo "</th></tr>\n";
    echo "<tr><td>\n";
    echo _NQ_KEYWORD.": [".$flag['keyword']."]\n";
    echo "</td><td>\n";
    echo _NQ_FONT.": [".$flag['fontclr']."]\n";
    echo "</td></tr>\n";
    echo "<tr><td colspan=\"2\" style=\"text-align:center;\">\n";
    echo "<input type=\"submit\" name=\"Confirm\" id=\"Comfirm\" value=\""._NQ_CONFIRM."\" />\n";
    echo "&nbsp;<input type=\"submit\" name=\"Cancel\" id=\"Cancel\" value=\""._NQ_CANCEL."\" />\n";
    echo "</td></tr>\n";
    echo "</table>\n";
    echo "</form>\n";
    CloseTable();
    xoops_cp_footer();
    break;
  case "delflag":
    if (!isset($_POST['Confirm']) || $_POST['Confirm'] != _NQ_CONFIRM)
    {
        redirect_header("index.php?op=flags", 1);
        exit;
    }
    $flag_id = $_POST['flag_id'];
    if ($getflag = $xoopsDB->query("SELECT * FROM ".$xoopsDB->prefix("netquery_flags")." WHERE flag_id = '$flag_id'"))
    {
        $flag = $xoopsDB->fetchArray($getflag);
    }
    else
    {
        echo "Could not retrieve flag lookup data from the database.";
    }
    $sql = "DELETE FROM ".$xoopsDB->prefix("netquery_flags")." WHERE flag_id = " . $flag_id;
    $xoopsDB->query($sql);
    redirect_header("index.php?op=flags", 1);
    exit;
    break;
  case "newflag":
    $colors = array('black', 'blue', 'purple', 'red', 'brown', 'orange', 'yellow', 'green', 'cyan', 'violet');
    xoops_cp_header();
    OpenTable();
    echo "<form action=\"index.php?op=addflag\" method=\"post\">\n";
    echo "<table class=\"nqinput\">\n";
    echo "<tr><th colspan=\"4\">\n";
    echo _NQ_FLAGS_FORM." ~ "._NQ_ADDNEW."\n";
    echo "</th></tr>\n";
    echo "<tr><td>\n";
    echo "<label for=\"flag_flagnum\">"._NQ_FLAGNUM."</label>\n";
    echo "</td><td>\n";
    echo "<label for=\"flag_keyword\">"._NQ_KEYWORD."</label>\n";
    echo "</td><td>\n";
    echo "<label for=\"flag_fontclr\">"._NQ_FONT."</label>\n";
    echo "</td><td>\n";
    echo "<label for=\"flag_lookup_1\">"._NQ_FLAGLU1."</label>\n";
    echo "</td></tr><tr><td>\n";
    echo "<input type=\"text\" name=\"flag_flagnum\" id=\"flag_flagnum\" value=\"\" size=\"2\" maxlength=\"9\" />\n";
    echo "</td><td>\n";
    echo "<input type=\"text\" name=\"flag_keyword\" id=\"flag_keyword\" value=\"\" size=\"10\" maxlength=\"20\" />\n";
    echo "</td><td>\n";
    echo "<select name=\"flag_fontclr\" id=\"flag_fontclr\" tabindex=\"0\">\n";
    foreach ($colors as $color)
    {
        if (!empty($color))
        {
            if ($color == 'black')
            {
                echo "<option selected=\"selected\" style=\"color:".$color.";\" value=\"".$color."\">".$color."</option>\n";
            }
            else
            {
                echo "<option style=\"color:".$color.";\" value=\"".$color."\">".$color."</option>\n";
            }
        }
    }
    echo "</select>\n";
    echo "</td><td>\n";
    echo "<input type=\"text\" name=\"flag_lookup_1\" id=\"flag_lookup_1\" value=\"\" size=\"30\" maxlength=\"100\" />\n";
    echo "</td></tr>\n";
    echo "<tr><td colspan=\"4\" style=\"text-align:center\">\n";
    echo "<input type=\"submit\" name=\"Submit\" id=\"Submit\" value=\""._NQ_SUBMIT."\" />\n";
    echo "&nbsp;<input type=\"submit\" name=\"Cancel\" id=\"Cancel\" value=\""._NQ_CANCEL."\" />\n";
    echo "</td></tr>\n";
    echo "</table>\n";
    echo "</form>\n";
    CloseTable();
    xoops_cp_footer();
    break;
  case "addflag":
    if (!isset($_POST['Submit']) || $_POST['Submit'] != _NQ_SUBMIT)
    {
        redirect_header("index.php?op=flags", 1);
        exit;
    }
    $nextId = $xoopsDB->genId($xoopsDB->prefix("netquery_flags")."_flag_id_seq");
    $flag_flagnum = $_POST['flag_flagnum'];
    $flag_keyword = $_POST['flag_keyword'];
    $flag_fontclr = $_POST['flag_fontclr'];
    $flag_lookup_1 = $_POST['flag_lookup_1'];
    $sql = "INSERT INTO ".$xoopsDB->prefix("netquery_flags")." (
        flag_id,
        flagnum,
        keyword,
        fontclr,
        lookup_1)
        VALUES (
        $nextId,
        '" . $flag_flagnum. "',
        '" . $flag_keyword. "',
        '" . $flag_fontclr . "',
        '" . $flag_lookup_1 . "')";
    $xoopsDB->query($sql);
    if ($nextId == 0)
    {
        $nextId = $xoopsDB->getInsertId();
    }
    redirect_header("index.php?op=flags", 1);
    exit;
    break;
  case "bblogedit":
    $bbstats = bb2_stats();
    $bbsettings = bb2_settings();
    $bbmode = (isset($_REQUEST['bbmode'])) ? $_REQUEST['bbmode'] : "select";
    xoops_cp_header();
    echo "<script type=\"text/javascript\" src=\"".XOOPS_URL."/modules/netquery/include/nqUtility.js\"></script>\n";
    OpenTable();
    echo "<table class=\"nqinput\" style=\"".$table_nqinput."\">\n";
    echo "<tr><th style=\"".$table_nqinput_th."\" colspan=\"1\">\n";
    echo _NQ_SPAMBLOCKER_ADMIN."\n";
    echo "</th><th style=\"".$table_nqinput_th.";text-align:right\">\n";
    echo "<a href=\"javascript:NQpopup('".$hlpfile."#admin','console','400','600');\"><img class=\"gobutton\" style=\"".$img_gobutton."\" src=\"".$buttondir."/btn_help.gif\" alt=\""._NQ_MANBUTTON_ALT."\" /></a>";
    echo "</th></tr>\n";
    echo "<tr><td colspan=\"2\">";
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
    echo ('<form action="index.php?op=bblogedit&amp;bbmode=sql" method="post">');
    echo ('<fieldset>');
    echo ('<legend>'._NQ_SPAMBLOCKER_ENTRIESIN.'</legend>');
    echo ('<table>');
    echo ('<tr><td>');
    echo ('Show all where ');
    echo ('<select name="field" size="1">
          <option value="id">ID</option>
          <option value="ip">IP</option>
          <option value="request_entity">Request entity</option>
          <option value="request_method">Request method</option>
          <option value="request_uri">Request URL</option>
          <option value="server_protocol">Server Protocol</option>
          <option value="user_agent">User agent</option>
          <option value="http_headers">HTTP Headers</option>
          <option value="bb_key">Key</option>
         </select> ');
    echo ('<select name="where" size="1">
          <option value="=">Is</option>
          <option value="!=">Is not</option>
          <option value="LIKE">Includes</option>
          </select> ');
    echo ('<input type="Text" name="search" value="" size="10" maxlength="300"> ');
    echo ('Max records: <input type="Text" name="limit" value="30" size="3" maxlength="3"> ');
    echo ('&nbsp;<input type="submit" name="bbmode" value="sql">');
    echo ('</td></tr>');
    echo ('</table>');
    echo ('</fieldset>');
    echo ('</form>');
    switch(strtolower($bbmode))
    {
      case 'all':
        $id = $_REQUEST['id'];
        $query = "SELECT * FROM ".$xoopsDB->prefix("netquery_spamblocker")." WHERE id = ".$id;
        $result = $xoopsDB->query($query);
        $x = $xoopsDB->fetchRow($result);
        list($id, $ip, $date, $request_method, $request_uri, $server_protocol, $user_agent, $http_headers, $request_entity, $bb_key) = $x;
        $response = bb2_response($bb_key);
        $entry = array('id'              => $id,
                       'ip'              => $ip,
                       'date'            => $date,
                       'request_method'  => $request_method,
                       'request_uri'     => $request_uri,
                       'server_protocol' => $server_protocol,
                       'user_agent'      => $user_agent,
                       'http_headers'    => $http_headers,
                       'request_entity'  => $request_entity,
                       'bb_key'          => $bb_key,
                       'response'        => $response['response'],
                       'explanation'     => $response['explanation'],
                       'log'             => $response['log']);
        echo("<table>");
        echo("<tr>
            <th width=\"20px\">id</th>
                <th width=\"40px\">ip</th>
                <th width=\"40px\">date</th>
                <th width=\"20px\">request method</th>
                <th width=\"60px\">request uri</th>
                <th width=\"30px\">server protocol</th>
            </tr>");
        echo("<tr>
                <td>".$entry['id']."</td>
                <td>".$entry['ip']."</td>
                <td>".$entry['date']."</td>
                <td>".$entry['request_method']."</td>
                <td>".$entry['request_uri']."</td>
                <td>".$entry['server_protocol']."</td>
            </tr>");
        echo("<tr>
                <th colspan=\"1\">user agent</th>
                <th colspan=\"3\">http headers</th>
                <th colspan=\"1\">request entity</th>
                <th colspan=\"1\">key</th>
        </tr>");
        echo("<tr>
                <td colspan=\"1\">".$entry['user_agent']."</td>
                <td colspan=\"3\">".$entry['http_headers']."</td>
                <td colspan=\"1\">".$entry['request_entity']."</td>
                <td colspan=\"1\">".$entry['bb_key']."<br />".$entry['response'].":".$entry['log']."</td>
            </tr>");
        echo("</table>");
        echo "<tr><th style=\"".$table_nqinput_th."\" colspan=\"2\">\n";
        echo "[<a style=\"".$table_nqinput_th_alink."\" onmouseover=\"this.style.textDecoration='underline';\" onmouseout=\"this.style.textDecoration='none';\" href=\"index.php?op=bblogedit\">"._NQ_SPAMBLOCKER_BACK."</a>]\n";
        echo "[<a style=\"".$table_nqinput_th_alink."\" onmouseover=\"this.style.textDecoration='underline';\" onmouseout=\"this.style.textDecoration='none';\" href=\"index.php?op=bbdelid&amp;id=".$entry['id']."\">"._NQ_SPAMBLOCKER_DELETE."</a>]\n";
        echo "</th></tr>\n";
        break;
      case 'whoisip':
        $ip_addr = $_REQUEST['ip_addr'];
        $whois_result = adm_whoisip(array('target' => $ip_addr));
        echo ("<table>");
        echo ("<tr><td>"._NQ_SPAMBLOCKER_WHOISIP." ".$ip_addr."</td></tr>");
        echo ("<tr><td>$whois_result</td></tr>");
        echo ("</table>");
        echo "<tr><th style=\"".$table_nqinput_th."\" colspan=\"2\">\n";
        echo "[<a style=\"".$table_nqinput_th_alink."\" onmouseover=\"this.style.textDecoration='underline';\" onmouseout=\"this.style.textDecoration='none';\" href=\"index.php?op=bblogedit\">"._NQ_SPAMBLOCKER_BACK."</a>]\n";
        echo "</th></tr>\n";
        break;
      case 'sql':
        $field = htmlspecialchars($_REQUEST['field']);
        $where = htmlspecialchars($_REQUEST['where']);
        $search = htmlspecialchars($_REQUEST['search']);
        if ($field=="" OR $where=="" OR $search=="")
        {
            $bbmode = 'select';
        }
        else
        {
            if ($where=="LIKE") $search = "%".$search."%";
            if (!is_numeric($search)) $search = "'".$search."'";
            $limit = (isset($_REQUEST['limit'])) ? intval($_REQUEST['limit']) : 30;
            if (empty($limit) OR $limit==0) $limit = 30;
            $query = "SELECT * FROM `".$xoopsDB->prefix("netquery_spamblocker")."` WHERE `$field` $where $search ORDER BY id DESC LIMIT 0,$limit";
            $result = $xoopsDB->query($query);
        }
      case 'select':
      default:
        if ($bbmode!='sql')
        {
            $limit = (isset($_REQUEST['limit'])) ? intval($_REQUEST['limit']) : 30;
            if (empty($limit) OR $limit==0) $limit = 30;
            $query = "SELECT * FROM ".$xoopsDB->prefix("netquery_spamblocker")." ORDER BY id DESC LIMIT 0,$limit";
            $result = $xoopsDB->query($query);
        }
        $bbmode = "select";
        $entries = array();
        while ($x = $xoopsDB->fetchRow($result))
        {
            list($id, $ip, $date, $request_method, $request_uri, $server_protocol, $user_agent, $http_headers, $request_entity, $bb_key) = $x;
            $response = bb2_response($bb_key);
            $entries[] = array('id'              => $id,
                               'ip'              => $ip,
                               'date'            => $date,
                               'request_method'  => $request_method,
                               'request_uri'     => $request_uri,
                               'server_protocol' => $server_protocol,
                               'user_agent'      => $user_agent,
                               'http_headers'    => $http_headers,
                               'request_entity'  => $request_entity,
                               'bb_key'          => $bb_key,
                               'response'        => $response['response'],
                               'explanation'     => $response['explanation'],
                               'log'             => $response['log']);
        }
        echo('<form name="bbselect" id="bbselect" action="index.php?op=bbdelete" method="post">');
        echo('<table>
              <tr>
                 <th><input type="checkbox" name="checker" id="checker" onclick="NQcheckall(bbselect, checker)"></th>
                 <th>Entry</th>
                 <th>Show</th>
                 <th>Whois&nbsp;IP</th>
                 <th>Date&nbsp;Time</th>
                 <th>Key</th>
                 <th>Drop</th>
               </tr>');
        if (!empty($entries))
        {
            foreach ($entries as $entry)
            {
                if (!empty($entry['id']))
                {
                    echo ("<tr>
                           <td><input type=\"checkbox\" name=\"selection[".$entry['id']."]\" id=\"selection[".$entry['id']."]\" value=\"".$entry['id']."\"></td>
                             <td>".$entry['id']."</td>
                             <td><a href=\"index.php?op=bblogedit&amp;bbmode=all&amp;id=".$entry['id']."\">Show</a></td>
                             <td><a href=\"index.php?op=bblogedit&amp;bbmode=whoisip&amp;ip_addr=".$entry['ip']."\">".$entry['ip']."</a></td>
                             <td>".$entry['date']."</td>
                             <td><span title=\"".$entry['response'].": ".$entry['log']."\">".$entry['bb_key']."</span></td>
                             <td><a href=\"index.php?op=bbdelid&amp;id=".$entry['id']."\">Drop</a></td>
                           </tr>");
                }
            }
            echo ('<tr><td colspan="7">
                   <input type="submit" name="DelSel" id="DelSel" value="'._NQ_SPAMBLOCKER_DELSEL.'">
                   <input type="submit" name="DelAll" id="DelAll" value="'._NQ_SPAMBLOCKER_DELALL.'">
                   </td></tr>');
        }
        else
        {
            echo ('<tr><td colspan="7">'._NQ_SPAMBLOCKER_NORECORDS.'</td></tr>');
        }
        echo ('</table>');
        echo ('</form>');
        echo "<tr><th style=\"".$table_nqinput_th."\" colspan=\"2\">\n";
        echo "[<a style=\"".$table_nqinput_th_alink."\" onmouseover=\"this.style.textDecoration='underline';\" onmouseout=\"this.style.textDecoration='none';\" href=\"index.php?op=main\">"._NQ_RETMAIN."</a>]\n";
        echo "</th></tr>\n";
        break;
      }
    echo "</table>\n";
    echo ('<p align="center">'._NQ_SPAMBLOCKER_CREDIT.' Bad Behavior '.$bbsettings['version'].'<br />
          '._NQ_SPAMBLOCKER_INFOSITE.' <a href="http://www.homelandstupidity.us/software/bad-behavior/">Bad Behavior</a> '._NQ_SPAMBLOCKER_HOMEPAGE.'.</p>');
    CloseTable();
    xoops_cp_footer();
    break;
  case 'bbdelid':
    $id = $_REQUEST['id'];
    $query = "DELETE FROM ".$xoopsDB->prefix("netquery_spamblocker")." WHERE id = ".$id;
    $xoopsDB->queryF($query);
    redirect_header("index.php?op=bblogedit", 1);
    exit;
    break;
  case 'bbdelete':
    if (isset($_POST['DelSel']) AND $_POST['DelSel'] == _NQ_SPAMBLOCKER_DELSEL)
    {
        $selection = $_REQUEST['selection'];
        $selected = implode(",", $selection);
        $query = "DELETE FROM ".$xoopsDB->prefix("netquery_spamblocker")." WHERE id IN (".$selected.")";
        $xoopsDB->query($query);
    } else if (isset($_POST['DelAll']) AND $_POST['DelAll'] == _NQ_SPAMBLOCKER_DELALL)
    {
        $query = "TRUNCATE TABLE ".$xoopsDB->prefix("netquery_spamblocker")."";
        $xoopsDB->query($query);
    }
    redirect_header("index.php?op=bblogedit", 1);
    exit;
    break;
  case "none":
  default:
    break;
}
?>