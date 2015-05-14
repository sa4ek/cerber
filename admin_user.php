<?php 
include($_SERVER['DOCUMENT_ROOT']."/cerber/access_user_class.php");

class Admin_user extends Access_user {
	
	var $user_found = false;
	var $user_id;
	var $user_name;
	var $old_user_email;
	var $user_access_level;
	var $activation;

	function get_userdata($for_user, $type = "login") {
		if ($type == "login") {
			$sql = sprintf("SELECT id, login, email, access_level, active FROM %s WHERE login = '%s'", $this->table_name, trim($for_user));
		} else {
			$sql = sprintf("SELECT id, login, email, access_level, active FROM %s WHERE id = %d", $this->table_name, intval($for_user));
		}
		$result = mysql_query($sql);
		if (mysql_num_rows($result) == 1) {
			$obj = mysql_fetch_object($result);
			$this->user_id = $obj->id;
			$this->user_name = $obj->login;
			$this->old_user_email = $obj->email;
			$this->user_access_level = $obj->access_level;
			$this->activation = $obj->active;
			if ($this->user_name != $_SESSION['user']) {
				$this->user_found = true;
			} else {
				$this->user_found = false;
				$this->the_msg = "Вы не можете поменять информацию о себе!";
			}
			mysql_free_result($result);
		} else {
			$this->the_msg = "Информация по этому пользователю не найдена.!";
		}	
	}
	function update_user_by_admin($new_level, $user_id, $def_pass, $new_email, $active, $confirmation = "no") {
		$this->user_found = true;
		$this->user_access_level = $new_level;
		if ($def_pass != "" && strlen($def_pass) < 6) {
			$this->the_msg = "Пароль короче 6 символов.";
		} else {
			if ($this->check_email($new_email)) {
				$sql = "UPDATE %s SET access_level = %d, email = '%s', active = '%s'";
				$sql .= ($def_pass != "") ? sprintf(", pw = '%s'", md5($def_pass)) : "";
				$sql .= " WHERE id = %d";
				$sql_compl = sprintf($sql, $this->table_name, $new_level, $new_email, $active, $user_id);
				if (mysql_query($sql_compl)) {
					$this->the_msg = "Данные изменены для пользователя с id#<b>".$user_id."</b>";
					if ($confirmation == "yes") {
						if ($this->send_confirmation($user_id)) {
							$this->the_msg .= "<br>...подтверждающее письмо выслано пользователю.";
						} else {
							$this->the_msg .= "<br>...Ошибка, письмо пользователю не отправлено.";
						}
					}
				} else {
					$this->the_msg = "Ошибка базы данных!";
				}
			} else {
				$this->the_msg = "Допущена ошибка в email!";
			}
		}
	}
	function access_level_menu($curr_level, $element_name = "level") {
		$menu = "<select name=\"".$element_name."\">\n";
		for ($i = MIN_ACCESS_LEVEL; $i <= MAX_ACCESS_LEVEL; $i++) {
			$menu .= "  <option value=\"".$i."\"";
			$menu .= ($curr_level == $i) ? " selected>" : ">";
			$menu .= $i."</option>\n";
		}
		$menu .= "</select>\n";
		return $menu;
	}
	// modified in version 1.97
	function activation_switch($formelement = "activation") {
		$radio_group = "<label for=\"".$formelement."\">Active?</label>\n";
		$labels = array("y"=>"yes", "n"=>"no", "b"=>"blocked");
		foreach ($labels as $key => $val) {
			$radio_group .= " <input name=\"".$formelement."\" type=\"radio\" value=\"".$key."\" ";
			$radio_group .= ($this->activation == $key) ? "checked=\"checked\" />\n" : "/>\n";
			$radio_group .= $val;
		}
		return $radio_group;        
	}
}
$admin_update = new Admin_user;
$admin_update->access_page($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING'], DEFAULT_ADMIN_LEVEL); // check the level inside the config file

if (isset($_POST['Submit'])) {
	if ($_POST['Submit'] == "Update") {
		$conf_str = (isset($_POST['send_confirmation'])) ? $_POST['send_confirmation'] : ""; // the checkbox value to send a confirmation mail 
		$admin_update->update_user_by_admin($_POST['level'], $_POST['user_id'], $_POST['password'], $_POST['email'], $_POST['activation'], $conf_str);
		$admin_update->get_userdata($_POST['login_name']); // this is needed to get the modified data after update
	} elseif ($_POST['Submit'] == "Search") {
		$admin_update->get_userdata($_POST['login_name']);
	}
} elseif (isset($_GET['login_id']) && intval($_GET['login_id']) > 0) {
		$admin_update->get_userdata($_GET['login_id'], "is_id");
} 
$error = $admin_update->the_msg; // error message

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Администрирование</title>
<style type="text/css">
<!--
label {
	display: block;
	float: left;
	width: 140px;
}
-->
</style>
</head>

<body>
<h2>Администрирование</h2>
<form name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
  <?php if ($admin_update->user_found) { ?>
<p>Используйте эту страницу для редактирования информации о пользователях.</p>
  <label for="login">Логин пользователя :</label>
  <b><?php echo $admin_update->user_name; ?></b><br>
  <label for="level">Уровень доступа :</label>
  <?php echo $admin_update->access_level_menu($admin_update->user_access_level); ?>
  <br>
  <label for="password">Пароль :</label>
  <input type="password" name="password" size="4" value="<?php echo (isset($_POST['password'])) ? $_POST['password'] : ""; ?>">
  (min. 4 chars.) <br>
  <label for="email">E-mail:</label>
  <input type="text" name="email" size="25" value="<?php echo (isset($_POST['email'])) ? $_POST['email'] : $admin_update->old_user_email; ?>">
  <br>
  <?php echo $admin_update->activation_switch(); ?><br>
  <label for="send_confirmation">Отправить подтверждающее письмо?</label>
  <input name="send_confirmation" type="checkbox" value="yes"><br>
  <input type="hidden" name="user_id" value="<?php echo (isset($_POST['user_id'])) ? $_POST['user_id'] : $admin_update->user_id; ?>">
  <input type="hidden" name="login_name" value="<?php echo $admin_update->user_name; ?>">
  <input type="submit" name="Submit" value="Update">
  <p style="margin-top:50px;"><a href="<?php echo $_SERVER['PHP_SELF']; ?>">search next user...</a></p>
  <?php } else { ?>
  <p>Enter the login name from the user where the modifications has to be done:</p>
  <?php // the next element name (login_name) is used inside the class don't use a different name ?>
  <input type="text" name="login_name" value="<?php echo (isset($_POST['login_name'])) ? $_POST['login_name'] : ""; ?>">
  <input type="submit" name="Submit" value="Search">
  <?php } // end if / else show update search form ?>
</form>
<p><b><?php echo (isset($error)) ? $error : "&nbsp;"; ?></b></p>
<p>&nbsp;</p>
<!-- Notice! you have to change this links here, if the files are not in the same folder -->
<p><a href="<?php echo $admin_update->main_page; ?>">Главная</a></p>
</body>
</html>
