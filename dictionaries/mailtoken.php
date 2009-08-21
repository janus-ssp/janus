<?php
// Dictionary for MailToken authentication spurce
$lang = array(

	// Error messages for MailToken login
	'error_mail_not_valid' => array(
		'da' => 'Email addressen du indtastede er ikke korrekt. Prøv igen.',
		'en' => 'The email address you just entered is not valid. Please try again.',		
	),
	
	'error_mail_not_send' => array(
		'da' => 'Der er sket en fejl. Der er ikke sendt en mail. Prøv igen.',
		'en' => 'An error has occured. Please try again.',		
	),
	
	// Status messages for MailToken login
	'send_mail_new_token' => array (
		'da' => 'JANUS har registreret email-adressen.<br />
		En email med et link er blevet sendt til %USERMAIL%.<br />
		For at logge ind i JANUS skal du klikke på linket i emailen.',
		'en' => 'JANUS has registered the email address.<br />
		An email with a link has been sent to %USERMAIL%.<br />
		To log into the JANUS, click the link in the email.',
	),

	'send_mail_new_token_by_old' => array(
		'da' => 'Du kan kun logge ind én gang med den samme link.<br />Vi har sendt dig en ny loginlink. Du modtager en mail med en ny login link.',
		'en' => 'It is not possible to reuse your login link.<br />You will receive a mail containing a new login link.',		
	),

	// Text
	'help_header' => array(
		'da' => 'Hjælp',
		'en' => 'Help',
	),

	'help_text' => array(
		'da' => 'I tilfælde af problemer med JANUS, kontakt %ADMINNAME%, <a href="mailto:%ADMINEMAIL%">%ADMINEMAIL%</a>',
		'en' => 'In case of problems contact %ADMINNAME%, <a href="mailto:%ADMINEMAIL%">%ADMINEMAIL%</a>',		
	),

	// Login text	
	'text_login_header' => array(
		'da' => 'Login',
		'en' => 'Login',		
	),
	
	'text_login_help' => array(
		'da' => 'For at logge ind skal du svare på en kontrol-email.<br /> Indtast din adresse, og klik på linket i den email, du modtager.',
		'en' => 'To login you need to recive an email.<br/>Enter your enail address og click the link in the recived email.',		
	),

	// New user text
	'text_create_account_header' => array(
		'da' => 'Kontooprettelse',
		'en' => 'New accoount',		
	),

	'text_create_account_help' => array(
		'da' => 'For at blive oprettet som bruger skal du svare på en kontrol-email.<br />Indtast din adresse, og klik på linket i den email, du modtager.',
		'en' => 'To create a new account you ned to recive an email.<br />Enter your enail address og click the link in the recived email.',		
	),

	// Misc text
	'text_send_button' => array(
		'da' => 'Send',
		'en' => 'Send',		
	),

	'text_success_header' => array(
		'da' => 'Succes',
		'en' => 'Success',		
	),
);
?>
