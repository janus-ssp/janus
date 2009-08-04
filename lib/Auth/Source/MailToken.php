<?php
/**
 * MailToken authentication source for simpleSAMLphp
 *
 * @author Jacob Christiansen, <jach@wayf.dk>
 * @package simpleSAMLphp
 * @subpackage JANUS
 * @version $Id$
 */
/**
 * Authentication source for authentication via an email token.
 *
 * When sing this auth source every one can log in by providiing a valid email 
 * address. An email with a link is send to the provided email adress. By 
 * clicking the link the token is validated and the user is authenticated. If a 
 * link is clicked a secon time, the user will not be validated, but a new mail 
 * with a new link is send to the email address that requested the original 
 * token.
 *
 * @package simpleSAMLphp
 * @subpackage JANUS
 */
class sspmod_janus_Auth_Source_MailToken extends SimpleSAML_Auth_Source{

	/**
	 * The state ID.
	 */
	const STAGEID = 'sspmod_janus_Auth_Source_MailToken.state';

	/**
 	 * The authentication source ID.
 	 */
	const AUTHID = 'sspmod_janus_Auth_Source_MailToken.AuthId';

	/**
	 * Database handle
	 * @var PDO
	 */
	private static $db;

	/**
	 * Name of table the users are stord in.
	 * @var string
	 */
	private static $table;

	/**
	 * Initiates the auth source.
	 *
	 * @param array $info Information about this authentication source.
	 * @param array $config Configuration of the auth source.
	 */
	public function __construct($info, $config) {
		assert('is_array($info)');
		assert('is_array($config)');
		assert('array_key_exists("dsn", $config)');
		assert('array_key_exists("username", $config)');
		assert('array_key_exists("password", $config)');
		assert('array_key_exists("table", $config)');

		// Call the parent constructor first, as required.
		parent::__construct($info, $config);

		// Set up the database connection
		self::$db = new PDO($config['dsn'], $config['username'], $config['password']);
		self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		self::$table = $config['table'];
		
	}

	/**
	 * Initialize login.
	 *
	 * This method saves the information about the login, and redirects to a login
	 *  page where the user can enter an email address.
	 *
	 *  @param array &$state Information about the current authentication.
	 */
	public function authenticate(&$state) {
		// We are going to need the authId in order to retrieve this 
		// authentication source later.
		$state[self::AUTHID] = $this->authId;

		$id = SimpleSAML_Auth_State::saveState($state, self::STAGEID);

		$url = SimpleSAML_Module::getModuleURL('janus/mailtoken.php');
		$params = array('AuthState' => $id);
		SimpleSAML_Utilities::redirect($url, $params);

	}

	/**
	 * Handle login request.
	 *
	 * This function is used by the login form (janus/www/mailtoken.php) when the
	 * user enters an email address. If the token is valid it will not return. If
	 * the email address of the token is known a new token is send to the email
	 * address. If no token is givan and the given email address is valid a token
	 * is send to the email address. On error an error code will be returned.
	 *
	 * @param string $authStateid The identifier of the authentication state.
	 * @param string $mail The email enterd.
	 * @param string $The token parsed from the link.
	 * @return string Error code in case of error.
	 */
	public static function handleLogin($authStateId, $mail, $token) {
		assert('is_string($authStateId)');
		assert('is_string($mail) || is_null($mail)');
		assert('is_string($token) || is_null($token)');

		// Retrieve the authentication state.
		$state = SimpleSAML_Auth_State::loadState($authStateId, self::STAGEID);
		assert('array_key_exists(self::AUTHID, $state)');
		$source = SimpleSAML_Auth_Source::getById($state[self::AUTHID]);
		if ($source === NULL) {
			throw new Exception('Could not find authentication source with id ' . $state[self::AUTHID]);
		}

		$returnURLarray = parse_url($state['SimpleSAML_Auth_Default.ReturnURL']);

		$returnURL = $returnURLarray['scheme'].'://'.$returnURLarray['host'].$returnURLarray['path'];

		$tokenok = self::is_token_valid($token);
		$mailbytoken = self::get_email_by_token($token);

		if($tokenok && $mailbytoken) {
			// The token is valid
			$state['Attributes'] = array('mail' => array($mailbytoken));
   			SimpleSAML_Auth_Source::completeAuth($state);	   
		} else if($mailbytoken) {
			// Old token. Sending new token
			if($error =	self::newToken($mailbytoken, $returnURL)) {
				return $error;
			}
			return "send_mail_new_token_by_old";
		} else if($mail) {
			// Email address enterd
			if(self::check_email_address($mail)) {
				if($error =	self::newToken($mail, $returnURL)) {
					return $error;
				}
				return "send_mail_new_token";
			}
			return "error_mail_not_valid";
		} else {
			return;
		}
	}

