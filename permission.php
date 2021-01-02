<?php
include_once("dbcon.php");
include_once("functions.php");
include_once("session.php");
?>
<?php

if($_SESSION['permission']<$ADMIN_LEVEL)
	header('Location: inventory.php');

formInit();
$toastOutput=array();
if(isset($_POST['Submit'])){
	getPostData();
	modify_permission($staff_id, $permission);
}


function formInit(){
	global $staff_id, $permission;
	//here staff_id is serial of staff in the db(please dont confuse it with staff_id of db)
	$staff_id=-1;
	$permission=1;
}

function getPostData(){
	global $staff_id, $permission;
	$staff_id=isset($_POST['staff_id'])?$_POST['staff_id']:-1;
	$permission=isset($_POST['permission'])?$_POST['permission']:1;
}

function modify_permission($staff_id, $permission){
	global  $connection, $ADMIN_LEVEL;
	$query="UPDATE staff set permission={$permission} where serial={$staff_id}";
	if(perform_query($connection, $query ,"Couldn't Update"))
		addToToast("Permission Changed",1);
	if($_SESSION['id']==$staff_id){
		//only for the case where the one who is modifying the permission for self
		$_SESSION['permission']=$permission;
		if($_SESSION['permission']<$ADMIN_LEVEL) //if he changes his own permission to lower
			header('Location: inventory.php');
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Permission</title>
	<link rel="icon" href="favicon.ico" type="image/x-icon" />
	<link rel = "stylesheet" 
	href = "iconfont/material-icons.css">
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>  
	<link rel="stylesheet" href="materialize/css/materialize.css">     
	<style type="text/css">
	body {
		display: flex;
		min-height: 100vh;
		flex-direction: column;
	}
	main {
		flex: 1 0 auto;
	}

</style>
</head>
<body class="grey lighten-4">
	<header><?php displayNavbar('permission.php');?></header>
	<main>
		<div class="container grey lighten-5">
			<form action="permission.php" method="POST" class="col s12 z-depth-2">
				<div class="container">
					<div class="row">
						<br><span class="center"><h5 class="grey-text text-darken-2 ">Manage Permissions</h5></span>
						<div class="input-field col s12">
							<?php
							$query="SELECT 
										serial,
										CONCAT(name,', Staff ID:  ',staff_id, ' ( Permission: ', permission, ' )')
									FROM staff where lock_staff=0";
							outputDropdown(perform_query($connection, $query, "failed to get staff"), "staff_id", $staff_id);
							?>
							<label for='staff_id'>Select a Staff</label>
						</div>
						<div class="input-field col s12">
							<?php 
							outputDropdownFromArray(array(array(1,1), array(2,2), array(3,3), array(4,4), array(5,5)), 
									"permission", $permission);
							?>
							<label for="permission">Permission</label>
						</div>
					</div>
					<div class="center-align">
						<button type="submit" name="Submit" value="Submit" class="btn waves-effect waves-light">Submit
							<i class="material-icons right">send</i>
						</button>
					</div>
				</div>
				<Br/><Br/>
			</form>
		</div>
		<Br/><Br/>
	</main>
	<?php displayFooter();?>
	<script type = "text/javascript"
	src = "jquery-2.1.1.min.js"></script>   
	<!-- Compiled and minified JavaScript -->
	<script src="materialize/js/materialize.js"></script>   
	<script type="text/javascript">
			$(document).ready(function() {
				$('input, textarea').characterCounter();
				$('.sidenav').sidenav();
				$(".dropdown-trigger").dropdown({constrainWidth: false});
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
