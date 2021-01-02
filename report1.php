<?php
include_once('dbcon.php');
include_once('functions.php');
include_once('session.php');


// 								  tab width: 4

/******************************************************************************
 * 							    Generating report							  *
 ******************************************************************************   
 *   Generating report is an art, and i am no artist. So, i will leave this   *
 * 					    	work to some talented artists    				  *
 ******************************************************************************
 *
 * parameters: 	1) Lab
 *				2) Manufacturer
 *				3) Category
 *				4) Item
 *				5) Item Code --> narrowest, useful to track item history
 *				6) Purchase Year --> must search inventory_log, first record
 *								-->isnt implemented
 * And i say, lets give a "pro mode" where user can enter query. What say?
 *
 *****************************************************************************/
?>

<?php

/*
					variables used

	$file 			-> file handle
	$mode 			-> current or log mode (1 or 2)
	$lab 			-> array of lab serials
	$manufacturer 	-> array of manufacturer serials
	$category 		->  ""      category serials
	$item 			-> array of serials of item table
	$item_name 		-> array of serials of inventory table
	$to_date 		-> 
	$from_date 		->
*/
formInit();
$toastOutput=array();
if(isset($_POST['Submit'])){
	getPostData();
	$query="SELECT serial, lab_number from lab";
	$lab_names=mysqli_fetch_all(perform_query($connection, $query, "failed to fetch labs"), MYSQL_ASSOC);
	$file = fopen('demosaved.csv', 'w'); 		//open file in write mode
	if($mode==1){ 		//current mode
		$query="SELECT 
					iv.item_code, 
					CONCAT(i.name,' | ' ,m.name,' | ',i.version) as Item, 
					CONCAT(l.lab_name, ' | ', l.lab_number) as Lab,
					(CASE 
						WHEN iv.lock_item=1 then 'Removed'
						WHEN iv.status=2 then 'Broken'
						WHEN iv.status=3 then 'Under Repair'
						WHEN iv.status=4 then 'Working'
						ELSE 'Working'
					END) AS status,
					iv.lab_serial as 'current_lab',
					MAX(IF(il.status=2, il.`timestamp`, NULL)) as broken,
					MAX(IF(il.status=3, il.`timestamp`, NULL)) as 'under repair',
					MAX(IF(il.status=4, il.`timestamp`, NULL)) as repaired
				from 
					manufacturer as m, 
					item as i, 
					inventory_log as il, 
					inventory as iv, 
					lab as l, 
					category as c
				where ";
		$headerRow=array('Item Code', 'Name|Make|Description', 'Lab|Number', 'Status','Current_Lab','Broken','Under Repair', 'Repaired' );
		fputcsv($file,$headerRow ); 	//column header
		$headerRow=array('Item Code', 'Name|Make|Description', 'Status','Current_Lab','Broken','Under Repair', 'Repaired' );
	}
	else 				//log mode
	{
		$query="SELECT 
					iv.item_code, 
					CONCAT(l.lab_name, ' | ', l.lab_number) as Lab,
					CONCAT(i.name,' | ' ,m.name,' | ',i.version) as Item,   
					s.operation,
					il.comments, 
					staff.name, 
					il.timestamp 
				from 
					inventory iv,
					inventory_log il, 
					manufacturer m, 
					item i, 
					status s, 
					lab l, 
					category c, 
					staff 
				where ";
		$headerRow=array('Item Code','Lab|Number','Name|Make|Description', 'Status','Comment','Staff Name','TimeStamp');
		fputcsv($file, $headerRow);
	}
	$lab_serial=getLabForStaff($staff_serial, $permission);
	if(!count($lab)) 		//if user has selected some labs
		$lab=$lab_serial;
	$query.=" l.serial in (";
	$query.=implode(',', $lab);
	$query.=") AND ";

	if(count($manufacturer)){
		$query.=" m.serial in ( ";
		$query.=implode(',',$manufacturer);
		$query.=") AND ";
			
	}
	if(count($category)){
		$query.=" c.serial in(";
		$query.=implode(',', $category);
		$query.=") AND ";
	}
	if(count($item)){ 		//item. in the sense the one we created in item
		$query.=" i.serial in(";
		$query.=implode(',', $item);
		$query.=") AND ";
	} 
	if(count($item_name)){ 	//this is the one in inventory
		$query.=" iv.serial in(";
		$query.=implode(', ', $item_name);
		$query.=") AND ";
	}
	if($to_date&&$from_date){
		if($to_date!="") $to_date=date_create($to_date);
		if($from_date!="") $from_date=date_create($from_date);
		if($to_date>=$from_date){
			$to_date=date_format($to_date,"Y-m-d");
			$from_date=date_format($from_date, "Y-m-d");
			$query.="  il.timestamp between '{$from_date} 00:00:00' and '{$to_date} 23:59:59' and ";
		}
		else{
			$to_date=date_format($to_date,"Y-m-d");
			$from_date=date_format($from_date, "Y-m-d");
		}
	}

	if($mode==1)
	{
		$query.="
				iv.serial=il.inventory_serial AND
				l.serial=il.lab_serial AND
				i.serial=iv.serial_item AND
				i.category_serial=c.serial and 
				m.serial=i.manufacturer_serial
			GROUP BY
				l.serial,
				iv.serial
			ORDER BY
				l.serial ASC";
	}	
	else{
		$query.=" 	
				i.serial=iv.serial_item and 
				i.manufacturer_serial=m.serial and 
				i.category_serial=c.serial and 
				il.status=s.serial and 
				il.inventory_serial=iv.serial and 
				l.serial=il.lab_serial and 
				staff.serial=il.staff_id 
			ORDER BY 
				il.serial DESC";
	}
	//echo($query);
	$result=perform_query($connection, $query, "failed to report");
	$tableRow=mysqli_fetch_all($result, MYSQL_NUM);

	$result=perform_query($connection, $query, "failed to report");
	while($row=mysqli_fetch_assoc($result)){
		fputcsv($file, $row); 					//put row-wise
	}									
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Reports</title>
	<link rel="icon" href="favicon.ico" type="image/x-icon" />
		<link rel = "stylesheet" 
		href = "https://fonts.googleapis.com/icon?family=Material+Icons">
		<link rel="stylesheet" href="materialize/css/materialize.css">
		<link rel="stylesheet" type="text/css" href="nav_bar.css">
		<script type = "text/javascript" src = "jquery-2.1.1.min.js"></script> 
    	<!-- Compiled and minified JavaScript -->
    	<script src="materialize/js/materialize.min.js"></script>   
		<style type="text/css">
		.container {
			width: 90%;
			height: 90%;
		}
	</style>
</head>
<body class="grey lighten-4">
	<div class="center-align row ">
		<h4 class="grey-text text-darken-3">Department of Electronics& Communication</h4>	
	</div>
	<div class="row ">
		<nav >
			<div class="nav-wrapper teal  ">
				<ul class="right">
					<li><a href="item.php">Apparatus</a></li>
					<li ><a href="inventory.php">Inventory</a></li>
					<li ><a href="lab.php">Lab</a></li>
					<li><a href="transfer_item.php">Transfer Apparatus</a></li>
					<li><a href="snapshot.php">Snapshot</a></li>
					<li class="active"><a href="report.php">Reports</a></li>
					<li><a href="logout.php">Logout</a></li>
					<!-- put rest of the tabs here -->
				</ul>
			</div>
		</nav>
	</div>
	<div class="container grey lighten-5">
		<form action="report1.php" method="POST" class="col s12 z-depth-2">
			<div class="container ">
				<br/>
				<div class="row">
					<div class="input-field col s12 l6 m6">
						<?php 
						outputDropdownFromArray(array(array(1, 'current'), array(2, 'log')), "mode", 1); 
						?>
						<label>Mode</label>
					</div>
					<div class="input-field col s12 l6 m6 ">
						<?php 
						$lab_serial=getLabForStaff($staff_serial, $permission);
						$query="SELECT 
									serial, 
									CONCAT(lab_name, ' | ', lab_number) as name 
								from lab
								WHERE ";
						$query.="serial in(";
						$query.=implode(',',$lab_serial);
						$query.=')';
						$result=perform_query($connection, $query, "failed to get lab");
						outputMultipleSelect($result, "lab", -1);
						?>
						<label for="lab">Lab</label>
					</div>
					<div class="input-field col s12 m6 l6">
						<?php
						$query="SELECT * from manufacturer";
						$result=perform_query($connection, $query, "failed to get manufacturer");
						outputMultipleSelect($result, "manufacturer", -1);
						?>
						<label>Manufacturer</label>
					</div>
					<div class="input-field col s12 m6 l6">
						<?php
						$query="SELECT * from category";
						$result=perform_query($connection, $query, "failed to get category");
						outputMultipleSelect($result, "category", -1);
						?>
						<label>Categories</label>
					</div>
					<div class="input-field col s6">
						<?php
						$query="SELECT 
									i.serial, 
									concat(i.name,' | ', i.version, ' | ', m.name) as name 
								FROM 
									item as i, 
									manufacturer as m 
								WHERE 
									m.Serial=i.manufacturer_serial 
								ORDER BY 
									i.serial";
						$result=perform_query($connection, $query, "failed to get item");
						outputMultipleSelect($result, "item", -1);
						?>
						<label>Apparatus</label>
					</div>
					<div class="input-field col s6 m6 l6">
						<?php
						//list only items from labs associated to them or previously was in their lab
						$query="SELECT DISTINCT
									iv.serial, 
									iv.item_code
								FROM 
									inventory as iv, 
									inventory_log as il 
								WHERE 
									iv.serial=il.inventory_serial AND ";
						$query.="il.lab_serial in(";
						$query.=implode(',',$lab_serial);
						$query.=') ';
						$result=perform_query($connection, $query, "failed to get item code");
						outputMultipleSelect($result, "item_name", -1);
						?>
						<label>Item Codes</label> 
					</div>
					<div class="input-field col s12 m6 l6">
						<?php
						outputDateField("from_date",'', 0);
						?>
						<label class="active">From Date</label>
					</div>
					<div class="input-field col s12 m6 l6">
						<?php
						outputDateField("to_date",'', 0);
						?>
						<label class="active">To Date</label>
					</div>
				</div>
			</div>
			<div class="center-align">
				<button type="submit" name="Submit" value="Submit" class="btn waves-effect waves-light">Submit
					<i class="material-icons right">send</i>
				</button>
				<button type="Reset" name="Reset" value="Reset" class="btn waves-effect waves-light">Reset</button>
			</div>
			<br/><br/>
		</form>
	</div>
	<Br/>
	<?php
	if(isset($_POST['Submit'])){
		//start to display table
		if(!empty($tableRow)){

			/*
				following set of some 10 lines are to replace current_lab serial 
				with corresponding lab code, seems confusing piece of code right?
								only for mode 1
			*/
			if($mode==='1'){
				$currentLab=$tableRow[0][2];
				$nextLab=$currentLab;
				$rowCount=0;
				$startIndex=0;
				foreach ($tableRow as $key => $value) {
					$temp_lab_serial=$value[4];
					foreach ($lab_names as $keyLab => $valueLab) {
						if($valueLab['serial']==$temp_lab_serial)
							$tableRow[$key][4]=$valueLab['lab_number'];
					}
					//starting to split the table to display as seperate tables
					$nextLab=$value[2];
					if($currentLab!=$nextLab){
						echo "<div class='container z-depth-3 white'>";
						echo "<Br/><h6 class='center-align grey-text'>{$currentLab}</h6>";
						$temp_array=array_slice($tableRow,$startIndex,$rowCount);
						delete_col($temp_array, 2);
						outputTable($headerRow, $temp_array);
						echo "</div><Br/>";
						$startIndex=$key;
						$rowCount=0;
					}
					$currentLab=$nextLab;
					$rowCount++;
				}
				echo "<div class='container z-depth-3 white'>";
				echo "<Br/><h6 class='center-align grey-text'>{$currentLab}</h6>";
				$temp_array=array_slice($tableRow,$startIndex,$rowCount);
				delete_col($temp_array, 2);
				outputTable($headerRow, $temp_array);
				echo "</div>";	
			}
			else{
				echo "<div class='container z-depth-3 white'>";
				outputTable($headerRow, $tableRow);
				echo "</div>";	
			}
			echo "	
				<p class='center-align'>
				<a class='btn waves-light waves-effect' href='demosaved.csv' download='report.csv'>
					Download Report
				</a>
				</p>";
		}
		else{
			addToToast("Sorry, no records found",0);
		}
	}
	function delete_col(&$array, $offset) {
	    return array_walk($array, function (&$v) use ($offset) {
	        array_splice($v, $offset, 1);
	    });
	}
	?>
	</div>
	<Br/><Br/>
	<footer class="grey lighten-2">
		<Br/>
		<h6 class="center-align grey-text text-darken-1">&copy; 2018-<?php echo date('Y');?> Department Of Electronics& Communication-NMAMIT</h6> 
		<Br/><Br/>
	</footer>
		<script type="text/javascript">
			<?php
				outputToast();
			?>
			document.addEventListener('DOMContentLoaded', function() {
				var elems = document.querySelectorAll('select');
				var instances = M.FormSelect.init(elems, {});
			});

			document.addEventListener('DOMContentLoaded', function() {
	    		var elems = document.querySelectorAll('.datepicker');
	    		var instances = M.Datepicker.init(elems, {
	    			format:'yyyy-mm-dd'
	    		});
	  		});
	</script>
</body>
</html>


<?php

function formInit(){
	global $lab, $manufacturer, $category, $item, $item_name, $to_date, $from_date, $mode;
	$lab=array();
	$manufacturer=array();
	$category=array();
	$item_name=array();
	$item=array();
	$to_date="";
	$from_date="";
	$mode=1;
}

function getPostData(){
	global $lab, $manufacturer, $category, $item, $item_name, $to_date, $from_date, $mode;
	$lab=isset($_POST['lab'])?$_POST['lab']:array();
	$category=isset($_POST['category'])?$_POST['category']:array();
	$manufacturer=isset($_POST['manufacturer'])?$_POST['manufacturer']:array();
	$item=isset($_POST['item'])?$_POST['item']:array();
	$item_name=isset($_POST['item_name'])?$_POST['item_name']:array();
	$to_date=isset($_POST['to_date'])?$_POST['to_date']:"";
	$from_date=isset($_POST['from_date'])?$_POST['from_date']:"";
	$mode=isset($_POST['mode'])?$_POST['mode']:1;
}




?>