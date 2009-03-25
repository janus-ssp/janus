<?php
$config = array(
	/*
	 * Configuration for the database connection.
	 */
	'store' => array(
		'dsn' 		=> 'mqsql:host=localhost;dbname=jach_db',
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
			'default' = FALSE,

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
				'default' = TRUE,	
			),	
			'Operation' => array(
				
				/*
				 * Default permission for test system for
				 * Operation.
				 * REMARK: Overwrites default permission.
				 */
				'default' = TRUE,	
			),
		),	
	),
);
?>
