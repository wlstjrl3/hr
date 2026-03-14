<?php
include "public_html/sys/sql_safe_helper.php";

$res = mysqli_query($conn, "SELECT USER_PASS FROM BONDANG_HR.USER_TB LIMIT 1");
if(!$res) die("DB Error: " . mysqli_error($conn));
$row = mysqli_fetch_assoc($res);
$key = $row['USER_PASS'];

// Test URL parameters (assuming gender group by default)
$_REQUEST['key'] = $key;
$_REQUEST['STT_DATE'] = '2024-01-01';
$_REQUEST['END_DATE'] = '2024-03-31';
$_REQUEST['WORK_TYPE'] = 'ALL';
$_REQUEST['INTERVAL'] = 'month';
$_REQUEST['GROUP_BY'] = 'gender';

// Catch errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "public_html/sys/statWorkTypeGraph.php";
?>
