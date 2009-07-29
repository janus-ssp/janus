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
		'password'	=> 'xxx',
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

	'attributes.sp' => array(
		'USERDEFINED',						 
	),
	
	'attributes.idp' => array(
		'USERDEFINED',						 
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

	'required.idp' => array(
		'SingleLogoutService' => 1,
		'SingleSignOnService' => 1,
		'certData' => 1,
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

	/*
	 * Configuration of usertypes in JANUS.
	 */
	'usertypes' => array(
		//SAML 2.0 contact types
		'technical', 
		'support', 
		'administrative', 
		'billing', 
		'other',
		// WAYF
		'entityAdmin',
		'admin',
		'operation',	
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

	'workflow_states' => array(

		'test:accepted' => array(
			'QA:pending' => array(
				'role' => array(
					'admin',				
				),					  
			),			
		),

		'QA:pending' => array(
			'QA:accepted' => array(
				'role' => array(
					'admin',				
				),					   
			),				
	  		'test:accepted' => array(
				'role' => array(
					'all',				
				),						 
			),		
		),

		'QA:accepted' => array(
			'test:accepted' => array(
				'role' => array(
					'all',				
				),						 
			),
 			'prod:pending' => array(
				'role' => array(
					'admin',				
				),						
			),			
		),

		'prod:pending' => array(
			'prod:accepted' => array(
				'role' => array(
					'admin',				
				),						 
			),
			'test:accepted' => array(
				'role' => array(
					'all',				
				),						 
			),		
		),

		'prod:accepted' => array(
			'test:accepted' => array(
				'role' => array(
					'all',				
				),						 
			),						
		),
	),
);
?>
