<?php
class sspmod_janus_Auth_Source_MailToken extends SimpleSAML_Auth_Source{
	/**
	 * 	 * The string used to identify our states.
	 */
	const STAGEID = 'sspmod_janus_Auth_Source_MailToken.state';

	/**
 	 * The key of the AuthId field in the state.
 	 */
	const AUTHID = 'sspmod_janus_Auth_Source_MailToken.AuthId';

	private static $db;
	private static $table;

	public function __construct($info, $config) {
		assert('is_array($info)');
		assert('is_array($config)');
		assert('array_key_exists("dsn", $config)');
		assert('array_key_exists("username", $config)');
		assert('array_key_exists("password", $config)');
		assert('array_key_exists("table", $config)');

		/* Call the parent constructor first, as required by the interface. */
		parent::__construct($info, $config);

		self::$db = new PDO($config['dsn'], $config['username'], $config['password']);
		self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		self::$table = $config['table'];
		
	}

	public function authenticate(&$state) {

		/* We are going to need the authId in order to retrieve this 
		 * authentication source later. */
		$state[self::AUTHID] = $this->authId;

		$id = SimpleSAML_Auth_State::saveState($state, self::STAGEID);

		$url = SimpleSAML_Module::getModuleURL('janus/mailtoken.php');
		$params = array('AuthState' => $id);
		SimpleSAML_Utilities::redirect($url, $params);

	}

	public static function handleLogin($authStateId, $mail, $token) {
		assert('is_string($authStateId)');
		assert('is_string($mail) || is_null($mail)');
		assert('is_string($token) || is_null($token)');

		/* Retrieve the authentication state. */
		$state = SimpleSAML_Auth_State::loadState($authStateId, self::STAGEID);

		/* Find authentication source. */
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
			$state['Attributes'] = array('mail' => array($mailbytoken));
   			SimpleSAML_Auth_Source::completeAuth($state);	   
		} else if($mailbytoken) {
			if($error =	self::newToken($mailbytoken, $returnURL)) {
				return $error;
			}
			return "send_mail_new_token_by_old";
		} else if($mail) {
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

	private static function newToken($mail, $returnURL) {
		assert('is_string($mail)');

 		if (isset($_COOKIE['language'])) {
			$language = $_COOKIE['language'];
		} else {
			$language = 'en';
		}

		$token = sha1(uniqid(rand().$mail, true));
	
		if (self::create_token($mail, $token)) {
	
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
		}
	}

	private static function create_token($mail, $token) {

		$st = self::$db->prepare("INSERT INTO ". self::$table ." (mail, token, notvalidafter) VALUES (?, ?, ?);");

		//TODO: Lav time out konfigurerbart
		$notvalidafter = date('c', time()+3600*24);

		if($st->execute(array($mail, $token, $notvalidafter))) {
			return TRUE;
		}

		return FALSE;
	}

	private static function is_token_valid($token) {
		$sth = self::$db->prepare('UPDATE '. self::$table .' SET usedat = ? WHERE token = ? AND notvalidafter > ? AND usedat is null;');
		$now = date('c');
		$sth->execute(
					  array($now, $token, $now)
					 );

		return $sth->rowCount() == 1;
	}

	private static function get_email_by_token($token) {
		$sth = self::$db->prepare("SELECT mail FROM ". self::$table ." WHERE token = ?;");
		$sth->execute(array($token));
		$row = $sth->fetch(PDO::FETCH_ASSOC);
		return $row['mail'];
	}

	private static function check_email_address($email) {
		if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
			return false;
		}
		//  Split it into sections to make life easier
		$email_array = explode("@", $email);
		$local_array = explode(".", $email_array[0]);
		for ($i = 0; $i < sizeof($local_array); $i++) {
			if (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i])) {
				return false;
			}
		}  
		if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) { // Check if domain is IP. If not, it should be valid domain name
			$domain_array = explode(".", $email_array[1]);
			if (sizeof($domain_array) < 2) {
				return false; // Not enough parts to domain
			}
			for ($i = 0; $i < sizeof($domain_array); $i++) {
				if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])) {
					return false;
				}
			}
		}

		if (!dns_get_record($email_array[1])) return false;
		return true;
	}
}
?>
