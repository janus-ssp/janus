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
	'workflowstates' => array(
		'test:accepted' => array(
			'name' => 'test:accepted - Her kan alt tilføjes'						 
		),
		'QA:pending' => array(
			'name' => 'QA:pending - Afventer godkendelse til QA',					  
		),
		'QA:accepted' => array(
			'name' => 'QA:accepted - Godkendt til QA',					  
		),
		'prod:pending' => array(
			'name' => 'prod:pending - Afventer godkendelse til produktionssystemet',					  
		),
		'prod:accepted' => array(
			'name' => 'prod:accepted - Godkendt til produktion',					  
		),
	),

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
		// Buildin admin user type. Define if you want to create more admin user 
		// accounts.
		'admin',
		//SAML 2.0 contact types
		'technical', 
		'support', 
		'administrative', 
		'billing', 
		'other',
	),

	/*
	 * Access configuration of JANUS.
	 *
	 * If a permission is not set for a given user for a given system, the default
	 * permission is given.
	 */
	'access' => array(
		// Change entity type
		'changeentitytype' => array(
			'default' => FALSE,
			'test:accepted' => array(
				'role' => array(
					'admin',				
				),						 
			),
			'QA:pending' => array(
				'role' => array(
					'-all',				
				),					  
			),			
		),
		// Export metadata
		'exportmetadata' => array(
			'default' => FALSE,
			'test:accepted' => array(
				'role' => array(
					'-all',
					'admin',				
				),						 
			),						  
		),
		// Block or unblock remote entities
		'blockremoteentity' => array(
			'default' => FALSE,
			'test:accepted' => array(
				'role' => array(
					'all',
				),						 
			),						  
		),
		// Change workflow state
		'changeworkflow' => array(
			'default' => TRUE,
			'test:accepted' => array(
				'role' => array(
					'admin',				
				),						 
			),
			'QA:accepted' => array(
				'role' => array(
					'admin',				
				),					   
			),
		),
		// Add metadata
		'addmetadata' => array(
			'default' => FALSE,
			'test:accepted' => array(
				'role' => array(
					'all',				
				),						 
			),			
		),
		// Delete metadata
		'deletemetadata' => array(
			'default' => FALSE,
			'test:accepted' => array(
				'role' => array(
					'all',				
				),						 
			),			
		),
		// Modify metadata
		'modifymetadata' => array(
			'default' => FALSE,
			'test:accepted' => array(
				'role' => array(
					'all',				
				),						 
			),			
		),
		// Import metadata
		'importmetadata' => array(
			'default' => FALSE,
			'test:accepted' => array(
				'role' => array(
					'all',				
				),						 
			),			
		),
		// History
		'entityhistory' => array(
			'default' => FALSE,
			'test:accepted' => array(
				'role' => array(
					'all',				
				),						 
			),			
		),

		/* 
		 * General permissions
		 */

		// Create new entity
		'createnewentity' => array(
			'role' => array(
				'all',							
			),					 			   
		),
	),




	'workflow_states' => array(

		'test:accepted' => array(
			'QA:pending' => array(
				'role' => array(
					'admin',
					'technical',
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
