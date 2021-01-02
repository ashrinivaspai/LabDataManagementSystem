<?php
include_once("dbcon.php");
include_once("functions.php");
include_once("session.php");
?>

<?php
if($permission<$MIN_ACCESS_LEVEL)
	header('Location: search_item.php');
//initialize the form with default values
formInit();
//if submit button is pressed
if(isset($_POST['Submit'])){
	getPostData();
	
}	

?>
<?php
$toastOutput=array();
formInit();
//validations tu karta nave? please go makka bore jatta te koruka.
if(isset($_POST['Submit'])){
	getPostData();
	switch ($operations) {
		case 1:
			addItem();
			break;
		case 2:
			removeItem();
			break;
		case 3:
			updateItem();
			break;
		case 4:
			viewItem();
			break;
		default: 		
			addToToast("Please select Operation",0);
			break;
	}
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Item</title>
	<link rel="icon" href="favicon.ico" type="image/x-icon" />
	<link rel = "stylesheet" 
	href = "iconfont/material-icons.css">
	<link rel="stylesheet" href="materialize/css/materialize.css">  
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>      
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
	<header><?php displayNavbar('item.php'); ?></header>
	<main>
		<div class="container grey lighten-5">
			<form action="item.php" method="POST" class="col s12 z-depth-2  ">
				<div class="container ">
					<div class="row">
						<br><span class="center"><h5 class="grey-text text-darken-2 ">Manage Apparatus</h5></span>
						<div class="input-field col s12">
							<?php
							$operations = array(array(1,'Add') ,array(2,'Remove'), array(3,'Edit'), array(4,'View') );
							outputDropdownFromArray($operations, "operations", 1);
							?>
							<label>Select Operation</label>
						</div>
						<div class="input-field col s12" id=item_row  style="display: none">
							<?php
							$query="SELECT i.serial, concat(i.name,', ', i.version,
							', ', m.name) from item i, manufacturer m where 
							i.manufacturer_serial=m.serial and lock_item=0";
							$result=perform_query($connection, $query, "");
							outputDropdown($result, "item", $item);
							?>
							<label>Item</label>
						</div>
						<div id=man_cat_row >
							<div class="input-field col s12 m6 l6">
								<?php
									//display category dropdown
								$query="select * from `category` order by `name`";
								$result=mysqli_query($connection, $query);
								outputDropdown($result,"category", $category);
								?>
								<label>Category</label>
							</div>
							<div class="input-field col s12 m6 l6">
								<?php
								$query="select * from `manufacturer` order by `name`";
								$result=mysqli_query($connection, $query); 
								outputDropdown($result, "manufacturer", $manufacturer);
								?>
								<label>Manufacturer</label>
							</div>
						</div>
						<div class="input-field col s12" id=name_row>
							 <?php outputTextField("name", $name,60 ,1);?>	
							 <label >Name</label>
						 </div>
						<div class="input-field col s12" id=version_row>
							<?php outputTextField("version", $version,25, 0);?>
							<label for="version">Version/Model Number</label>
						</div>
						<div class="input-field col s12" id=description_row>
							<?php outputTextField("description", $description,100, 0);?>
							<label for="description">Comments/ Description</label>
						</div>
					</div>
				</div>
				<div class="center-align">
					<button type="submit" name="Submit" value="Submit" class="btn waves-effect waves-light">Submit
						<i class="material-icons right">send</i>
					</button>
					<button type="Reset" name="Reset" value="Reset" class="btn waves-effect waves-light">
						Reset
					</button>
				</div>
				<br/><br/>
			</form>
		</div>
		<Br/><Br/>
	</main>
	<?php displayFooter();
	?>
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
		function showDiv(elem){
			var name=elem.name;
			if(name=='operations'){
				switch(elem.value){
					case "1": 		//add
						addingItem();
						break;
					case "2":
						removingItem();
						break;
					case "3":
						updatingItem();
						break;
					case "4":
						viewingItem();
						break;
				}
			}
		}

		function addingItem(){
			document.getElementById('item_row').style.display="none";		//dont display already existing
			document.getElementById('man_cat_row').style.display="block";
			document.getElementById('version_row').style.display="block";
			document.getElementById('description_row').style.display="block";
			document.getElementById('name_row').style.display="block";
			document.getElementById('manufacturer').setAttribute("required", "");
			document.getElementById('category').setAttribute("required", "");
			document.getElementById('name').setAttribute("required", "");
			document.getElementById('version').setAttribute("required", "");
			document.getElementById('item').removeAttribute("required"); 	//remove it from required
		}
		function removingItem(){
			document.getElementById('item_row').style.display="block";	
			document.getElementById('man_cat_row').style.display="none";	
			document.getElementById('version_row').style.display="none";
			document.getElementById('description_row').style.display="none";
			document.getElementById('name_row').style.display="none";
			document.getElementById('manufacturer').removeAttribute("required");
			document.getElementById('name').removeAttribute("required");
			document.getElementById('version').removeAttribute("required");
			document.getElementById('category').removeAttribute("required");
			document.getElementById('item').setAttribute("required", ""); 	
		}

		function updatingItem(){
			addingItem();
			document.getElementById('item_row').style.display="block"; 	
		}

		function viewingItem(){
			removingItem();
		}
	  document.addEventListener('DOMContentLoaded', function() {
	    var elems = document.querySelectorAll('select');
	    var instances = M.FormSelect.init(elems, {});
	  });

	 
	</script>
