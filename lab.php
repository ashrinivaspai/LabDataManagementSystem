<?php
include_once("dbcon.php");
include_once("functions.php");
include_once('session.php');
?>
<?php
/*										ADMIN PAGE
					ALLOW ONLY STAFF WITH PERMISSION>=$ADMIN_LEVEL (or some number)
*/

if($permission<$ADMIN_LEVEL)
	header('Location:inventory.php');

?>
<?php
 formInit();
 $toastOutput=array();
if(isset($_POST['Submit'])){
	getPostData();
	escapeChars();
	switch ($operations) {
		case 1:
			addLab();
			break;
		case 2:
			editLab();
			break;
		case 3:
			removeLab();
			break;
		case 4:
			viewLab();
			break;
		default:
			addToToast("Please select an Operation",0);
			break;
	}


}

function addLab(){
	global  $lab_incharge, $staff_incharge, $lab, $operations, $connection,$description_sql, $name_sql, $lab_id_sql;
	if($lab_incharge==-1) {
		addToToast("Please select a valid Lab Incharge",0);
	}
	else if($staff_incharge==-1){
		addToToast("Please select a valid Staff Incharge",0);
	}
	else if(doesItemExist('lab', 'lab_number', $lab_id_sql)){
		if(1==checkValue('lab', 'lock_lab', 'lab_number', $lab_id_sql)){
			setValue('lab', 'lock_lab', 0, 'lab_number', $lab_id_sql);
			addToToast("Lab is unlocked",1);
		}
		else
			addToToast("Lab exists",0);
	}
	else{
		$query="insert into lab (lab_name, staff_incharge, lab_incharge, lab_number, description) 
		values('{$name_sql}', {$staff_incharge}, {$lab_incharge}, '{$lab_id_sql}', '{$description_sql}')";
		perform_query($connection, $query, "failed to create lab");
		addToToast("New lab is created",1);
	}	
}

function removeLab(){
	global $name, $lab_incharge, $staff_incharge, $lab_id, $description, $lab, $operations, $connection;
	$query="UPDATE lab set lock_lab=1 where serial={$lab}";
	perform_query($connection, $query, "could not remove lab");
	addToToast("Lab is removed",0);

}

function editLab(){
	global  $lab_incharge, $staff_incharge, $lab, $operations, $connection,$description_sql, $name_sql, $lab_id_sql;
	if(doesItemExist("lab", "serial", $lab)){
		$query="UPDATE lab set lab_name='{$name_sql}', lab_number='{$lab_id_sql}', staff_incharge={$staff_incharge}, lab_incharge={$lab_incharge},
				 description='{$description_sql}' where lab.serial={$lab}";
		if(perform_query($connection, $query, "failed to update"))
			addToToast("Lab is updated",1);
	}
	else
		addToToast("Lab doesn't exist",0);

}
function viewLab(){
	global $name, $lab_incharge, $staff_incharge, $lab_id, $description, $lab, $operations, $connection;
	if(doesItemExist("lab", "serial", $lab)){
		$query="select * from lab where serial={$lab}";
		$result=perform_query($connection, $query, "couldnt retrieve lab");
		$row=mysqli_fetch_assoc($result);
		$name=$row['lab_name'];
		$lab_id=$row['lab_number'];
		$staff_incharge=$row['staff_incharge'];
		$lab_incharge=$row['lab_incharge'];
		$description=$row['description'];
		$lab=$row['serial'];
	}
	else
		addToToast("Lab doesn't exist", 0);
}

