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

    /**
     * Mailtoken specific stuff
     */

    // Token lifetime in seconds
    'token.lifetime' => 3600*24,

    // Content of token mail
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
     * Enable entity types
     */
    'enable.saml20-sp' =>   true,
    'enable.saml20-idp' =>  true,
    'enable.shib13-sp' =>   false,
    'enable.shib13-idp' =>  false,
	
    /*
	 * Configuration of systems in JANUS.
	 */
	'workflowstates' => array(
		'testaccepted' => array(
			'name' => array(
                'en' => 'testaccepted - en',
                'da' => 'testaccepted - da',
            ),
            'description' => array(
                'en' => 'Desc 1 en',
                'da' => 'Desc 1 da',
            ),
		),
		'QApending' => array(
			'name' => array(
                'en' => 'QApending - en',
                'da' => 'QApending - da',
            ),
            'description' => array(
                'en' => 'Desc 2 en',
                'da' => 'Desc 2 da',
            ),
		),
		'QAaccepted' => array(
			'name' => array(
                'en' => 'QAaccepted - en',
                'da' => 'QAaccepted - da',
            ),
            'description' => array(
                'en' => 'Desc 3 en',
                'da' => 'Desc 3 da',
            ),
		),
		'prodpending' => array(
			'name' => array(
                'en' => 'prodpending - en',
                'da' => 'prodpending - da',
            ),
            'description' => array(
                'en' => 'Desc 4 en',
                'da' => 'Desc 4 da',
            ),
		),
		'prodaccepted' => array(
			'name' => array(
                'en' => 'prodaccepted - en',
                'da' => 'prodaccepted - da',
            ),
            'description' => array(
                'en' => 'Desc 5 en',
                'da' => 'Desc 5 da',
            ),
		),
	),

    // Default workflow state when creating new entities
    'workflowstate.default' => 'testaccepted',
    
    /*
     * Allowed attribute names
     */
	'attributes.saml20-sp' => array(
		'uid' => array(
            'description' => 'The description',               
        ),						 
        'mail' => array(
            'description' => 'The description',                
        ),
	),
	
	'attributes.saml20-idp' => array(
		'uid' => array(
            'description' => 'The description',               
        ),						 
        'mail' => array(
            'description' => 'The description',                
        ),
	),

	'attributes.shib13-sp' => array(
		'uid' => array(
            'description' => 'The description',               
        ),						 
        'mail' => array(
            'description' => 'The description',                
        ),
	),
	
	'attributes.shib13-idp' => array(
		'uid' => array(
            'description' => 'The description',               
        ),						 
        'mail' => array(
            'description' => 'The description',                
        ),
	),

	/*
	 * Allowed metadata names for IdPs. If USERDEFINED is set no restrictions is
	 * put on metadata names.
	 */
	'metadatafields.saml20-idp' => array(
		'SingleLogoutService' => array(
            'type' => 'text',
            'order' => 10,
            'default' => 'defaultvalue',
            'description' => 'Description',
            'required' => true,
        ),
	   	'SingleSignOnService' => array(
            'type' => 'text',
            'order' => 20,
            'default' => 'defaultvalue',
            'description' => 'Description',
            'required' => true,
        ),
	   	'certFingerprint' => array(
            'type' => 'text',
            'order' => 30,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
	   	'certData' => array(
            'type' => 'text',
            'order' => 40,
            'default' => 'defaultvalue',
            'description' => 'Description',
            'required' => true,
        ),
	   	'entity:name:da' => array(
            'type' => 'text',
            'order' => 50,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
	   	'entity:description:da' => array(
            'type' => 'text',
            'order' => 60,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
	   	'entity:url:da' => array(
            'type' => 'text',
            'order' => 70,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
        // Contact fields
	   	'contacts:telephoneNumber' => array(
            'type' => 'text',
            'order' => 80,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
	   	'contacts:company' => array(
            'type' => 'text',
            'order' => 90,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
	   	'contacts:surName' => array(
            'type' => 'text',
            'order' => 100,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
	   	'contacts:givenName' => array(
            'type' => 'text',
            'order' => 110,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
	   	'contacts:contactType' => array(
            'type' => 'text',
            'order' => 120,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
	   	'contacts:name' => array(
            'type' => 'text',
            'order' => 130,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
	   	'contacts:emailAddress' => array(
            'type' => 'text',
            'order' => 140,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
	   	'organization:name:da' => array(
            'type' => 'text',
            'order' => 150,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
	   	'organization:description:da' => array(
            'type' => 'text',
            'order' => 160,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
	   	'organization:url:da' => array(
            'type' => 'text',
            'order' => 170,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
	),

	/*
	 * Allowed metadata names for SPs. If USERDEFINED is set no restrictions is
	 * put on metadata names.
	 */
	'metadatafields.saml20-sp' => array(
		'AssertionConsumerService' => array(
            'type' => 'text',
            'order' => 10,
            'default' => 'defaultvalue',
            'description' => 'Description',
            'required' => true,
        ),
		'SingleLogoutService' => array(
            'type' => 'text',
            'order' => 20,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
		'NameIDFormat' => array(
            'type' => 'text',
            'order' => 30,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
		'certData' => array(
            'type' => 'text',
            'order' => 40,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
		'entity:name:da' => array(
            'type' => 'text',
            'order' => 50,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
		'entity:description:da' => array(
            'type' => 'text',
            'order' => 60,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
		'entity:url:da' => array(
            'type' => 'text',
            'order' => 70,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
        // Contact fields
	   	'contacts:telephoneNumber' => array(
            'type' => 'text',
            'order' => 80,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
	   	'contacts:company' => array(
            'type' => 'text',
            'order' => 90,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
	   	'contacts:surName' => array(
            'type' => 'text',
            'order' => 100,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
	   	'contacts:givenName' => array(
            'type' => 'text',
            'order' => 110,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
	   	'contacts:contactType' => array(
            'type' => 'text',
            'order' => 120,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
	   	'contacts:name' => array(
            'type' => 'text',
            'order' => 130,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
	   	'contacts:emailAddress' => array(
            'type' => 'text',
            'order' => 140,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
		'organization:name:da' => array(
            'type' => 'text',
            'order' => 150,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
		'organization:description:da' => array(
            'type' => 'text',
            'order' => 160,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
		'organization:url:da' => array(
            'type' => 'text',
            'order' => 170,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
	),

	/*
     * Allowed metadata names for Shib IdPs. If USERDEFINED is set no restrictions is
     * put on metadata names.
     */
    'metadatafields.shib13-idp' => array(
        'SingleSignOnService' => array(
            'type' => 'text',
            'order' => 10,
            'default' => 'defaultvalue',
            'description' => 'Description',
            'required' => true,
        ),
        'certFingerprint' => array(
            'type' => 'text',
            'order' => 20,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
		'NameIDFormat' => array(
            'type' => 'text',
            'order' => 30,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
        'certData' => array(
            'type' => 'text',
            'order' => 40,
            'default' => 'defaultvalue',
            'description' => 'Description',
            'required' => true,
        ),
        'entity:name:da' => array(
            'type' => 'text',
            'order' => 50,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
        'entity:description:da' => array(
            'type' => 'text',
            'order' => 60,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
        'entity:url:da' => array(
            'type' => 'text',
            'order' => 70,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
        // Contact fields
	   	'contacts:telephoneNumber' => array(
            'type' => 'text',
            'order' => 80,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
	   	'contacts:company' => array(
            'type' => 'text',
            'order' => 90,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
	   	'contacts:surName' => array(
            'type' => 'text',
            'order' => 100,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
	   	'contacts:givenName' => array(
            'type' => 'text',
            'order' => 110,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
	   	'contacts:contactType' => array(
            'type' => 'text',
            'order' => 120,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
	   	'contacts:name' => array(
            'type' => 'text',
            'order' => 130,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
	   	'contacts:emailAddress' => array(
            'type' => 'text',
            'order' => 140,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
        'organization:name:da' => array(
            'type' => 'text',
            'order' => 150,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
        'organization:description:da' => array(
            'type' => 'text',
            'order' => 160,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
        'organization:url:da' => array(
            'type' => 'text',
            'order' => 170,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
    ),

    /*
     * Allowed metadata names for SPs. If USERDEFINED is set no restrictions is
     * put on metadata names.
     */
    'metadatafields.shib13-sp' => array(
        'AssertionConsumerService' => array(
            'type' => 'text',
            'order' => 10,
            'default' => 'defaultvalue',
            'description' => 'Description',
            'required' => true,
        ),
        'NameIDFormat' => array(
            'type' => 'text',
            'order' => 20,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
        'certData' => array(
            'type' => 'text',
            'order' => 30,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
        'entity:name:da' => array(
            'type' => 'text',
            'order' => 40,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
        'entity:description:da' => array(
            'type' => 'text',
            'order' => 50,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
        'entity:url:da' => array(
            'type' => 'text',
            'order' => 60,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
        // Contact fields
	   	'contacts:telephoneNumber' => array(
            'type' => 'text',
            'order' => 70,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
	   	'contacts:company' => array(
            'type' => 'text',
            'order' => 80,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
	   	'contacts:surName' => array(
            'type' => 'text',
            'order' => 90,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
	   	'contacts:givenName' => array(
            'type' => 'text',
            'order' => 100,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
	   	'contacts:contactType' => array(
            'type' => 'text',
            'order' => 110,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
	   	'contacts:name' => array(
            'type' => 'text',
            'order' => 120,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
	   	'contacts:emailAddress' => array(
            'type' => 'text',
            'order' => 130,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
        'organization:name:da' => array(
            'type' => 'text',
            'order' => 140,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
        'organization:description:da' => array(
            'type' => 'text',
            'order' => 150,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
        'organization:url:da' => array(
            'type' => 'text',
            'order' => 160,
            'default' => 'defaultvalue',
            'description' => 'Description',
        ),
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

    'export.states' => array( 
        'prodaccepted', 
        'QAaccepted',         
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
        // Add attribute
        'addattribute' => array(
            'default' => false,
            'testaccepted' => array(
                'role' => array(
                    'all',            
                ),                        
            ),            
        ),

        //Modify  attribute
        'modifyattribute' => array(
            'default' => false,
            'testaccepted' => array(
                'role' => array(
                    'all',            
                ),                        
            ),            
        ),

        // Delete attribute
        'deleteattribute' => array(
            'default' => false,
            'testaccepted' => array(
                'role' => array(
                    'all',            
                ),                        
            ),            
        ),

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
		'exportallentities' => array(
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
