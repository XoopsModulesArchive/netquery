<?php
include_once '../../../include/cp_header.php';
global $xoopsDB, $xoopsModule;
$step = (isset($_REQUEST['step'])) ? $_REQUEST['step'] : '1';
if ($step == '1')
{
$body = '<br /><br />The first step in building a new table is to delete the existing geoip table along with the related geocc table.';
$body .= ' Unless it has been backed up, all of the data contained in both tables will be lost.';
$body .= '<br /><br />Do you wish to proceed?: [<a href="xogeoip.php?step=2">Yes</a>] [<a href="index.php">No</a>]<br /><br />';
}
if ($step == '2')
{
$query = "DROP TABLE IF EXISTS ".$xoopsDB->prefix('netquery_geocc');
$xoopsDB->queryF($query);
$query = "CREATE TABLE ".$xoopsDB->prefix('netquery_geocc')." (
        `ci` tinyint(3) unsigned NOT NULL auto_increment,
        `cc` char(2) NOT NULL default '',
        `cn` varchar(50) NOT NULL default '',
        `lat` decimal(7,4) NOT NULL default '0.0000',
        `lon` decimal(7,4) NOT NULL default '0.0000',
        `users` mediumint(9) unsigned NOT NULL default '0',
        PRIMARY KEY  (`ci`) )";
