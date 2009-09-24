<?php
// Dictionary for MailToken authentication spurce
$lang = array(

	// Error messages for MailToken login
	'error_mail_not_valid' => array(
		'da' => 'Email addressen du indtastede er ikke korrekt. Prøv igen.',
		'en' => 'The email address you just entered is not valid. Please try again.',
		'es' => 'El email que ha introducido es inválido. Por favor intentelo de nuevo.',
	),
	
	'error_mail_not_send' => array(
		'da' => 'Der er sket en fejl. Der er ikke sendt en mail. Prøv igen.',
		'en' => 'An error has occured. Please try again.',
		'es' => 'Ha ocurrido un error. Por favor intentelo de nuevo.',
	),
	
	// Status messages for MailToken login
	'send_mail_new_token' => array (
		'da' => 'JANUS har registreret email-adressen.<br />
		En email med et link er blevet sendt til %USERMAIL%.<br />
		For at logge ind i JANUS skal du klikke på linket i emailen.',
		'en' => 'JANUS has registered the email address.<br />
		An email with a link has been sent to %USERMAIL%.<br />
		To log into the JANUS, click the link in the email.',
		'es' => 'JANUS ha registrado su dirección de correo.<br />
		Le ha sido enviado un correo con un enlace a %USERMAIL%.<br />
		Para acceder a JANUS, haga click en el enlace del correo.',
	),

	'send_mail_new_token_by_old' => array(
		'da' => 'Du kan kun logge ind én gang med den samme link.<br />Vi har sendt dig en ny loginlink. Du modtager en mail med en ny login link.',
		'en' => 'It is not possible to reuse your login link.<br />You will receive a mail containing a new login link.',
		'es' => 'No es posible reutilizar el enlace de acceso.<br />Recibirás un correo que contenga un nuevo enlace de acceso.',
	),

	// Text
	'help_header' => array(
		'da' => 'Hjælp',
		'en' => 'Help',
		'es' => 'Ayuda',
	),

	'help_text' => array(
		'da' => 'I tilfælde af problemer med JANUS, kontakt %ADMINNAME%, <a href="mailto:%ADMINEMAIL%">%ADMINEMAIL%</a>',
		'en' => 'In case of problems contact %ADMINNAME%, <a href="mailto:%ADMINEMAIL%">%ADMINEMAIL%</a>',
		'es' => 'Si tiene problemas contacte con %ADMINNAME%, <a href="mailto:%ADMINEMAIL%">%ADMINEMAIL%</a>',
	),

	// Login text	
	'text_login_header' => array(
		'da' => 'Login',
		'en' => 'Login',
		'es' => 'Acceso',
	),
	
	'text_login_help' => array(
		'da' => 'For at logge ind skal du svare på en kontrol-email.<br /> Indtast din adresse, og klik på linket i den email, du modtager.',
		'en' => 'To login you need to recive an email.<br/>Enter your email address or click the link in the recived email.',
		'es' => 'Para acceder necesitas recibir un correo..<br/> Introduce una dirección de correo o clickea el enlace del correo que recibiste.',
	),

	// New user text
	'text_create_account_header' => array(
		'da' => 'Kontooprettelse',
		'en' => 'New accoount',
		'es' => 'Nueva cuenta',
	),

	'text_create_account_help' => array(
		'da' => 'For at blive oprettet som bruger skal du svare på en kontrol-email.<br />Indtast din adresse, og klik på linket i den email, du modtager.',
		'en' => 'To create a new account you need to recive an email.<br />Enter your email address or click the link in the recived email.',
		'es' => 'Para crear una nueva cuenta necesitas recibir un correo.<br />Introduce tu dirección de correo o clickea el enlace del correo que recibiste.',
	),

	// Misc text
	'text_send_button' => array(
		'da' => 'Send',
		'en' => 'Send',
		'es' => 'Enviar',
	),

	'text_success_header' => array(
		'da' => 'Succes',
		'en' => 'Success',
		'es' => 'Éxito',
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
