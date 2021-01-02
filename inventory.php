<?php
include_once("dbcon.php");
include_once("functions.php");
include_once('session.php');
?>
<?php

if($permission<$MIN_ACCESS_LEVEL)
	header('Location: search_item.php');
/*

$item 			--> serial of item table
$lab 			--> serial of lab table
$another_lab 	--> serial of lab table(used in tranfer lab)
$cost 			--> of each item
$status 		--> serial of status table
$comments 		--> 
$operation 		--> serial of operation table
$iv_serial 		--> serial of inventory table
$product_code	--> 
$vendor 		--> serial of vendor table
$bill 			--> serial of receipt table
$amount 		--> amount of receipt table
$staff_serial 	--> serial of staff logged in ( set in session.php!)

serial in the sense the column serial of particular table in MySQL

*/
formInit();
$toastOutput=array();
if(isset($_POST['Submit'])){
	getPostData();
	escapeChars();
	switch ($operation) {
		case 1: //adding item to inventory
		addItem();
		break;
		case 3: 		//update item status 
		updateStatus();
		break;
		case 4:			//remove item from inventory
		removeItem();
		break;
		case 2:
		editItem();
		break;
		case 5:
		viewItem();
		break;
		case 6:
		viewAll();
		break;
		default:
		addToToast("Please select an operation",0);
		break;
	}
}

function addItem(){

// the item_name must be unique(it is set in db as unique, so no duplication is possible).
// this means, lets say last week i created a item with code x. then i deleted it.(set lock_item=1)
// today as that item_name doesnt exist anymore, i want to reuse it to another item. i cant do 
// insert item query as it will lead to unique index criteria erro. so i must update earlier row.
// current addItem function only sets lock_item=0, but doesnt update other rows.

	global $item, $lab, $item_name, $cost, $status, $comments, $operation, $connection, $result, $bill_serial,
	$iv_serial, $product_code, $tequip, $vendor,$comments_sql, $item_name_sql, $product_code_sql;
	if($item==-1||$lab==-1||$bill_serial==-1){
		addToToast("please select Lab/Vendor/Apparatus from dropdown",0);
	}
	else{ 
		/*			Inserting items one by one 				*/
		foreach ($item_name_sql as $key => $value) {
		 //checking if the item is presenet in the inventory
			if(!doesItemExist("inventory", "item_code", $item_name_sql[$key])){ //if item is not present in inventory
				$query="
				INSERT INTO 
				inventory (	lab_serial, 
				serial_item, 
				item_code,
				product_code, 
				tequip, 
				cost, 
				status,
				receipt_serial,
				last_operation
				) 
				values(
				{$lab},
				{$item}, 
				'{$item_name_sql[$key]}',
				'{$product_code_sql[$key]}',
				{$tequip}, 
				{$cost},
				{$status},
				{$bill_serial},
				{$operation}
			) ";
			perform_query($connection, $query, "failed to insert into inventory");
				 // to insert into inventory log, but we need serial of that item_code
			$iv_serial=mysqli_insert_id($connection);
			inventoryLog();
			addToToast("Created new apparatus of name {$item_name[$key]} of cost {$cost}",1);
		}
		else{ 		
			 //if item is already present, it may be an error
			 			//probability is that the item may have been locked and now needs to be unlocked
			$row=mysqli_fetch_assoc($result);
			if(1==$row['lock_item']){
				$date= date("Y-m-d H:i:s");
				$query="SELECT serial from inventory where item_code={$item_name_sql[$key]}";
				$result=perform_query($connection, $query, "failed to get serial");
				$row=mysqli_fetch_assoc($result);
				$iv_serial=$row['serial'];
				$query="UPDATE 
				inventory 
				set 
				lock_item=0, 
				updated='{$date}', 
				lab_serial={$lab}, 
				cost={$cost} 
				where 
				item_code='{$item_name_sql[$key]}'";
				perform_query($connection, $query, "failed to update");
				$comments.=" item was unlocked";
				inventoryLog();
				addToToast("{$comments}",1);
			}
			else
				addToToast("its already present ",0);
		}	
	}

} 
}