</body>
</html>

<?php

function formInit(){
	global $category, $manufacturer, $name, $version, $description, $operations, $item;
	$operations=-1;
	$category=-1; 			//serial number of imaginery category
	$manufacturer=-1; 	//serial number of imaginery manufacturer
	$name="";
	$version="";
	$description="";
	$item=-1;
}

function getPostData(){
	//category and manufacturer contains serial numbers and not text value
	global $category, $manufacturer, $name, $version, $description, $operations, $item, $connection;
	$operations=$_POST['operations'];
	$category=isset($_POST['category'])?$_POST['category']:$category;
	$manufacturer=isset($_POST['manufacturer'])?$_POST['manufacturer']:$manufacturer;
	$name=isset($_POST['name'])?$_POST['name']:"";
	$version=isset($_POST['version'])?$_POST['version']:"";
	$description=isset($_POST['description'])?$_POST['description']:"";
	$item=isset($_POST['item'])?$_POST['item']:-1;
}

function addItem(){
	global $category, $manufacturer, $name, $version, $description, $connection,
			$result,$name_sql, $version_sql, $description_sql;
	escapeChars();
	if(($category!=-1)&&($manufacturer!=-1)){
		$query="select serial, lock_item from item where category_serial={$category} AND 
		manufacturer_serial={$manufacturer} and name='{$name}' and version='{$version}'";
		$result=perform_query($connection, $query, "failed to fetch");
		if(mysqli_num_rows($result)){
			$row=mysqli_fetch_assoc($result);
			if($row['lock_item']==1){
				$query="UPDATE item set lock_item=0 where serial={$row['serial']}";
				if(perform_query($connection, $query, "failed to update lock"))
					addToToast("Item is unlocked",0);
			}
			else
				addToToast("Item already exists",0);
		}
		else{
			$query="INSERT INTO
						item(
						category_serial, 
						manufacturer_serial, 
						name, 
						version, 
						description
						) 
					values(
						{$category}, 
						{$manufacturer}, 
						'{$name_sql}',
						'{$version_sql}',
						'{$description_sql}'
						)";
			if(perform_query($connection, $query, "Failed to Create Item"))
				addToToast("Item is Created",1);
		}
	}
	else{
		addToToast("Please select Category and manufacturer",0);
	}
}

function removeItem(){
	global $category, $manufacturer, $name, $version, $description, $connection, $result, $item;
	if($item==-1){
		addToToast("Item doesnt exist",0);
	}
	else{
		$query="SELECT count(iv.serial) as occurance from inventory as iv, item as i where iv.serial_item={$item} and iv.lock_item=0";
		$result=mysqli_fetch_assoc(perform_query($connection, $query));
		if($result['occurance']!=0){
			addToToast("Item exists in Database, Couldnt remove",0);
		}
		else{
			$query="UPDATE item set lock_item=1 where serial={$item}";
			if(perform_query($connection, $query, "failed to remove item"))
				addToToast("Item is Removed",1);
		}

	}
}

function updateItem(){
	global $category, $manufacturer, $name, $version, $description, $connection, 
			$result, $item, $name_sql, $version_sql, $description_sql;
	escapeChars();
	if($item==-1){
		addToToast("Item doesnt exist",0);
	}
	else{
		$query="UPDATE item set category_serial={$category}, manufacturer_serial={$manufacturer},
		 name='{$name_sql}', version='{$version_sql}', description='{$description_sql}' where serial={$item}";
		if(perform_query($connection, $query, "cannot update"))
			addToToast("Item is updated",1);
	}

}

function viewItem(){
	global $category, $manufacturer, $name, $version, $description, $connection, $result, $item;
	$query="SELECT * from item where serial={$item}";
	$result=perform_query($connection, $query, "failed to get item");
	$row=mysqli_fetch_assoc($result);
	$category=$row['category_serial'];
	$manufacturer=$row['manufacturer_serial'];
	$name=$row['name'];
	$version=$row['version'];
	$description=$row['description'];
	$item=$row['serial'];
}

function escapeChars(){
	global $connection, $name, $version, $description, $name_sql, $version_sql, $description_sql;
	$name_sql=mysqli_real_escape_string($connection, $name);
	$version_sql=mysqli_real_escape_string($connection,$version);
	$description_sql= mysqli_real_escape_string($connection,$description);
}

?>