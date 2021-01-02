<?php
require_once("dbcon.php");
require_once 'functions.php';
?>

<?php
session_start();
$staff_name=isset($_SESSION['name'])?$_SESSION['name']:"Guest";
formInit();
$toastOutput=array();
if(isset($_POST['Submit'])){
	getPostData();
	$query="SELECT
				CONCAT(l.lab_name, ' | ', l.lab_number) as name,
				COUNT(CASE when (iv.status=1 or iv.status=4) then 1 else NULL END) as 'Working',
			    COUNT(CASE when iv.status=2 then 1 else NULL END) as 'Broken',
			    COUNT(CASE when iv.status=3 then 1 else NULL END) as 'Under Repair'
			From 
				item as i,
			    inventory as iv,
			    lab as l
			where
				i.serial={$item} AND
				l.serial=iv.lab_serial AND
				i.serial=iv.serial_item AND
				iv.lock_item=0 AND
				l.lock_lab=0
			GROUP BY
				l.serial
				";
	$tableBody=mysqli_fetch_all(perform_query($connection, $query, "faild to get report"));
	$tableHead=array("Lab Name | Code","Working", "Broken", "Under Repair");
}



?>


<!DOCTYPE html>
<html>
<head>
	<title>Search</title>
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
	<header><?php displayNavbar('search_item.php'); ?></header>
	<main>
		<div class="container grey lighten-5">
			<form action="search_item.php" class="card grey lighten-5" method="POST">
				<div class="container">
					<div class="row">
						<br/><span class="center"><h5 class="grey-text text-darken-2 ">Search Apparatus</h5></span>
						<div class="col s12 m6 l6 input-field">
							<?php
							/*display only those items which are there in inventory*/
							$query="SELECT 
										i.serial, 
										concat(i.name,' | ', i.version, ' | ', m.name) as name,
										c.serial, 
										c.name
									from 
										inventory as iv,
										item as i,
										category as c, 
										manufacturer as m 
									where 
										iv.serial_item=i.serial AND
										m.Serial=i.manufacturer_serial and
										c.serial=i.category_serial AND
										iv.lock_item=0
									GROUP BY
										i.serial
									order by 
										c.serial";
							$result=perform_query($connection, $query, "failed to get apparatus");
							outputDropdownOptGrp($result, "item", $item);
							?>
							<label>Select an Item</label>
						</div>
						<div class="center-align">
							<p>
								<button type="submit" name="Submit" value="Submit" class="btn waves-effect waves-light">Search
									<i class="material-icons right">search</i>
								</button>
							</p>
						</div>
					</div>
				</div>
			</form>
		</div>
		<div>
			<?php
			if(isset($_POST['Submit'])){
				if(!empty($tableBody)){
					echo "<div class='container white z-depth-2'> ";
					outputTable($tableHead, $tableBody);
					echo "</div>";
				}
				else
					addToToast("Item is not available",0);
			}
			?>
		</div>
	</main>
	<script type = "text/javascript"
	src = "jquery-2.1.1.min.js"></script>  
	<!-- Compiled and minified JavaScript -->
	<script src="materialize/js/materialize.js"></script>
	<script type="text/javascript">
		<?php
		outputToast();
		?>
		$(document).ready(function() {
			$('input, textarea').characterCounter();
			$('.sidenav').sidenav();
			$(".dropdown-trigger").dropdown({constrainWidth: false});
		});
		document.addEventListener('DOMContentLoaded', function() {
			var elems = document.querySelectorAll('select');
			var instances = M.FormSelect.init(elems, {});
		});
	</script>
	<Br/><Br/>
	<?php displayFooter();?>
</body>
</html>

<?php

function formInit(){
	global $item;
	$item=-1;
}

function getPostData(){
	global $item;
	$item=isset($_POST['item'])?$_POST['item']:-1;
}

?>