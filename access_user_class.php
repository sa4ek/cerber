<?php
/************************************************************************
Access_user Class ver. 1.99
A complete PHP suite to protect pages and maintain members

Copyright (c) 2004 - 2008, Olaf Lederer
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

    * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
    * Neither the name of the finalwebsites.com nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

_________________________________________________________________________
available at http://www.finalwebsites.com/snippets.php?id=10
Comments & suggestions: http://www.finalwebsites.com/forums/forum/php-classes-support-forum

*************************************************************************/

header("Cache-control: private"); // //IE 6 Fix 
// error_reporting (E_ALL); // I use this only for testing
require_once($_SERVER['DOCUMENT_ROOT']."/cerber/db_config.php"); // this path works for me...

// new since version 1.92: storage of sessions in MySQL
session_start();


class Access_user {
	
	var $table_name = USER_TABLE; 
	
	var $user;
	var $user_pw;
	var $user_full_name;
	var $user_info;
	var $user_email;
	var $save_login = "yes";
	var $cookie_name = COOKIE_NAME;
	var $cookie_path = COOKIE_PATH; 
	var $is_cookie;

    var $provider;
    var $socialId;
    var $socialPage;
    var $sex;
    var $birthday;
    var $avatar;

	var $count_visit;
	
	var $id;
	var $the_msg;
	var $auto_activation; // use this variable in your login script
	var $send_copy = false; // send a mail copy (after activation) to the administrator
	
	var $webmaster_mail = WEBMASTER_MAIL;
	var $webmaster_name = WEBMASTER_NAME;
	var $admin_mail = ADMIN_MAIL;
	var $admin_name = ADMIN_NAME;
	
	var $login_page = LOGIN_PAGE;
	var $main_page = START_PAGE;
	var $password_page = ACTIVE_PASS_PAGE;
	var $deny_access_page = DENY_ACCESS_PAGE;
	var $admin_page = ADMIN_PAGE;
	
