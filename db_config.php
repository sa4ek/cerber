<?php 
include_once($_SERVER['DOCUMENT_ROOT'].'/cerber/db.php');

// use this pathes and/or define the pathes for the "standard" pages
define("CLASS_PATH", dirname($_SERVER['PHP_SELF'])."/"); // the location where the class is executed
$sec_path = "/cerber/"; // a second location where the scripts should be
define("APPLICATION_PATH", $sec_path);



// these are the names for the standard table names
// !!! Important
// It's possible that your server doesn't allow the database name inside a query
// if this forms a problem don't use them here and unescape the mysql_select_db() function
// inside the connect_db() method.
define("USER_TABLE", DB_NAME.".users"); 

// variables (locations) standard pages (combine the pathes from the top or use your own)
define("LOGIN_PAGE", CLASS_PATH."index.php");
define("START_PAGE", "/cerber/info.php");
define("ACTIVE_PASS_PAGE", APPLICATION_PATH."activate_password.php");
define("DENY_ACCESS_PAGE", APPLICATION_PATH."deny_access.php");
define("ADMIN_PAGE", APPLICATION_PATH."admin_user.php");
define("LOGOUT_PAGE", APPLICATION_PATH."logout.php"); // if you use the setting "USE_MYSQL_SESSIONS" you need a logout page without class object to clear the old session data from the database

// your path must be related to the site root.
 
// change this constants to the right mail settings
define("WEBMASTER_MAIL", "mail@domain.com"); 
define("WEBMASTER_NAME", "The webmaster"); 
define("ADMIN_MAIL", "contact@domain.com"); 
define("ADMIN_NAME", "The site admin"); 

// change this vars if you need...
define("PW_LENGTH", 6);
define("LOGIN_LENGTH", 6);
define("SECRET_STRING", "tellme"); // since version 1.98

define("COOKIE_NAME", "user"); 
define("COOKIE_PATH", APPLICATION_PATH);
define("MIN_ACCESS_LEVEL", 1);
define("MAX_ACCESS_LEVEL", 10);
define("DEFAULT_ACCESS_LEVEL", 1);
define("DEFAULT_ADMIN_LEVEL", 10);

// change these settings to use phpmailer in place of the PHP mail() function
define("USE_PHP_MAILER", false); // true = use phpmailer
define("PHP_MAILER_SMTP", true); // true send by SMTP, false = sendmail
// if you enable these features you need to install the phpmailer class!
if (USE_PHP_MAILER) include_once($_SERVER['DOCUMENT_ROOT']."/cerber/PHPMailer/class.phpmailer.php");
if (PHP_MAILER_SMTP) {
	define("SMTP_SERVER", "smtp.gmail.com");
	define("SMTP_LOGIN", "sa4ek.92@gmail.com");
	define("SMTP_PASSWD", "15051992q");
}

//////////////////////////////////////////////////
// constants and field names user profile table //
/////////////////////////////////////////////// //
// stamdard fields are: address, postcode, city, country, phone, fax, homepage, textfield
// variables must match your user profile table design
// use this four user defined fields (type varchar(100) too
// change the value of a constant but change them in the mysql table too
// otherwise you wil get some ERROR'S !!!
define("TBL_USERFIELD_1", "user_1");
define("TBL_USERFIELD_2", "user_2");
define("TBL_USERFIELD_3", "user_3");
define("TBL_USERFIELD_4", "user_4");
define("TBL_USERFIELD_5", "user_5");
define("TBL_USERFIELD_6", "user_6");
?>