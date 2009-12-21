<?php
/**
 * Main dictionary
 *
 * PHP version 5
 *
 * JANUS is free software: you can redistribute it and/or modify it under the
 * terms of the GNU Lesser General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 *
 * JANUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with JANUS. If not, see <http://www.gnu.org/licenses/>.
 *
 * @category   SimpleSAMLphp
 * @package    JANUS
 * @subpackage Dictionary
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @author     Sixto Martín, <smartin@yaco.es>
 * @copyright  2009 Jacob Christiansen
 * @license    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/janus-ssp/
 * @since      File available since Release 1.5.0
 */
$lang = array(

    // new user
    'text_new_user_created' => array(
        'da' => 'En brugerkonto er nu oprettet for %USERID%.<br /> For at indtaste flere oplysninger om dig selv, følg linket.',
        'en' => 'A user account has been created for %USERID%.<br /> To enter more information about yourself, follow the link.',
        'es' => 'Una cuenta de usuario ha sido creada para %USERID%.<br /> Para introducir más información acerca de ti, sigue el enlace.',
    ),

    'header_new_user' => array(
        'da' => 'Opret ny brugerkonto',
        'en' => 'Create new user account',
        'es' => 'Crear una nueva cuenta de usuario',
    ),

    'text_create_new_user' => array(
        'da' => 'Du er ved at oprette en konto i JANUS med brugernavnet %USERID%.<br />Tryk på enter for at oprette en konto.',
        'en' => 'You are about to create an account I JANUS with the username %USERID%.<br />Push enter to create account',
        'es' => 'Vas a crear una cuenta de usuario en JANUS con el nombre de usuario %USERID%.<br />Pulsa enter para crear la cuenta',
    ),

    // No new user
    'header_no_new_user' => array(
        'da' => 'Kan ikke oprette ny bruger',
        'en' => 'Can not create new user account',
        'es' => 'No crear una nueva cuenta de usuario',
    ),
    
    'text_no_new_user' => array(
        'da' => 'Automatisk oprettelse af nye brugerer er ikke understøttet på denne installation af JANUS.',
        'en' => 'Automatic creation of new users is not supported on this installation of JANUS.',
        'es' => 'Creación automática de nuevos usuarios no se admite en esta instalación de JANUS.',
    ),
    
    // Misc
    'text_mimetype' => array(
        'da' => 'MIME-type',
        'en' => 'MIME type',
        'es' => 'Tipo MIME',
    ),

    'text_select_mimetype' => array(
        'da' => 'Vælg venligst MIME-type',
        'en' => 'Please select MIME type',
        'es' => 'Por favor seleccione el tipo MIME',
    ),

    'text_external' => array(
        'da' => 'Ekstern',
        'en' => 'External',
        'es' => 'Externo',
    ),

    'text_select_external' => array(
        'da' => 'Vælg venligst ekstern destination',
        'en' => 'Please select extern destination',
        'es' => 'Por favor seleccione el destino extern',
    ),

    'text_select_state' => array(
        'da' => 'Vælg tilstand',
        'en' => 'Please select state',
        'es' => 'Por favor selecciona un estado',
    ),

    'text_select_type' => array(
        'da' => 'Vælg type',
        'en' => 'Please select type',
        'es' => 'Por favor selecciona un tipo',
    ),

    'text_submit_button' => array(
        'da' => 'Opret',
        'en' => 'Create',
        'es' => 'Crear',
    ),

    'text_dashboard' => array(
        'da' => 'Kontrolpanel',
        'en' => 'Dashboard',
        'es' => 'Panel',
    ),

    'text_entity_created' => array(
        'da' => 'Forbindelse oprettet',
        'en' => 'Connection created',
        'es' => 'Conexión creada',
    ),

    'text_export_federation_desc' => array (
        'da' => 'Vælg et filter om om du vil downloade eller se i xml eller tekst format',
        'en' => 'Choose a filter and choose if you want download or view in xml or text format',
        'es' => 'Seleccione uno de los filtros y si quiere descargarlo, verlo en forma de xml o de texto',
    ),

    'text_idp&sp-all' => array(
        'da' => 'Alle institutioner (IdP) og alle tjenester (SP)',
        'en' => 'All identity Providers (IdP) and all service Providers (SP)',
        'es' => 'Proveedores de Identidad (IdP) y Proveedores de Servicio (SP)',
    ),

    'text_idp-all' => array(
        'da' => 'Alle Institutioner (IdP)',
        'en' => 'All Identity Providers (IdP)',
        'es' => 'Todos los Proveedores de Identidad (IdP)',
    ),

    'text_sp-all' => array(
        'da' => 'Alle Tjenester (SP)',
        'en' => 'All Service Providers (SP)',
        'es' => 'Todos los Proveedores de Servicio (SP)',
    ),

    'text_saml20-all' => array(
        'da' => 'Alle SAML Institutioner (IdP) og alle SAML Tjenester (SP)',
        'en' => 'Alle SAML Identity Providers (IdP) and all SAML Service Providers (SP)',
        'es' => 'Proveedores de Identidad SAML(IdP) y Proveedores de Servicio SAML(SP)',
    ),

    'text_shib13-all' => array(
        'da' => 'Alle Shib Institutioner (IdP) og alle Shib Tjenester (SP)',
        'en' => 'All Shib Identity Providers (IdP) and all Shib Service Providers (SP)',
        'es' => 'Proveedores de Identidad Shib (IdP) y Proveedores de Servicio Shib (SP)',
    ),

    'text_saml20-idp' => array(
        'da' => 'Alle SAML Institutioner (IdP)',
        'en' => 'All SAML Identity Providers (IdP)',
        'es' => 'Proveedores de Identidad SAML (IdP)',
    ),

    'text_saml20-sp' => array(
        'da' => 'Alle SAML Tjenester (SP)',
        'en' => 'All SAML Service Providers (SP)',
        'es' => 'Proveedores de Servicio SAML (SP)',
    ),

    'text_shib13-sp' => array(
        'da' => 'Alle Shib Tjenester (SP)',
        'en' => 'All Shib Service Providers (SP)',
        'es' => 'Proveedores de Servicio Shib (SP)',
    ),

    'text_shib13-idp' => array(
        'da' => 'Alle Shib Institutioner (IdP)',
        'en' => 'All Shib Identity Providers (IdP)',
        'es' => 'Proveedores de Identidad Shib (IdP)',
    ),

    // Dashboard Tabs
    'tab_user_data_header' => array(
        'da' => 'Bruger',
        'en' => 'User',
        'es' => 'Usuario',
    ),

    'tab_user_data_subheader' => array(
        'da' => 'Konto oplyninger',
        'en' => 'Account information',
        'es' => 'Información de la cuenta',
    ),

    'tab_user_data_email' => array(
        'da' => 'E-mail',
        'en' => 'E-mail',
        'es' => 'E-mail',
    ),

    'tab_user_data_username' => array(
        'da' => 'Bruger ID',
        'en' => 'User ID',
        'es' => 'Nombre de usuario',
    ),

    'tab_user_data_otherinfo' => array(
        'da' => 'Andre oplysninger',
        'en' => 'Other informations',
        'es' => 'Otra información',
    ),

    'tab_entities_header' => array(
        'da' => 'Forbindelser',
        'en' => 'Connections',
        'es' => 'Conexiones',
    ),

    'tab_message_header' => array(
        'da' => 'Indbakke',
        'en' => 'Inbox',
    ),

    'tab_entities_new_entity_subheader' => array(
        'da' => 'Ny forbindelse',
        'en' => 'New Connection',
        'es' => 'Nueva conexión',
    ),

    'tab_entities_new_entity_text' => array(
        'da' => 'Indtast nyt forbindelsesID',
        'en' => 'Enter new connection ID',
        'es' => 'Introducir nueva ID de conexión',
    ),

    'tab_entities_entities_subheader' => array(
        'da' => 'Eksisterende forbindelse',
        'en' => 'Existing Connection',
        'es' => 'Conexiones existentes',
    ),

    'tab_admin_header' => array(
        'da' => 'Administrator',
        'en' => 'Admin',
        'es' => 'Administrador',
    ),

    'tab_federation_header' => array(
        'da' => 'Føderation',
        'en' => 'Federation',
        'es' => 'Federación',
    ),

    'tab_admin_tab_users_header' => array(
        'da' => 'Brugere',
        'en' => 'Users',
        'es' => 'Usuarios',
    ),

    'tab_admin_tab_entities_header' => array(
        'da' => 'Forbindelser',
        'en' => 'Connections',
        'es' => 'Conexiones',
    ),

    // Errors
    'error_header' => array(
        'da' => 'Fejl',
        'en' => 'Error',
        'es' => 'Error',
    ),

    'error_entity_exists' => array(
        'da' => 'Forbindelses ID findes i forvejen',
        'en' => 'Connection ID already exists',
        'es' => 'La ID de conexion ya existe',
    ),

    'error_entity_not_url' => array(
        'da' => 'Forbindelses ID skal være en gyldig URL jf. RFC1738.<br />Kun alfanumeriske tegn og $&\'-_.,;=+!*()~/% kan bruges i forbindelses ID\'er.',
        'en' => 'The connection ID should be a valid URL complying to the RFC1738.</br />Only alphanumeric characters and $&\'-_.,;=+!*()~/% may be used in entity ids.',
        'es' => 'La ID de conexión debería ser una URL válida que cumpla en RFC1738.</br>Solo caracteres alfanuméricos y $&\'-_.,;=+!*()~/% deben ser usados en los identificadores de indentidad.',
    ),

    'error_no_type' => array(
        'da' => 'Angiv forbindelsestype',
        'en' => 'Please select connection type',
        'es' => 'Por favor seleccione un tipo de conexión',
    ),

    //Edit entity tabs
    'edit_entity_header' => array(
        'da' => 'Rediger forbindelse',
        'en' => 'Edit connection',
        'es' => 'Editar conexión',
    ),

    'tab_edit_entity_connection' => array(
        'da' => 'Forbindelse',
        'en' => 'Connection',
        'es' => 'Conexión',
    ),

    'tab_edit_entity_connection_metadataurl' => array(
        'da' => 'Metadata URL',
        'en' => 'Metadata URL',
        'es' => 'URL de metadatos',
    ),

    'tab_edit_entity_connection_revision' => array(
        'da' => 'Revision',
        'en' => 'Revision',
        'es' => 'Revisión',
    ),

    'tab_edit_entity_revision_note' => array(
        'da' => 'Revisionsnote',
        'en' => 'Revision note',
        'es' => 'Notas de la revisión',
    ),

    'tab_edit_entity_history' => array(
        'da' => 'Historik',
        'en' => 'History',
        'es' => 'Histórico',
    ),

    'tab_edit_entity_show_hide' => array(
        'da' => 'Vis/Gem hele historikken',
        'en' => 'Show/Hide complete history',
        'es' => 'Mostrar/Ocultar la historia completa',
    ),

    'tab_edit_entity_import_from_url' => array(
        'da' => 'Importer fra URL',
        'en' => 'Import from URL',
        'es' => 'Importar de una URL',
    ),

    'tab_edit_entity_import_from_url' => array(
        'da' => 'Importer fra URL',
        'en' => 'Import from URL',
        'es' => 'Importar de una URL',
    ),

    'tab_edit_entity_export' => array(
        'da' => 'Eksporter',
        'en' => 'Export',
        'es' => 'Exportar',
    ),

    'tab_edit_entity_export_metadata' => array(
        'da' => 'Eksporter metadata',
        'en' => 'Export metadata',
        'es' => 'Exportar medatados',
    ),

    'tab_edit_entity_save' => array(
        'da' => 'Gem',
        'en' => 'Save',
        'es' => 'Guardar',
    ),

    'tab_edit_entity_select' => array(
        'da' => 'Vælg',
        'en' => 'Select',
        'es' => 'Seleccionar',
    ),

    'tab_edit_entity_import_xml' => array(
        'da' => 'Importer XML',
        'en' => 'Import XML',
        'es' => 'Importar XML',
    ),

    'tab_edit_entity_entry' => array(
        'da' => 'Felt',
        'en' => 'Entry',
        'es' => 'Entrada',
    ),

    'tab_edit_entity_value' => array(
        'da' => 'Værdi',
        'en' => 'Value',
        'es' => 'Valor',
    ),

    'tab_edit_entity_parent_revision' => array(
        'da' => 'Forrig revision',
        'en' => 'Parent revision',
        'es' => 'Revisión padre',
    ),

    'tab_edit_entity_state' => array(
        'da' => 'Tilstand',
        'en' => 'State',
        'es' => 'Estado',
    ),

    'tab_disable_consent' => array(
        'da' => 'Administrativ samtykke',
        'en' => 'Consent disabling',
    ),

    'tab_disable_consent_help' => array(
        'da' => 'Herunder kan du give samtykke for alle brugere.',
        'en' => 'Mark all services that you want to disable consent for.',
    ),

    'tab_edit_entity_connection_entityid' => array(
        'da' => 'Forbindelses ID',
        'en' => 'Connection ID',
        'es' => 'ID de conexión',
    ),

    'tab_remote_entity_saml20-sp' => array(
        'da' => 'Institution (IdP)',
        'en' => 'Indentity Provider (IdP)',
        'es' => 'Proveedores de Identidad (IdP)',
    ),

    'tab_remote_entity_saml20-idp' => array(
        'da' => 'Tjenester (SP)',
        'en' => 'Service Provider (SP)',
        'es' => 'Proveedores de Servicio (SP)',
    ),

    'tab_remote_entity_help_saml20-idp' => array(
        'da' => 'Marker de tjenester som du ikke ønsker dine brugere skal have adgang til.',
        'en' => 'Mark services that your users are not allowed to access.',
        'es' => 'Marca los servicios a los que a tus usuarios no les está permitido acceder',

    ),

    'tab_remote_entity_help_saml20-sp' => array(
        'da' => 'Marker de institutioner som du ikke ønsker brugere fra.',
        'en' => 'Mark those identity providers which are not allowed to acces your service.',
        'es' => 'Marca aquellos Proveedores de identidad a los que no les está permitido el acceso a tu servicio.', 
    ),

    'tab_remote_entity_allowall' => array(
        'da' => 'Tillad alle',
        'en' => 'Allow all',
        'es' => 'Permitir todos',
    ),

    'tab_metadata' => array(
        'da' => 'Metadata',
        'en' => 'Metadata',
        'es' => 'Metadatos',
    ),

    'tab_import_metadata' => array(
        'da' => 'Importer metadata',
        'en' => 'Import metadata',
        'es' => 'Importar metadatos',
    ),

    'text_entities_help' => array(
        'da' => 'Klik på en tjeneste eller institution for at administrere forbindelser.',
        'en' => 'Click on a service provider or identity provider to administer connections.',
        'es' => 'Haz clik sobre el proveedor de servicio o el identificador de identidad para administrar las conexiones.',
    ),

    'status_metadata_parsed_ok' => array(
        'da' => 'Metadata importeret korrekt',
        'en' => 'Metadata imported',
        'es' => 'Metadatos importados',
    ),
    
    
    'delete_metadata_question' => array(
        'da' => 'Vil du slette metadata?',
        'en' => 'Remove metadata?',
        'es' => '¿Borrar metadatos?',
    ),

    'delete_attribute_question' => array(
        'da' => 'Vil du slette attribute?',
        'en' => 'Remove attribute?',
        'es' => '¿Borrar atributo?',
    ),

    // Import
    'add_metadata_from_url_desc' => array(
        'da' => 'Hent metadata fra en URL. Denne URL skal pege på en xml-fil med metadata.',
        'en' => 'Get metadata from a URL. This URL must target a xml file with the metadatas of the entity.',
        'es' => 'Obtener los metadatos a partir de una url. Esta url debe apuntar a un xml que contenga los metadatos de la entidad.',
    ),

    'get_metadata' => array(
        'da' => 'Hent metadata',
        'en' => 'Get metadata',
        'es' => 'Obtener metadatos',
    ),

    'error_import_metadata_permission' => array(
        'da' => 'Du har ikke tilladelse til at importere metadata',
        'en' => 'You do not have permission to import metadata.',
        'es' => 'No tienes permisos para importar metadatos.',
    ),

    'error_import_metadata_url' => array(
        'da' => 'Kan ikke importere. Ugyldig metadata URL.',
        'en' => 'Can not import. Invalid metadata URL.',
        'es' => 'No se pudo importar. URL de metadatos no Válida.',
    ),

    'error_entityid_no_match' => array(
        'da' => 'Entity id i metadata er ikke korrekt',
        'en' => 'Entity id in metadata do not match',
        'es' => 'El identificador de entidad no concuerda con los metadatos',
    ),

    'error_metadata_not_parsed' => array(
        'da' => 'Fejl ved import af metadata',
        'en' => 'Error when parsing imported metadata',
        'es' => 'Error mientras se parseaban los metadatos importados',
    ),
    
    'error_metadata_not_parsed_due_expiration' => array(
        'da' => 'Fejl under import af metadata, fordi de var forældede',
        'en' => 'Error when importing metadata due metadata are expired',
        'es' => 'Error mientras se importaban los metadatos debido a que estaban caducados',
    ),


    'error_not_valid_saml20' => array(
        'da' => 'Fejl ved import af metadata. Ikke gyldig SAML 2.0 metadata',
        'en' => 'Error when parsing imported metadata. Not valid SAML 2.0 metadata',
    ),

    // Export
    'export_text' => array(
        'da' => 'Her under kan du se dit metadata',
        'en' => 'Here you can see your metadata',
        'es' => 'Aquí puedes ver tus metadatos',
    ),

    'export_intro' => array(
        'da' => 'Her under kan du se dit metadata',
        'en' => 'Here you can see your metadata',
        'es' => 'Aquí puedes ver tus metadatos',
    ),

    'tab_entities_federation_entity_subheader' => array(
        'da' => 'Føderations metadata',
        'en' => 'Federation metadata',
        'es' => 'Federación de metadatos',
    ),

    'tab_entities_federation_entity_description' => array(
        'da' => 'Klik her for at eksportere føderationsmetadata',
        'en' => 'Click here to export the metadata set of entities of the federation',
        'es' => 'Haz click aqui para exportar el conjunto de metadatos de las entidades de la federación',
    ),

    // Admin tab
    'admin_permission' => array(
        'da' => 'Tilladelser',
        'en' => 'Permissions',
        'es' => 'Permisos',
    ),

    'admin_users' => array(
        'da' => 'Brugere',
        'en' => 'Users',
        'es' => 'Usuarios',
    ),

    'admin_add_user' => array(
        'da' => 'Tilføj ny bruger',
        'en' => 'Add new user',
        'es' => 'Añadir un nuevo usuario',
    ),

    'admin_add' => array(
        'da' => 'Tilføj',
        'en' => 'Add',
        'es' => 'Añadir',
    ),

    'admin_remove' => array(
        'da' => 'Fjern',
        'en' => 'Remove',
        'es' => 'Borrar',
    ),

    'admin_action' => array(
        'da' => 'Handling',
        'en' => 'Action',
        'es' => 'Acción',
    ),

    'admin_delete' => array(
        'da' => 'Slet',
        'en' => 'Delete',
        'es' => 'Eliminar',
    ),

    'admin_edit' => array(
        'da' => 'Rediger',
        'en' => 'Edit',
        'es' => 'Editar',
    ),

    'admin_save' => array(
        'da' => 'Gem',
        'en' => 'Save',
        'es' => 'Guardar',
    ),

    'admin_active' => array(
        'da' => 'Aktiv',
        'en' => 'Active',
        'es' => 'Activo',
    ),

    'admin_type' => array(
        'da' => 'Type',
        'en' => 'Type',
        'es' => 'Tipo',
    ),

    'admin_email' => array(
        'da' => 'E-mail',
        'en' => 'E-mail',
        'es' => 'E-mail',
    ),

    'admin_userid' => array(
        'da' => 'Bruger ID',
        'en' => 'User ID',
        'es' => 'ID de usuario',
    ),

    'admin_select_remove_user' => array(
        'da' => 'Vælg bruger som skal fjernes',
        'en' => 'Select user to remove',
        'es' => 'Selecciona usuario a eliminar',
    ),

    'admin_select_add_user' => array(
        'da' => 'Vælg bruger som skal tilføjes',
        'en' => 'Select user to add',
        'es' => 'Selecciona usuario a añadir',
    ),

    'accessblocker_header' => array(
        'da' => 'Adgang blokret',
        'en' => 'Access blocked',
        'es' => 'Acceso bloqueado',
    ),

    'error_no_access' => array(
        'da' => 'Du har ikke adgang til denne ressource',
        'en' => 'You do not have access to this resource',
    'es' => 'No tienes acceso para acceder a este recurso',
    ),

    'text_help' => array(
        'da' => 'Hjælp',
        'en' => 'Help',
        'es' => 'Ayuda',
    ),

    'error_user_not_created' => array(
        'da' => 'Brugeren blev ikke oprettet',
        'en' => 'The user was not created',
        'es' => 'Usuario no creado',
    ),

    'error_user_not_created_due_params' => array(
        'da' => 'Brugeren blev ikke oprettet. Kontroller, at bruger-id og type er gyldige',
        'en' => 'The user was not created. Verify that the user ID and type are valid',
        'es' => 'usuario no creado. Compruebe que el ID de usuario y el tipo son válidos',
    ),

    // Install script
    'install_tables_created' => array(
        'da' => 'Følgende tabeller er blevet oprettet',
        'en' => 'The following tables have been created',
        'es' => 'Las siguientes tablas se han creado',
    ),

    'install_users_created' => array(
        'da' => 'Følgende brugere er blevet oprettet',
        'en' => 'The follwoing users have been created',
        'es' => 'Los usuarios follwoing se han creado',
    ),

    'install_created' => array(
        'da' => 'oprettet',
        'en' => 'created',
        'es' => 'creado',
    ),

    'install_success' => array(
        'da' => 'Tillykke. JANUS er nu installeret.',
        'en' => 'Congratulation. JANUS is now installed.',
        'es' => 'Felicidades. JANUS está instalado.',
    ),

    'install_add_authsource' => array(
        'da' => 'Du skal tilføje følgende til <tt>authsources.php</tt> for at Mailtoken login modulet virker',
        'en' => 'You need to add the following to <tt>authsources.php</tt> for Mailtoken auth source to work',
        'es' => 'Es necesario añadir lo siguiente a <tt>authsources.php</tt> para fuente Mailtoken autoridades a trabajar',
    ),

    'install_config_file' => array(
        'da' => 'Konfigurationsfil',
        'en' => 'Config file',
        'es' => 'Archivo de configuración',
    ),

    'install_delete_install' => array(
        'da' => 'HUSK at slette installationsbiblioteket, da din installation ellers kan overskrives.',
        'en' => 'REMEMBER to delete the install directory. Your installation can be overwritten.',
        'es' => 'RECUERDE eliminar el directorio de instalación. Su instalación se puede sobrescribir.',
    ),

    'install_header' => array(
        'da' => 'JANUS - Installation',
        'en' => 'JANUS - Installation',
        'es' => 'JANUS - Instalación',
    ),

    'install_error_db' => array(
        'da' => 'Der er sket en fejl. Kontroller at forbindelsen til din database, samt konfigurationen er korrekt og prøv igen.',
        'en' => 'An error has occured. Please checkk that the connection to the database and configuration is correct and try again.',
        'es' => 'Ha ocurrido un error. Si checkk que la conexión a la base de datos y la configuración es correcta y vuelva a intentarlo.',
    ),

    'install_welcome' => array(
        'da' => 'Velkommen til JANUS web-installer',
        'en' => 'Welcome to JANUS web installer',
        'es' => 'Bienvenido al instalador web JANUS',
    ),

    'install_instruction' => array(
        'da' => 'Når du trykker `Installer` oprettes alle tabeller som JANUS skal bruge inkl. tabeller til autensificerings modulet. Derudover laves der en konfigurationsfil, som du selv skal kopierer til din SimpleSAMLphp installation. Kode til authsource laves også.</p>
<p><strong>OBS!</strong> Denne installer er kun til brug med en MySQL database.</p>
<p>Du skal desuden udfylde informationer om administratoren. Der vil efterfølgende blive oprettet en admin bruger med disse informationer.',
        'en' => 'When you hit `install` all database tables for JANSU will be created including tables for the Mailtoken auth source. A configuration file will be created as well. You must manually include the configuration file into your local installation of SimpleSAMLphp. configuration for the Mailtoken auth source will also be created.</p><p><strong>NB</strong> This installer can only use a MySQL database.</p><p>You also need to fill out admin infomation. A admin account will then be created.',
        'es' => 'Al llegar a «instalar», todas las tablas de base de datos para jansu se creará incluyendo las tablas de la fuente de autoridades Mailtoken. Un archivo de configuración se crea también. Manualmente, debe incluir el archivo de configuración en su instalación de locales de simpleSAMLphp. de configuración de la fuente de autoridades Mailtoken también será creado. </ p> <strong> Nota: </ strong> Este instalador sólo se puede utilizar una base de datos MySQL. </ p> También es necesario llenar la información de admin . Una cuenta de administrador será creado.',
    ),

    'install_database' => array(
        'da' => 'Database',
        'en' => 'Database',
        'es' => 'Base de datos',
    ),

    'install_database_type' => array(
        'da' => 'Database type',
        'en' => 'Database type',
        'es' => 'Tipo de base de datos',
    ),

    'install_database_host' => array(
        'da' => 'Database host',
        'en' => 'Database host',
        'es' => 'Base de datos de acogida',
    ),

    'install_database_name' => array(
        'da' => 'Database navn',
        'en' => 'Database name',
        'es' => 'Nombre de base de datos',
    ),

    'install_database_prefix' => array(
        'da' => 'Database prefix',
        'en' => 'Database prefix',
        'es' => 'Base de datos de prefijo',
    ),

    'install_database_username' => array(
        'da' => 'Database brugernavn',
        'en' => 'Database username',
        'es' => 'Nombre de usuario de base de datos',
    ),

    'install_database_password' => array(
        'da' => 'Database kodeord',
        'en' => 'Database password',
        'es' => 'Contraseña de base de datos',
    ),

    'install_adminuser' => array(
        'da' => 'Administrator bruger',
        'en' => 'Administrator user',
        'es' => 'Usuario de administrador',
    ),

    'install_adminuser_email' => array(
        'da' => 'E-mail',
        'en' => 'E-mail',
        'es' => 'E-mail',
    ),

    'install_adminuser_name' => array(
        'da' => 'Navn',
        'en' => 'Name',
        'es' => 'Nombre',
    ),

    'install_install' => array(
        'da' => 'Installer',
        'en' => 'Install',
        'es' => 'Instalar',
    ),

    'text_select_multiple' => array(
        'da' => 'Du kan vælge flere',
        'en' => 'You can select multiple',
        'es' => 'Usted puede seleccionar varios',
    ),
     
    'externalexportok' => array(
        'da' => 'Metadata eksporteret til ekstern destination',
        'en' => 'Metadata exported to external destination',
        'es' => 'Metadatos exportados a destino externo',
    ),
    'hour_expires' => array(
        'da' => 'Udløber i %META_EXPIRES_TIME% timer',
        'en' => 'Expires in %META_EXPIRES_TIME% hour(s)',
        'es' => 'Caduca en %META_EXPIRES_TIME% hora(s)',
    ),

    'hour_expired' => array(
        'da' => 'Udløbet %META_EXPIRED_TIME% timer siden',
        'en' => 'Expired %META_EXPIRED_TIME% hour(s) ago',
        'es' => 'Caducó hace %META_EXPIRED_TIME% hora(s)',
    ),

    'day_expires' => array(
        'da' => 'Udløber i %CERT_EXPIRES_TIME% dage',
        'en' => 'Expires in %CERT_EXPIRES_TIME% day(s)',
        'es' => 'Caduca en %CERT_EXPIRES_TIME% día(s)',
    ),

    'expired' => array(
        'da' => 'Udløbet',
        'en' => 'Expired',
        'es' => 'Caducado',
    ),

    'no_expired' => array(
        'da' => 'Ikke er udløbet',
        'en' => 'Not Expired',
        'es' => 'No Caducado',
    ),

    'metadata_expired' => array(
        'da' => 'Metadata er udløbet',
        'en' => 'Metadata expired',
        'es' => 'Metadatos Caducados',
    ),

    /*
    
    '' => array(
        'da' => '',
        'en' => '',
        'es' => '',
    ),
    */
);
?>