	function Access_user($redirect = true) {
		$this->connect_db();
		if (empty($_SESSION['logged_in'])) {
			$this->login_reader();
			if ($this->is_cookie) {
				$this->set_user($redirect);
			}
		} 		
		if (isset($_SESSION['user'])) {
			$this->user = $_SESSION['user'];
			$this->user_pw = $_SESSION['pw'];
		}
	}
	// removed check for encoded var $this->user_pw
	// replaced in default case var $password with $this->user_pw
	// added MD5 to sql statement for "new_pass"
	function check_user($pass = "") {
		switch ($pass) {
			case "new": 
			$sql = sprintf("SELECT COUNT(*) AS test FROM %s WHERE email = %s OR login = %s", $this->table_name, $this->ins_string($this->user_email), $this->ins_string($this->user));
			break;
			case "lost":
			$sql = sprintf("SELECT COUNT(*) AS test FROM %s WHERE email = %s AND active = 'y'", $this->table_name, $this->ins_string($this->user_email));
			break;
			// new login name based check before new password activation
			case "new_pass":
			$sql = sprintf("SELECT COUNT(*) AS test FROM %s WHERE MD5(CONCAT(login, %s)) = %s", $this->table_name, $this->ins_string(SECRET_STRING), $this->ins_string($this->check_user));
			break;
			case "active":
			$sql = sprintf("SELECT COUNT(*) AS test FROM %s WHERE id = %d AND active = 'n'", $this->table_name, $this->id);
			break;
			case "validate":
			$sql = sprintf("SELECT COUNT(*) AS test FROM %s WHERE id = %d AND tmp_mail <> ''", $this->table_name, $this->id);
			break;
			default:
			$sql = sprintf("SELECT COUNT(*) AS test FROM %s WHERE (BINARY login = %s AND pw = %s) OR (provider = %s AND social_id = %s) AND active = 'y'", $this->table_name, $this->ins_string($this->user), $this->ins_string($this->user_pw), $this->ins_string($this->provider), $this->ins_string($this->socialId));
		}
		$result = mysql_query($sql) or die(mysql_error());
		if (mysql_result($result, 0, "test") == 1) {
			return true;
		} else {
			return false;
		}
	}
	// New methods to handle the access level	
	function get_access_level() {
		$sql = sprintf("SELECT access_level FROM %s WHERE login = %s AND active = 'y'", $this->table_name, $this->ins_string($this->user));
		if (!$result = mysql_query($sql)) {
		   $this->the_msg = $this->messages(14);
		} else {
			return mysql_result($result, 0, "access_level");
		}
	}
	function set_user($goto_page) {
		$_SESSION['user'] = $this->user;
		$_SESSION['pw'] = $this->user_pw;
		$_SESSION['logged_in'] =  time(); // to offer a time limited access (later)
		if (!empty($_SESSION['referer'])) {
			$next_page = $_SESSION['referer'];
			unset($_SESSION['referer']);
		} else {
			$next_page = $this->main_page;
		}
		if ($goto_page) {
			header("Location: ".$next_page);
			exit;
		}
	}
	function connect_db() {
		$conn_str = @mysql_connect(DB_SERVER, DB_USER, DB_PASSWORD);
		mysql_select_db(DB_NAME); // if there are problems with the tablenames inside the config file use this row
	}
	// added md5 to var $password
	// changed argument for req_visit to $this->user_pw
	function login_user($user, $password, $socialId) {
		if ($user != "" && $password != "" ) {
			$this->user = $user;
			$this->user_pw = md5($password);
			if ($this->check_user()) {
				$this->login_saver();
				if ($this->count_visit) {
					$this->reg_visit($user, $this->user_pw);
				}
				$this->set_user(true);
			} else {
				$this->the_msg = $this->messages(10);
			}
		}
        else if($socialId !=""){
            if ($this->check_user()) {
                $this->login_saver();
                if ($this->count_visit) {
                    $this->reg_visit($user, $this->user_pw);
                }
                $this->set_user(START_PAGE);
            }
        }
        else {
			$this->the_msg = $this->messages(11);
		}
	}
	function login_saver() {
		if ($this->save_login == "no") {
			if (isset($_COOKIE[$this->cookie_name])) {
				$expire = time()-3600;
			} else {
				return;
			}
		} else {
			$expire = time()+2592000;
		}	
		$cookie_str = $this->user.chr(31).base64_encode($this->user_pw);
		setcookie($this->cookie_name, $cookie_str, $expire, $this->cookie_path);
	}
	function login_reader() {
		if (isset($_COOKIE[$this->cookie_name])) {
			$cookie_parts = explode(chr(31), $_COOKIE[$this->cookie_name]);
			$this->user = $cookie_parts[0];
			$this->user_pw = base64_decode($cookie_parts[1]);
			if ($this->check_user()) {
				$this->is_cookie = true;
			} else {
				unset($this->user);
				unset($this->user_pw);
			}
		}			 
	}
	// removed the md5 from var $pass
	function reg_visit($login, $pass) {
		$visit_sql = sprintf("UPDATE %s SET extra_info = '%s' WHERE login = %s AND pw = %s", $this->table_name, date("Y-m-d H:i:s"), $this->ins_string($login), $this->ins_string($pass));
		mysql_query($visit_sql);
	}
	function log_out($page_protect) {
        if($page_protect)
        {
            unset($_SESSION['user']);
            setcookie("vk_app_4666347", '', 0, "/", '.'.$_SERVER['HTTP_HOST']);
            unset($_SESSION['logged_in']);
        }
        else {
            unset($_SESSION['user']);
            unset($_SESSION['pw']);
            unset($_SESSION['logged_in']);
            session_destroy(); // new in version 1.92
            header("Location: " . LOGOUT_PAGE);
            exit;
        }
	}
	function access_page($refer = "", $qs = "", $level = DEFAULT_ACCESS_LEVEL) {
		$refer_qs = $refer;
		$refer_qs .= ($qs != "") ? "?".$qs : "";
		if (!$this->check_user()) {
			$_SESSION['referer'] = $refer_qs;
			header("Location: ".$this->login_page);
			exit;
		}
		if ($this->get_access_level() < $level) {
			header("Location: ".$this->deny_access_page);
			exit;
		}
	}
	function get_user_info() {
		$sql_info = sprintf("SELECT name, extra_info, email, id, social_id, social_page, sex, birthday,avatar FROM %s WHERE login = %s AND pw = %s", $this->table_name, $this->ins_string($this->user), $this->ins_string($this->user_pw));
		$res_info = mysql_query($sql_info);
		$this->id = mysql_result($res_info, 0, "id");
		$this->user_full_name = mysql_result($res_info, 0, "name");
		$this->user_info = mysql_result($res_info, 0, "extra_info");
		$this->user_email = mysql_result($res_info, 0, "email");

        $this->socialId = mysql_result($res_info, 0, "social_id");
        $this->socialPage = mysql_result($res_info, 0, "social_page");
        $this->sex = mysql_result($res_info, 0, "sex");
        $this->birthday = mysql_result($res_info, 0, "birthday");
        $this->avatar = mysql_result($res_info, 0, "avatar");
    }