function updateStatus(){
	global $item, $lab, $item_name, $status, $comments, $operation, $connection, $result, 
	$iv_serial, $product_code, $tequip, $vendor, $purchase_date, $bill;
	$date= date("Y-m-d H:i:s");
	$lock_item=0;
	if(doesItemExist('inventory', 'serial', $iv_serial)){
		if($status==5){
			$operation=4; 		//delete
			$lock_item=1;
		}
		$query="UPDATE 
		inventory 
		set 
		status={$status}, 
		updated='{$date}', 
		lock_item={$lock_item},
		last_operation={$operation} 
		where 
		serial={$iv_serial}";
		perform_query($connection, $query, "failed to update inventory");
		//reflect it in log table too
		$lab=getLabSerial($iv_serial);
		inventoryLog();
		$query="SELECT operation from status where serial={$status}";
		$temp=mysqli_fetch_assoc(perform_query($connection, $query, "failed to get status"));
		$temp=$temp['operation'];
		addToToast("Status of item is {$temp}",1);
	}
	else{
		addToToast("Please select an Item to Update",0);
	}
}

function removeItem(){
	global $item, $lab, $item_name, $status, $comments, $operation, $connection, $result, 
	$iv_serial, $product_code, $tequip, $vendor, $purchase_date, $bill;
	if(doesItemExist("inventory", "serial", $iv_serial)){
		//this is super easy to implement just set lock to 1 and add a comment
		$date= date("Y-m-d H:i:s");
		$status=5;
		$query="UPDATE 
		inventory 
		set 
		lock_item=1 ,
		status=5,  
		updated='{$date}',
		last_operation={$operation} 
		where 
		serial='{$iv_serial}'";
		perform_query($connection, $query, "failed to update inventory");
		$comments.=". item is removed/locked";
		$lab=getLabSerial($iv_serial);
		inventoryLog();
		addToToast("Item is removed",0);
	}
	else{
		addToToast("Item doesn't exist",0);
	}

}

function editItem(){
	global $item, $lab, $item_name, $status, $comments, $operation, $connection, $result, $bill_serial,
	$iv_serial, $product_code, $tequip, $cost, $product_code_sql;
	if(doesItemExist("inventory", "serial", $iv_serial)){
		$item_name=array(getValue('inventory', 'item_code', 'serial', $iv_serial));
		$query="UPDATE 
		inventory 
		set  
		serial_item={$item}, 
		product_code='{$product_code_sql[0]}', 
		tequip={$tequip}, 
		cost={$cost}, 
		receipt_serial={$bill_serial},
		last_operation={$operation}
		where 
		serial={$iv_serial}";
		perform_query($connection, $query, "failed to edit inventory");
		addToToast("Successfully updated Inventory",1);
		$lab=getLabSerial($iv_serial);
		inventoryLog();
	}
	else
		addToToast("Item doesnt exist",0);
}

function viewItem(){
	global $item, $lab, $item_name, $status, $comments, $connection, $amount,$bill_serial,
	$iv_serial, $product_code, $tequip, $vendor, $purchase_date, $bill, $cost, $tableHead, $tableBody;
	if(doesItemExist("inventory", "serial", $iv_serial)){
		$query="SELECT * from inventory as iv, inventory_log as il, receipt as r where iv.serial={$iv_serial} 
		and iv.serial=il.inventory_serial and r.serial=iv.receipt_serial ORDER by il.serial DESC limit 1";
		$result=perform_query($connection, $query, "failed to fill form");
		$row=mysqli_fetch_assoc($result);
		$lab=$row['lab_serial'];
		$item_name=array(0=>$row['item_code']);
		$item=$row['serial_item'];
		$product_code=array(0=>$row['product_code']);
		$tequip=$row['tequip'];
		$status=$row['status'];
		$comments=$row['comments'];
		$cost=$row['cost'];
		$bill_serial=$row['receipt_serial'];
		$query="SELECT r.filepath from receipt as r, inventory as iv where r.serial=iv.receipt_serial and iv.serial={$iv_serial}";
		$result=perform_query($connection, $query, "failed to fill form");
		$row=mysqli_fetch_assoc($result);
		$bill=$row['filepath'];
		$tableHead=array('Item Code','Lab','Apparatus', 'Product Code', 'Vendor', 'Teqip', 'Purchase Date', 'Status');
		$query="SELECT 
		iv.item_code,
		l.lab_number,
		concat(i.name,' | ', m.name) as name,
		iv.product_code,
		v.name,
		(CASE 
		WHEN iv.tequip=0 THEN 'No'
		ELSE 'Yes'
		END) AS tequip,
		r.purchase_date,
		(CASE 
		WHEN iv.status=2 then 'Broken'
		WHEN iv.status=3 then 'Under Repair'
		WHEN iv.status=4 then 'Working'
		WHEN iv.status=5 then 'removed'
		ELSE 'Working'
		END) AS status 
		from 
		inventory as iv,
		item as i, 
		manufacturer as m ,
		lab as l,
		vendor as v,
		receipt as r
		where 
		i.serial=iv.serial_item AND
		l.serial=iv.lab_serial AND
		v.serial=r.vendor_serial AND
		m.serial=i.manufacturer_serial AND
		r.serial=iv.receipt_serial AND
		iv.serial={$iv_serial}";
		//echo $query;
		$tableBody=mysqli_fetch_all(perform_query($connection, $query, "failed to fill table"));

	}
	else
		addToToast("Item doesnt exist",0);
}

