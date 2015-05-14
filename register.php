<?php 
include($_SERVER['DOCUMENT_ROOT']."/cerber/access_user_class.php");

$new_member = new Access_user;
// $new_member->language = "de"; // use this selector to get messages in other languages

if (isset($_POST['Submit'])) { // the confirm variable is new since ver. 1.84
	// if you don't like the confirm feature use a copy of the password variable
	$new_member->register_user($_POST['login'], $_POST['password'], $_POST['confirm'], $_POST['name'], "", $_POST['email']); // the register method
} 
$error = $new_member->the_msg; // error message

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Регистрация</title>
<style type="text/css">
<!--
label {
	display: block;
	float: left;
	width: 200px;
}
-->
</style>
</head>

<body>
<h2>Регистрация:</h2>
<p>Пожалуйста зополните информацию о себе. Поля помеченные звездочкой, обязательны для заполнения.</p>
<form name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
  <label for="login">Логин:</label>
  <input type="text" name="login" size="12" value="<?php echo (isset($_POST['login'])) ? $_POST['login'] : ""; ?>">
  * (минимум 6 символов) <br>
  <label for="password">Пароль:</label>
  <input type="password" name="password" size="12" value="<?php echo (isset($_POST['password'])) ? $_POST['password'] : ""; ?>">
  * (минимум 6 символов) <br>
  <label for="confirm">Подтвердите пароль:</label>
  <input type="password" name="confirm" size="12" value="<?php echo (isset($_POST['confirm'])) ? $_POST['confirm'] : ""; ?>">
  * <br>
  <label for="name">Имя и Фамилия:</label>
  <input type="text" name="name" size="30" value="<?php echo (isset($_POST['name'])) ? $_POST['name'] : ""; ?>">
  <br>
  <label for="email">E-mail:</label>
  <input type="text" name="email" size="30" value="<?php echo (isset($_POST['email'])) ? $_POST['email'] : ""; ?>">
  *<br>
  <input type="submit" name="Submit" value="Submit">
</form>
<p><b><?php echo (isset($error)) ? $error : "&nbsp;"; ?></b></p>
<p>&nbsp;</p>
<!-- Notice! you have to change this links here, if the files are not in the same folder -->
<p><a href="<?php echo $new_member->login_page; ?>">Войти</a></p>
</body>
</html>