	function update_user($new_password, $new_confirm, $new_name, $new_info, $new_mail) {
		if ($new_password != "") {
			if ($this->check_new_password($new_password, $new_confirm)) {
				$ins_password = md5($new_password);
				$update_pw = true;
			} else {
				return;
			}
		} else {
			$ins_password = $this->user_pw;
			$update_pw = false;
		}
		if (trim($new_mail) <> $this->user_email) {
			if  ($this->check_email($new_mail)) {
				$this->user_email = $new_mail;
				if (!$this->check_user("lost")) {
					$update_email = true;
				} else {
					$this->the_msg = $this->messages(31);
					return;
				}
			} else {
				$this->the_msg = $this->messages(16);
				return;
			}
		} else {
			$update_email = false;
			$new_mail = "";
		}
		$upd_sql = sprintf("UPDATE %s SET pw = %s, name = %s, extra_info = %s, tmp_mail = %s WHERE id = %d",
			$this->table_name,
			$this->ins_string($ins_password),
			$this->ins_string($new_name),
			$this->ins_string($new_info),
			$this->ins_string($new_mail),
			$this->id);
		$upd_res = mysql_query($upd_sql);
		if ($upd_res) {
			if ($update_pw) {
				$_SESSION['pw'] = $this->user_pw = $ins_password;
				if (isset($_COOKIE[$this->cookie_name])) {
					$this->save_login = "yes";
					$this->login_saver();
				}
			}
			$this->the_msg = $this->messages(30);
			if ($update_email) {
				if ($this->send_mail($new_mail, 33)) {
					$this->the_msg = $this->messages(27);
				} else {
					mysql_query(sprintf("UPDATE %s SET tmp_mail = ''", $this->table_name));
					$this->the_msg = $this->messages(14);
				} 
			}
		} else {
			$this->the_msg = $this->messages(15);
		}
	}
	function check_new_password($pass, $pw_conform) {
		if ($pass == $pw_conform) {
			if (strlen($pass) >= PW_LENGTH) {
				return true;
			} else {
				$this->the_msg = $this->messages(32);
				return false;
			}
		} else {
			$this->the_msg = $this->messages(38);
			return false;
		}	
	}
	function check_email($mail_address) {
		if (preg_match("/^[0-9a-z]+(([\.\-_])[0-9a-z]+)*@[0-9a-z]+(([\.\-])[0-9a-z-]+)*\.[a-z]{2,4}$/i", $mail_address)) {
			return true;
		} else {
			return false;
		}
	}
	function ins_string($value) {
		if (preg_match("/^(.*)(##)(int|date|eu_date)$/", $value, $parts)) {
			$value = $parts[1];
			$type = $parts[3];
		} else {
			$type = "";
		}
		$value = (!get_magic_quotes_gpc()) ? addslashes($value) : $value;
		switch ($type) {
			case "int":
			$value = ($value != "") ? intval($value) : NULL;
			break;
			case "eu_date":
			$date_parts = preg_split ("/[\-\/\.]/", $value); 
			$time = mktime(0, 0, 0, $date_parts[1], $date_parts[0], $date_parts[2]);
			$value = strftime("'%Y-%m-%d'", $time);
			break;
			case "date":
			$value = "'".preg_replace("/[\-\/\.]/", "-", $value)."'";
			break;
			default:
			$value = ($value != "") ? "'" . $value . "'" : "''";
		}
		return $value;
	}

