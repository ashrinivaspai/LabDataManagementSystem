<?php
include_once("dbcon.php");
include_once("functions.php");
include_once('session.php');
?>
<?php

/*

$item 			--> serial of item table
$lab 			--> serial of lab table
$another_lab 	--> serial of lab table(used in tranfer lab)
$cost 			--> 
$status 		--> serial of status table
$comments 		--> 
$operation 		--> serial of operation table
$iv_serial 		--> serial of inventory table
$product_code	--> 
$vendor 		--> serial of vendor table
$bill 			--> unused as of now

serial in the sense the column serial of particular table in MySQL

*/
formInit();
if(isset($_POST['Submit'])){
	getPostData();
	switch ($operation) {
		case 1: //adding item to inventory
			addItem();
			break;
		case 2: 		//update item status 
			updateItem();
			break;
		case 3: 		//transfer item to another lab
			transferItem();
			break;
		case 4:			//remove item from inventory
			removeItem();
			break;
		case 5:
			editItem();
			break;
		case 6:
			viewItem();
			break;
		default:
			outputAlertBox("dont mess up buddy");
			break;
	}
}

function addItem(){

// the item_name must be unique(it is set in db as unique, so no duplication is possible).
// this means, lets say last week i created a item with code x. then i deleted it.(set lock_item=1)
// today as that item_name doesnt exist anymore, i want to reuse it to another item. i cant do 
// insert item query as it will lead to unique index criteria erro. so i must update earlier row.
// current addItem function only sets lock_item=0, but doesnt update other rows.

	global $item, $lab, $item_name, $cost, $status, $comments, $operation, $connection, $result,
	 $iv_serial, $product_code, $tequip, $vendor, $purchase_date, $bill;
	 $billPath = 'uploads/temp.pdf';
	 if(!move_uploaded_file($_FILES['bill']['tmp_name'], $billPath))
	 	echo "error";
	 foreach ($item_name as $key => $value) {
		 //checking if the item is presenet in the inventory
		 if(!doesItemExist("inventory", "item_code", $item_name[$key])){ //if item is not present in inventory
		 	$query="INSERT INTO 
		 				inventory (	lab_serial, 
		 							serial_item, 
		 							item_code,
		 							product_code, 
		 							tequip, 
		 							cost, 
		 							vendor_serial, 
		 							purchase_date, 
		 							status
		 						) 
		 				values 	(	{$lab},
		 							{$item}, 
		 							'{$item_name[$key]}',
		 							'{$product_code[$key]}',
		 							{$tequip}, 
		 							{$cost},
		 							{$vendor}, 
		 							'{$purchase_date}', 
		 							{$status}
		 						) ";
		 	perform_query($connection, $query, "failed to insert into inventory");
		 	$destinationPath='uploads/'.$item_name[$key].'.pdf';
		 	copy($billPath, $destinationPath);
		 	//insert into inventory log, but we need serial of that item_code
		 	$query="SELECT serial from inventory where item_code='{$item_name[$key]}'";
		 	$result=perform_query($connection, $query, "failed to get serial");
		 	$row=mysqli_fetch_assoc($result);
		 	$iv_serial=$row['serial'];
		 	inventoryLog();
		 	outputAlertBox("Created new apparatus of name {$item_name[$key]} of cost {$cost}");
		 }
		 else{ 		//if item is already present, it may be an error
		 	//probability is that the item may have been locked and now needs to be unlocked
		 	$row=mysqli_fetch_assoc($result);
		 	if(1==$row['lock_item']){
		 		$date= date("Y-m-d H:i:s");
		 		$query="SELECT serial from inventory where item_code={$item_name[$key]}";
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
		 					item_code='{$item_name[$key]}'";
		 		perform_query($connection, $query, "failed to update");
		 		$comments.=" item was unlocked";
		 		inventoryLog();
		 		outputAlertBox("{$comments}");
		 	}
		 	else
		 		outputAlertBox("its already present ");
		 }	
	 }
}

