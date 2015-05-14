<?php 
include($_SERVER['DOCUMENT_ROOT']."/cerber/access_user_class.php");
$access_denied = new Access_user;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Доступ запрещен!</title>
<meta name="description" content="">
<meta name="keywords" content="">
</head>
<body>
<h2>Доступ запрещен!</h2>
<p>У вас нет прав для просмотра этой страницы!</p>
<p>&nbsp;</p>
<p><a href="<?php echo $access_denied->main_page; ?>">Главная</a></p>
</body>
</html>