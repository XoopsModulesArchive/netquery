# phpMyAdmin SQL Dump
# version 2.9.0.1
# http://www.phpmyadmin.net
#
# --------------------------------------------------------

#
# Table structure for table `netquery_whois`
#

CREATE TABLE `netquery_whois` (
  `whois_id` mediumint(9) NOT NULL auto_increment,
  `whois_tld` varchar(10) NOT NULL default '',
  `whois_server` varchar(50) NOT NULL default '',
  `whois_prefix` varchar(20) NOT NULL default '',
  `whois_suffix` varchar(20) NOT NULL default '',
  `whois_unfound` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`whois_id`),
  UNIQUE KEY `keyword` (`whois_tld`)
) ;

#
# Dumping data for table `netquery_whois`
#

INSERT INTO `netquery_whois` (`whois_id`, `whois_tld`, `whois_server`, `whois_prefix`, `whois_suffix`, `whois_unfound`) VALUES
(1, 'ac', 'whois.nic.ac', '', '', 'No match'),
(2, 'ad', 'whois.ripe.net', '', '', 'no entries found'),
(3, 'ag', 'whois.nic.ag', '', '', 'NOT FOUND'),
(4, 'al', 'whois.ripe.net', '', '', 'no entries found'),
(5, 'am', 'amnic.net', '', '', 'No match'),
(6, 'as', 'whois.nic.as', '', '', 'Domain Not Found'),
(7, 'at', 'whois.nic.at', '', '', 'nothing found'),
(8, 'au', 'whois.ausregistry.com.au', '', '', 'No Data Found'),
(9, 'az', 'whois.ripe.net', '', '', 'no entries found'),
(10, 'ba', 'whois.ripe.net', '', '', 'no entries found'),
(11, 'be', 'whois.dns.be', '', '', ' FREE\n'),
(12, 'bg', 'whois.ripe.net', '', '', 'no entries found'),
(13, 'biz', 'whois.neulevel.biz', '', '', 'Not found'),
(14, 'br', 'whois.nic.br', '', '', 'No match for domain'),
(15, 'by', 'whois.ripe.net', '', '', 'no entries found'),
(16, 'bz', 'whois.belizenic.bz', '', '', 'NOMATCH'),
(17, 'ca', 'whois.cira.ca', '', '', ' AVAIL\n'),
(18, 'cat', 'whois.cat', '', '', 'NOT FOUND'),
(19, 'cc', 'whois.nic.cc', '', '', 'No match'),
(20, 'ch', 'whois.nic.ch', '', '', 'We do not have an entry'),
(21, 'cl', 'nic.cl', '', '', 'no existe'),
(22, 'cn', 'whois.cnnic.net.cn', '', '', 'no matching record'),
(23, 'com', 'whois.crsnic.net', '', '', 'No match'),
(24, 'coop', 'whois.nic.coop', '', '', 'No domain records were found'),
(25, 'cx', 'whois.nic.cx', '', '', 'Not Registered'),
(26, 'cy', 'whois.ripe.net', '', '', 'no entries found'),
(27, 'cz', 'whois.nic.cz', '', '', 'No data found'),
(28, 'de', 'whois.denic.de', '-T ace,dn', '', 'not found in database'),
(29, 'dk', 'whois.dk-hostmaster.dk', '', '', 'No entries found'),
(30, 'dz', 'whois.ripe.net', '', '', 'no entries found'),
(31, 'edu', 'whois.educause.net', '', '', 'No Match'),
(32, 'ee', 'whois.eenet.ee', '', '', 'NOT FOUND'),
(33, 'eg', 'whois.ripe.net', '', '', 'no entries found'),
(34, 'es', 'whois.ripe.net', '', '', 'no entries found'),
(35, 'eu', 'whois.eu', '', '', ' FREE\n'),
(36, 'fi', 'whois.ficora.fi', '', '', 'Domain not found'),
(37, 'fo', 'whois.ripe.net', '', '', 'no entries found'),
(38, 'fr', 'whois.nic.fr', '', '', 'no entries found'),
(39, 'ga', 'whois.ripe.net', '', '', 'no entries found'),
(40, 'gb', 'whois.ripe.net', '', '', 'no entries found'),
(41, 'ge', 'whois.ripe.net', '', '', 'no entries found'),
(42, 'gg', 'whois.isles.net', '', '', 'Domain not found'),
(43, 'gl', 'whois.ripe.net', '', '', 'no entries found'),
(44, 'gm', 'whois.ripe.net', '', '', 'no entries found'),
(45, 'gr', 'whois.ripe.net', '', '', 'no entries found'),
(46, 'gs', 'whois.adamsnames.tc', '', '', 'is not registered'),
(47, 'hk', 'ns1.hkdnr.net.hk', '', '', 'Domain name not found'),
(48, 'hm', 'whois.registry.hm', '', '', 'null'),
(49, 'hr', 'whois.ripe.net', '', '', 'no entries found'),
(50, 'ie', 'whois.domainregistry.ie', '', '', 'Not Registered'),
(51, 'il', 'whois.isoc.org.il', '', '', 'No data was found'),
(52, 'in', 'whois.inregistry.net', '', '', 'NOT FOUND'),
(53, 'info', 'whois.afilias.net', '', '', 'NOT FOUND'),
(54, 'int', 'whois.iana.org', '', '', 'not found'),
(55, 'io', 'whois.nic.io', '', '', 'No match'),
(56, 'ir', 'whois.nic.ir', '', '', 'no entries found'),
(57, 'is', 'whois.isnet.is', '', '', 'No entries found'),
(58, 'it', 'whois.nic.it', '', '', 'No entries found'),
(59, 'je', 'whois.isles.net', '', '', 'Domain not found'),
(60, 'jo', 'whois.ripe.net', '', '', 'no entries found'),
(61, 'jp', 'whois.jprs.jp', '', '/e', 'No match'),
(62, 'kr', 'whois.krnic.net', '', '', 'is not registered'),
(63, 'la', 'whois2.afilias-grs.net', '', '', 'NOT FOUND'),
(64, 'li', 'whois.nic.li', '', '', 'do not have an entry'),
(65, 'lt', 'whois.domreg.lt', '', '', 'No matches found'),
(66, 'lu', 'whois.dns.lu', '', '', 'No such domain'),
(67, 'lv', 'whois.ripe.net', '', '', 'Nothing found'),
(68, 'ma', 'whois.ripe.net', '', '', 'no entries found'),
(69, 'mc', 'whois.ripe.net', '', '', 'no entries found'),
(70, 'md', 'whois.ripe.net', '', '', 'no entries found'),
(71, 'mk', 'whois.ripe.net', '', '', 'no entries found'),
(72, 'ms', 'whois.adamsnames.tc', '', '', 'is not registered'),
(73, 'mt', 'whois.ripe.net', '', '', 'no entries found'),
(74, 'museum', 'whois.museum', '', '', 'has not been delegated'),
(75, 'mx', 'whois.nic.mx', '', '', 'No Encontradas'),
(76, 'my', 'whois2.mynic.net.my', '', '', 'does not Exist'),
(77, 'name', 'whois.nic.name', '', '', 'No Match'),
(78, 'net', 'whois.crsnic.net', '', '', 'No Match'),
(79, 'nl', 'whois.domain-registry.nl', '', '', 'is free'),
(80, 'no', 'whois.norid.no', '', '', 'no matches'),
(81, 'nu', 'whois.nic.nu', '', '', 'NO MATCH'),
(82, 'nz', 'whois.srs.net.nz', '', '', 'not managed by this register'),
(83, 'org', 'whois.publicinterestregistry.net', '', '', 'NOT FOUND'),
(84, 'pl', 'whois.dns.pl', '', '', 'No information'),
(85, 'pt', 'hercules.dns.pt', '', '', 'no match'),
(86, 're', 'winter.nic.fr', '', '', 'No entries found'),
(87, 'ro', 'whois.rotld.ro', '', '', 'No entries found'),
(88, 'ru', 'whois.ripn.net', '', '', 'No entries found'),
(89, 'sa', 'arabic-domains.org.sa', '', '', 'No match'),
(90, 'se', 'whois.nic-se.se', '', '', 'No data found'),
(91, 'sg', 'whois.nic.net.sg', '', '', 'NOMATCH'),
(92, 'sh', 'whois.nic.sh', '', '', 'No match'),
(93, 'si', 'whois.arnes.si', '', '', 'No entries found'),
(94, 'sk', 'whois.sk-nic.sk', '', '', 'Not found'),
(95, 'sm', 'whois.ripe.net', '', '', 'No entries found'),
(96, 'st', 'whois.nic.st', '', '', 'No entries found'),
(97, 'su', 'whois.ripn.net', '', '', 'No entries found'),
(98, 'tc', 'whois.adamsnames.tc', '', '', 'is not registered'),
(99, 'tf', 'winter.nic.fr', '', '', 'No entries found'),
(100, 'th', 'whois.thnic.net', '', '', 'No entries found'),
(101, 'tk', 'whois.dot.tk', '', '', 'domain name not known'),
(102, 'tn', 'whois.ripe.net', '', '', 'No entries found'),
(103, 'to', 'monarch.tonic.to', '', '', 'No match'),
(104, 'tr', 'whois.metu.edu.tr', '', '', 'No match'),
(105, 'tv', 'whois.nic.tv', '', '', 'No match'),
(106, 'tw', 'whois.twnic.net.tw', '', '', 'No found'),
(107, 'ua', 'whois.net.ua', '', '', 'No entries found'),
(108, 'uk', 'whois.nic.uk', '', '', 'No match'),
(109, 'us', 'whois.nic.us', '', '', 'Not found'),
(110, 'va', 'whois.ripe.net', '', '', 'No entries found'),
(111, 'vg', 'whois.adamsnames.tc', '', '', 'is not registered'),
(112, 'ws', 'whois.worldsite.ws', '', '', 'No match'),
(113, 'yu', 'whois.ripe.net', '', '', 'No entries found');