$xoopsDB->queryF($query);
$query = "INSERT INTO ".$xoopsDB->prefix('netquery_geocc')." VALUES
(1, 'A0', 'Reserved Address', 0.0000, 0.0000, 0),
(2, 'A1', 'Anonymous Proxy', 0.0000, 0.0000, 0),
(3, 'A2', 'Satellite Provider', 0.0000, 0.0000, 0),
(4, 'A3', 'Private Address', 0.0000, 0.0000, 0),
(5, 'AD', 'Andorra', 42.5000, 1.5000, 0),
(6, 'AE', 'United Arab Emirates', 24.0000, 54.0000, 0),
(7, 'AF', 'Afghanistan', 33.0000, 65.0000, 0),
(8, 'AG', 'Antigua and Barbuda', 17.0500, -61.8000, 0),
(9, 'AI', 'Anguilla', 18.2500, -63.1667, 0),
(10, 'AL', 'Albania', 41.0000, 20.0000, 0),
(11, 'AM', 'Armenia', 40.0000, 45.0000, 0),
(12, 'AN', 'Netherlands Antilles', 12.2500, -68.7500, 0),
(13, 'AO', 'Angola', -12.5000, 18.5000, 0),
(14, 'AP', 'Asia-Pacific Region', 35.0000, 105.0000, 0),
(15, 'AQ', 'Antarctica', -90.0000, 0.0000, 0),
(16, 'AR', 'Argentina', -34.0000, -64.0000, 0),
(17, 'AS', 'American Samoa', -14.3333, -170.0000, 0),
(18, 'AT', 'Austria', 47.3333, 13.3333, 0),
(19, 'AU', 'Australia', -27.0000, 133.0000, 0),
(20, 'AW', 'Aruba', 12.5000, -69.9667, 0),
(21, 'AX', 'Aland Islands', 60.0000, 20.0000, 0),
(22, 'AZ', 'Azerbaijan', 40.5000, 47.5000, 0),
(23, 'BA', 'Bosnia and Herzegovina', 44.0000, 18.0000, 0),
(24, 'BB', 'Barbados', 13.1667, -59.5333, 0),
(25, 'BD', 'Bangladesh', 24.0000, 90.0000, 0),
(26, 'BE', 'Belgium', 50.8333, 4.0000, 0),
(27, 'BF', 'Burkina Faso', 13.0000, -2.0000, 0),
(28, 'BG', 'Bulgaria', 43.0000, 25.0000, 0),
(29, 'BH', 'Bahrain', 26.0000, 50.5500, 0),
(30, 'BI', 'Burundi', -3.5000, 30.0000, 0),
(31, 'BJ', 'Benin', 9.5000, 2.2500, 0),
(32, 'BM', 'Bermuda', 32.3333, -64.7500, 0),
(33, 'BN', 'Brunei Darussalam', 4.5000, 114.6667, 0),
(34, 'BO', 'Bolivia', -17.0000, -65.0000, 0),
(35, 'BR', 'Brazil', -10.0000, -55.0000, 0),
(36, 'BS', 'Bahamas', 24.2500, -76.0000, 0),
(37, 'BT', 'Bhutan', 27.5000, 90.5000, 0),
(38, 'BV', 'Bouvet Island', -54.4333, 3.4000, 0),
(39, 'BW', 'Botswana', -22.0000, 24.0000, 0),
(40, 'BY', 'Belarus', 53.0000, 28.0000, 0),
(41, 'BZ', 'Belize', 17.2500, -88.7500, 0),
(42, 'CA', 'Canada', 60.0000, -95.0000, 0),
(43, 'CC', 'Cocos (Keeling) Islands', 35.0000, 105.0000, 0),
(44, 'CD', 'Congo', 0.0000, 25.0000, 0),
(45, 'CF', 'Central African Republic', 7.0000, 21.0000, 0),
(46, 'CG', 'Congo', -1.0000, 15.0000, 0),
(47, 'CH', 'Switzerland', 47.0000, 8.0000, 0),
(48, 'CI', 'Cote D''Ivoire', 8.0000, -5.0000, 0),
(49, 'CK', 'Cook Islands', -21.2333, -159.7667, 0),
(50, 'CL', 'Chile', -30.0000, -71.0000, 0),
(51, 'CM', 'Cameroon', 6.0000, 12.0000, 0),
(52, 'CN', 'China', 35.0000, 105.0000, 0),
(53, 'CO', 'Colombia', 4.0000, -72.0000, 0),
(54, 'CR', 'Costa Rica', 10.0000, -84.0000, 0),
(55, 'CU', 'Cuba', 21.5000, -80.0000, 0),
(56, 'CV', 'Cape Verde', 16.0000, -24.0000, 0),
(57, 'CX', 'Christmas Island', -10.5000, 105.6667, 0),
(58, 'CY', 'Cyprus', 35.0000, 33.0000, 0),
(59, 'CZ', 'Czech Republic', 49.7500, 15.5000, 0),
(60, 'DE', 'Germany', 51.0000, 9.0000, 0),
(61, 'DJ', 'Djibouti', 11.5000, 43.0000, 0),
(62, 'DK', 'Denmark', 56.0000, 10.0000, 0),
(63, 'DM', 'Dominica', 15.4167, -61.3333, 0),
(64, 'DO', 'Dominican Republic', 19.0000, -70.6667, 0),
(65, 'DZ', 'Algeria', 28.0000, 3.0000, 0),
(66, 'EC', 'Ecuador', -2.0000, -77.5000, 0),
(67, 'EE', 'Estonia', 59.0000, 26.0000, 0),
(68, 'EG', 'Egypt', 27.0000, 30.0000, 0),
(69, 'EH', 'Western Sahara', 24.5000, -13.0000, 0),
(70, 'ER', 'Eritrea', 15.0000, 39.0000, 0),
(71, 'ES', 'Spain', 40.0000, -4.0000, 0),
(72, 'ET', 'Ethiopia', 8.0000, 38.0000, 0),
(73, 'EU', 'Europe', 47.0000, 8.0000, 0),
(74, 'FI', 'Finland', 64.0000, 26.0000, 0),
(75, 'FJ', 'Fiji', -18.0000, 175.0000, 0),
(76, 'FK', 'Falkland Islands', -51.7500, -59.0000, 0),
(77, 'FM', 'Micronesia', 6.9167, 158.2500, 0),
(78, 'FO', 'Faroe Islands', 62.0000, -7.0000, 0),
(79, 'FR', 'France', 46.0000, 2.0000, 0),
(80, 'GA', 'Gabon', -1.0000, 11.7500, 0),
(81, 'GB', 'United Kingdom', 54.0000, -2.0000, 0),
(82, 'GD', 'Grenada', 12.1167, -61.6667, 0),
(83, 'GE', 'Georgia', 42.0000, 43.5000, 0),
(84, 'GF', 'French Guiana', 4.0000, -53.0000, 0),
(85, 'GG', 'Guernsey', 49.4603, -2.5270, 0),
(86, 'GH', 'Ghana', 8.0000, -2.0000, 0),
(87, 'GI', 'Gibraltar', 36.1833, -5.3667, 0),
(88, 'GL', 'Greenland', 72.0000, -40.0000, 0),
(89, 'GM', 'Gambia', 13.4667, -16.5667, 0),
(90, 'GN', 'Guinea', 11.0000, -10.0000, 0),
(91, 'GP', 'Guadeloupe', 16.2500, -61.5833, 0),
(92, 'GQ', 'Equatorial Guinea', 2.0000, 10.0000, 0),
(93, 'GR', 'Greece', 39.0000, 22.0000, 0),
(94, 'GS', 'South Georgia', -54.5000, -37.0000, 0),
(95, 'GT', 'Guatemala', 15.5000, -90.2500, 0),
(96, 'GU', 'Guam', 13.4667, 144.7833, 0),
(97, 'GW', 'Guinea-Bissau', 12.0000, -15.0000, 0),
(98, 'GY', 'Guyana', 5.0000, -59.0000, 0),
(99, 'HK', 'Hong Kong', 22.2500, 114.1667, 0),
(100, 'HM', 'Heard and McDonald Islands', -53.1000, 72.5167, 0),
(101, 'HN', 'Honduras', 15.0000, -86.5000, 0),
(102, 'HR', 'Croatia', 45.1667, 15.5000, 0),
(103, 'HT', 'Haiti', 19.0000, -72.4167, 0),
(104, 'HU', 'Hungary', 47.0000, 20.0000, 0),
(105, 'ID', 'Indonesia', -5.0000, 120.0000, 0),
(106, 'IE', 'Ireland', 53.0000, -8.0000, 0),
(107, 'IL', 'Israel', 31.5000, 34.7500, 0),
(108, 'IM', 'Isle of Man', 54.2307, -4.5697, 0),
(109, 'IN', 'India', 20.0000, 77.0000, 0),
(110, 'IO', 'British Indian Ocean Territory', -6.0000, 71.5000, 0),
(111, 'IQ', 'Iraq', 33.0000, 44.0000, 0),
(112, 'IR', 'Iran', 32.0000, 53.0000, 0),
(113, 'IS', 'Iceland', 65.0000, -18.0000, 0),
(114, 'IT', 'Italy', 42.8333, 12.8333, 0),
(115, 'JE', 'Jersey', 49.1919, -2.1071, 0),
(116, 'JM', 'Jamaica', 18.2500, -77.5000, 0),
(117, 'JO', 'Jordan', 31.0000, 36.0000, 0),
(118, 'JP', 'Japan', 36.0000, 138.0000, 0),
(119, 'KE', 'Kenya', 1.0000, 38.0000, 0),
(120, 'KG', 'Kyrgyzstan', 41.0000, 75.0000, 0),
(121, 'KH', 'Cambodia', 13.0000, 105.0000, 0),
(122, 'KI', 'Kiribati', 1.4167, 173.0000, 0),
(123, 'KM', 'Comoros', -12.1667, 44.2500, 0),
(124, 'KN', 'Saint Kitts and Nevis', 17.3333, -62.7500, 0),
(125, 'KP', 'Korea, DPR', 40.0000, 127.0000, 0),
(126, 'KR', 'Korea, ROK', 37.0000, 127.5000, 0),
(127, 'KW', 'Kuwait', 29.5000, 45.7500, 0),
(128, 'KY', 'Cayman Islands', 19.5000, -80.5000, 0),
(129, 'KZ', 'Kazakstan', 48.0000, 68.0000, 0),
(130, 'LA', 'Lao People''s Democratic Republic', 18.0000, 105.0000, 0),
(131, 'LB', 'Lebanon', 33.8333, 35.8333, 0),
(132, 'LC', 'Saint Lucia', 13.8833, -61.1333, 0),
(133, 'LI', 'Liechtenstein', 47.1667, 9.5333, 0),
(134, 'LK', 'Sri Lanka', 7.0000, 81.0000, 0),
(135, 'LR', 'Liberia', 6.5000, -9.5000, 0),
(136, 'LS', 'Lesotho', -29.5000, 28.5000, 0),
(137, 'LT', 'Lithuania', 56.0000, 24.0000, 0),
(138, 'LU', 'Luxembourg', 49.7500, 6.1667, 0),
(139, 'LV', 'Latvia', 57.0000, 25.0000, 0),
(140, 'LY', 'Libyan Arab Jamahiriya', 25.0000, 17.0000, 0),
(141, 'MA', 'Morocco', 32.0000, -5.0000, 0),
(142, 'MC', 'Monaco', 43.7333, 7.4000, 0),
(143, 'MD', 'Moldova', 47.0000, 29.0000, 0),
(144, 'ME', 'Montenegro', 44.0000, 21.0000, 0),
(145, 'MG', 'Madagascar', -20.0000, 47.0000, 0),
(146, 'MH', 'Marshall Islands', 9.0000, 168.0000, 0),
(147, 'MK', 'Macedonia', 41.8333, 22.0000, 0),
(148, 'ML', 'Mali', 17.0000, -4.0000, 0),
(149, 'MM', 'Myanmar', 22.0000, 98.0000, 0),
(150, 'MN', 'Mongolia', 46.0000, 105.0000, 0),
(151, 'MO', 'Macau', 22.1667, 113.5500, 0),
(152, 'MP', 'Northern Mariana Islands', 15.2000, 145.7500, 0),
(153, 'MQ', 'Martinique', 14.6667, -61.0000, 0),
(154, 'MR', 'Mauritania', 20.0000, -12.0000, 0),
(155, 'MS', 'Montserrat', 16.7500, -62.2000, 0),
(156, 'MT', 'Malta', 35.8333, 14.5833, 0),
(157, 'MU', 'Mauritius', -20.2833, 57.5500, 0),
(158, 'MV', 'Maldives', 3.2500, 73.0000, 0),
(159, 'MW', 'Malawi', -13.5000, 34.0000, 0),
(160, 'MX', 'Mexico', 23.0000, -102.0000, 0),
(161, 'MY', 'Malaysia', 2.5000, 112.5000, 0),
(162, 'MZ', 'Mozambique', -18.2500, 35.0000, 0),
(163, 'NA', 'Namibia', -22.0000, 17.0000, 0),
(164, 'NC', 'New Caledonia', -21.5000, 165.5000, 0),
(165, 'NE', 'Niger', 16.0000, 8.0000, 0),
(166, 'NF', 'Norfolk Island', -29.0333, 167.9500, 0),
(167, 'NG', 'Nigeria', 10.0000, 8.0000, 0),
(168, 'NI', 'Nicaragua', 13.0000, -85.0000, 0),
(169, 'NL', 'Netherlands', 52.5000, 5.7500, 0),
(170, 'NO', 'Norway', 62.0000, 10.0000, 0),
(171, 'NP', 'Nepal', 28.0000, 84.0000, 0),
(172, 'NR', 'Nauru', -0.5333, 166.9167, 0),
(173, 'NU', 'Niue', -19.0333, -169.8667, 0),
(174, 'NZ', 'New Zealand', -41.0000, 174.0000, 0),
(175, 'OM', 'Oman', 21.0000, 57.0000, 0),
(176, 'PA', 'Panama', 9.0000, -80.0000, 0),
(177, 'PE', 'Peru', -10.0000, -76.0000, 0),
(178, 'PF', 'French Polynesia', -15.0000, -140.0000, 0),
(179, 'PG', 'Papua New Guinea', -6.0000, 147.0000, 0),
(180, 'PH', 'Philippines', 13.0000, 122.0000, 0),
(181, 'PK', 'Pakistan', 30.0000, 70.0000, 0),
(182, 'PL', 'Poland', 52.0000, 20.0000, 0),
(183, 'PM', 'Saint Pierre and Miquelon', 46.8333, -56.3333, 0),
(184, 'PN', 'Pitcairn Islands', 25.0667, -130.0833, 0),
(185, 'PR', 'Puerto Rico', 18.2500, -66.5000, 0),
(186, 'PS', 'Palestinian Territory', 32.0000, 35.2500, 0),
(187, 'PT', 'Portugal', 39.5000, -8.0000, 0),
(188, 'PW', 'Palau', 7.5000, 134.5000, 0),
(189, 'PY', 'Paraguay', -23.0000, -58.0000, 0),
(190, 'QA', 'Qatar', 25.5000, 51.2500, 0),
(191, 'RE', 'Reunion', -21.1000, 55.6000, 0),
(192, 'RO', 'Romania', 46.0000, 25.0000, 0),
(193, 'RS', 'Serbia', 44.0000, 21.0000, 0),
(194, 'RU', 'Russia', 60.0000, 100.0000, 0),
(195, 'RW', 'Rwanda', -2.0000, 30.0000, 0),
(196, 'SA', 'Saudi Arabia', 25.0000, 45.0000, 0),
(197, 'SB', 'Solomon Islands', -8.0000, 159.0000, 0),
(198, 'SC', 'Seychelles', -4.5833, 55.6667, 0),
(199, 'SD', 'Sudan', 15.0000, 30.0000, 0),
(200, 'SE', 'Sweden', 62.0000, 15.0000, 0),
(201, 'SG', 'Singapore', 1.3667, 103.8000, 0),
(202, 'SH', 'Saint Helena', -15.9333, -5.7000, 0),
(203, 'SI', 'Slovenia', 46.0000, 15.0000, 0),
(204, 'SJ', 'Svalbard', 78.0000, 20.0000, 0),
(205, 'SK', 'Slovakia', 48.6667, 19.5000, 0),
(206, 'SL', 'Sierra Leone', 8.5000, -11.5000, 0),
(207, 'SM', 'San Marino', 43.7667, 12.4167, 0),
(208, 'SN', 'Senegal', 14.0000, -14.0000, 0),
(209, 'SO', 'Somalia', 10.0000, 49.0000, 0),
(210, 'SR', 'Suriname', 4.0000, -56.0000, 0),
(211, 'ST', 'Sao Tome and Principe', 1.0000, 7.0000, 0),
(212, 'SU', 'Russian Federation', 60.0000, 100.0000, 0),
(213, 'SV', 'El Salvador', 13.8333, -88.9167, 0),
(214, 'SY', 'Syrian Arab Republic', 35.0000, 38.0000, 0),
(215, 'SZ', 'Swaziland', -26.5000, 31.5000, 0),
(216, 'TC', 'Turks and Caicos Islands', 21.7500, -71.5833, 0),
(217, 'TD', 'Chad', 15.0000, 19.0000, 0),
(218, 'TF', 'French Southern Territories', -43.0000, 67.0000, 0),
(219, 'TG', 'Togo', 8.0000, 1.1667, 0),
(220, 'TH', 'Thailand', 15.0000, 100.0000, 0),
(221, 'TJ', 'Tajikistan', 39.0000, 71.0000, 0),
(222, 'TK', 'Tokelau', -9.0000, -172.0000, 0),
(223, 'TL', 'Timor-Leste', -8.8333, 125.7500, 0),
(224, 'TM', 'Turkmenistan', 40.0000, 60.0000, 0),
(225, 'TN', 'Tunisia', 34.0000, 9.0000, 0),
(226, 'TO', 'Tonga', -20.0000, -175.0000, 0),
(227, 'TR', 'Turkey', 39.0000, 35.0000, 0),
(228, 'TT', 'Trinidad and Tobago', 11.0000, -61.0000, 0),
(229, 'TV', 'Tuvalu', -8.0000, 178.0000, 0),
(230, 'TW', 'Taiwan', 23.5000, 121.0000, 0),
(231, 'TZ', 'Tanzania', -6.0000, 35.0000, 0),
(232, 'UA', 'Ukraine', 49.0000, 32.0000, 0),
(233, 'UG', 'Uganda', 1.0000, 32.0000, 0),
(234, 'UK', 'United Kingdom', 54.0000, -2.0000, 0),
(235, 'UM', 'United States Minor Outlying Islands', 19.2833, 166.6000, 0),
(236, 'US', 'United States', 38.0000, -97.0000, 0),
(237, 'UY', 'Uruguay', -33.0000, -56.0000, 0),
(238, 'UZ', 'Uzbekistan', 41.0000, 64.0000, 0),
(239, 'VA', 'Holy See (Vatican City State)', 41.9000, 12.4500, 0),
(240, 'VC', 'Saint Vincent and the Grenadines', 13.2500, -61.2000, 0),
(241, 'VE', 'Venezuela', 8.0000, -66.0000, 0),
(242, 'VG', 'Virgin Islands, British', 18.5000, -64.5000, 0),
(243, 'VI', 'Virgin Islands, U.S.', 18.3333, -64.8333, 0),
(244, 'VN', 'Vietnam', 16.0000, 106.0000, 0),
(245, 'VU', 'Vanuatu', -16.0000, 167.0000, 0),
(246, 'WF', 'Wallis and Futuna', -13.3000, -176.2000, 0),
(247, 'WS', 'Samoa', -13.5833, -172.3333, 0),
(248, 'YE', 'Yemen', 15.0000, 48.0000, 0),
(249, 'YT', 'Mayotte', -12.8333, 45.1667, 0),
(250, 'ZA', 'South Africa', -29.0000, 24.0000, 0),
(251, 'ZM', 'Zambia', -15.0000, 30.0000, 0),
(252, 'ZW', 'Zimbabwe', -20.0000, 30.0000, 0)";
$xoopsDB->queryF($query);
$query = "DROP TABLE IF EXISTS ".$xoopsDB->prefix('netquery_geoip');
$xoopsDB->queryF($query);
$query = "CREATE TABLE ".$xoopsDB->prefix('netquery_geoip')." (
       `start` int(10) unsigned NOT NULL default '0',
       `end` int(10) unsigned NOT NULL default '0',
       `ci` tinyint(3) unsigned NOT NULL default '0' )";