	/**
	 * Send new token.
	 *
	 * The function generates a new token and emails it to the given email 
	 * address. An error code is returned on error.
	 * The content of the email should be edited in this function.
	 *
	 * @param string $mail A valid email address.
	 * @param string $returnURL The URL that handles the token validation
	 * @return string An error code in case of an error. 
	 * @TODO Put configuration of email content in seperate file
	 */
	private static function newToken($mail, $returnURL) {
		assert('is_string($mail)');

		// Get the language in which the email should be send
 		if (isset($_COOKIE['language'])) {
			$language = $_COOKIE['language'];
		} else {
			$language = 'en';
		}

		// Create new token
		$token = sha1(uniqid(rand().$mail, true));
	
		if (self::create_token($mail, $token)) {
	
			// Construct the email
			$subject = 'JANUS: Login token';
			
			// To send HTML mail, the Content-type header must be set
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			
			// Additional headers
			$headers .= 'From: JANUS <no-reply@wayf.dk>' . "\r\n" .
	    		'Reply-To: WAYF <sekretariatet@wayf.dk>' . "\r\n" .
		    	'X-Mailer: PHP/' . phpversion();
			$body = array();

			$body['en'] = '
				<html>
				<head>
				<title>JANUS token</title>
				</head>
				<body>
				<p>To login to JANUS click the following link:</p>
				<a href="'. $returnURL .'?token='. $token .'">'. $returnURL .'?token='. $token .'</a>
				<p>If the link does not work, please try to copy the link directly into your browsers address bar.</p>
				<p>In case of problems contact the WAYF Secreteriat.</p>
				<br />
				<p>Best regards</p>
				<p>WAYF Secreteriat</p>
				<p>sekretariat@wayf.dk</p>
				</body>
				</html>';
			
			$body['da'] = '
				<html>
				<head>
				<title>JANUS token</title>
				</head>
				<body>
				<p>For at logge ind i JANUS, klik p&aring; linket:</p>
				<a href="'. $returnURL .'?token='. $token .'">'. $returnURL .'?token='. $token .'</a>
				<p>Hvis det ikke virker, pr&oslash;v at kopiere linket til adressefeltet i din browser.</p>
				<p>I tilf&aelig;lde af problemer med JANUS, kontakt WAYF sekretariatet.</p>
				<br />
				<p>Venlig hilsen</p>
				<p>WAYF sekretariatet</p>
				<p>sekretariat@wayf.dk</p>
				</body>
				</html>';


			if(!mail($mail, $subject, $body[$language], $headers)) {
				return "error_mail_not_send";
			}
		} else {
			return 'error_token_not_created';
		}
	}

	/**
	 * Put new token into database.
	 *
	 * The function takes an email and a token and creates a new entry in the 
	 * database for later retrivel.
	 *
	 * @param string $mail A valid email address.
	 * @param string $token A token
	 * @return bool TRUE on success and FALSE on error.
	 * @TODO Make toekn lifetime configurable
	 */
	private static function create_token($mail, $token) {

		$st = self::$db->prepare("INSERT INTO ". self::$table ." (mail, token, notvalidafter) VALUES (?, ?, ?);");

		$notvalidafter = date('c', time()+3600*24);

		if($st->execute(array($mail, $token, $notvalidafter))) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Check validity of token.
	 *
	 * Check if the token is valid and have not been used.
	 *
	 * @param string $token The token
	 * @return bool TRUE if token is valid, FALSE otherwise
	 */
	private static function is_token_valid($token) {
		$sth = self::$db->prepare('UPDATE '. self::$table .' SET usedat = ? WHERE token = ? AND notvalidafter > ? AND usedat is null;');
		$now = date('c');
		$sth->execute(array($now, $token, $now));

		return $sth->rowCount() == 1;
	}

	/**
	 * Get email from token.
	 *
	 * Retrive the emailaddress accociated with the token.
	 *
	 * @param string $token A valid token
	 * @return string The email address accociated with the token
	 */
	private static function get_email_by_token($token) {
		$sth = self::$db->prepare("SELECT mail FROM ". self::$table ." WHERE token = ?;");
		$sth->execute(array($token));
		$row = $sth->fetch(PDO::FETCH_ASSOC);
		
		if(!isset($row['mail'])) {
			return FALSE;
		}
		return $row['mail'];
	}

	/**
	 * Validate an email address
	 *
	 * The function validates the given email addresse. The address is validated 
	 * by using PHP filter_var function and the DNS record is checked.
	 *
	 * @param string $email An email address
	 */
	private static function check_email_address($email) {
		// Validate email form
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	    	return FALSE;
		}
		$email_array = explode("@", $email);

		// Validate DNS record for email
		if(!dns_get_record($email_array[1])) {
			return FALSE;
		}
		return TRUE;
	}
}
?>
