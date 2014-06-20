<?php
/**
*   Table definitions for the Profile plugin
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2010 Lee Garner <lee@leegarner.com>
*   @package    forms
*   @version    0.1.1
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/

/** @global array $_TABLES */
global $_TABLES;

$_SQL['forms_frmdef'] = "CREATE TABLE {$_TABLES['forms_frmdef']} (
  `id` varchar(40) NOT NULL DEFAULT '',
  `name` varchar(32) NOT NULL,
  `onsubmit` tinyint(1) NOT NULL DEFAULT '2',
  `email` varchar(80) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `moderate` tinyint(1) NOT NULL DEFAULT '0',
  `owner_id` mediumint(8) unsigned NOT NULL DEFAULT '2',
  `group_id` mediumint(8) unsigned NOT NULL DEFAULT '1',
  `fill_gid` mediumint(8) unsigned NOT NULL DEFAULT '1',
  `results_gid` mediumint(8) unsigned NOT NULL DEFAULT '1',
  `redirect` varchar(255) DEFAULT '',
  `onetime` tinyint(1) NOT NULL DEFAULT '0',
  `introtext` text DEFAULT '',
  `submit_msg` text DEFAULT '',
  `noaccess_msg` text DEFAULT '',
  `noedit_msg` text DEFAULT '',
  `max_submit_msg` text default '',
  `captcha` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `inblock` tinyint(1) NOT NULL DEFAULT '0',
  `max_submit` int(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
)";

$_SQL['forms_results'] = "CREATE TABLE {$_TABLES['forms_results']} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `frm_id` varchar(40) NOT NULL DEFAULT '',
  `instance_id` varchar(60),
  `uid` int(11) NOT NULL DEFAULT '0',
  `dt` int(11) NOT NULL DEFAULT '0',
  `approved` tinyint(1) DEFAULT '1',
  `ip` varchar(16) DEFAULT NULL,
  `token` varchar(40) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
)";

$_SQL['forms_flddef'] = "CREATE TABLE {$_TABLES['forms_flddef']} (
  `fld_id` int(11) NOT NULL AUTO_INCREMENT,
  `frm_id` varchar(40) NOT NULL DEFAULT '',
  `name` varchar(32) NOT NULL,
  `type` varchar(32) NOT NULL DEFAULT 'text',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `access` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `prompt` varchar(80) DEFAULT '',
  `options` text,
  `orderby` smallint(5) unsigned NOT NULL DEFAULT '0',
  `help_msg` varchar(255) DEFAULT '',
  `fill_gid` mediumint(8) unsigned NOT NULL DEFAULT '1',
  `results_gid` mediumint(8) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`fld_id`)
)";

$_SQL['forms_values'] = "CREATE TABLE {$_TABLES['forms_values']} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `results_id` int(11) NOT NULL DEFAULT '0',
  `fld_id` int(11) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `res_fld` (`results_id`,`fld_id`)
)";

global $FRM_sampledata;
$FRM_sampledata = array();
$FRM_sampledata[] = "INSERT INTO {$_TABLES['forms_frmdef']} VALUES (
    'testform', 'Test Profile Form', 
    1, '{$_CONF['site_mail']}', 1, 0, 2, 1, 1, 1,
    '', 0, '', '', '', '', '', 0, 0, 0)";

$insert = "INSERT INTO {$_TABLES['forms_flddef']} VALUES (";
$FRM_sampledata[] = "$insert 1, 'testform', 'address1', 'text', 1, 1,
    'Address Line 1', 
    'a:2:{s:4:\"size\";i:40;s:9:\"maxlength\";i:80;}', 10, '', 1, 1)";
$FRM_sampledata[] = "$insert 2, 'testform', 'address2', 'text', 1, 0,
    'Address Line 2', 
    'a:2:{s:4:\"size\";i:40;s:9:\"maxlength\";i:80;}', 20, '', 1, 1)";
$FRM_sampledata[] = "$insert 3, 'testform', 'city', 'text', 1, 1,
    'City', 
    'a:2:{s:4:\"size\";i:40;s:9:\"maxlength\";i:80;}', 30, '', 1, 1)";
$FRM_sampledata[] = "$insert 4, 'testform', 'state', 'text', 1, 1,
    'State', 
    'a:2:{s:4:\"size\";i:2;s:9:\"maxlength\";i:2;}', 40, '', 1, 1)";
