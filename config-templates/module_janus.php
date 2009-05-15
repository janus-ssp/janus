<?php
/**
 * Config file for JANUS
 *
 * @author Jacob Christiansen, <jach@wayf.dk>
 * @package simpleSAMLphp
 * @subpackage JANUS
 * @version $Id$
 */
$config = array(

	'password' => 'test',
				
	/*
	 * Configuration for the database connection.
	 */
	'store' => array(
		'dsn' 		=> 'mysql:host=localhost;dbname=jach_db',
		'username' 	=> 'jach',
		'password'	=> 'Jacob82NG',
		'prefix'	=> 'janus',
	),

	/*
	 * Configuration of systems in JANUS.
	 */
	'systems' => array(
		'test',
		'QA',
		'prod',	
	),

	'states' => array(
		'accepted',
		'pending',
		'pendingdelete',
		'deleted'
	),

	'types' => array(
		'sp',
		'idp'		
	),

	/*
	 * Allowed metadata names for IdPs. If USERDEFINED is set no restrictions is
	 * put on metadata names.
	 */
	'metadatafields.idp' => array(
		'USERDEFINED',
		'SingleLogoutService',
		'SingleSignOnService',
		'certFingerprint',
		'certData',
		'organization:name',
		'organization:displayname',
		'organization:url',
		'contact:name',
		'contact:email',
	),
	
	/*
	 * Allowed metadata names for SPs. If USERDEFINED is set no restrictions is
	 * put on metadata names.
	 */
	'metadatafields.sp' => array(
		'USERDEFINED',
		'AssertionConsumerService',
		'SingleLogoutService',
		'NameIDFormat',
		'certData',
	),

	/*
	 * Configuration of usertypes in JANUS.
	 */
	'users' => array(
		'EntityAdmin',
		'WAYFAdmin',
		'Operation',	
	),

	/*
	 * Access configuration of JANUS.
	 *
	 * If a permission is not set for a given user for a given system, the default
	 * permission is given.
	 */
	'access' => array(
		/*
		 * Default permission on all systems for all users.
		 */
		'default' => FALSE,
		
		/*
		 * Configuration of 'test',
		 */
		'test' => array(
			/*
			 * Default permission on test. 
			 * REMARK: Overwrites default permission.
			 */
			'default' => FALSE,

			/*
			 * Permissions of EntityAdmin in test system.
			 */
			'EntityAdmin' => array(
				
				/*
				 * Default permission for test system for
				 * EntityAdmin.
				 * REMARK: Overwrites default permission.
				 */
				'default' => FALSE,
				'changeName' => TRUE,	
			),
			'WAYFAdmin' => array(
				
				/*
				 * Default permission for test system for
				 * WAYFAdmin.
				 * REMARK: Overwrites default permission.
				 */
				'default' => TRUE,	
			),	
			'Operation' => array(
				
				/*
				 * Default permission for test system for
				 * Operation.
				 * REMARK: Overwrites default permission.
				 */
				'default' => TRUE,	
			),
		),	
	),
);
?>
