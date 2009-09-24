<?php
// Dictionary for JANUS module
$lang = array(
	
	// new user		  
	'text_new_user_created' => array(
		'da' => 'En brugerkonto er nu oprettet for %USERMAIL%.<br /> 
		For at indtaste flere oplysninger om dig selv, følg linket.',
		'en' => 'A user account has been created for %USERMAIL%.<br />
		To enter more information about yourself, folow the link.',		
	),
	
	'header_new_user' => array(
		'da' => 'Opret ny brugerkonto',
		'en' => 'Create new user account',		
	),
	
	'text_create_new_user' => array(
		'da' => 'Du er ved at oprette en konto i JANUS med brugernavnet %USERMAIL%.<br />Tryk på enter for at oprette en konto.',
		'en' => 'You are about to create an account I JANUS with the username %USERMAIL%.<br />Push enter to create account',		
	),

	// Misc
	'text_select_type' => array(
		'da' => 'Vælg type',
		'en' => 'Please select type',		
	),
	
	'text_submit_button' => array(
		'da' => 'Opret',
		'en' => 'Create',		
	),
	
	'text_dashboard' => array(
		'da' => 'Kontrolpanel',
		'en' => 'Dashboard',		
	),

	'text_entity_created' => array(
		'da' => 'Forbindelse oprettet',
		'en' => 'Connection created',		
	),

	'text_saml20-idp' => array(
		'da' => 'Institutioner (IdP)',
		'en' => 'Identity Providers (IdP)',		
	),

	'text_saml20-sp' => array(
		'da' => 'Tjenester (SP)',
		'en' => 'Service Providers (SP)',		
	),

	'text_shib13-sp' => array(
		'da' => 'Shib Tjenester (SP)',
		'en' => 'Shib Service Providers (SP)',		
	),

	'text_shib13-idp' => array(
		'da' => 'Shib Tjenester (SP)',
		'en' => 'Shib Service Providers (SP)',		
	),

	// Dashboard Tabs
	'tab_user_data_header' => array(
		'da' => 'Bruger',
		'en' => 'User',		
	),

	'tab_user_data_subheader' => array(
		'da' => 'Konto oplyninger',
		'en' => 'Account information',		
	),

	'tab_user_data_username' => array(
		'da' => 'Brugernavn',
		'en' => 'User name',		
	),

	'tab_user_data_otherinfo' => array(
		'da' => 'Andre oplysninger',
		'en' => 'Other informations',		
	),
	
	'tab_entities_header' => array(
		'da' => 'Forbindelser',
		'en' => 'Connections',		
	),

	'tab_message_header' => array(
		'da' => 'Inbakke',
		'en' => 'Inbox',		
	),

	'tab_entities_new_entity_subheader' => array(
		'da' => 'Ny forbindelse',
		'en' => 'New Connection',		
	),

	'tab_entities_new_entity_text' => array(
		'da' => 'Indtast nyt forbindelsesID',
		'en' => 'Enter new connection ID',		
	),
	
	'tab_entities_entities_subheader' => array(
		'da' => 'Eksisterende forbindelse',
		'en' => 'Existing Connection',		
	),

	'tab_admin_header' => array(
		'da' => 'Administrator',
		'en' => 'Admin',
	),

	'tab_admin_tab_users_header' => array(
		'da' => 'Brugere',
		'en' => 'Users',
	),
	
	'tab_admin_tab_entities_header' => array(
		'da' => 'Forbindelser',
		'en' => 'Connections',
	),
	
	// Errors
	'error_header' => array(
		'da' => 'Fejl',
		'en' => 'Error',		
	),
	
	'error_entity_exists' => array(
		'da' => 'Forbindelses ID findes i forvejen',
		'en' => 'Connection ID already exists'
	),

    'error_entity_not_url' => array(
        'da' => 'Forbindelses ID skal være en gyldig URL jf. RFC1738.<br />Kun alfanumeriske tegn og $&\'-_.,;=+!*()~/% kan bruges i forbindelses ID\'er.',
        'en' => 'The connection ID should be a valid URL complying to the RFC1738.</br />Only alphanumeric characters and $&\'-_.,;=+!*()~/% may be used in entity ids.',        
    ),

    'error_no_type' => array(
        'da' => 'Angiv forbindelsestype',
        'en' => 'Please select connection type',        
    ),
	
    //Edit entity tabs
	'edit_entity_header' => array(
		'da' => 'Rediger forbindelse',
		'en' => 'Edit connection',		
	),
	
	'tab_edit_entity_connection' => array(
		'da' => 'Forbindelse',
		'en' => 'Connection',		
	),

	'tab_edit_entity_connection_revision' => array(
		'da' => 'Revision',
		'en' => 'Revision',		
	),
	
	'tab_edit_entity_revision_note' => array(
		'da' => 'Revisionsnote',
		'en' => 'Revision note',		
	),
	
	'tab_edit_entity_history' => array(
		'da' => 'Historik',
		'en' => 'History',		
	),
	
	'tab_edit_entity_show_hide' => array(
		'da' => 'Vis/Gem',
		'en' => 'Show/Hide',		
	),
	
	'tab_edit_entity_export' => array(
		'da' => 'Eksporter',
		'en' => 'Export',		
	),
	
	'tab_edit_entity_export_metadata' => array(
		'da' => 'Eksporter metadata',
		'en' => 'Export metadata',		
	),
	
    'tab_edit_entity_save' => array(
        'da' => 'Gem',
        'en' => 'Save',		
    ),
	
    'tab_edit_entity_select' => array(
		'da' => 'Vælg',
		'en' => 'Select',		
	),
	
	'tab_edit_entity_import_xml' => array(
		'da' => 'Importer XML',
		'en' => 'Import XML',		
	),
	
    'tab_edit_entity_entry' => array(
        'da' => 'Felt',
        'en' => 'Entry',		
    ),

	'tab_edit_entity_value' => array(
		'da' => 'Værdi',
		'en' => 'Value',		
	),
	
    'tab_edit_entity_parent_revision' => array(
        'da' => 'Forrig revision',
        'en' => 'Parent revision',		
    ),
    
    'tab_edit_entity_state' => array(
        'da' => 'Tilstand',
        'en' => 'State',		
    ),


	'tab_edit_entity_connection_entityid' => array(
		'da' => 'Forbindelses ID',
		'en' => 'Connection ID',		
	),
	
	'tab_remote_entity_saml20-sp' => array(
		'da' => 'Institution (IdP)',
		'en' => 'Indentity Provider (IdP)',		
	),
	
	'tab_remote_entity_saml20-idp' => array(
		'da' => 'Tjenester (SP)',
		'en' => 'Service Provider (SP)',		
	),

	'tab_remote_entity_help_saml20-idp' => array(
		'da' => 'Marker de tjenester som du ikke ønsker dine brugere skal have adgang til.',
		'en' => 'Mark services that your users are not allowed to access.',		
	),

	'tab_remote_entity_help_saml20-sp' => array(
		'da' => 'Marker de institutioner som du ikke ønsker brugere fra.',
		'en' => 'Mark those identity providers which are not allowed to acces your service.',		
	),

	'tab_remote_entity_allowall' => array(
		'da' => 'Tillad alle',
		'en' => 'Allow all',		
	),

	'tab_metadata' => array(
		'da' => 'Metadata',
		'en' => 'Metadata',		
	),

	'tab_import_metadata' => array(
		'da' => 'Importer metadata',
		'en' => 'Import metadata,'		
	),
	
	'text_entities_help' => array(
		'da' => 'Klik på en tjeneste eller institution for at administrere forbindelser.',
		'en' => 'Click on a service provider or identity provider to administer connections.',		
	),
	
	'status_metadata_parsed_ok' => array(
		'da' => 'Metadata importeret korrekt',
		'en' => 'Metadata imported',		
	),
	
	'error_entityid_no_match' => array(
		'da' => 'Entity id i metadata er ikke korrekt',
		'en' => 'Entity id in metadata do not match',		
	),
	
	'error_metadata_not_parsed' => array(
		'da' => 'Fejl ved import af metadata',
		'en' => 'Error when parsing imported metadata',		
	),
	
	// Export
	'export_text' => array(
		'da' => 'Her under kan du se dit metadata',
		'en' => 'Here you can se your metadata',		
	),
	
	'export_intro' => array(
		'da' => 'Her under kan du se dit metadata',
		'en' => 'Here you can se your metadata',		
	),

    // Admin tab
	'admin_permission' => array(
		'da' => 'Tilladelser',
		'en' => 'Permissions',		
	),
	
    'admin_users' => array(
		'da' => 'Brugere',
		'en' => 'Users',		
	),

    'admin_add' => array(
		'da' => 'Tilføj',
		'en' => 'Add',		
	),

    'admin_remove' => array(
		'da' => 'Fjern',
		'en' => 'Remove',		
	),

    'admin_action' => array(
		'da' => 'Handling',
		'en' => 'Action',		
	),

    'admin_delete' => array(
		'da' => 'Slet',
		'en' => 'Delete',		
	),

    'admin_type' => array(
		'da' => 'Type',
		'en' => 'Type',		
	),

    'admin_email' => array(
        'da' => 'E-mail',
        'en' => 'E-mail',		
    ),

	'admin_select_remove_user' => array(
		'da' => 'Vælg bruger som skal fjernes',
		'en' => 'Select user to remove',		
	),
	
	'admin_select_add_user' => array(
		'da' => 'Vælg bruger som skal tilføjes',
		'en' => 'Select user to add',		
	),
	
	'accessblocker_header' => array(
		'da' => 'Adgang blokret',
		'en' => 'Access blocked',		
	),

    'error_no_access' => array(
        'da' => 'Du har ikke adgang til denne ressource',
        'en' => 'You do not have access to this resource'        
    ),
    
    'text_help' => array(
        'da' => 'Hjælp',
        'en' => 'Help',        
    ),
    /*
	'' => array(
		'da' => '',
		'en' => '',		
	),
	*/
);
?>
