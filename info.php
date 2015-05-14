<?php
require_once '/lib/SocialAuther/autoload.php';
include($_SERVER['DOCUMENT_ROOT']."/cerber/access_user_class.php");

$page_protect = new Access_user;
// $page_protect->login_page = "index.php"; // change this only if your login is on another page
$page_protect->access_page(); // only set this this method to protect your page
$page_protect->get_user_info();

$hello_name = ($page_protect->user_full_name != "") ? $page_protect->user_full_name : $page_protect->user;

if (isset($_GET['action']) && $_GET['action'] == "log_out") {
    if ($page_protect->socialId != 0) {
        /*$params = array(
            'act' => "logout",
            'client_id' => "4666347"
        );
        $class = 'SocialAuther\Adapter\Vk';
        $class->get('http://login.vk.com/', $params);*/
        $page_protect->log_out(true); // the method to log off
    }
    else
        $page_protect->log_out(false);
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Главная страница</title>
</head>

<body>
<h2><?php echo "Привет ".$hello_name." !"; ?></h2>
<p>Вы уже авторизованы.</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<!-- Notice! you have to change this links here, if the files are not in the same folder -->
<p><a href="./update_user.php">Изменить информацию обо мне</a></p>
<p><a href="/cerber/test_access_level.php">проверить уровень доступа </a>(используется 5 уровень) </p>
<p><a href="/cerber/admin_user.php">Страница администрирования</a> (используется <?php echo DEFAULT_ADMIN_LEVEL; ?> уровень) </p>
<p><a href="<?php echo $_SERVER['PHP_SELF']; ?>?action=log_out">Выход</a></p>
</body>

</html>