    function register_social_network($values) {
        echo "register";
       /* $query = sprintf("INSERT INTO %s (provider, social_id, name, email, social_page, sex, birthday, avatar,access_level, active) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, 'y')",
        $this->table_name,
        $this->ins_string($auther->getProvider()),
        $this->ins_string(md5( $auther->getSocialId())),
        $this->ins_string($auther->getName()),
        $this->ins_string($auther->getEmail()),
        $this->ins_string($auther->getSocialPage()),
        $this->ins_string($auther->getSex()),
        $this->ins_string(date('Y-m-d', strtotime($auther->getBirthday()))),
        $this->ins_string($auther->getAvatar()),
        $this->ins_string(DEFAULT_ACCESS_LEVEL));
        $ins_res = mysql_query($query) or die(mysql_error());
        */
        $query = "INSERT INTO users (id, provider, social_id, name, email, social_page, sex, birthday, avatar, active) VALUES (NULL,'";
        $query .= implode("', '", $values) . "', 'y')";
        mysql_query($query);


    }

    function update_social_network($user, $idToUpdate) {
        $birthday = date('Y-m-d', strtotime($user->birthday));
        echo "update";

        mysql_query(
            "UPDATE '.$this->table_name.' SET " .
            "social_id = '{$user->socialId}', name = '{$user->user_full_name}', email = '{$user->user_email}', " .
            "social_page = '{$user->socialPage}', sex = '{$user->sex}', " .
            "birthday = '{$birthday}', avatar = '$user->avatar' " .
            "WHERE id='{$idToUpdate}'"
        );
    }