function viewAll(){
	global $item, $lab, $item_name, $status, $comments, $connection, $fileName,
	$iv_serial, $product_code, $tequip, $vendor, $purchase_date, $bill, $cost, $tableHead, $tableBody, $staff_id, $permission;
	$tableHead=array('Item Code','Lab','Apparatus', 'Product Code', 'Vendor', 'Teqip', 'Purchase Date', 'Status','Bill');
	$labs=getLabForStaff($staff_id, $permission);
	if(empty($labs)){
		addToToast("No Associated Lab/Item found",0);
	}
	else{
		$query="
		SELECT 
		iv.item_code,
		l.lab_number,
		concat(i.name,' | ', m.name) as name,
		iv.product_code,
		v.name,
		(CASE 
		WHEN iv.tequip=0 THEN 'No'
		ELSE 'Yes'
		END) AS tequip,
		r.purchase_date,
		(CASE 
		WHEN iv.status=2 then 'Broken'
		WHEN iv.status=3 then 'Under Repair'
		WHEN iv.status=4 then 'Working'
		WHEN iv.status=1 then 'Working'
		END) AS status,
		r.filepath 
		from 
		inventory as iv,
		item as i, 
		manufacturer as m ,
		lab as l,
		vendor as v,
		receipt as r
		where 
		i.serial=iv.serial_item AND
		l.serial=iv.lab_serial AND
		r.serial=iv.receipt_serial AND
		r.vendor_serial=v.serial AND 
		m.serial=i.manufacturer_serial AND 
		iv.status<>5 and ";
		$query.="iv.lab_serial in(";
		$query.=implode(',',$labs);
		$query.=') ORDER BY iv.lab_serial ASC, r.purchase_date DESC';
		$tableBody=mysqli_fetch_all(perform_query($connection, $query, "failed to fill table"));
		$fileName=uniqid("inv");
		$file=fopen('reports/'.$fileName.'.'.'csv', 'w');
		fputcsv($file, $tableHead);
		foreach ($tableBody as $key => $value) {
			//8 is index of bill, 0 is of item_code
			$tableBody[$key][8]="<a class='btn waves-light waves-effect' href=\"{$value[8]}\" download=\"{$value[0]}.pdf\" ";
			if($value[8]==NULL)
				$tableBody[$key][8].="disabled ";
			$tableBody[$key][8].= ">		Download Bill
			<i class='material-icons right'>file_download</i>
			</a>";
			fputcsv($file, $value);
		}
	}
	

}

function inventoryLog(){
	global $item, $lab, $item_name, $status, $comments, $operation, $connection, $result,
	$iv_serial, $product_code, $tequip, $vendor, $purchase_date, $bill, $staff_serial;	
	$comments_sql=mysqli_real_escape_string($connection, $comments);
	$query="insert into inventory_log (lab_serial, inventory_serial, status,operation_serial, comments, staff_id) 
	values({$lab}, '{$iv_serial}', {$status},{$operation},'{$comments_sql}', {$staff_serial}) ";
	perform_query($connection, $query, "failed to insert into log");
}

function getLabSerial($iv_serial){
	global $connection;
	$query="select lab_serial from inventory where serial={$iv_serial}";
	$result=perform_query($connection, $query, "get lab details");
	$row=mysqli_fetch_assoc($result);
	return $row['lab_serial'];
}

