<?php
	//database connection
	$dbhost="localhost";
	$dbuser="root";
	$dbpassword="*****";
	$dbname="info";
	$connection=mysqli_connect($dbhost,$dbuser,$dbpassword,$dbname);
	if(mysqli_connect_errno())
	{
		die(mysqli_connect_error());
	}
?>