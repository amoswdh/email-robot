# Host: localhost  (Version 5.5.53)
# Date: 2019-05-07 17:04:21
# Generator: MySQL-Front 5.4  (Build 4.153) - http://www.mysqlfront.de/

/*!40101 SET NAMES utf8 */;

#
# Structure for table "site"
#

CREATE TABLE `site` (
  `site_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `site_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `group` int(11) NOT NULL,
  `site_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `site_db_server` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `site_db_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `site_db_user` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `site_db_password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `comment` text COLLATE utf8_unicode_ci NOT NULL,
  `status` tinyint(4) NOT NULL,
  `welcome_msg` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `category_default_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `search_default_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `logo_src` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `logo_alt` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `default_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `default_keywords` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `default_description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `send_email_address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `send_mail_host` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `send_email_from_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `service_email_address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `site_image_host` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `site_telphone` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `site_fax` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `pp_client_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `pp_secret` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `pp_webhook_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `pp_dispute_status` tinyint(4) NOT NULL,
  `pp_merchant_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`site_id`)
) ENGINE=MyISAM AUTO_INCREMENT=57 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

#
# Data for table "site"
#

INSERT INTO `site` VALUES (56,'luvyle_com','luvyle.com',0,'luvyle.com','','','','','',1,'','','','http://ups.aopcdn.com/s1/common/17777/41u2b8161da91ec41f6821fb960a570e439.png','','','','','','','','service@luvyle.com','storage.orderplus.com','','','ASveOb_ZF0nUD1mdjLNZ43xWbG-cRwYVF45Yr8yAeK5xqjF93lS_Q9c7txzdVvtK-YlGiuEzRRN_A-SP','EDRoOdRKXL2Km9NgBY-pPHMhA1hTOyRHJnFi-MV9SOSlOvgEQpgrEG8VKQrqtmiytVGCpTj1gXduPJe3','0',1,'X8WBCRXHP7ZTG',NULL,NULL);
