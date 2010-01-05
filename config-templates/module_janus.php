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
        'dsn'       => 'mysql:host=localhost;dbname=jach_db',
        'username'  => 'jach',
        'password'  => 'xxx', 
        'prefix'    => 'janus__',
    ),

    /*
     * Automatically create a new user if user do not exists on login
     */
    'user.autocreate' => true,

    /*
     * Dashboard configuration.
     */
    'dashboard.inbox.paginate_by' => 20,

    /*
     * Enable entity types
     */
    'enable.saml20-sp' =>   true,
    'enable.saml20-idp' =>  true,
    'enable.shib13-sp' =>   false,
    'enable.shib13-idp' =>  false,


    /*
     * Enable self user creation
     */
    'usercreation.allow' => true,

    /*
     * Configuration of systems in JANUS.
     */
    'workflowstates' => array(
        'testaccepted' => array(
            'name' => array(
                'en' => 'testaccepted - en',
                'da' => 'testaccepted - da',
                'es' => 'testaccepted - es',
            ),
            'description' => array(
                'en' => 'Desc 1 en',
                'da' => 'Desc 1 da',
                'es' => 'Desc 1 es',
            ),
        ),
        'QApending' => array(
            'name' => array(
                'en' => 'QApending - en',
                'da' => 'QApending - da',
                'es' => 'QApending - es',
            ),
            'description' => array(
                'en' => 'Desc 2 en',
                'da' => 'Desc 2 da',
                'es' => 'Desc 2 es',
            ),
        ),
        'QAaccepted' => array(
            'name' => array(
                'en' => 'QAaccepted - en',
                'da' => 'QAaccepted - da',
                'es' => 'QAaccepted - es',
            ),
            'description' => array(
                'en' => 'Desc 3 en',
                'da' => 'Desc 3 da',
                'es' => 'Desc 3 es',
            ),
        ),
        'prodpending' => array(
            'name' => array(
                'en' => 'prodpending - en',
                'da' => 'prodpending - da',
                'es' => 'prodpending - es',
            ),
            'description' => array(
                'en' => 'Desc 4 en',
                'da' => 'Desc 4 da',
                'es' => 'Desc 4 es',
            ),
        ),
        'prodaccepted' => array(
            'name' => array(
                'en' => 'prodaccepted - en',
                'da' => 'prodaccepted - da',
                'es' => 'prodaccepted - es',
            ),
            'description' => array(
                'en' => 'Desc 5 en',
                'da' => 'Desc 5 da',
                'es' => 'Desc 5 es',
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
            'description' => array(
                'da' => 'Beskrivelsen',
                'en' => 'The description',
            ),
        ),
        'mail' => array(
            'description' => array(
                'da' => 'Beskrivelsen',
                'en' => 'The description',
            ),
        ),
    ),

    'attributes.saml20-idp' => array(
        'uid' => array(
            'description' => array(
                'da' => 'Beskrivelsen',
                'en' => 'The description',
            ),
        ),
        'mail' => array(
            'description' => array(
                'da' => 'Beskrivelsen',
                'en' => 'The description',
            ),
        ),
    ),

    'attributes.shib13-sp' => array(
        'uid' => array(
            'description' => array(
                'da' => 'Beskrivelsen',
                'en' => 'The description',
            ),
        ),
        'mail' => array(
            'description' => array(
                'da' => 'Beskrivelsen',
                'en' => 'The description',
            ),
        ),
    ),

    'attributes.shib13-idp' => array(
        'uid' => array(
            'description' => array(
                'da' => 'Beskrivelsen',
                'en' => 'The description',
            ),
        ),
        'mail' => array(
            'description' => array(
                'da' => 'Beskrivelsen',
                'en' => 'The description',
            ),
        ),
    ),

    /*
     * Allowed metadata names for IdPs.
     */
    'metadatafields.saml20-idp' => array(
        'SingleLogoutService' => array(
            'type' => 'text',
            'order' => 10,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Cero o más elementos de tipo EndpointType que describen los receptores que soportan los profiles de Single Logout definidos en [SAMLProf].',
            ),
            'required' => true,
        ),
        'SingleSignOnService' => array(
            'type' => 'text',
            'order' => 20,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Uno o más elementos de tipo EndpointType que describen los receptores que soportan los perfiles del protocolo de Peticion de Autenticacion definidos en [SAMLProf].',
            ),
            'required' => true,
        ),
        'certFingerprint' => array(
            'type' => 'text',
            'order' => 30,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Pequeña secuencia de bytes obtenida aplicando una funcion hash al certificado certData.',
            ),
            'validate' => 'leneq40',
        ),
        'certData' => array(
            'type' => 'text',
            'order' => 40,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Certificado codificado en base 64.',
            ),
            'required' => true,
        ),
        'entity:name' => array(
            'type' => 'text',
            'order' => 50,
            'default' => 'defaultvalue',
            'supported' => array('da','en','es'),
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'The name of this IdP.',
                'es' => 'Nombre de este IdP.',
            ),
        ),
        'entity:description' => array(
            'type' => 'text',
            'order' => 60,
            'default' => 'defaultvalue',
            'supported' => array('da','en','es'),
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'A description of this IdP.',
                'es' => 'Descripción de este IdP.',
            ),
        ),
        'entity:url' => array(
            'type' => 'text',
            'order' => 70,
            'default' => 'defaultvalue',
            'supported' => array('da','en','es'),
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'An URL to your identity provider.',
                'es' => 'URL del proveedor de identidad.',
            ),
        ),
        // Contact fields
        'contacts:telephoneNumber' => array(
            'type' => 'text',
            'order' => 80,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Cero o mas cadenas que especifican el numero de telefono de la persona de contacto.',
            ),
        ),
        'contacts:company' => array(
            'type' => 'text',
            'order' => 90,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Cadena que especifica el nombre de la empresa de la persona de contacto.',
            ),
        ),
        'contacts:surName' => array(
            'type' => 'text',
            'order' => 100,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Cadena opcional que especifica los apellidos de la persona de contacto.',
            ),
        ),
        'contacts:givenName' => array(
            'type' => 'text',
            'order' => 110,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Cadena opcional que especifica el apodo de la persona de contacto.',
            ),
        ),
        'contacts:contactType' => array(
            'type' => 'select',
            'order' => 120,
            'default' => 'technical',
            'select_values' => array("technical", "support", "administrative", "billing", "other"),
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Especifica los tipos de contactos. Los posibles valores son: technical, support, administrative, billing, and other.',
            ),
        ),
        'contacts:name' => array(
            'type' => 'text',
            'order' => 130,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Elemento opcional de tipo cadena que especifica el nombre de la persona de contacto.',
            ),
        ),
        'contacts:emailAddress' => array(
            'type' => 'text',
            'order' => 140,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Cero o mas elementos que representan los emails pertenecientes a la persona de contacto.',
            ),
        ),
        'organization:name' => array(
            'type' => 'text',
            'order' => 150,
            'default' => 'defaultvalue',
            'supported' => array('da','en','es'),
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'Optional element identifying the organization responsible for the SAML entity described by the element.',
                'es' => 'Elemento opcional que identifica la organización responsable de la entidad SAML.',
            ),
        ),
        'organization:description' => array(
            'type' => 'text',
            'order' => 160,
            'default' => 'defaultvalue',
            'supported' => array('da','en','es'),
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'Optional element describing the organization responsible for the SAML entity.',
                'es' => 'Elemento opcional que describe la organización responsable de la entidad SAML.',
            ),
        ),
        'organization:url' => array(
            'type' => 'text',
            'order' => 170,
            'default' => 'defaultvalue',
            'supported' => array('da','en','es'),
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'URL that specify a location to which to direct a user for additional information.',
                'es' => 'URL que especifica una dirección a la que se puede dirigir un usuario para obtener información adicional.',
            ),
        ),
    ),


    /*
     * Allowed metadata names for shib IdPs.
     */
    'metadatafields.shib13-idp' => array(
        'SingleLogoutService' => array(
            'type' => 'text',
            'order' => 10,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Cero o más elementos de tipo EndpointType que describen los receptores que soportan los profiles de Single Logout definidos en [SAMLProf].',
            ),
            'required' => true,
        ),
        'SingleSignOnService' => array(
            'type' => 'text',
            'order' => 20,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Uno o más elementos de tipo EndpointType que describen los receptores que soportan los perfiles del protocolo de Peticion de Autenticacion definidos en [SAMLProf].',
            ),
            'required' => true,
        ),
        'certFingerprint' => array(
            'type' => 'text',
            'order' => 30,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Pequeña secuencia de bytes obtenida aplicando una funcion hash al certificado certData.',
            ),
        ),
        'certData' => array(
            'type' => 'text',
            'order' => 40,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Certificado codificado en base 64.',
            ),
            'required' => true,
        ),
        'entity:name' => array(
            'type' => 'text',
            'order' => 50,
            'default' => 'defaultvalue',
            'supported' => array('da','en','es'),
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'The name of this IdP.',
                'es' => 'Nombre de este IdP.',
            ),
        ),
        'entity:description' => array(
            'type' => 'text',
            'order' => 60,
            'default' => 'defaultvalue',
            'supported' => array('da','en','es'),
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'A description of this IdP.',
                'es' => 'Descripción de este IdP.',
            ),
        ),
        'entity:url' => array(
            'type' => 'text',
            'order' => 70,
            'default' => 'defaultvalue',
            'supported' => array('da','en','es'),
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'An URL to your identity provider.',
                'es' => 'URL del proveedor de identidad.',
            ),
        ),
        // Contact fields
        'contacts:telephoneNumber' => array(
            'type' => 'text',
            'order' => 80,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Cero o mas cadenas que especifican el numero de telefono de la persona de contacto.',
            ),
        ),
        'contacts:company' => array(
            'type' => 'text',
            'order' => 90,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Cadena que especifica el nombre de la empresa de la persona de contacto.',
            ),
        ),
        'contacts:surName' => array(
            'type' => 'text',
            'order' => 100,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Cadena opcional que especifica los apellidos de la persona de contacto.',
            ),
        ),
        'contacts:givenName' => array(
            'type' => 'text',
            'order' => 110,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Cadena opcional que especifica el apodo de la persona de contacto.',
            ),
        ),
        'contacts:contactType' => array(
            'type' => 'select',
            'order' => 120,
            'default' => 'technical',
            'select_values' => array("technical", "support", "administrative", "billing", "other"),
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Especifica los tipos de contactos. Los posibles valores son: technical, support, administrative, billing, and other.',
            ),
        ),
        'contacts:name' => array(
            'type' => 'text',
            'order' => 130,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Elemento opcional de tipo cadena que especifica el nombre de la persona de contacto.',
            ),
        ),
        'contacts:emailAddress' => array(
            'type' => 'text',
            'order' => 140,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Cero o mas elementos que representan los emails pertenecientes a la persona de contacto.',
            ),
        ),
        'organization:name' => array(
            'type' => 'text',
            'order' => 150,
            'default' => 'defaultvalue',
            'supported' => array('da','en','es'),
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'Optional element identifying the organization responsible for the SAML entity described by the element.',
                'es' => 'Elemento opcional que identifica la organización responsable de la entidad SAML.',
            ),
        ),
        'organization:description' => array(
            'type' => 'text',
            'order' => 160,
            'default' => 'defaultvalue',
            'supported' => array('da','en','es'),
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'Optional element describing the organization responsible for the SAML entity.',
                'es' => 'Elemento opcional que describe la organización responsable de la entidad SAML.',
            ),
        ),
        'organization:url' => array(
            'type' => 'text',
            'order' => 170,
            'default' => 'defaultvalue',
            'supported' => array('da','en','es'),
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'URL that specify a location to which to direct a user for additional information.',
                'es' => 'URL que especifica una dirección a la que se puede dirigir un usuario para obtener información adicional.',
            ),
        ),
    ),



    /*
     * Allowed metadata names for SPs.
     */
    'metadatafields.saml20-sp' => array(
        'AssertionConsumerService' => array(
            'type' => 'text',
            'order' => 10,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Uno o mas elementos que describen los endpoints indexados que soportan los perfiles del protocolo de Peticion de Autenticacion definido en [SAMLProf]. Todos los proveedores de servicios soportan al menos un endpoint por definicion.',
            ),
            'required' => true,
        ),
        'SingleLogoutService' => array(
            'type' => 'text',
            'order' => 20,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Cero o más elementos de tipo EndpointType que describen los receptores que soportan los profiles de Single Logout definidos en [SAMLProf].',
            ),
        ),
        'NameIDFormat' => array(
            'type' => 'text',
            'order' => 30,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Cero o mas elementos de tipo type anyURI que enumeran los formatos de identificacion de nombres soportados por la entidad sistema. Ver la seccion 8.3 de [SAMLCore] para ver algunos posibles valores para este elemento.',
            ),
            'validate' => 'leneq40',
        ),
        'certData' => array(
            'type' => 'text',
            'order' => 40,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Certificado codificado en base 64.',
            ),
        ),
        'entity:name' => array(
            'type' => 'text',
            'order' => 50,
            'default' => 'defaultvalue',
            'supported' => array('da','en','es'),
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'The name of this SP.',
                'es' => 'Nombre de este SP.',
            ),
        ),
        'entity:description' => array(
            'type' => 'text',
            'order' => 60,
            'default' => 'defaultvalue',
            'supported' => array('da','en','es'),
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'A description of this SP.',
                'es' => 'Descripción de este SP.',
            ),
        ),
        'entity:url' => array(
            'type' => 'text',
            'order' => 70,
            'default' => 'defaultvalue',
            'supported' => array('da','en','es'),
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'An URL to your service provider.',
                'es' => 'URL de tu proveedor de servicios.',
            ),
        ),
        // Contact fields
        'contacts:telephoneNumber' => array(
            'type' => 'text',
            'order' => 80,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Cero o mas cadenas que especifican el numero de telefono de la persona de contacto.',
            ),
        ),
        'contacts:company' => array(
            'type' => 'text',
            'order' => 90,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Cadena que especifica el nombre de la empresa de la persona de contacto.',
            ),
        ),
        'contacts:surName' => array(
            'type' => 'text',
            'order' => 100,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Cadena opcional que especifica los apellidos de la persona de contacto.',
            ),
        ),
        'contacts:givenName' => array(
            'type' => 'text',
            'order' => 110,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Cadena opcional que especifica el apodo de la persona de contacto.',
            ),
        ),
        'contacts:contactType' => array(
            'type' => 'select',
            'order' => 120,
            'default' => 'technical',
            'select_values' => array("technical", "support", "administrative", "billing", "other"),
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Especifica los tipos de contactos. Los posibles valores son: technical, support, administrative, billing, and other.',
            ),
        ),
        'contacts:name' => array(
            'type' => 'text',
            'order' => 130,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Elemento opcional de tipo cadena que especifica el nombre de la persona de contacto.',
            ),
        ),
        'contacts:emailAddress' => array(
            'type' => 'text',
            'order' => 140,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Cero o mas elementos que representan los emails pertenecientes a la persona de contacto.',
            ),
        ),
        'organization:name' => array(
            'type' => 'text',
            'order' => 150,
            'default' => 'defaultvalue',
            'supported' => array('da','en','es'),
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'Optional element identifying the organization responsible for the SAML entity described by the element.',
                'es' => 'Elemento opcional que identifica la organización responsable de la entidad SAML.',
            ),
        ),
        'organization:description' => array(
            'type' => 'text',
            'order' => 160,
            'default' => 'defaultvalue',
            'supported' => array('da','en','es'),
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'Optional element describing the organization responsible for the SAML entity.',
                'es' => 'Elemento opcional que describe la organización responsable de la entidad SAML.',
            ),
        ),
        'organization:url' => array(
            'type' => 'text',
            'order' => 170,
            'default' => 'defaultvalue',
            'supported' => array('da','en','es'),
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'URL that specify a location to which to direct a user for additional information.',
                'es' => 'URL que especifica una direcciónn a la que se puede dirigir un usuario para obtener información adicional.',
            ),
        ),
    ),

    /*
     * Allowed metadata names for shib SPs.
     */
    'metadatafields.shib13-sp' => array(
        'AssertionConsumerService' => array(
            'type' => 'text',
            'order' => 10,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Uno o mas elementos que describen los endpoints indexados que soportan los perfiles del protocolo de Peticion de Autenticacion definido en [SAMLProf]. Todos los proveedores de servicios soportan al menos un endpoint por definicion.',
            ),
            'required' => true,
        ),
        'NameIDFormat' => array(
            'type' => 'text',
            'order' => 30,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Cero o mas elementos de tipo type anyURI que enumeran los formatos de identificacion de nombres soportados por la entidad sistema. Ver la seccion 8.3 de [SAMLCore] para ver algunos posibles valores para este elemento.',
            ),
        ),
        'certData' => array(
            'type' => 'text',
            'order' => 40,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Certificado codificado en base 64.',
            ),
        ),
        'entity:name' => array(
            'type' => 'text',
            'order' => 50,
            'default' => 'defaultvalue',
            'supported' => array('da','en','es'),
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'The name of this SP.',
                'es' => 'Nombre de este SP.',
            ),
        ),
        'entity:description' => array(
            'type' => 'text',
            'order' => 60,
            'default' => 'defaultvalue',
            'supported' => array('da','en','es'),
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'A description of this SP.',
                'es' => 'Descripción de este SP.',
            ),
        ),
        'entity:url' => array(
            'type' => 'text',
            'order' => 70,
            'default' => 'defaultvalue',
            'supported' => array('da','en','es'),
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'An URL to your service provider.',
                'es' => 'URL de tu proveedor de servicios.',
            ),
        ),
        // Contact fields
        'contacts:telephoneNumber' => array(
            'type' => 'text',
            'order' => 80,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Cero o mas cadenas que especifican el numero de telefono de la persona de contacto.',
            ),
        ),
        'contacts:company' => array(
            'type' => 'text',
            'order' => 90,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Cadena que especifica el nombre de la empresa de la persona de contacto.',
            ),
        ),
        'contacts:surName' => array(
            'type' => 'text',
            'order' => 100,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Cadena opcional que especifica los apellidos de la persona de contacto.',
            ),
        ),
        'contacts:givenName' => array(
            'type' => 'text',
            'order' => 110,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Cadena opcional que especifica el apodo de la persona de contacto.',
            ),
        ),
        'contacts:contactType' => array(
            'type' => 'select',
            'order' => 120,
            'default' => 'technical',
            'select_values' => array("technical", "support", "administrative", "billing", "other"),
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Especifica los tipos de contactos. Los posibles valores son: technical, support, administrative, billing, and other.',
            ),
        ),
        'contacts:name' => array(
            'type' => 'text',
            'order' => 130,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Elemento opcional de tipo cadena que especifica el nombre de la persona de contacto.',
            ),
        ),
        'contacts:emailAddress' => array(
            'type' => 'text',
            'order' => 140,
            'default' => 'defaultvalue',
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'EN - Description',
                'es' => 'Cero o mas elementos que representan los emails pertenecientes a la persona de contacto.',
            ),
        ),
        'organization:name' => array(
            'type' => 'text',
            'order' => 150,
            'default' => 'defaultvalue',
            'supported' => array('da','en','es'),
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'Optional element identifying the organization responsible for the SAML entity described by the element.',
                'es' => 'Elemento opcional que identifica la organización responsable de la entidad SAML.',
            ),
        ),
        'organization:description' => array(
            'type' => 'text',
            'order' => 160,
            'default' => 'defaultvalue',
            'supported' => array('da','en','es'),
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'Optional element describing the organization responsible for the SAML entity.',
                'es' => 'Elemento opcional que describe la organización responsable de la entidad SAML.',
            ),
        ),
        'organization:url' => array(
            'type' => 'text',
            'order' => 170,
            'default' => 'defaultvalue',
            'supported' => array('da','en','es'),
            'description' => array(
                'da' => 'DA - Description',
                'en' => 'URL that specify a location to which to direct a user for additional information.',
                'es' => 'URL que especifica una direcciónn a la que se puede dirigir un usuario para obtener información adicional.',
            ),
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

    /*
     * Cron tags says when Janus hook is executed
     * Uncomment to enable the cron job
     */
    //'cron' => array('daily'),

    'export.external' => array(
        'filesystem' => array(
            'class' => 'janus:FileSystem',
            'name' => 'Filesystem',
            'option' => array(
                'path' => '/path/to/put/metadata.xml',
            ),
        ),
        'FTP' => array(
            'class' => 'janus:FTP',
            'name' => 'FTP',
            'option' => array(
                'host' => 'hostname',
                'path' => '/path/to/put/metadata.xml',
                'username' => 'jach',
                'password' => 'xxx',
            ),
        ),   
    ),

    'export.entitiesDescriptorName' => 'Federation',

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
        // Disable consent
        'disableconsent' => array(
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


        // Show subscriptions
        'showsubscriptions' => array(
            'role' => array(
                'admin',
            ),
        ),
        
        // Export all entities
        'exportallentities' => array(
            'role' => array(
                'all',
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
