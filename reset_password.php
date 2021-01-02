<?php
include("dbcon.php");
include("functions.php");
?>

<?php
formInit();
$toastOutput=array();
if(isset($_POST['Submit'])){
	getPostData();

	$email_sql=mysqli_real_escape_string($connection, $email);
	$staff_id_sql=mysqli_real_escape_string($connection, $staff_id);
	$query="SELECT serial from staff where staff_id={$staff_id_sql} and email='{$email_sql}' and lock_staff=0";
	$result=mysqli_query($connection, $query);
	if(!mysqli_num_rows($result)){
		addToToast("Email-ID& Staff ID combination doesnt match",0);
	}
	else{
		$hashed_password=encryptPassword($password);
		$query="UPDATE staff SET password='{$hashed_password}' WHERE email='{$email_sql}'";
		if(perform_query($connection, $query,"failed to reset"))
			addToToast("Password is updated",1);
	}

}

?>
<!DOCTYPE html>
<html>
<head>
	<title>Staff ID</title>
	<link rel="icon" href="favicon.ico" type="image/x-icon" />
	<link rel = "stylesheet" 
	href = "iconfont/material-icons.css">
	<link rel="stylesheet" href="materialize/css/materialize.css">
	<link rel="stylesheet" type="text/css" href="style_for_login.css">
	<script type = "text/javascript"
	src = "jquery-2.1.1.min.js"></script>   
	<!-- Compiled and minified JavaScript -->
	<script src="materialize/js/materialize.js"></script> 
</head>
<body class="">
	<div class="container">
		<div class="row">
			<span class="center"><h5 class="grey-text ">Reset Password</h5></span>
			<form action="reset_password.php" method="POST" class="col s12" id="reg-form" >
				<div class="input-field col s12">
					<input id="email" type="email" name=email class="validate" required>
					<label for="email">Email</label>
				</div>
				<div class="input-field col s12">
					<?php outputNumericField("staff_id", "", 1); ?>
					<label>Staff ID</label>		
				</div>
				<div class="col s12 input-field">
					<input type="password" name="password" class="validate" required id=password>
					<label>New Password</label>
				</div>
				<div class="center-align">
					<button type="submit" name="Submit" value="Submit" class="btn waves-effect waves-light">Reset Password
						<i class="material-icons right">send</i>
					</button>
				</div>
			</form>
		</div>
		<a title="Login" class="ngl btn-floating btn-large waves-effect waves-light red" href="login.php"><i class="material-icons">input</i></a>
	</div>
	<script>

		$(document).ready(function() {
		$('input#name, textarea').characterCounter();
		});

		<?php
		outputToast();
		?>
		document.addEventListener('DOMContentLoaded', function() {
			var elems = document.querySelectorAll('select');
			var instances = M.FormSelect.init(elems, {});
		});


	</script>
</body>

</html>

<?php
function formInit(){
	global $staff_id, $email, $password;
	$staff_id="";
	$email="";
	$password="";
}

function getPostData(){
	global $staff_id, $email, $password;
	$staff_id=$_POST['staff_id'];
	$email=$_POST['email'];
	$password=$_POST['password'];
}
?>