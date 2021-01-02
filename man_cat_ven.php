<?php
include_once("dbcon.php");
include_once("functions.php");
include_once("session.php");
?>
<?php
if($permission<$MIN_ACCESS_LEVEL)
	header('Location: search_item.php');

 formInit();
 $toastOutput=array();
if(isset($_POST['Submit'])){
	getPostData();
	if(ctype_alpha(str_replace(' ', '', $value)) === false)
		addToToast("Please enter a valid Value",0);
	else{
		switch ($entity) {
			case 1:
				insert('manufacturer');
				break;
			
			case 2:
				insert("category");
				break;
			case 3:
				insert("vendor");
				break;
			default:
				addToToast("Plase select a entity",0);
		}

	}
}


function formInit(){
	global $entity, $value;
	$entity=-1;
	$value="";
}

function getPostData(){
	global $entity, $value;
	$entity=isset($_POST['entity'])?$_POST['entity']:-1;
	$value=ucwords(strtolower(trim($_POST['value'])));
}

function insert($table){
	global $value, $connection;
	$value_sql=mysqli_real_escape_string($connection,$value);
	$query="INSERT INTO `{$table }` (name) values('{$value_sql}')";
	if(perform_query($connection, $query ,"failed to insert"))
		addToToast("Entity created",1);
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Lab</title>
	<link rel="icon" href="favicon.ico" type="image/x-icon" />
	<link rel = "stylesheet" 
	href = "iconfont/material-icons.css">
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>  
	<link rel="stylesheet" href="materialize/css/materialize.css">
	<script type = "text/javascript"
	src = "jquery-2.1.1.min.js"></script>   
	<!-- Compiled and minified JavaScript -->
	<script src="materialize/js/materialize.js"></script>        
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
	<header><?php displayNavbar('man_cat_ven.php');?></header>
	<main>
		<div class="container grey lighten-5">
			<form action="man_cat_ven.php" method="POST" class="col s12 z-depth-2">
				<div class="container">
					<div class="row">
						<br><span class="center"><h5 class="grey-text text-darken-2 ">Manage Entities</h5></span>
						<div class="input-field col s12">
							<?php
							outputDropdownFromArray(
								array(
									array(1,'Manufacturer'),
									array(2,'Category'),
									array(3,'Vendor')
								),'entity',-1);
							?>
							<label for='entity'>Entity</label>
						</div>
						<div class="input-field col s12">
							<?php 
							outputTextField("value", $value, 60,1);
							?>
							<label for="value">Name</label>
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
