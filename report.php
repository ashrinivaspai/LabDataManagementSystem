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
 *
 * And i say, lets give a "pro mode" where user can enter query. What say?
 *
 *****************************************************************************/
?>

<?php
if($permission<$MIN_ACCESS_LEVEL)
	header('Location: search_item.php');
/*
					variables used

	$file 			-> file handle
	$mode 			-> current or log mode (1 or 2)
	$lab 			-> array of lab serials
	$manufacturer 	-> array of manufacturer serials
	$category 		->  ""      category serials
	$item 			-> array of serials of item table
	$item_name 		-> array of serials of inventory table
	$to_date 		-> this is date of purchase(in current mode) or timestamp(log mode)
	$from_date 		-> same here
*/
?>

<!DOCTYPE html>
<html>
<head>
	<title>Reports</title>
	<link rel="icon" href="favicon.ico" type="image/x-icon" />
	<link rel = "stylesheet" href = "iconfont/material-icons.css">
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
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body class="grey lighten-4">
	<header><?php displayNavbar('report.php');?></header>
	<main>
		<div class="container grey lighten-5">
			<form action="report.php" method="POST" class="col s12 z-depth-2">
				<div class="container ">
					<br/><span class="center"><h5 class="grey-text text-darken-2 ">Generate Report</h5></span>
					<div class="row">
						<div class="input-field col s12 l6 m6">
							<?php 
							outputDropdownFromArray(array(array(1, 'Status'), array(2, 'Log')), "mode", 1); 
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
						<div class="input-field col s12 m12 l6">
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
						<div class="input-field col s12 m12 l6">
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
		formInit();
		$toastOutput=array();
		if(isset($_POST['Submit'])){
			$isEmpty=1;
			getPostData();
			$fileName='reports/'.uniqid("rep").'.csv';
			$file=fopen($fileName,'w');
			$query="SELECT serial, lab_name,lab_number from lab";
			$lab_names=mysqli_fetch_all(perform_query($connection, $query, "failed to fetch labs"), MYSQL_NUM);
			$lab_serial=getLabForStaff($staff_serial, $permission);
			if($mode==='1'){ 		//current mode
				if(!count($lab)) 		//if user hasnt selected any lab then load default
					$lab=$lab_serial;
				$headerRow=array('Item Code', 'Name|Make|Description', 'Cost','Status',
					'Latest Operation','Broken','Under Repair', 'Repaired' );
				fputcsv($file, $headerRow);
				foreach ($lab as $key => $value) {
					foreach ($lab_names as $i => $valueLab) {
						if($valueLab[0]==$value)
							$currentLab=$valueLab[1].' | '.$valueLab[2];
					}
					$query="SELECT 
								iv.item_code, 
								CONCAT(i.name,' | ' ,m.name,' | ',i.version) as Item, 
								iv.cost,
								(CASE 
									WHEN iv.status=5 then 'Removed'
									WHEN iv.status=2 then 'Broken'
									WHEN iv.status=3 then 'Under Repair'
									WHEN iv.status=4 then 'Working'
									ELSE 'Working'
								END) AS status,
								o.name as 'last operation',
								MAX(IF(il.status=2 AND il.operation_serial=3, il.`timestamp`, NULL)) as broken,
								MAX(IF(il.status=3 AND il.operation_serial=3, il.`timestamp`, NULL)) as 'under repair',
								MAX(IF(il.status=4 AND il.operation_serial=3, il.`timestamp`, NULL)) as repaired
							from 
								manufacturer as m, 
								item as i, 
								inventory_log as il, 
								inventory as iv, 
								lab as l, 
								category as c,
								operation as o,
								receipt as r
							where ";
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
							$query.="  ((il.timestamp between '{$from_date}' and '{$to_date}') 
										OR
										(r.purchase_date<='{$to_date}' and r.purchase_date>='{$from_date}')) and ";
						}
						else{
							$to_date=date_format($to_date,"Y-m-d");
							$from_date=date_format($from_date, "Y-m-d");
						}
					}

					$query.="
							il.lab_serial={$value} AND
							iv.serial=il.inventory_serial AND
							l.serial=il.lab_serial AND
							i.serial=iv.serial_item AND
							i.category_serial=c.serial and 
							o.serial=iv.last_operation and
							m.serial=i.manufacturer_serial AND
							il.lab_serial=iv.lab_serial AND
							r.serial=iv.receipt_serial
						GROUP BY
							iv.serial
						ORDER BY
							iv.serial ASC";
					$result=perform_query($connection, $query, "failed to report");
					$tableRow=mysqli_fetch_all($result, MYSQL_NUM);
					if(!empty($tableRow)){
						$isEmpty=0;
						fputcsv($file, array($currentLab));
						foreach ($tableRow as $i => $row) {
/*							foreach ($lab_names as $key => $value) {
								if($value[0]==$row[4]){
									$tableRow[$i][4]=$value[2];
								}
							}*/
							fputcsv($file, $tableRow[$i]);
						}
						echo "<div class='container z-depth-3 white'>";
						echo "<Br/><h6 class='center-align grey-text'>{$currentLab}</h6>";
						outputTable($headerRow, $tableRow);
						echo "</div><Br/>";	
					}
				}	
			}
			//if log mode
			else{
				$headerRow=array('Item Code', 'Lab Name| Code', 'Apparatus','Status','Operation','Comments','Staff','Timestamp');
				$query="SELECT 
							iv.item_code, 
							CONCAT(l.lab_name, ' | ', l.lab_number) as Lab,
							CONCAT(i.name,' | ' ,m.name,' | ',i.version) as Item,   
							s.operation,
							o.name,
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
							operation o,
							staff 
						where ";
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
				$query.=" 	
						i.serial=iv.serial_item and 
						i.manufacturer_serial=m.serial and 
						il.operation_serial=o.serial AND
						i.category_serial=c.serial and 
						il.status=s.serial and 
						il.inventory_serial=iv.serial and 
						l.serial=il.lab_serial and 
						staff.serial=il.staff_id 
					ORDER BY 
						iv.serial DESC,
						il.serial DESC";
				$result=perform_query($connection, $query, "failed to fetch report");
				if($result)
					$tableRow=mysqli_fetch_all($result, MYSQLI_NUM);
				fputcsv($file, $headerRow);
				if(!empty($tableRow)){
					$isEmpty=0;
					echo "<div class='container z-depth-3 white'>";
					outputTable($headerRow, $tableRow);
					echo "</div>";	
					foreach ($tableRow as $key => $value) {
						fputcsv($file, $value);
					}	
				}
			}
			if($isEmpty){
				addToToast("Sorry, No records found",0);
			}
			else
				echo "	
					<p class='center-align'>
					<a class='btn waves-light waves-effect' href=\"{$fileName}\" download='report.csv'>
						Download Report
						<i class='material-icons right'>file_download</i>
					</a>
					</p>";
		}
		function delete_col(&$array, $offset) {
		    return array_walk($array, function (&$v) use ($offset) {
		        array_splice($v, $offset, 1);
		    });
		}
		?>
		</div>
		<Br/><Br/>
	</main>
	<?php displayFooter();?>
		<script type = "text/javascript" src = "jquery-2.1.1.min.js"></script> 
    	<!-- Compiled and minified JavaScript -->
    	<script src="materialize/js/materialize.min.js"></script>   
		<script type="text/javascript">
			<?php
				outputToast();
			?>
			$(document).ready(function() {
				$('.sidenav').sidenav();
				$(".dropdown-trigger").dropdown({constrainWidth: false});
			});
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