function updateItem(){
	global $item, $lab, $item_name, $status, $comments, $operation, $connection, $result, 
	$iv_serial, $product_code, $tequip, $vendor, $purchase_date, $bill;
	$date= date("Y-m-d H:i:s");
	//updating is only status, as of now no other attributes are editable
	$query="UPDATE inventory set status={$status}, updated='{$date}' where serial={$iv_serial}";
	perform_query($connection, $query, "failed to update inventory");
	//reflect it in log table too
	$lab=getLabSerial($iv_serial);
	inventoryLog();
	outputAlertBox("updated with status {$status}");

}

function transferItem(){
	global $item, $lab, $item_name, $status, $comments, $operation, $connection, $result, $another_lab, 
	$iv_serial, $product_code, $tequip, $vendor, $purchase_date, $bill;
	if(doesItemExist('inventory', 'serial', $iv_serial)){
		$date= date("Y-m-d H:i:s");
		$query="UPDATE inventory set lab_serial={$another_lab}, updated='{$date}' 
				where lab_serial={$lab} and serial={$iv_serial}";
		$result=perform_query($connection, $query, "couldnt modify lab");
		$comments.=". transferred to {$another_lab} from {$lab}";
		$lab=$another_lab; 	//otherwise lab in log table will be old lab itself
		inventoryLog();
		outputAlertBox("item was transferred to {$lab}");
	}
	else
		outputAlertBox("item doesnt exist");
}

function removeItem(){
	global $item, $lab, $item_name, $status, $comments, $operation, $connection, $result, 
	$iv_serial, $product_code, $tequip, $vendor, $purchase_date, $bill;
	if(doesItemExist("inventory", "serial", $iv_serial)){
		//this is super easy to implement just set lock to 1 and add a comment
		$date= date("Y-m-d H:i:s");
		$query="UPDATE 
					inventory 
				set 
					lock_item=1 ,  
					updated='{$date}' 
				where 
					serial='{$iv_serial}'";
		perform_query($connection, $query, "failed to update inventory");
		$comments.=". item was removed/locked";
		$lab=getLabSerial($iv_serial);
		inventoryLog();
		outputToast("item was removed");
	}
	else{
		outputToast('item doesnt exist');
	}

}

function editItem(){
	global $item, $lab, $item_name, $status, $comments, $operation, $connection, $result,
	 $iv_serial, $product_code, $tequip, $vendor, $purchase_date, $bill, $cost;
	$query="UPDATE 
				inventory 
			set 
				lab_serial={$lab}, 
				serial_item={$item}, 
				item_code='{$item_name}', 
				product_code='{$product_code}', 
				tequip={$tequip}, 
				cost={$cost}, 
				vendor_serial={$vendor}, 
				purchase_date='{$purchase_date}', 
				status={$status} 
			where 
				serial={$iv_serial}";
	perform_query($connection, $query, "failed to edit inventory");
	outputAlertBox("success in updating inventory");
}

function viewItem(){
	global $item, $lab, $item_name, $status, $comments, $operation, $connection, $result, 
	$iv_serial, $product_code, $tequip, $vendor, $purchase_date, $bill, $cost;
	$query="SELECT * from inventory as i, inventory_log as il where i.serial={$iv_serial} 
			and i.serial=il.inventory_serial ORDER by il.serial DESC limit 1";;
	$result=perform_query($connection, $query, "failed to fill form");
	$row=mysqli_fetch_assoc($result);
	print_r($row);
	$lab=$row['lab_serial'];
	$item_name=array(0=>$row['item_code']);
	$item=$row['serial_item'];
	$product_code=array(0=>$row['product_code']);
	$tequip=$row['tequip'];
	$cost=$row['cost'];
	$vendor=$row['vendor_serial'];
	$purchase_date=$row['purchase_date'];
	$status=$row['status'];
	$comments=$row['comments'];
}

function inventoryLog(){
	global $item, $lab, $item_name, $status, $comments, $operation, $connection, $result,
	 $iv_serial, $product_code, $tequip, $vendor, $purchase_date, $bill, $staff_serial;	
	$query="insert into inventory_log (lab_serial, inventory_serial, status, comments, staff_id) 
	values({$lab}, '{$iv_serial}', {$status}, '{$comments}', {$staff_serial}) ";
	perform_query($connection, $query, "failed to insert into log");
}

