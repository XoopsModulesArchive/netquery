<?php
// ------------------------------------------------------------------------- //
// Original Author : Richard Virtue - http://www.virtech.org/
// Licence Type : Public GNU/GPL
// ------------------------------------------------------------------------- //
include_once '../../mainfile.php';
if ( file_exists("language/".$xoopsConfig['language']."/main.php") ) {
        include_once "language/".$xoopsConfig['language']."/main.php";
} else {
        include_once "language/english/main.php";
}
global $xoopsDB, $xoopsModule;
$user_op = (isset($_REQUEST['user_op'])) ? $_REQUEST['user_op'] : 'newport';
if ($user_op == 'newport')
{
    $portnum = (isset($_REQUEST['portnum'])) ? $_REQUEST['portnum'] : '80';
    include_once XOOPS_ROOT_PATH."/header.php";
    OpenTable();
    echo "<form action=\"submit.php\" method=\"post\">\n";
    echo "<table class=\"nqinput\">\n";
    echo "<tr><th colspan=\"4\">\n";
    echo "Submit a New Port Service or Exploit\n";
    echo "<input type=\"hidden\" name=\"user_op\" value=\"addport\" />\n";
    echo "<input type=\"hidden\" name=\"portnum\" value=\"".$portnum."\" />\n";
    echo "<input type=\"hidden\" name=\"port_flag\" value=\"99\" />\n";
    echo "</th></tr>\n";
    echo "<tr><th>\n";
    echo "<span class=\"help\" title=\"The port number\"><label for=\"port_port\">Port</label></span>\n";
    echo "</td><th>\n";
    echo "<span class=\"help\" title=\"The port protocol\"><label for=\"port_protocol\">Protocol</label></span>\n";
    echo "</td><th>\n";
    echo "<span class=\"help\" title=\"The port service\"><label for=\"port_service\">Service/Exploit</label></span>\n";
    echo "</td><th>\n";
    echo "<span class=\"help\" title=\"Notes and comments\"><label for=\"port_comment\">Comment</label></span>\n";
    echo "</th></tr>\n";
    echo "<tr><td>\n";
    echo "<input type=\"text\" name=\"port_port\" id=\"port_port\" value=\"".$portnum."\" size=\"2\" maxlength=\"5\" />\n";
    echo "</td><td>\n";
    echo "<input type=\"text\" name=\"port_protocol\" id=\"port_protocol\" value=\"\" size=\"3\" maxlength=\"3\" />\n";
    echo "</td><td>\n";
    echo "<input type=\"text\" name=\"port_service\" id=\"port_service\" value=\"\" size=\"20\" maxlength=\"35\" />\n";
    echo "</td><td>\n";
    echo "<input type=\"text\" name=\"port_comment\" id=\"port_comment\" value=\"\" size=\"25\" maxlength=\"50\" />\n";
    echo "</td></tr><tr><td colspan=\"4\" class=\"nq-center\">\n";
    echo "<input type=\"submit\" name=\"Submit\" id=\"Submit\" value=\""._NQ_SUBMIT."\" />\n";
    echo "&nbsp;<input type=\"submit\" name=\"Cancel\" id=\"Cancel\" value=\""._NQ_CANCEL."\" />\n";
    echo "</td></tr>\n";
    echo "</table>\n";
    echo "</form>\n";
    CloseTable();
    include_once XOOPS_ROOT_PATH."/footer.php";
}
if ($user_op == 'addport')
{
    if (!isset($_POST['Submit']) || $_POST['Submit'] != _NQ_SUBMIT) {redirect_header("index.php" ,1);}
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
    if ($nextId == 0) {
        $nextId = $xoopsDB->getInsertId();
    }
    redirect_header("submit.php?user_op=thankyou", 1);
}
if ($user_op == 'thankyou')
{
    include_once XOOPS_ROOT_PATH."/header.php";
    echo "<h2 align=\"center\">Thank You</a></h2>\n";
    echo "<p>Your submission has been processed for the administrator's attention.\n";
    echo "Upon approval, it will be visible in the services and exploits listing for the port specified.</p>\n";
    echo "<p>Please click <a href=\"index.php\">HERE</a> to return to the Netquery user interface.</p>\n";
    include_once XOOPS_ROOT_PATH."/footer.php";
}
?>