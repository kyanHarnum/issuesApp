<?php
session_start();
require 'database.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

if($_SESSION['user_admin'] !== 'Yes'){
    header("Location: issues_list.php");
    exit();
}
?>