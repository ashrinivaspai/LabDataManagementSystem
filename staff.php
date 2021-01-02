<?php
include("dbcon.php");
include("functions.php");
?>

<?php
formInit();
$toastOutput=array();
if(isset($_POST['Submit'])){
	getPostData();
	if($designation==-1){
		addToToast("Please select a designation",0);
	}
	else{
		$email_sql=mysqli_real_escape_string($connection, $email);
		$name_sql=mysqli_real_escape_string($connection, $name);
		$staff_id_sql=mysqli_real_escape_string($connection, $staff_id);
		$year_sql=mysqli_real_escape_string($connection, $year);
		$query="select serial from staff where staff_id={$staff_id_sql} or email='{$email_sql}'";
		$result=mysqli_query($connection, $query);
		if(mysqli_num_rows($result)){
			addToToast("Email/Staff ID exists in system",0);
		}
		else{
			$hashed_password=encryptPassword($password);
			$query="insert into staff (name, staff_id, designation, joining_year, email, password) values('{$name_sql}', 
			{$staff_id_sql}, {$designation}, {$year_sql}, '{$email_sql}','{$hashed_password}')";
			perform_query($connection, $query,"failed to create");
			addToToast("{$name} is added to Database",1);
		}
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
			<span class="center"><h5 class="grey-text ">Register</h5></span>
			<form action="staff.php" method="POST" class="col s12" id="reg-form" >
				<div class="input-field col s12">
					<?php outputTextField("name", $name, 60,1);?>
					<label for="name">Name</label>		
				</div>
				<div class="input-field col s12">
					<input id="email" type="email" name=email class="validate" required>
					<label for="email">Email</label>
				</div>
				<div class="input-field col s6">
					<?php outputNumericField("staff_id", $staff_id, 1); ?>
					<label>Staff ID</label>		
				</div>
				<div class="col s6 input-field">
					<input type="password" name="password" class="validate" required id=password>
					<label>Password</label>
				</div>
				<div class="input-field col s6">
					<?php
					$query="select * from designation order by post";
					$result=mysqli_query($connection, $query);
					outputDropdown($result, "designation", -1);
					?>
					<label>Designation</label>
				</div>
				<div class="input-field col s6">
					<?php outputNumericField("year", $year, 1);?>
					<label>Joining Year</label>
				</div>
<!-- 				<div class="input-field col s6">
					<select name="permission" required="" >
						<?php
						for($i=1;$i<5;$i=$i+1)
							echo "<option value={$i}>{$i}</option>";
						?>							
					</select>
					<label>Permission</label>
				</div> -->
				<div class="center-align">
					<button type="submit" name="Submit" value="Submit" class="btn waves-effect waves-light">Register
						<i class="material-icons right">send</i>
					</button>
					<button type="Reset" name="Reset" value="Reset" class="btn waves-effect waves-light">Reset</button>
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
	global $name, $staff_id, $designation, $permission, $year, $email, $password;
	$name="";
	$staff_id="";
	$designation="";
	$permission="";
	$year="";
	$email="";
	$password="";
}

function getPostData(){
	global $name, $staff_id, $designation, $permission, $year, $email, $password;
	$name=ucwords(strtolower(trim(($_POST['name']))));
	$staff_id=$_POST['staff_id'];
	$designation=isset($_POST['designation'])?$_POST['designation']:-1;
//	$permission=$_POST['permission'];
	$year=$_POST['year'];
	$email=$_POST['email'];
	$password=$_POST['password'];
}
?>