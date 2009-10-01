<?php
/**
 * Config file for JANUS
 *
 * @author Jacob Christiansen, <jach@wayf.dk>
 * @author Sixto Martín, <smartin@yaco.es>
 * @package simpleSAMLphp
 * @subpackage JANUS
 * @version $Id$
 */
$config = array(

	'admin.name' => 'WAYF sekretariatet',
	'admin.email' => 'sekretariatet@wayf.dk',

	'auth' => 'mailtoken',
	'useridattr' => 'mail',

    'email' => array(
        'en' => array(
            'body' => '
                <html>
                <head>
                <title>JANUS token</title>
                </head>
                <body>
                <p>To login to JANUS click the following link:</p>
                <a href="%RETURNURL%?token=%TOKEN%">%RETURNURL%?token=%TOKEN%</a>
                <p>If the link does not work, please try to copy the link
                directly into your browsers address bar.</p>
                <p>In case of problems contact the WAYF Secreteriat.</p>
                <br />
                <p>Best regards</p>
                <p>WAYF Secreteriat</p>
                <p>sekretariat@wayf.dk</p>
                </body>
                </html>',
            'headers' => 'MIME-Version: 1.0' . "\r\n".
                'Content-type: text/html; charset=iso-8859-1' . "\r\n".
                'From: JANUS <no-reply@wayf.dk>' . "\r\n" .
                'Reply-To: WAYF <sekretariatet@wayf.dk>' . "\r\n" .
                'X-Mailer: PHP/' . phpversion(),
            'subject' => 'JANUS: Login token',
        ),
        'da' => array(
            'body' => '
                <html>
                <head>
                <title>JANUS token</title>
                </head>
                <body>
                <p>For at logge ind i JANUS, klik p&aring; linket:</p>
                <a href="%RETUENURL%?token=%TOKEN%">%RETURNURL%?token=%TOKEN%</a>
                <p>Hvis det ikke virker, pr&oslash;v at kopiere linket til
                adressefeltet i din browser.</p>
                <p>I tilf&aelig;lde af problemer med JANUS, kontakt WAYF
                sekretariatet.</p>
                <br />
                <p>Venlig hilsen</p>
                <p>WAYF sekretariatet</p>
                <p>sekretariat@wayf.dk</p>
                </body>
                </html>
            ',
            'headers' => 'MIME-Version: 1.0' . "\r\n".
                'Content-type: text/html; charset=iso-8859-1' . "\r\n".
                'From: JANUS <no-reply@wayf.dk>' . "\r\n" .
                'Reply-To: WAYF <sekretariatet@wayf.dk>' . "\r\n" .
                'X-Mailer: PHP/' . phpversion(),
            'subject' => 'JANUS: Login token',
        ),             
    ),

    /*
	 * Configuration for the database connection.
	 */
	'store' => array(
		'dsn' 		=> 'mysql:host=localhost;dbname=jach_db',
		'username' 	=> 'jach',
		'password'	=> 'xxx',
		'prefix'	=> 'janus__',
	),

	/*
	 * Configuration of systems in JANUS.
	 */
	'workflowstates' => array(
		'testaccepted' => array(
			'name' => 'test:accepted - Her kan alt tilføjes',
            'description' => 'Desc 1',            
		),
		'QApending' => array(
			'name' => 'QA:pending - Afventer godkendelse til QA',					  
            'description' => 'Desc 2',            
		),
		'QAaccepted' => array(
			'name' => 'QA:accepted - Godkendt til QA',					  
            'description' => 'Desc 3',            
		),
		'prodpending' => array(
			'name' => 'prod:pending - Afventer godkendelse til produktionssystemet',					  
            'description' => 'Desc 4',            
		),
		'prodaccepted' => array(
			'name' => 'prod:accepted - Godkendt til produktion',					  
            'description' => 'Desc 4',            
		),
	),

    'workflowstate.default' => 'testaccepted',
    
    // To be removed
	'systems' => array(
		'test',
		'QA',
		'prod',	
	),

    // To be removed
	'states' => array(
		'accepted',
		'pending',
		'pendingdelete',
		'deleted'
	),

	'attributes.saml20-sp' => array(
		'USERDEFINED',						 
	),
	
	'attributes.saml20-idp' => array(
		'USERDEFINED',						 
	),

	/*
	 * Allowed metadata names for IdPs. If USERDEFINED is set no restrictions is
	 * put on metadata names.
	 */

	'metadatafields.saml20-idp' => array(
		'SingleLogoutService',
	   	'SingleSignOnService',
	   	'certFingerprint',
	   	'certData',
	   	'entity:name:da',
	   	'entity:description:da',
	   	'entity:url:da',
	   	'contact:name',
	   	'contact:email',
	   	'organization:name:da',
	   	'organization:description:da',
	   	'organization:url:da',
	),

	'required.metadatafields.saml20-idp' => array(
		'SingleLogoutService',
		'SingleSignOnService',
		'certData',
	),

	/*
	 * Allowed metadata names for SPs. If USERDEFINED is set no restrictions is
	 * put on metadata names.
	 */
	'metadatafields.saml20-sp' => array(
		'AssertionConsumerService',
		'SingleLogoutService',
		'NameIDFormat',
		'certData',
		'entity:name:da',
		'entity:description:da',
		'entity:url:da',
		'contact:name',
		'contact:email',
		'organization:name:da',
		'organization:description:da',
		'organization:url:da',
	),

	'required.metadatafields.saml20-sp' => array(
		'AssertionConsumerService',
    	),


	/*
     * Allowed metadata names for Shib IdPs. If USERDEFINED is set no restrictions is
     * put on metadata names.
     */
    'metadatafields.shib13-idp' => array(
        'SingleSignOnService',
        'certFingerprint',
		'NameIDFormat',
        'certData',
        'entity:name:da',
        'entity:description:da',
        'entity:url:da',
        'contact:name',
        'contact:email',
        'organization:name:da',
        'organization:description:da',
        'organization:url:da',
    ),


    'required.metadatafields.shib13-idp' => array(
        'SingleSignOnService',
        'certData',
    ),

    /*
     * Allowed metadata names for SPs. If USERDEFINED is set no restrictions is
     * put on metadata names.
     */
    'metadatafields.shib13-sp' => array(
        'AssertionConsumerService',
        'NameIDFormat',
        'certData',
        'entity:name:da',
        'entity:description:da',
        'entity:url:da',
        'contact:name',
        'contact:email',
        'organization:name:da',
        'organization:description:da',
        'organization:url:da',
    ),

    'required.metadatafields.shib13-sp' => array(
        'AssertionConsumerService',
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


	'export.entitiesDescriptorName' => 'Federacion Confia',

	'maxCache'      => 60*60*24, // 24 hour cache time
	'maxDuration'   => 60*60*24*5, // Maximum 5 days duration on ValidUntil.

	/* Whether metadata should be signed. */
	'sign.enable' => TRUE,

	/* Private key which should be used when signing the metadata. */
	'sign.privatekey' => 'server.pem',

	/* Password to decrypt private key, or NULL if the private key is unencrypted. */
	'sign.privatekey_pass' => NULL,

	/* Certificate which should be included in the signature. Should correspond to the private key. */
	'sign.certificate' => 'server.crt',


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
			'testaccepted' => array(
				'role' => array(
					'all',				
				),						 
			),
			'QApending' => array(
				'role' => array(
					'-all',				
				),					  
			),			
		),
		// Export metadata
		'exportmetadata' => array(
			'default' => FALSE,
			'testaccepted' => array(
				'role' => array(
					'-all',
					'admin',				
				),						 
			),						  
		),

		// Block or unblock remote entities
		'blockremoteentity' => array(
			'default' => FALSE,
			'testaccepted' => array(
				'role' => array(
					'all',
				),						 
			),					  
		),
		// Change workflow state
		'changeworkflow' => array(
			'default' => TRUE,
			'testaccepted' => array(
				'role' => array(
					'admin',				
				),						 
			),
			'QAaccepted' => array(
				'role' => array(
					'admin',				
				),					   
			),
		),
		// Add metadata
		'addmetadata' => array(
			'default' => FALSE,
			'testaccepted' => array(
				'role' => array(
					'all',				
				),						 
			),			
		),
		// Delete metadata
		'deletemetadata' => array(
			'default' => FALSE,
			'testaccepted' => array(
				'role' => array(
					'all',				
				),						 
			),			
		),
		// Modify metadata
		'modifymetadata' => array(
			'default' => FALSE,
			'testaccepted' => array(
				'role' => array(
					'all',				
				),						 
			),			
		),
		// Import metadata
		'importmetadata' => array(
			'default' => FALSE,
			'testaccepted' => array(
				'role' => array(
					'all',				
				),						 
			),			
		),
		// History
		'entityhistory' => array(
			'default' => FALSE,
			'testaccepted' => array(
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
		
        // Export all entities. (all Federation metadata)
		'exportallentities' =>
			'role' => array (
			    'admin',
			),
		),
	),



	'workflow_states' => array(

		'testaccepted' => array(
			'QApending' => array(
				'role' => array(
					'admin',
					'technical',
				),					  
			),			
		),

		'QApending' => array(
			'QAaccepted' => array(
				'role' => array(
					'admin',				
				),					   
			),				
	  		'testaccepted' => array(
				'role' => array(
					'all',				
				),						 
			),		
		),

		'QAaccepted' => array(
			'testaccepted' => array(
				'role' => array(
					'all',				
				),						 
			),
 			'prodpending' => array(
				'role' => array(
					'admin',				
				),						
			),			
		),

		'prodpending' => array(
			'prodaccepted' => array(
				'role' => array(
					'admin',				
				),						 
			),
			'testaccepted' => array(
				'role' => array(
					'all',				
				),						 
			),		
		),

		'prodaccepted' => array(
			'testaccepted' => array(
				'role' => array(
					'all',				
				),						 
			),						
		),
	),
);
?>
