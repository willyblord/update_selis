<?php

try {
	$db = new PDO('mysql:host=localhost; dbname=update_seris', 'root', '');
} catch (PDOException $ex) {
	die('error : ' . $ex->errorMessage());
}