function escapeChars(){
	global $connection, $comments, $item_name, $product_code,
	$comments_sql, $item_name_sql, $product_code_sql;
	$item_name_sql=array();
	$product_code_sql=array();
	$comments_sql=mysqli_real_escape_string($connection, $comments);
	foreach ($item_name as $key => $value) {
		array_push($item_name_sql,mysqli_real_escape_string($connection, $value));
	}
	foreach ($product_code as $key => $value) {
		array_push($product_code_sql,mysqli_real_escape_string($connection, $value));
	}

}



?>
<!DOCTYPE html>
<html>
<head>
	<title>Inventory</title>
	<link rel="icon" href="favicon.ico" type="image/x-icon" />
	<link rel = "stylesheet" 
	href = "iconfont/material-icons.css">
	<link rel="stylesheet" href="materialize/css/materialize.css">
	<!-- 	<link rel="stylesheet" type="text/css" href="nav_bar.css"> -->
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
	<header><?php displayNavbar('inventory.php');?></header>
	<main>

		<?php
		/*
				display a nice table if view option is selected
		*/
		if(isset($_POST['Submit'])&&($operation==5)&&!empty($tableBody)){	//5==view item, then display a row
			echo"<div class='container grey lighten-5 z-depth-2'>";
			outputTable($tableHead, $tableBody);
			echo("<Br/>");
			echo "	
			<p class='center-align'>
			<a class='btn waves-light waves-effect' href=\"{$bill}\" download=\"{$item_name[0]}.pdf\" ";
			if(is_null($bill))
				echo "disabled ";
			echo ">
			Download Bill
			<i class='material-icons right'>file_download</i>
			</a>
			</p>
			<Br>";
			echo "</div><Br/>";
		}

		if(isset($_POST['Submit'])&&($operation==6)&&!empty($tableBody)){ //6==view all items
			echo"<div class='container grey lighten-5 z-depth-2'>";
			outputTable($tableHead, $tableBody);
			echo "
			<p class='center-align'>
			<a href=\"reports\\{$fileName}.csv\" class='btn waves-light waves-effect' download='inventory.csv'>
			Download
			<i class='material-icons right'>file_download</i>
			</a>
			</p>
			<Br>";
			echo "</div><Br/>";
		}
		?>

		<div class="container grey lighten-5">		
			<form class="col s12 z-depth-2 grey lighten-5" action="inventory.php" method="POST" enctype="multipart/form-data" name="invForm">
				<div class="container">
					<div class="row">
						<br/>
						<span class="center"><h5 class="grey-text text-darken-2 ">Inventory</h5></span>
						<div class="input-field col s12">
							<?php
							outputDropdownFromArray(
								array(
									array(1,"Create Item"),
									array(2,"Edit Item"),
									array(3,"Update Status"),
									array(4,"Remove Item"),
									array(5,"View Item"), 
									array(6,"View All")),
								"operation", -1);
								?>
								<label for="operation">Select Operation</label>
						</div>
						<div class="input-field col s12" id=iv_serial_row >
							<?php
						//list only items from labs associated to them
							$lab_serial=getLabForStaff($staff_serial, $permission);
							if(empty($lab_serial))
								$lab_serial=array(-1); 		//otherwise sql will be messed up
							$query="SELECT 
							iv.serial, 
							CONCAT(iv.item_code, ' | ', i.name, ' | ', m.name),
							l.serial, 
							CONCAT(l.lab_name, ' | ', l.lab_number) as lab_name
							from 
							inventory as iv, 
							item as i, 
							manufacturer as m,
							lab as l
							where 
							iv.serial_item=i.serial and 
							l.serial=iv.lab_serial and
							m.serial=i.manufacturer_serial and 
							iv.lock_item=0 AND ";
							$query.="iv.lab_serial in(";
							$query.=implode(',',$lab_serial);
							$query.=')
							ORDER BY iv.lab_serial ASC';
							$result=perform_query($connection, $query, "failed to fetch items");
							outputDropdownOptGrp($result, "iv_serial", $iv_serial);
							?>
						<label>Item</label>
					</div>
					<div id=add_only>	
						<div class="input-field col s12 m6 l6 " id=apparatus_row>
							<?php
							$query="SELECT 
							i.serial, 
							concat(i.name,' | ', i.version, ' | ', m.name) as name 
							from 
							item as i, 
							manufacturer as m 
							where 
							m.Serial=i.manufacturer_serial AND
							i.lock_item=0 
							order by 
							i.serial";
							$result=perform_query($connection, $query, "failed to get apparatus");
							outputDropdown($result, "item", $item);
							?>
							<label>Apparatus</label>
						</div>
						<div class="col s12 m4 l4 input-field" id=lab_row>
							<?php
							//show only their labs
							$query="SELECT
							serial, 
							concat(lab_name, ' | ', lab_number) as name 
							from 
							lab 
							where 
							lock_lab=0 AND
							(staff_incharge={$staff_serial} OR lab_incharge={$staff_serial} OR {$permission}>1)";
							$result=perform_query($connection, $query, "failed to get lab");
							outputDropdown($result, "lab", $lab);
							?>
							<label>Lab</label>					
						</div>
						<div class=" col  s12 m2 l2" id="tequip_row">
							<?php
							outputRadioButton(array('0' => "Non-Teqip",'1'=>"Teqip" ),"tequip", $tequip);
							?>
						</div>
						<div >  
							<div class="col s12 m12 l6 input-field" id=cost_row >
								<?php 
								outputNumericField("cost", $cost, 1); ?>
								<label>Cost of Each Apparatus</label>
							</div>
							<div class="input-field col s12 m12 l6" id="bill_row">
								<?php
								$query="SELECT 
								r.serial, 
								CONCAT(r.serial,') ',v.name, ' | Rs.', r.amount, ' | ', r.purchase_date) as receipts
								from 
								vendor as v, 
								receipt as r
								where 
								v.serial=r.vendor_serial AND
								r.lock_receipt=0 
								ORDER BY r.serial DESC";
								outputDropdown(
									perform_query($connection, $query, "failed to get receipt"), 
									"billSerial",
									$bill_serial
								);
								?>
								<label>Select a Receipt</label>
							</div>
						</div>
						<div id="uniqueToItemContainter">
							<div id="uniqueToItem">
								<div class="input-field col s6 " >
									<?php
									outputTextField("item_name[]", $item_name[0], 60,1);
									?>
									<label>Item Code</label>
								</div>
								<div class="input-field col s5 m5 l5" >
									<?php
									outputTextField("product_code[]", $product_code[0], 60,1);
									?>
									<label>Product Serial</label>
								</div>
								<div class="col s1 input-field">
									<button type="button" class="btn waves-light waves-effect disabled " id="button" value="remove" onclick="removeRow(this.id)">
										<i class="material-icons">delete</i>
									</button>
								</div>
							</div>
						</div>
						<div class="col s12 center-align " id='add_button'>
							<button type="button" class="btn waves-light waves-effect green" value="Add" onclick="addRow()">
								<i class="material-icons">add</i>
							</button>
						</div>
					</div>
					<div class="col s12 m12 l12 input-field" id=status_row>
						<?php
						$query="select serial, operation from status";
						$result=perform_query($connection, $query, "failed to get status");
						outputDropdown($result, "status", $status);
						?>
						<label>Status</label>
					</div>
					<div class="col s12 m12 l12 input-field" id=comments_row>
						<?php outputTextField("comments", $comments, 100,0); ?>
						<label>Comments</label>	
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
		<Br/><Br/>
	</main>
	<?php displayFooter();?>
	<script type = "text/javascript"
	src = "jquery-2.1.1.min.js"></script>  
	<!-- Compiled and minified JavaScript -->
	<script src="materialize/js/materialize.js"></script>
	<script type="text/javascript">
		<?php
		outputToast();
		?>

		$(document).ready(function() {
			// $('input, textarea').characterCounter();
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

		var counter=1;
		function addRow(){
			var original=document.getElementById('uniqueToItem');
			var clone=original.cloneNode(true);
			clonedId='uniqueToItem'+ ++counter;
			clone.id=clonedId;
			var outerDiv=document.getElementById('uniqueToItemContainter');
			outerDiv.appendChild(clone);
			clone.getElementsByTagName('input')[0].value="";
			clone.getElementsByTagName('input')[1].value="";
			clone.getElementsByTagName('button')[0].id=counter;
			clone.getElementsByTagName('button')[0].className="btn waves-light waves-effect red";
		}

		function removeRow(id){
			var element =document.getElementById('uniqueToItem'+id);
			element.parentNode.removeChild(element);
		}
		
		function showDiv(elem){
			var name=elem.name;
			var value=elem.value;
			if(name=="operation"){

				if(value=="1")
					addingItem();
				else if(value=="3")
					updatingItem();		
				else if(value=="4")
					removingItem();
				else if(value=="2")
					editingItem();
				else if(value=="5")
					viewingItem();
				else if (value=="6")
					viewAll();
			}
		}

		function add_edit(){
			document.getElementById('lab_row').style.display="block";
			document.getElementById('add_only').style.display="block";
			document.getElementById('status_row').style.display="none";
			document.getElementById('comments_row').style.display="block";
			document.getElementById('item').setAttribute("required", "");
			document.getElementById('product_code[]').setAttribute("required", "");
			document.getElementById('cost').setAttribute("required", "");
			document.getElementById('item_name[]').setAttribute("required", "");
			document.getElementById('status').removeAttribute("required");
			document.getElementById('comments').removeAttribute("required");
			document.getElementById('item_name[]').removeAttribute("disabled");
		}

		function addingItem(){
			add_edit();
			document.getElementById('item_name[]').removeAttribute("required");
			document.getElementById('iv_serial').removeAttribute("required");
			document.getElementById('iv_serial_row').style.display="none";
			document.getElementById('add_button').style.display="block";
		}

		function editingItem(){
			add_edit();
			document.getElementById('item_name[]').setAttribute("disabled", "true");
			document.getElementById('iv_serial').setAttribute("required", "");
			document.getElementById('iv_serial_row').style.display="block";
			document.getElementById('add_button').style.display="none";
		}

		function otherOperation(){
			document.getElementById('iv_serial_row').style.display="block";
			document.getElementById('lab_row').style.display="none";
			document.getElementById('add_only').style.display="none";
			document.getElementById('status_row').style.display="none";
			document.getElementById('comments_row').style.display="block";
			$("input").removeAttr("required");
			document.getElementById('iv_serial').setAttribute("required", "");
			document.getElementById('lab').removeAttribute("required");
			document.getElementById('item').removeAttribute("required");
			document.getElementById('product_code[]').removeAttribute("required");
			document.getElementById('cost').removeAttribute("required");
			document.getElementById('item_name[]').removeAttribute("required");
			document.getElementById('status').removeAttribute("required");
			document.getElementById('comments').setAttribute("required","");
		}

		function removingItem(){
			otherOperation();
		}


		function updatingItem(){
			otherOperation();
			document.getElementById('status_row').style.display="block";
			document.getElementById('status').setAttribute("required","");
		}

		function viewingItem(){
			otherOperation();
			document.getElementById('comments_row').style.display="none";
			document.getElementById('comments').removeAttribute("required");
		}

		function viewAll(){
			viewingItem();
			document.getElementById('iv_serial').removeAttribute("required");
			document.getElementById('iv_serial_row').style.display="none";

		}
	</script>
</body>
</html>

<?php
function formInit(){
	global $item, $lab, $item_name, $cost, $status, $comments,
	$operation, $iv_serial, $product_code, $tequip, $bill_serial;
	$item=-1;
	$lab=-1;
	$item_name=array(0=>"");
	$cost="";
	$status=1; 				//default is operational
	$comments="";
	$operation=-1;
	$iv_serial=-1;
	$product_code=array(0=>"");
	$tequip=0;
	$vendor=-1;
	$purchase_date="2018-01-15";
	$bill="";
	$amount="";
	$bill_serial=-1;
	$haveBill=0;
}

function getPostData(){
	global $item, $lab, $item_name, $cost, $status, $comments,
	$operation, $iv_serial, $product_code, $tequip, $bill_serial;
	$item=isset($_POST['item'])?$_POST['item']:-1;
	$lab=isset($_POST['lab'])?$_POST['lab']:-1;
	$item_name=isset($_POST['item_name'])?$_POST['item_name']:array(0=>"");
	$cost=isset($_POST['cost'])?$_POST['cost']:0;
	$status=isset($_POST['status'])?$_POST['status']:1;   		//default status is operational
	$comments=isset($_POST['comments'])?$_POST['comments']:"";
	$operation=isset($_POST['operation'])?$_POST['operation']:-1;
	$iv_serial=isset($_POST['iv_serial'])?$_POST['iv_serial']:-1;
	$product_code=isset($_POST['product_code'])?$_POST['product_code']:array(0=>"");
	$tequip=isset($_POST['tequip'])?$_POST['tequip']:0;
	$bill_serial=isset($_POST['billSerial'])?$_POST['billSerial']:-1;
}



?>