function getLabSerial($iv_serial){
	global $connection;
	$query="select lab_serial from inventory where serial={$iv_serial}";
	$result=perform_query($connection, $query, "get lab details");
	$row=mysqli_fetch_assoc($result);
	return $row['lab_serial'];
}

?>
<!DOCTYPE html>
<html>
<head>
	<title>Inventory</title>
	<link rel="icon" href="favicon.ico" type="image/x-icon" />
	<link rel = "stylesheet" 
	href = "https://fonts.googleapis.com/icon?family=Material+Icons">
	<link rel="stylesheet" href="materialize/css/materialize.css">
	<link rel="stylesheet" type="text/css" href="nav_bar.css">
	<script type = "text/javascript"
	src = "jquery-2.1.1.min.js"></script>  
    	<!-- Compiled and minified JavaScript -->
    	<script src="materialize/js/materialize.js"></script>
         
	<style type="text/css">
	.container {
		width: 90%;
		height: 90%;
	}
</style>
</head>
<body class="">
	<div class="center-align">
		<h2>NMAMIT</h2>	
	</div>
	<div class="row ">
		<nav>
			<div class="nav-wrapper teal">
				<ul class="right">
					<li><a href="item.php">Apparatus</a></li>
					<li class="active"><a href="inventory.php">Inventory</a></li>
					<li><a href="lab.php">Lab</a></li>
					<li><a href="report.php">Reports</a></li>
					<li><a href="logout.php">Logout</a></li>
					<!-- put rest of the tabs here -->
				</ul>
			</div>
		</nav>
	</div>
	<div class="container">
		<form class="col s12 z-depth-2" action="inventory.php" method="POST" enctype="multipart/form-data" name="invForm">
			<div class="container">
				<div class="row">
					<br/>
					<div class="input-field col s12">
						<?php
						$query="select serial, operation from operations order by operation ";
						$result=perform_query($connection, $query, "failed to get operations");
						outputDropdown($result, "operation", -1);
						?>
						<label for="operation">Select Operation</label>
					</div>
					<div class="input-field col s12" id=iv_serial_row >
						<?php
						//we have to actually list only the items which are in lab associated with the staff, but for now all will do
						$query="SELECT 
									iv.serial, 
									CONCAT(iv.item_code, ' | ', i.name, ', ', m.name) 
								from 
									inventory as iv, 
									item as i, 
									manufacturer as m 
								where 
									iv.serial_item=i.serial and 
									m.serial=i.manufacturer_serial and 
									iv.lock_item=0";
						$result=perform_query($connection, $query, "failed to fetch items");
						outputDropdown($result, "iv_serial", $iv_serial);
						?>
						<label>Item</label>
					</div>
					<div class =row id=labs>
						<div class="col s12 m6 l6 input-field" id=lab_row>
							<?php
							$query="SELECT
										serial, 
										concat(lab_name, ' | ', lab_number) as name 
									from 
										lab 
									where 
										lock_lab=0";
							$result=perform_query($connection, $query, "failed to get lab");
							outputDropdown($result, "lab", $lab);
							?>
							<label>Lab</label>					
						</div>
						<div class="col s12 m6 l6 input-field" id=another_lab_row >
							<?php
							$query="SELECT 
										serial, 
										concat(lab_name, ' | ', lab_number) as name 
									from 
										lab 
									where 
										lock_lab=0";
							$result=perform_query($connection, $query, "failed to get lab");
							outputDropdown($result, "another_lab", $another_lab);
							?>
							<label>To lab</label>
						</div>
					</div>
					<div id=add_only>	
						<div class="input-field col s12 m6 l6 " id=apparatus_row>
							<?php
							$query="SELECT 
										i.serial, 
										concat(i.name,' ', i.version, ', ', m.name) as name 
									from 
										item as i, 
										manufacturer as m 
									where 
										m.Serial=i.manufacturer_serial 
									order by 
										i.serial";
							$result=perform_query($connection, $query, "failed to get apparatus");
							outputDropdown($result, "item", $item);
							?>
							<label>Apparatus</label>
						</div>
						<div class="input-field col s12 m4 l4" id="vendor_row">
							<?php
							$query="SELECT serial, name from vendor";
							$result=perform_query($connection, $query, "failed to get vendor");
							outputDropdown($result, "vendor", $vendor);
							?>
							<label>Vendor</label>
						</div>
						<div class=" col  s12 m2 l2" id="tequip_row">
							<?php
							outputRadioButton(array('0' => "Non-Tequip",'1'=>"Tequip" ),"tequip", $tequip);
							?>
						</div>
						<div class="file-field input-field col s12 m12 l6" id="upload_row">
							<div class="btn">
								<span>Browse</span>
								<input type="file" name="bill" id=bill >
							</div>
							<div class="file-path-wrapper">
								<input type="text" class="file-path" placeholder="Upload Receipt" >
							</div>
						</div>
						<div class="input-field col s12 m6 l4" id=date_purchase_row>
							<?php
							outputDateField('purchase_date',$purchase_date,1);
							?>
							<label>Date of Purchase</label>
						</div>
						<div class="col s12 m6 l2 input-field" id=cost_row>
							<?php 
							outputNumericField("cost", $cost, 1); ?>
							<label>Cost</label>
						</div>
						<div id="uniqueToItemContainter">
							<div id="uniqueToItem">
								<div class="input-field col s6 " >
									<?php
									outputTextField("item_name[]", $item_name[0], 1);
									?>
									<label>Item Code</label>
								</div>
								<div class="input-field col s5 m5 l5" >
									<?php
									outputTextField("product_code[]", $product_code[0], 1);
									?>
									<label>Product Serial</label>
								</div>
								<div class="col s1  ">
									<button type="button" class="btn waves-light waves-effect disabled" id="button" value="remove" onclick="removeRow(this.id)">	-
									</button>
								</div>
							</div>
						</div>
						<div class="col s12 center-align ">
							<button type="button" class="btn waves-light waves-effect" value="Add" onclick="addRow()">Add another row</button>
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
						<?php outputTextField("comments", $comments, 0); ?>
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
</body>
<Br/><Br/>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var elems = document.querySelectorAll('select');
    var instances = M.FormSelect.init(elems, {});
  });

 
