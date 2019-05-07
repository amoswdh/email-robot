# Host: localhost  (Version 5.5.53)
# Date: 2019-05-07 17:04:06
# Generator: MySQL-Front 5.4  (Build 4.153) - http://www.mysqlfront.de/

/*!40101 SET NAMES utf8 */;

#
# Structure for table "email_account"
#

CREATE TABLE `email_account` (
  `account_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nickname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `in_remote_system_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `in_port` int(11) NOT NULL,
  `in_flags` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `in_is_ssl` tinyint(4) NOT NULL,
  `in_is_nocert` tinyint(4) NOT NULL,
  `out_remote_system_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `out_port` tinyint(4) NOT NULL,
  `out_is_smtpauth` tinyint(4) NOT NULL,
  `out_is_ssl` tinyint(4) NOT NULL,
  `email_count` int(11) NOT NULL,
  `check_time` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `site_id` int(11) NOT NULL,
  `add_time` int(11) NOT NULL,
  `email_qiye` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `department_status` tinyint(4) NOT NULL,
  `type` enum('synchronous','asynchronous') COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`account_id`)
) ENGINE=MyISAM AUTO_INCREMENT=55 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

#
# Data for table "email_account"
#

INSERT INTO `email_account` VALUES (54,'luvyle.com','service@luvyle.com','password','imap.secureserver.net',993,'imap',1,0,'',0,1,0,0,0,1,56,0,'0',1,'asynchronous',NULL,NULL);
