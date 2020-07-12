<?php
$isIE = false;
if (eregi("MSIE", $_SERVER['HTTP_USER_AGENT'])) $isIE = true;
$table_nqinput = 'text-align:left;color:#000000;background-color:#D0E0F0;';
$table_nqinput_th = 'color:#FFFFFF;background-color:#2F5376;';
$table_nqinput_th_alink = 'color:#D0E0F0;background-color:transparent;text-decoration:none;';
$form_nqadmin = 'text-align:left;color:#FFFFFF;background-color:#2F5376;margin:0 auto;width:42em;padding:3px 3px 3px 3px;';
if ($isIE) {
$form_nqadmin_fieldset = 'text-align:left;color:#000000;background-color:#D0E0F0;border-style:solid;border-color:#003366;border-width:1px;padding:0 10px 10px 10px;padding-top:10px;margin:20px 0 20px 0;margin-top:30px;position:relative;display:block;';
$form_nqadmin_legend = 'font-weight:bold;color:#FFFFFF;border-color:#3070A0;background-color:#3070A0;background-image:url('.XOOPS_URL.'/modules/netquery/styles/gradients/aquagrad.png);background-position:0px -120px;background-repeat:repeat-x;min-width:16em;width:16em;border-style:solid;border-color:#003366;border-width:1px;padding:2px;margin:2px;top:-12px;margin-top:-1px;position:absolute;';
} else {
$form_nqadmin_fieldset = 'text-align:left;color:#000000;background-color:#D0E0F0;border-style:solid;border-color:#003366;border-width:1px;padding:0 10px 10px 10px;margin:20px 0 20px 0;position:static;display:block;';
$form_nqadmin_legend = 'font-weight:bold;color:#FFFFFF;border-color:#3070A0;background-color:#3070A0;background-image:url('.XOOPS_URL.'/modules/netquery/styles/gradients/aquagrad.png);background-position:0px -120px;background-repeat:repeat-x;min-width:16em;width:16em;border-style:solid;border-color:#003366;border-width:1px;padding:2px;margin:2px;position:static;display:block;';
}
$input_gobutton = $img_gobutton = 'font-weight:bold;color:#FFFFFF;background-color:#3070A0;background-image:url('.XOOPS_URL.'/modules/netquery/styles/gradients/aquagrad.png);background-position:0px -120px;background-repeat:repeat-x;width:21px;height:22px;border:0;padding:0;margin:0 0 0 2px;vertical-align:top;';
$input_gobuttonup = $img_gobuttonup = 'font-weight:bold;color:#FFFFFF;background-color:#3070A0;background-image:url('.XOOPS_URL.'/modules/netquery/styles/gradients/aquagrad.png);background-position:0px -120px;background-repeat:repeat-x;width:21px;height:22px;border:0;padding:0;margin:0 0 0 2px;vertical-align:top;top:-23px;position:relative;';
$img_geoflag = 'height:20px;width:32px;border:0;padding:0;margin:2px 2px 0px 0px;vertical-align:middle;';
?>