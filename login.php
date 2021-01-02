<?php  
include_once('dbcon.php');
include_once('functions.php');
session_start();
$toastOutput=array();
//this makes sure logged in users cant access login screen if he's logged in
if($_SERVER['REQUEST_METHOD']!="POST"){
	if(isset($_SESSION['id'])){
		header("location:inventory.php");
	}	
}

//if submit button is pressed
if($_SERVER['REQUEST_METHOD']=="POST"){
	$email=mysqli_real_escape_string($connection, $_POST['email']);
	$password=mysqli_real_escape_string($connection, $_POST['password']);
	$admin_serial=getValue('staff', 'serial', 'email', 'admin@gmail.com');
	//we have set admin as locked, so we need to specifically check if lock is zero or its admin
	$query="SELECT serial,name,password, permission  from staff where email='{$email}' and (lock_staff=0 OR serial={$admin_serial})";
	$result=perform_query($connection, $query, "failed to get result");
	if($row=mysqli_fetch_assoc($result)){
		if(passwordCheck($password, $row['password'])){
			$_SESSION['id']=$row['serial'];
			$_SESSION['name']=$row['name'];
			$_SESSION['permission']=$row['permission'];
			header("location: inventory.php");
		}
	}
	else
		addToToast("Invalid E-Mail or Password",0);
}

?>

<!DOCTYPE html>
<html>
<head>
  <title>Login</title>
  <link rel="icon" href="favicon.ico" type="image/x-icon" />
  	<link rel = "stylesheet" 
	href = "iconfont/material-icons.css">
	<link rel="stylesheet" href="materialize/css/materialize.css">
	<link rel="stylesheet" type="text/css" href="style_for_login.css">
    	<!-- Compiled and minified JavaScript -->
    			<script type = "text/javascript"
    			src = "jquery-2.1.1.min.js"></script>   
    	    	<!-- Compiled and minified JavaScript -->
    	    	<script src="materialize/js/materialize.js"></script>     
	<style>
		.container{width: 500px;}
	</style>
</head>
<body>

	<div class="container">
		<div class="row">
			<span class="center"><h5 class="grey-text ">Login</h5></span>
			<form class="col s12" id="reg-form" action="login.php" method="POST">
				<div class="row">
					<div class="input-field col s12">
						<input id="email" name="email"  type="email" class="validate" required>
						<label for="email">E-Mail</label>
					</div>
				</div>
				<div class="row">
					<div class="input-field col s12">
						<input id="code" name="password" type="password" class="validate" required>
						<label for="code">Password</label>
					</div>
				</div>
				<div class="center">
					<button class="btn  btn-register waves-effect waves-light " type="submit" name="action" value="submit">Login
						<i class="material-icons right">input</i>
					</button>
				</div>
				<p class="center">
					<a href="reset_password.php">Forgot Password?Click here to reset</a>
				</p>
			</form>
			<a title="Register" class="ngl btn-floating btn-large waves-effect waves-light red " href="staff.php"><i class="material-icons">add</i></a>
		</div>
	</div>
	<script type="text/javascript">
		<?php
		outputToast();
		?>
	</script>
</body>
</html>