function escapeChars(){
	global $connection, $description, $name, $lab_id, $description_sql, $name_sql, $lab_id_sql;
	$description_sql=mysqli_real_escape_string($connection,$description);
	$name_sql=mysqli_real_escape_string($connection, $name);
	$lab_id_sql=mysqli_real_escape_string($connection, $lab_id);
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
	<header><?php displayNavbar('lab.php');?></header>
	<main>
		<div class="container grey lighten-5">
			<form action="lab.php" method="POST" class="col s12 z-depth-2">
				<div class="container">
					<div class="row">
						<br><span class="center"><h5 class="grey-text text-darken-2 ">Lab Management</h5></span>
						<div class="input-field col s12">
								<?php
								outputDropdownFromArray(
									array(
											array(1, 'Create'), 
											array(2,'Edit'), 
											array(3,'Remove'), 
											array(4, 'View') 
									), 'operations', 1);
								?>
								<label>Operation</label>
						</div>
						<div class="input-field col s12" id="lab_row" style="display: none;">
							<?php
							$query="SELECT l.serial, CONCAT(l.lab_name, ' | ', l.lab_number) from lab as l where lock_lab=0";
							$result=perform_query($connection, $query, "coulnt fetch labs");
							outputDropdown($result, 'lab', $lab);
							?>
							<label>Lab</label>
						</div>
						<div class="">
							<div class="input-field col s12 m6 l6" id=name_row>
								<?php outputTextField("name", $name,60, 1); ?>
								<label>Lab Name</label>		
							</div>
							<div class="input-field col s12 m6 l6" id=code_row>
								<?php outputTextField("lab_id", $lab_id,12, 1);?>
								<label>Lab Code</label>
							</div>
						</div>
						<div class="input-field col s12 m6 s6" id=lab_incharge_row>
							<?php
							$query="select serial from designation where post='Lab Incharge'";
							$result=perform_query($connection, $query, "error getting serial of lab incharge");
							$row=mysqli_fetch_assoc($result);
							$serial=$row['serial'];
							mysqli_free_result($result);
							$query=" select serial, name from staff where designation={$serial} and lock_staff=0";
							$result=perform_query($connection, $query, "failed to get staff_id");
							outputDropdown($result, "lab_incharge", $lab_incharge);
							?>
							<label>Lab Incharge</label>
						</div>
						<div class="input-field col s12 m6 s6" id=staff_incharge_row>
							<?php
							$query="select serial, name from staff where designation<>{$serial} and lock_staff=0";
							$result=perform_query($connection, $query, "failed to get staff_incharge");
							outputDropdown($result, "staff_incharge", $staff_incharge);
							?>
							<label>Staff Incharge</label>
						</div>
						<div class="input-field col s12" id=description_row>
							<?php outputTextField("description", $description,120, 0);?>
							<label>Description</label>
						</div>
					</div>
					<div class="center-align">
						<button type="submit" name="Submit" value="Submit" class="btn waves-effect waves-light">Submit
							<i class="material-icons right">send</i>
						</button>
						<button type="Reset" name="Reset" value="Reset" class="btn waves-effect waves-light">Reset</button>
					</div>
				</div>
				<br/><br/>
			</form>
		</div>
		<Br/><Br/>
	</main>
	<?php displayFooter();?>
	<script>
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

	  function showDiv(elem) {
	  	var name=elem.name;
	  	if(name=='operations'){
	  		switch(elem.value){
	  			case '1': 	//create
	  				createLab();
	  				break;
	  			case '2': 	//edit
	  				editLab();
	  				break;
	  			case '3':
	  				removeLab();
	  				break;
	  			case '4':
	  				viewLab();
	  				break;
	  		}
	  	}
	  }

	  function someRandom(){
	  	document.getElementById('name_row').style.display="block";
	  	document.getElementById('code_row').style.display="block";
	  	document.getElementById('staff_incharge_row').style.display="block";
	  	document.getElementById('lab_incharge_row').style.display="block";
	  	document.getElementById('description_row').style.display="block";
	  	document.getElementById('name').setAttribute("required", "");
	  	document.getElementById('lab_id').setAttribute("required", "");
	  	document.getElementById('staff_incharge').setAttribute("required", "");
	  	document.getElementById('lab_incharge').setAttribute("required", "");
	  }

	  function createLab() {
	  	someRandom();
	  	document.getElementById('lab_row').style.display="none";
	  	document.getElementById('lab').removeAttribute("required");
	  }

	  function editLab(){
	  	someRandom();
	  	document.getElementById('lab_row').style.display="block";
	  	document.getElementById('lab').setAttribute("required", "");
	  }

	  function removeLab(){
	  	document.getElementById('lab_row').style.display="block";
	  	document.getElementById('name_row').style.display="none";
	  	document.getElementById('code_row').style.display="none";
	  	document.getElementById('staff_incharge_row').style.display="none";
	  	document.getElementById('lab_incharge_row').style.display="none";
	  	document.getElementById('description_row').style.display="none";
	  	document.getElementById('lab').setAttribute("required", "");
	  	document.getElementById('name').removeAttribute("required");
	  	document.getElementById('lab_id').removeAttribute("required");
	  	document.getElementById('lab_incharge').removeAttribute("required");
	  	document.getElementById('staff_incharge').removeAttribute("required");
	  }

	  function viewLab(){
	  	//same attributes so, reuse
	  	removeLab();
	  }

	</script>
</body>
</html>

<?php

function formInit(){
	global $name, $lab_incharge, $staff_incharge, $lab_id, $description, $lab, $operations;
	$name="";
	$lab_incharge=-1;
	$staff_incharge=-1;
	$lab_id="";
	$description="";
	$lab=-1;
	$operations=1;
}

function getPostData(){
	//name is name of lab
	global $name, $lab_incharge, $staff_incharge, $lab_id, $description, $lab, $operations;
	$name=isset($_POST['name'])?(trim($_POST['name'])):"";
	$lab_incharge=isset($_POST['lab_incharge'])?$_POST['lab_incharge']:-1;
	$staff_incharge=isset($_POST['staff_incharge'])?$_POST['staff_incharge']:-1;
	$lab_id=isset($_POST['lab_id'])?$_POST['lab_id']:"";
	$description=isset($_POST['description'])?$_POST['description']:"";
	$lab=isset($_POST['lab'])?$_POST['lab']:-1;
	$operations=$_POST['operations'];
}

?>
