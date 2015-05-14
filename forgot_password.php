<?php 
include($_SERVER['DOCUMENT_ROOT']."/cerber/access_user_class.php");

$renew_password = new Access_user;

if (isset($_POST['Submit'])) {
	$renew_password->forgot_password($_POST['email']);
} 
$error = $renew_password->the_msg;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Восстановление пароля</title>
<style type="text/css">
<!--
label {
	display: block;
	float: left;
	width: 110px;
}
-->
</style>
</head>

<body>
<h2>Забыли логин или пароль?</h2>
<p>Введите ваш email.</p>
<form name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
  <label for="email">E-mail:</label>
  <input type="text" name="email" value="<?php echo (isset($_POST['email'])) ? $_POST['email'] : ""; ?>">
  <input type="submit" name="Submit" value="Submit">
</form>
<p><b><?php echo (isset($error)) ? $error : "&nbsp;"; ?></b></p>
<p>&nbsp;</p>
<!-- Notice! you have to change this links here, if the files are not in the same folder -->
<p><a href="<?php echo $renew_password->login_page; ?>">Вход</a></p>
</body>
</html>