#
# Table structure for table `netquery_lgrouter`
#

CREATE TABLE `netquery_lgrouter` (
  `router_id` mediumint(9) NOT NULL auto_increment,
  `router` varchar(100) NOT NULL default '',
  `address` varchar(100) NOT NULL default '',
  `username` varchar(20) NOT NULL default '',
  `password` varchar(20) NOT NULL default '',
  `zebra` tinyint(4) NOT NULL default '0',
  `zebra_port` mediumint(9) NOT NULL default '0',
  `zebra_password` varchar(20) NOT NULL default '',
  `ripd` tinyint(4) NOT NULL default '0',
  `ripd_port` mediumint(9) NOT NULL default '0',
  `ripd_password` varchar(20) NOT NULL default '',
  `ripngd` tinyint(4) NOT NULL default '0',
  `ripngd_port` mediumint(9) NOT NULL default '0',
  `ripngd_password` varchar(20) NOT NULL default '',
  `ospfd` tinyint(4) NOT NULL default '0',
  `ospfd_port` mediumint(9) NOT NULL default '0',
  `ospfd_password` varchar(20) NOT NULL default '',
  `bgpd` tinyint(4) NOT NULL default '0',
  `bgpd_port` mediumint(9) NOT NULL default '0',
  `bgpd_password` varchar(20) NOT NULL default '',
  `ospf6d` tinyint(4) NOT NULL default '0',
  `ospf6d_port` mediumint(9) NOT NULL default '0',
  `ospf6d_password` varchar(20) NOT NULL default '',
  `use_argc` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`router_id`),
  UNIQUE KEY `keyword` (`router`)
) ;