	function register_user($first_login, $first_password, $confirm_password, $first_name, $first_info, $first_email) {
		if ($this->check_new_password($first_password, $confirm_password)) {
			if (strlen($first_login) >= LOGIN_LENGTH) {
				if ($this->check_email($first_email)) {
					$this->user_email = $first_email;
					$this->user = $first_login;
					if ($this->check_user("new")) {
						$this->the_msg = $this->messages(12);
						return;
					} else {
						$sql = sprintf("INSERT INTO %s (id, login, pw, name, extra_info, email, access_level, active) VALUES (NULL, %s, %s, %s, %s, %s, %d, 'n')",
							$this->table_name,
							$this->ins_string($first_login),
							$this->ins_string(md5($first_password)),
							$this->ins_string($first_name),
							$this->ins_string($first_info),
							$this->ins_string($this->user_email),
							DEFAULT_ACCESS_LEVEL);
						$ins_res = mysql_query($sql) or die(mysql_error());
						if ($ins_res) {
							$this->id = mysql_insert_id();
							$this->user_pw = md5($first_password);
							if ($this->send_mail($this->user_email, 29, 28)) {
								$this->the_msg = $this->messages(13);
							} else {
								mysql_query(sprintf("DELETE FROM %s WHERE id = %d", $this->table_name, $this->id));
								$this->the_msg = $this->messages(14);
							}
						} else {
							$this->the_msg = $this->messages(15);
						}
					}
				} else {
					$this->the_msg = $this->messages(16);
				}
			} else {
				$this->the_msg = $this->messages(17);
			}
		}
	}
	function validate_email($validation_key, $key_id) {
		if ($validation_key != "" && strlen($validation_key) == 32 && $key_id > 0) {
			$this->id = $key_id;
			if ($this->check_user("validate")) {
				$upd_sql = sprintf("UPDATE %s SET email = tmp_mail, tmp_mail = '' WHERE id = %d AND MD5(pw) = %s", $this->table_name, $key_id, $this->ins_string($validation_key));
				if (mysql_query($upd_sql)) {
					$this->the_msg = $this->messages(18);
				} else {
					$this->the_msg = $this->messages(19);
				}
			} else {
				$this->the_msg = $this->messages(34);
			}
		} else {
			$this->the_msg = $this->messages(21);
		}
	}
	// upd. version 1.97 only activate status active = 'n', update the database table:
	// ALTER TABLE `users` CHANGE `active` `active` ENUM( 'y', 'n', 'b' ) DEFAULT 'n' NOT NULL 
	function activate_account($activate_key, $key_id) {
		if ($activate_key != "" && strlen($activate_key) == 32 && $key_id > 0) {
			$this->id = $key_id;
			if ($this->check_user("active")) {
				if ($this->auto_activation) {
					$upd_sql = sprintf("UPDATE %s SET active = 'y' WHERE id = %d AND MD5(pw) = %s AND active = 'n'", $this->table_name, $key_id, $this->ins_string($activate_key));
					if (mysql_query($upd_sql)) {
						if ($this->send_confirmation($key_id)) {
							$this->the_msg = $this->messages(18);
						} else {
							$this->the_msg = $this->messages(14);
						}
					} else {
						$this->the_msg = $this->messages(19);
					}
				} else {
					if ($this->send_mail($this->admin_mail, 40, 39)) {
						$this->the_msg = $this->messages(36);
					} else {
						$this->the_msg = $this->messages(14);
					}
				}
			} else {
				$this->the_msg = $this->messages(20);
			}
		} else {
			$this->the_msg = $this->messages(21);
		}
	}
	function forgot_password($forgot_email) { 
		if ($this->check_email($forgot_email)) {
			$this->user_email = $forgot_email;
			if (!$this->check_user("lost")) {
				$this->the_msg = $this->messages(22);
			} else {
				// changed from pw to login for verification string
				$forgot_sql = sprintf("SELECT login FROM %s WHERE email = %s", $this->table_name, $this->ins_string($this->user_email));
				if ($forgot_result = mysql_query($forgot_sql)) {
					$this->user = mysql_result($forgot_result, 0, "login");
					if ($this->send_mail($this->user_email, 35, 26)) {
						$this->the_msg = $this->messages(23);
					} else {
						$this->the_msg = $this->messages(14);
					}
				} else {
					$this->the_msg = $this->messages(15);
				}
			}
		} else {
			$this->the_msg = $this->messages(16);
		}
	}
	function check_activation_password($controle_str) {
		if ($controle_str != "" && strlen($controle_str) == 32) {
			$this->check_user = $controle_str;
			if ($this->check_user("new_pass")) {
				// this is a fix for version 1.76
				// we need this login name that teh user will remember the name too
				$sql_get_user = sprintf("SELECT login FROM %s WHERE MD5(CONCAT(login, %s)) = %s", $this->table_name, $this->ins_string(SECRET_STRING), $this->ins_string($this->check_user));
				$get_user = mysql_query($sql_get_user);
				$this->user = mysql_result($get_user, 0, "login"); // end fix
				return true;
			} else {
				$this->the_msg = $this->messages(21);
				return false;
			}
		} else {
			$this->the_msg = $this->messages(21);
			return false;
		}
	}
	function activate_new_password($new_pass, $new_confirm, $verif_str) {
		if ($this->check_new_password($new_pass, $new_confirm)) {
			// new password is set based on user name now
			$sql_new_pass = sprintf("UPDATE %s SET pw = '%s' WHERE MD5(CONCAT(login, %s)) = %s", $this->table_name, md5($new_pass), $this->ins_string(SECRET_STRING), $this->ins_string($verif_str));
			if (mysql_query($sql_new_pass)) {
				$this->the_msg = $this->messages(30);
				return true;
			} else {
				$this->the_msg = $this->messages(14);
				return false;
			}
		} else {
			return false;
		}
	}
	function send_confirmation($id) {
		$sql = sprintf("SELECT name, email FROM %s WHERE id = %d", $this->table_name, $id);
		$res = mysql_query($sql);
		$user_email = mysql_result($res, 0, "email");
		$this->user_full_name = mysql_result($res, 0, "name");
		if ($this->user_full_name == "") $this->user_full_name = "Пользователь"; // change "User" to whatever you want, it's just a default name
		if ($this->send_mail($user_email, 37, 24, $this->send_copy)) {
			return true;
		} else {
			return false;
		}
	}
	// new in version 1.99 support for phpmailer as alternative mail program
	function send_mail($mail_address, $msg = 29, $subj = 28, $send_admin = false) {
		$subject = $this->messages($subj);
		$body = $this->messages($msg);
		if (USE_PHP_MAILER) {
			$mail = new PHPMailer();
			if (PHP_MAILER_SMTP) {
				$mail->IsSMTP();
				$mail->Host = SMTP_SERVER;
                $mail->SMTPSecure= "ssl";
                $mail->Port = "465";
				$mail->SMTPAuth = true;  
				$mail->Username = SMTP_LOGIN;
				$mail->Password = SMTP_PASSWD; 
			} else {
				$mail->IsSendmail(); 
			}
			$mail->From = $this->webmaster_mail;
			$mail->FromName = $this->webmaster_name;
			$mail->AddAddress($mail_address);
			if ($send_admin) $mail->AddBCC(ADMIN_MAIL);
			$mail->Subject = $subject;
			$mail->Body = $body;
			if($mail->Send()) {
				return true;
			} else {
				return false;
			}
		} else {
			$header = "From: \"".$this->webmaster_name."\" <".$this->webmaster_mail.">\n";
			if ($send_admin) $header .= "Bcc: ".ADMIN_MAIL."\n";
			$header .= "MIME-Version: 1.0\n";
			$header .= "Content-Type: text/plain; charset=\"utf-8\"\n";
			$header .= "Content-Transfer-Encoding: 7bit\n";
			if (mail($mail_address, $subject, $body, $header)) {
				return true;
			} else {
				return false;
			} 
		}
	}
	
