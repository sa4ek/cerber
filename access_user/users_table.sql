CREATE DATABASE IF NOT EXISTS `cerber_auth` CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL auto_increment PRIMARY KEY, -- идентификатор пользователя
  `login` varchar(25) default '', -- логин пользователя, зарегистрированного через имейл (Возникает конфликт, тк поле не может быть уникальным возможно стоит убрать и оставить лишь имейл, или же хранить здесь social_id (хотя он все также не уникален))
  `pw` varchar(32) default '', -- пароль пользователя, зарегистрированного через имейл
  `name` text NOT NULL default '', -- полное имя пользователя
  `extra_info` varchar(100) default '', -- дополнительное поле
  `email` text  UNIQUE default '', -- имейл пользователя
  `tmp_mail` varchar(50) default '', -- имейл до подтверждения
  `access_level` tinyint(4)  default '0', -- уровень доступа (от 1 до 10)
  `active` enum('y','n','b') default 'n', -- активирован ли аккаунт (да, нет, заблокирован), для соцсетей по дефолту да
  `provider` ENUM('vk', 'odnoklassniki', 'mailru', 'yandex', 'google', 'facebook', 'native') DEFAULT 'native', -- провайдер соцсети, отсутствует при нашей регистрации
  `social_id` VARCHAR(255) default '', -- идентификатор в соцсети, отсутствует при нашей регистрации
  `social_page` VARCHAR(255) default '', -- страница в соцсети (не обязателен для пользователя с нашей регистрацией)
  `sex` ENUM('male', 'female') default 'male', -- пол (не обязателен для пользователя с нашей регистрацией)
  `birthday` DATE , -- день рождения (не обязателен для пользователя с нашей регистрацией)
  `avatar` VARCHAR(255) default '0' -- аватар (не обязателен для пользователя с нашей регистрацией)
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;