$FRM_sampledata[] = "$insert 5, 'testform', 'zip', 'text', 1, 1,
    'Zip Code', 
    'a:2:{s:4:\"size\";i:10;s:9:\"maxlength\";i:10;}', 50, '', 1, 1)";
$FRM_sampledata[] = "$insert 6, 'testform', 'favcolor', 'radio', 1, 1, 
    'Favorite color', 
    'a:2:{s:7:\"default\";s:4:\"Blue\";s:6:\"values\";s:51:\"a:3:{i:0;s:3:\"Red\";i:1;s:4:\"Blue\";i:2;s:5:\"Green\";}\";}', 60, 'Select your favorite color',
    1, 1)";
$FRM_sampledata[] = "$insert 7, 'testform', 'birthdate', 'date', 1, 1, 
    'BirthDate', 
    'a:5:{s:7:\"default\";s:0:\"\";s:8:\"showtime\";i:0;s:10:\"timeformat\";s:2:\"12\";s:6:\"format\";N;s:12:\"input_format\";i:1;}', 70, '', 1, 1)";


global $_FRM_UPGRADE_SQL;
$_FRM_UPGRADE_SQL = array(
    '0.0.5' => array(
        "ALTER TABLE {$_TABLES['forms_frmdef']}
            CHANGE email email varchar(80) default NULL",
        "ALTER TABLE {$_TABLES['forms_flddef']}
            DROP KEY `name`,
            CHANGE id fld_id int(11) unsigned not null auto_increment,
            ADD frm_id int(11) unsigned NOT NULL default '0' AFTER fld_id,
            ADD orderby tinyint(3) unsigned NOT NULL default '0'",
        "ALTER TABLE {$_TABLES['forms_values']}
            ADD fld_id int(11) unsigned NOT NULL AFTER fld_name",
    ),
    '0.1.0' => array(
        "ALTER TABLE {$_TABLES['forms_frmdef']}
            CHANGE user_gid fill_gid mediumint(8) unsigned not null default '1'",
    ),
    '0.1.2' => array(
        "ALTER TABLE {$_TABLES['forms_frmdef']}
            ADD captcha TINYINT(1) UNSIGNED NOT NULL DEFAULT 0",
        "ALTER TABLE {$_TABLES['forms_results']} ADD ip VARCHAR(16)",
    ),
    
    '0.1.3' => array(
        "ALTER TABLE {$_TABLES['forms_flddef']}
            ADD `help_msg` varchar(255)",
    ),
    '0.1.5' => array(
        "ALTER TABLE {$_TABLES['forms_results']}
            ADD `token` varchar(40) AFTER `ip`",
    ),
    '0.1.6' => array(
        "ALTER IGNORE TABLE {$_TABLES['forms_values']}
            ADD UNIQUE KEY `res_fld` (results_id, fld_id)",
    ),
    '0.1.7' => array(
        "ALTER TABLE {$_TABLES['forms_frmdef']}
            CHANGE id id varchar(40) NOT NULL DEFAULT '',
            ADD `inblock` tinyint(1) unsigned NOT NULL default '0',
            ADD `max_submit` int(5) unsigned NOT NULL default '0'",
        "ALTER TABLE {$_TABLES['forms_flddef']}
            CHANGE frm_id frm_id varchar(40) NOT NULL DEFAULT '',
            ADD `fill_gid` mediumint(8) unsigned NOT NULL default '1',
            ADD `results_gid` mediumint(8) unsigned NOT NULL default '1',
            CHANGE `required` `access` tinyint(1) unsigned NOT NULL default '0'",
        "ALTER TABLE {$_TABLES['forms_results']}
            CHANGE frm_id frm_id varchar(40) NOT NULL DEFAULT ''",
    ),
    '0.1.8' => array(
        "ALTER TABLE {$_TABLES['forms_frmdef']}
            ADD `max_submit_msg` text default '' AFTER `noaccess_msg`,
            ADD `noedit_msg` text default '' AFTER `noaccess_msg`",
    ),
    '0.2.0' => array(
        "ALTER TABLE {$_TABLES['forms_results']}
            ADD instance_id varchar(60) AFTER frm_id",
    ),
    '0.2.2' => array(
        "ALTER TABLE {$_TABLES['forms_flddef']}
            CHANGE orderby orderby smallint(5) unsigned NOT NULL DEFAULT '0'",
    ),
);

?>