#
# Dumping data for table `netquery_lgrouter`
#

INSERT INTO `netquery_lgrouter` (`router_id`, `router`, `address`, `username`, `password`, `zebra`, `zebra_port`, `zebra_password`, `ripd`, `ripd_port`, `ripd_password`, `ripngd`, `ripngd_port`, `ripngd_password`, `ospfd`, `ospfd_port`, `ospfd_password`, `bgpd`, `bgpd_port`, `bgpd_password`, `ospf6d`, `ospf6d_port`, `ospf6d_password`, `use_argc`) VALUES
(1, 'default', 'LG Default Settings', '', '', 1, 2601, '', 1, 2602, '', 1, 2603, '', 1, 2604, '', 1, 2605, '', 1, 2606, '', 1),
(2, 'ATT Public', 'route-server.ip.att.net', '', '', 1, 23, '', 0, 0, '', 0, 0, '', 1, 23, '', 1, 23, '', 0, 0, '', 1),
(3, 'Oregon-ix', 'route-views.oregon-ix.net', 'rviews', '', 1, 23, '', 0, 0, '', 0, 0, '', 1, 23, '', 1, 23, '', 0, 0, '', 1);

#
# Table structure for table `netquery_geocc`
#

CREATE TABLE `netquery_geocc` (
  `ci` tinyint(3) unsigned NOT NULL auto_increment,
  `cc` char(2) NOT NULL default '',
  `cn` varchar(50) NOT NULL default '',
  `lat` decimal(7,4) NOT NULL default '0.0000',
  `lon` decimal(7,4) NOT NULL default '0.0000',
  `users` mediumint(9) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ci`)
) ;

#
# Dumping data for table `netquery_geocc`
#

INSERT INTO `netquery_geocc` (`ci`, `cc`, `cn`, `lat`, `lon`, `users`) VALUES
(1, 'XX', '<a href=\"http://virtech.org/tools/\">No GeoIP</a>', 0.0000, 0.0000, 0);

#
# Table structure for table `netquery_geoip`
#

CREATE TABLE `netquery_geoip` (
  `start` int(10) unsigned NOT NULL default '0',
  `end` int(10) unsigned NOT NULL default '0',
  `ci` tinyint(3) unsigned NOT NULL default '0'
) ;

#
# Dumping data for table `netquery_geoip`
#

INSERT INTO `netquery_geoip` (`start`, `end`, `ci`) VALUES
(0, 1, 1);

#
# Table structure for table `netquery_flags`
#

CREATE TABLE `netquery_flags` (
  `flag_id` mediumint(9) NOT NULL auto_increment,
  `flagnum` mediumint(9) NOT NULL default '0',
  `keyword` varchar(20) NOT NULL default '',
  `fontclr` varchar(20) NOT NULL default '',
  `backclr` varchar(20) NOT NULL default '',
  `lookup_1` varchar(100) NOT NULL default '',
  `lookup_2` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`flag_id`),
  UNIQUE KEY keyword (`flagnum`)
) ;

#
# Dumping data for table `netquery_flags`
#

INSERT INTO `netquery_flags` (`flag_id`, `flagnum`, `keyword`, `fontclr`, `backclr`, `lookup_1`, `lookup_2`) VALUES
(1, 0, 'no data', 'red', 'white', 'http://www.virtech.org/tools/#', ''),
(2, 99, 'pending', 'green', 'white', '', '');

#
# Table structure for table `netquery_ports`
#

CREATE TABLE `netquery_ports` (
  `port_id` mediumint(9) NOT NULL auto_increment,
  `port` mediumint(9) NOT NULL default '0',
  `protocol` varchar(3) NOT NULL default '',
  `service` varchar(35) NOT NULL default '',
  `comment` varchar(50) NOT NULL default '',
  `flag` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`port_id`)
) ;

#
# Dumping data for table `netquery_ports`
#

INSERT INTO netquery_ports (`port_id`, `port`, `protocol`, `service`, `comment`, `flag`) VALUES
(1, 0, 'xxx', 'Unknown', 'Port services data not installed', 0);

#
# Table structure for table `netquery_spamblocker`
#

CREATE TABLE `netquery_spamblocker` (
  `id` mediumint(9) NOT NULL auto_increment,
  `ip` varchar(20) NOT NULL default '',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `request_method` varchar(20) NOT NULL default '',
  `request_uri` varchar(100) NOT NULL default '',
  `server_protocol` varchar(20) NOT NULL default '',
  `user_agent` varchar(100) NOT NULL default '',
  `http_headers` text,
  `request_entity` varchar(100) NOT NULL default '',
  `bb_key` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ;