$xoopsDB->queryF($query);
$query = "INSERT INTO ".$xoopsDB->prefix('netquery_geoip')." VALUES
(0, 16777215, 1),               # Reserved block 0/8
(167772160, 184549375, 4),      # Private address block 10/8 (Class A)
(2130706432, 2147483647, 4),    # Private address block 127/8 (Loopback)
(2147483648, 2147549183, 1),    # Reserved block 128.0/16
(2851995648, 2852061183, 4),    # Private address block 169.254/16 (Class B for DHCP)
(2886729728, 2887778303, 4),    # Private address blocks 172.16/12 (Class B x16 contiguous)
(3221159936, 3221225471, 1),    # Reserved block 191.255/16
(3221225472, 3221225727, 1),    # Reserved block 192.0.0/24
(3232235520, 3232301055, 4),    # Private address blocks 192.168/16 (Class C x256 contiguous)
(3758096128, 3758096383, 1)";   # Reserved block 223.255.255/24
$xoopsDB->queryF($query);
$body = '<br /><br />New geoip and geocc tables have been created and populated with a few initial entries.';
$body .= '<br /><br />Do you wish to fully populate the geoip table?: [<a href="xogeoip2.php?step=3">Yes</a>] [<a href="index.php">No</a>]<br /><br />';
}
xoops_cp_header();
echo '<font size="6"><div align="center">Netquery GeoIP Table Setup: Step '.$step.'</div></font>';
echo $body;
xoops_cp_footer();
?>