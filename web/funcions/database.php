<?php
	$server='localhost';
	$username='itiel';
	$password='asd123';
	$database='perruqueria';

	try {
		$conn = new PDO("mysql:host=$server;dbname=$database;charset=utf8", $username, $password);
	} catch (PDOException $e) {
		die('Conexion fallida: '.$e->getMessage());
	}

?>
