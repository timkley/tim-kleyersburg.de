<?
	//define the receiver of the email
	
	define('TO_NAME','Rbista');
	define('TO_EMAIL','rbista.team@gmail.com');

	define('TEMPLATE_PATH','template/default.php');
 

	define('SMTP_HOST','smtp.gmail.com');
	define('SMTP_USERNAME','rbista');
	define('SMTP_PASSWORD','');

	define('SUBJECT','Contact from your website');	
	
	// Messages
	define('MSG_INVALID_NAME','Please enter your name.');
	define('MSG_INVALID_EMAIL','Please enter valid e-mail.');
	define('MSG_INVALID_MESSAGE','Please enter your message.');
	define('MSG_SEND_ERROR','Sorry, we can\'t send this message.');

?>