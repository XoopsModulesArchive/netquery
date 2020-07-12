<?php
// ------------------------------------------------------------------------- //
// Original Author : Richard Virtue - http://virtech.org/
// Licence Type : Public GNU/GPL
// ------------------------------------------------------------------------- //
include_once '../../../include/cp_header.php';
    global $xoopsDB, $xoopsModule;
    $portnum = (isset($_REQUEST['portnum'])) ? $_REQUEST['portnum'] : '80';
    $ports = array();
    if ($getports = $xoopsDB->query("SELECT * FROM ".$xoopsDB->prefix("netquery_ports")." WHERE flag < 99 AND port = '$portnum'")) {
        while ( $ports[] = $xoopsDB->fetchArray($getports) ) {
        }
    }
    OpenTable();
    echo "<table class=\"nqoutput\">\n";
    echo "<tr><th colspan=\"4\">\n";
    echo "List of Services and Exploits for Port ".$portnum."\n";
    echo "</th></tr>\n";
    echo "<tr><th>Protocol</th><th>Service</th><th>Notes</th><th>Flag</th></tr>\n";
    foreach ($ports as $port)
    {
        if ($port['port_id']) {
            echo "<tr><td>".$port['protocol']."</td><td>".$port['service']."</td>\n";
            echo "<td>".$port['comment']."</td><td>".$port['flag']."</td></tr>\n";
        }
    }
    echo "</table>\n";
    echo '<p style="text-align:center" class="smallfont">
         <form><input type=button value="Close Window" onClick="javascript:window.close();">
         </form></p>';
    CloseTable();
?>