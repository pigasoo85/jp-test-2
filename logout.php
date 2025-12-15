<?php
session_start();
include_once './service/User.php';
User::logout($name, $password);
header('Location: login.php');
?>