</script>
	<script>
		document.addEventListener('DOMContentLoaded', function() {
    		var elems = document.querySelectorAll('.datepicker');
    		var instances = M.Datepicker.init(elems, {
    			format:'yyyy-mm-dd'
    		});
  		});
	</script>
	<script type="text/javascript">
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
			clone.getElementsByTagName('button')[0].className="btn waves-light waves-effect";
		}

		function removeRow(id){
			var element =document.getElementById('uniqueToItem'+id);
			element.parentNode.removeChild(element);
		}

	</script>
<script type="text/javascript">
	function showDiv(elem){
		var name=elem.name;
		if(name=="operation"){
			var value=elem.value;
			if(value=="1")
				addingItem();
			else if(value=="2")
				updatingItem();
			else if(value=="3")
				transferringItem();
			else if(value=="4")
				removingItem();
			else if(value=="5")
				editingItem();
			else if(value=="6")
				viewingItem();
		}
	}

	function add_edit(){
		document.getElementById('lab_row').style.display="block";
		document.getElementById('labs').style.display="block";
		document.getElementById('another_lab_row').style.display="none";
		document.getElementById('add_only').style.display="block";
		document.getElementById('status_row').style.display="block";
		document.getElementById('comments_row').style.display="block";
		document.getElementById('lab').setAttribute("required", "");
		document.getElementById('another_lab').removeAttribute("required");
		document.getElementById('item').setAttribute("required", "");
		document.getElementById('product_code[]').setAttribute("required", "");
		document.getElementById('vendor').setAttribute("required", "");
		document.getElementById('cost').setAttribute("required", "");
		document.getElementById('item_name[]').setAttribute("required", "");
		document.getElementById('purchase_date').setAttribute("required", "");
		document.getElementById('status').removeAttribute("required");
		document.getElementById('comments').removeAttribute("required");

	}

	function addingItem(){
		add_edit();
		document.getElementById('iv_serial').removeAttribute("required");
		document.getElementById('iv_serial_row').style.display="none";
		document.getElementById('bill').setAttribute("required", "");
	}

	function editingItem(){
		add_edit();
		document.getElementById('iv_serial').setAttribute("required", "");
		document.getElementById('iv_serial_row').style.display="block";
		document.getElementById('bill').removeAttribute("required");
	}

	function otherOperation(){
		document.getElementById('iv_serial_row').style.display="block";
		document.getElementById('lab_row').style.display="none";
		document.getElementById('labs').style.display="none"
		document.getElementById('another_lab_row').style.display="none";
		document.getElementById('add_only').style.display="none";
		document.getElementById('status_row').style.display="none";
		document.getElementById('comments_row').style.display="block";
		$("input").removeAttr("required");
		document.getElementById('iv_serial').setAttribute("required", "");
		document.getElementById('lab').removeAttribute("required");
		document.getElementById('another_lab').removeAttribute("required");
		document.getElementById('item').removeAttribute("required");
		document.getElementById('product_code[]').removeAttribute("required");
		document.getElementById('vendor').removeAttribute("required");
		document.getElementById('cost').removeAttribute("required");
		document.getElementById('item_name[]').removeAttribute("required");
		document.getElementById('purchase_date').removeAttribute("required");
		document.getElementById('status').removeAttribute("required");
		document.getElementById('comments').setAttribute("required","");
		document.getElementById('bill').removeAttribute("required");
	}

	function removingItem(){
		otherOperation();
	}

	function transferringItem(){
		otherOperation();
		document.getElementById('lab_row').style.display="block";
		document.getElementById('labs').style.display="block"
		document.getElementById('another_lab_row').style.display="block";
		document.getElementById('lab').setAttribute("required","");
		document.getElementById('another_lab').setAttribute("required","");
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
</script>
</html>

<?php
function formInit(){
	global $item, $lab,$another_lab, $item_name, $cost, $status, $comments,
	 $operation, $iv_serial, $product_code, $tequip, $vendor, $purchase_date, $bill;
	$item="";
	$lab="";
	$another_lab="";
	$item_name=array(0=>"");
	$cost="";
	$status=-1;
	$comments="";
	$operation=-1;
	$iv_serial=-1;
	$product_code=array(0=>"");
	$tequip=0;
	$vendor=-1;
	$purchase_date="2018-01-15";
	$bill="";
}

function getPostData(){
	global $item, $lab,$another_lab, $item_name, $cost, $status, $comments,
	 $operation, $iv_serial, $product_code, $tequip,$vendor, $purchase_date, $bill;
	$item=isset($_POST['item'])?$_POST['item']:"";
	$lab=isset($_POST['lab'])?$_POST['lab']:"";
	$another_lab=isset($_POST['another_lab'])?$_POST['another_lab']:"";
	$item_name=isset($_POST['item_name'])?$_POST['item_name']:array(0=>"");
	$cost=isset($_POST['cost'])?$_POST['cost']:0;
	$status=isset($_POST['status'])?$_POST['status']:1;
	$comments=isset($_POST['comments'])?$_POST['comments']:"";
	$operation=isset($_POST['operation'])?$_POST['operation']:-1;
	$iv_serial=isset($_POST['iv_serial'])?$_POST['iv_serial']:-1;
	$product_code=isset($_POST['product_code'])?$_POST['product_code']:array(0=>"");
	$tequip=isset($_POST['tequip'])?$_POST['tequip']:0;
	$vendor=isset($_POST['vendor'])?$_POST['vendor']:-1;
	$purchase_date=isset($_POST['purchase_date'])?$_POST['purchase_date']:"";
	$bill=isset($POST['bill'])?$_POST['bill']:"";
}

?>