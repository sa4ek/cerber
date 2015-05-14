<?php 
include($_SERVER['DOCUMENT_ROOT']."/cerber/access_user_class.php");

$update_member = new Access_user;
// $new_member->language = "de"; // use this selector to get messages in other languages

$update_member->access_page(); // protect this page too.
$update_member->get_user_info(); // call this method to get all other information

if (isset($_POST['Submit'])) {
	$update_member->update_user($_POST['password'], $_POST['confirm'], $_POST['name'], "", $_POST['email']); // the update method
} 
$error = $update_member->the_msg; // error message

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Обновить информацию о пользователе</title>
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
<h2>Обновить информацию о пользователе:</h2>
<p>Используйте эту форму для редактирования аккаунта. Поля помеченные звездочкой, обязательны для заполнения.</p>
<form name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
  <label for="login">Логин:</label>
  <b><?php echo $update_member->user; ?></b><br>
  <label for="password">Пароль:</label>
  <input name="password" type="password" value="<?php echo (isset($_POST['password'])) ? $_POST['password'] : ""; ?>" size="6">
  * (минимум 6 символов) <br>
  <label for="confirm">Подтвердите пароль:</label>
  <input name="confirm" type="password" value="<?php echo (isset($_POST['confirm'])) ? $_POST['confirm'] : ""; ?>" size="6">
  * <br>
  <label for="name">Имя и Фамилия:</label>
  <input name="name" type="text" value="<?php echo (isset($_POST['name'])) ? $_POST['name'] : $update_member->user_full_name; ?>" size="30">
  <br>
  <label for="email">E-mail:</label>
  <input name="email" type="text" value="<?php echo (isset($_POST['email'])) ? $_POST['email'] : $update_member->user_email; ?>" size="30">
  *<br>


    <label for="socialId">ID в социальной сети:</label>
    <input name="socialId" type="text" value="<?php echo (isset($_POST['socialId'])) ? $_POST['socialId'] : $update_member->socialId; ?>" size="30">
    <br>
    <label for="socialPage">Страница в социальной сети:</label>
    <input name="socialPagee" type="text" value="<?php echo (isset($_POST['socialPage'])) ? $_POST['socialPage'] : $update_member->socialPage; ?>" size="30">
    <br>

    <label for="sex">Пол</label>
    <select name="sex">
    <option value="male">
    <option value="female">
    </select>

    <br>
    <label for="birthday">День рождения:</label>
    <input name="birthday" type="text" value="<?php echo (isset($_POST['birthday'])) ? $_POST['birthday'] : $update_member->birthday; ?>" size="30">
    <br>
    <img src="<?php echo (isset($_POST['avatar'])) ? $_POST['avatar'] : $update_member->avatar; ?>" />
    <br>

  <input type="submit" name="Submit" value="Update">
</form>
<p><b><?php echo (isset($error)) ? $error : "&nbsp;"; ?></b></p>
<p>&nbsp;</p>
<!-- Notice! you have to change this links here, if the files are not in the same folder -->
<p><a href="<?php echo $update_member->main_page; ?>">Главная</a></p>
</body>
</html>