	// message no. 35 is changed because the verification string based in the user name now
	function messages($num) {
		$host = "http://".$_SERVER['HTTP_HOST'];

        $msg[10] = "Логин или пароль не верны.";
        $msg[11] = "Логин или пароль пусты!";
        $msg[12] = "Пользователь с таким логином или email уже существует.";
        $msg[13] = "Проверьте почту и следуйте инструкциям.";
        $msg[14] = "Неизвестная ошибка, попробуйте снова.";
        $msg[15] = "Ошибка, попробуйте позже.";
        $msg[16] = "Email некорректен.";
        $msg[17] = "Слишком короткий логин";
        $msg[18] = "Запрос выполняется. Войдите в систему.";
        $msg[19] = "Невозможно активировать аккаунт.";
        $msg[20] = "Нет аккаунта для активации.";
        $msg[21] = "Ключ активации содержит ошибку!";
        $msg[22] = "Отсутствуют активированные аккаунты с таким email.";
        $msg[23] = "Проверьте почту, для получения нового пароля.";
        $msg[24] = "Ваш аккаунт активирован. ";
        $msg[25] = "Неудается активировать ваш пароль.";
        $msg[26] = "Ваш забытый пароль.";
        $msg[27] = "Проверьте почту и подтвердите изменения.";
        $msg[28] = "Ваш запрос обрабатывается.";
        $msg[29] = "Здравствуйте,\r\n\r\nдля активации пройдите по ссылке:\r\n".$host.$this->login_page."?ident=".$this->id."&activate=".md5($this->user_pw)."\r\n\r\nХорошего времени суток\r\n".$this->admin_name;
        $msg[30] = "Ваш аккаунт модифицирован.";
        $msg[31] = "Этот email уже используется.";
        $msg[32] = "Слишком короткий пароль.";
        $msg[33] = "Здравствуйте,\r\n\r\nновый email должен быть подтвержден, проследуйте по ссылке:\r\n".$host.$this->login_page."?id=".$this->id."&validate=".md5($this->user_pw)."\r\n\r\nХорошего времени суток\r\n".$this->admin_name;
        $msg[34] = "Отсутствует email для подтверждения.";
        $msg[35] = "Здравствуйте,\r\n\r\nвведите новый пароль и перейдите по ссылке:\r\n".$host.$this->password_page."?activate=".md5($this->user.SECRET_STRING)."\r\n\r\nХорошего времени суток\r\n".$this->admin_name;
        $msg[36] = "Выш запрос обрабатывается администрацией. \r\nВы получите письмо после обработки.";
        $msg[37] = "Здравствуйте ".$this->user_full_name.",\r\n\r\nАккаунт активирован.\r\n\r\nВойти в систему:\r\n".$host.$this->login_page."\r\n\r\nХорошего времени суток\r\n".$this->admin_name;
        $msg[38] = "Пароли отличаются, попробуйте снова.";
        $msg[39] = "Новый пользователь.";
        $msg[40] = "Регистрация нового пользователя ".date("Y-m-d").":\r\n\r\nСтраница администрирования:\r\n\r\n".$host.$this->admin_page."?login_id=".$this->id;
        $msg[41] = "Подтвердите ваш email."; // subject in e-mail
        $msg[42] = "Ваш email изменен.";

		return $msg[$num];
	}
}
?>