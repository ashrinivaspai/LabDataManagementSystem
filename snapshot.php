<?php
//these three files u will use/need
require_once("dbcon.php");
require_once 'functions.php';
require_once 'session.php';
?>
<?php

if($permission<$MIN_ACCESS_LEVEL)
	header('Location: search_item.php');
/*

	This page will display snapshot of the labs associated with the staff

*/


?>
<!DOCTYPE html>
<html>
<head>
	<title>SnapShot</title>
	<link rel="icon" href="favicon.ico" type="image/x-icon" />
	<link rel = "stylesheet" 
	href = "iconfont/material-icons.css">
	<link rel="stylesheet" href="materialize/css/materialize.css">
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
		<script type = "text/javascript"
		src = "jquery-2.1.1.min.js"></script>    
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
	<header><?php displayNavbar('snapshot.php');?></header>
	<main>
		<div class="container">
			<span class="center"><h5 class="grey-text text-darken-2 ">Snapshot</h5></span>
			<?php
			$tableHead=array('Category','Apparatus','Working','Broken','Under Repair');
			$lab_serial=getLabForStaff($staff_id, $permission);
			$query="SELECT serial,CONCAT(lab_name,' | ',lab_number) from lab where serial in (";
			$query.=implode(',',$lab_serial);
			$query.=") AND lock_lab=0";
			$result=perform_query($connection, $query, "failed to get labs");
			$lab_names=mysqli_fetch_all($result);
			if(empty($lab_names))
				echo "<div class='card center-align grey-text text-darken-1'><br/>No Associated Labs Found<br/><br/></div>";
			else{
				echo "<ul class='collapsible popout center-align'>"; // start with collapsible
				foreach ($lab_names as $key => $value) {
					$query="SELECT
								c.serial,
								c.name, 
								COUNT(DISTINCT iv.serial_item)
							FROM
								category as c, 
								item as i, 
								inventory as iv, 
								lab as l
							WHERE
								iv.serial_item = i.serial AND
								i.category_serial=c.serial AND
								l.serial=iv.lab_serial AND
								l.serial= {$value[0]} AND
								iv.lock_item=0
							GROUP BY
								c.serial
							ORDER BY
								i.category_serial
							";
					$categoryCount=mysqli_fetch_all(perform_query($connection, $query, "failed to get count"), MYSQLI_NUM);
					$query="SELECT
								i.category_serial, 
								CONCAT(i.name, ' | ', i.version) as name,
								COUNT(CASE when (iv.status=1 or iv.status=4) then 1 else NULL END) as 'Working',
							    COUNT(CASE when iv.status=2 then 1 else NULL END) as 'Broken',
							    COUNT(CASE when iv.status=3 then 1 else NULL END) as 'Under Repair'
							From 
								item as i,
							    inventory as iv,
							    lab as l
							where
								l.serial={$value[0]} AND
								l.serial=iv.lab_serial AND
								i.serial=iv.serial_item AND
								iv.lock_item=0
							GROUP BY
								iv.serial_item
							ORDER BY
								i.category_serial
								";
					$tableBody=mysqli_fetch_all(perform_query($connection, $query, "failed to get report"));
					$query="SELECT SUM(cost) as value from inventory where lock_item=0 and lab_serial={$value[0]}";
					$row=mysqli_fetch_assoc(perform_query($connection, $query, "failed to get sum"));
					echo "<li><div class='collapsible-header'>{$value[1]}</div>";
					echo "<div class='collapsible-body'>";
					if(!empty($tableBody)){

						outputTableMerged($tableHead, $tableBody, $categoryCount, '0');
						echo "
							<p class='center-align grey-text'>
								Total Cost of all Apparatus in the Lab:
									<span class='grey-text text-darken-1'>
										{$row['value']}
									</span>
							</p>";
					}
					else{
						echo("<p class='center-align grey-text'>Lab is Empty</p><Br/>");
					}
					echo "</div></li>";
				}
				echo "</ul>";
			}

			?>
		</div>
	</main>
	<?php displayFooter();?>
	<script>
		$(document).ready(function() {
			$('input, textarea').characterCounter();
			$('.sidenav').sidenav();
			$(".dropdown-trigger").dropdown({constrainWidth: false});
			$('.collapsible').collapsible();
		});  

		document.addEventListener('DOMContentLoaded', function() {
			var elems = document.querySelectorAll('select');
			var instances = M.FormSelect.init(elems, {});
		});
	</script>
</body>
</html>

<?php 

//table header is 1D array, tablebody must be 2D array
function outputTableMerged($tableHeader, $tableBody, $mergeRow, $mergeCol){
	global $toastOutput;
	if(empty($tableBody)||empty($mergeRow)){
		addToToast("Sorry,no records found",0);
		return;
	}
	echo "<table>";
	echo "<thead>";
	echo "<tr>";
	foreach ($tableHeader as $key => $value) {
		echo "<th>{$value}</th>";
	}
	echo "</tr>";
	echo "</thead>";
	echo "<tbody>";
	$previousValue=-1; //initialize
	$indexToValue=-1;  
	foreach ($tableBody as $key => $row) {
		echo "<tr>";
		$currentValue=$row[0];							//for every row load current category
		if($currentValue!=$previousValue){				// compare if its not same as previous
			$indexToValue+=1;							// point to next category 
			$count=$mergeRow[$indexToValue][2]; 		// load count of that category
			$previousValue= $currentValue	; 			// you know this right
			foreach ($row as $key => $value) {
				if($key==$mergeCol){
					echo "<td rowspan='{$count}'>"; 	//span the rows according to the count
					echo $mergeRow[$indexToValue][1]; 	//name of category
					echo "</td>";
				}
				else{
					echo "<td> {$value}</td>";
				}
			}
		}
		else{
			foreach ($row as $key => $value) {
				if($key!=$mergeCol){ 				// display <td> only if its non merged one
					echo "<td>";
					echo $value;
					echo "</td>";
				}
			}
		}
		echo "</tr>";
	}
	echo "</tbody>";
	echo "</table>";
}
?>