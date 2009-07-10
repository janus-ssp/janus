<?php
/**
 * Config file for JANUS
 *
 * @author Jacob Christiansen, <jach@wayf.dk>
 * @package simpleSAMLphp
 * @subpackage JANUS
 * @version $Id: module_janus.php 47 2009-07-10 10:53:44Z jach@wayf.dk $
 */
$config_template = array(

	'admin.name' => 'Hans Hest',
	'admin.email' => 'hans@hest.dk',

	'auth' => 'mailtoken',
	'useridattr' => 'mail',
	'password' => 'test',			
	/*
	 * Configuration for the database connection.
	 */
	'store' => array(
		'dsn' 		=> 'mysql:host=localhost;dbname=jach_db',
		'username' 	=> 'jach',
		'password'	=> 'XXXXXX',
		'prefix'	=> 'janus',
	),

	/*
	 * Allowed metadata names for IdPs. If USERDEFINED is set no restrictions is
	 * put on metadata names.
	 */
	'metadatafields.idp' => array(
		'SingleLogoutService',
		'SingleSignOnService',
		'certFingerprint',
		'certData',
		'entity:name:da',
		'entity:description:da',
		'entity:url:da',
		'contact:name:da',
		'contact:email:da',
	),

	/*
	 * Allowed metadata names for SPs. If USERDEFINED is set no restrictions is
	 * put on metadata names.
	 */
	'metadatafields.sp' => array(
		'AssertionConsumerService',
		'SingleLogoutService',
		'NameIDFormat',
		'certData',
		'entity:name:da',
		'entity:description:da',
		'entity:url:da',
		'contact:name:da',
		'contact:email:da',
	),
);
?>
