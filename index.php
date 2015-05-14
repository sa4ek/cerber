<?php
require_once '/lib/SocialAuther/autoload.php';
include($_SERVER['DOCUMENT_ROOT']."/cerber/access_user_class.php");
$conn_str = @mysql_connect(DB_SERVER, DB_USER, DB_PASSWORD);
mysql_select_db(DB_NAME);

$my_access1 = new Access_user(false);

$my_access = new Access_user(false);

$adapterConfigs = array(
    'vk' => array(
        'client_id'     => '4666347',
        'client_secret' => 'MXwjOCdeTegkAiKrtKTj',
        'redirect_uri'  => 'http://localhost/cerber/?provider=vk'
    )
);

$adapters = array();
foreach ($adapterConfigs as $adapter => $settings) {
    $class = 'SocialAuther\Adapter\\' . ucfirst($adapter);
    $adapters[$adapter] = new $class($settings);
}

if (isset($_GET['provider']) && array_key_exists($_GET['provider'], $adapters) && !isset($_SESSION['user'])) {
    $auther = new SocialAuther\SocialAuther($adapters[$_GET['provider']]);

    if ($auther->authenticate()) {
        $result = mysql_query(
            "SELECT *  FROM users WHERE provider = '{$auther->getProvider()}' AND social_id = '{$auther->getSocialId()}' LIMIT 1");
        $record = mysql_fetch_array($result);

        if (!$record) {
            $values = array(
                $auther->getProvider(),
                $auther->getSocialId(),
                $auther->getName(),
                $auther->getEmail(),
                $auther->getSocialPage(),
                $auther->getSex(),
                date('Y-m-d', strtotime($auther->getBirthday())),
                $auther->getAvatar()
            );

            $my_access->register_social_network($values);
        }

        else {
            $my_access->provider = $record['provider'];
            $my_access->socialId = $record['social_id'];
            $my_access->user_full_name = $record['name'];
            $my_access->user_email = $record['email'];
            $my_access->socialPage = $record['social_page'];
            $my_access->sex = $record['sex'];
            $my_access->birthday = date('m.d.Y', strtotime($record['birthday']));
            $my_access->avatar = $record['avatar'];
        }

        $user1 = new Access_user(false);
        $user1->provider          = $auther->getProvider();
        $user1->socialId          = $auther->getSocialId();
        $user1->user_full_name    = $auther->getName();
        $user1->user_email        = $auther->getEmail();
        $user1->socialPage        = $auther->getSocialPage();
        $user1->sex               = $auther->getSex();
        $user1->birthday          = $auther->getBirthday();
        $user1->avatar            = $auther->getAvatar();

        if (isset($my_access) && $my_access != $user1) {
            $idToUpdate = $record['id'];
            $my_access->update_social_network($user1, $idToUpdate);
        }
        $my_access1->save_login = "yes";
        $my_access1->count_visit = true;
        $my_access->login_user("", "", $my_access->socialId);
    }

}


if (isset($_POST['Submit'])) {
	$my_access1->save_login = (isset($_POST['remember'])) ? $_POST['remember'] : "no"; // use a cookie to remember the login
	$my_access1->count_visit = true; // if this is true then the last visitdate is saved in the database (field extra info)
	$my_access1->login_user($_POST['login'], $_POST['password'], ""); // call the login method
} 
$error = $my_access1->the_msg;

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Вход</title>
<style type="text/css">
<!--
label {
	display: block;
	float: left;
	width: 120px;
}
-->
</style>
</head>

<body>
<h2>Вход:</h2>
<p>Введите логин и пароль.</p>
<form name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <label for="login">Логин:</label>
    <input type="text" name="login" size="20" value="<?php echo (isset($_POST['login'])) ? $_POST['login'] : $my_access->user; ?>"><br>
    <label for="password">Пароль:</label>
    <input type="password" name="password" size="8" value="<?php if (isset($_POST['password'])) echo $_POST['password']; ?>"><br>
    <label for="remember">Запомнить?</label>
    <input type="checkbox" name="remember" value="yes"<?php echo ($my_access->is_cookie == true) ? " checked" : ""; ?>>
    <br>
    <input type="submit" name="Submit" value="Login">
</form>

<p> Или войдите с помощью логина в социальной сети: </p>

<?php
foreach ($adapters as $title => $adapter) {
    echo '<p><a href="' . $adapter->getAuthUrl() . '">Аутентификация через ' . ucfirst($title) . '</a></p>';
}
?>

<p><b><?php echo (isset($error)) ? $error : "&nbsp;"; ?></b></p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<!-- Notice! you have to change this links here, if the files are not in the same folder -->
<p>Еще не зарегистрированы? <a href="register.php">Скорее сюда.</a></p>
<p><a href="forgot_password.php">Забыли ваш пароль?</a></p>
</body>
</html>
