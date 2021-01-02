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
			addBill();
			break;
		case 2:
			removeBill();
			break;
		case 3:
			updateBill();
			break;
		case 4:
			viewBill();
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
	<title>Receipt</title>
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
	<header><?php displayNavbar('receipt.php'); ?></header>
	<main>
		<div class="container grey lighten-5">
			<form action="receipt.php" method="POST" class="col s12 z-depth-2"  enctype="multipart/form-data">
				<div class="container ">
					<div class="row">
						<br><span class="center"><h5 class="grey-text text-darken-2 ">Receipts</h5></span>
						<div class="input-field col s12">
							<?php
							$operations = array(array(1,'Add') ,array(2,'Remove'), array(3,'Edit'), array(4,'View') );
							outputDropdownFromArray($operations, "operations", 1);
							?>
							<label>Select Operation</label>
						</div>
						<div class="input-field col s12" id=bill_row  style="display: none">
							<?php
							$query="SELECT 
										r.serial, 
										CONCAT(r.serial, ') ',v.name, ' | Rs.', r.amount, ' | ', r.purchase_date) as receipts
									from 
										vendor as v, 
										receipt as r
									where 
										r.lock_receipt=0 AND
										v.serial=r.vendor_serial
									ORDER BY
										r.serial desc";
							outputDropdown(
								perform_query($connection, $query, "failed to get receipt"), 
								"billSerial",
								$bill_serial
							);
							?>
							<label>Select a Receipt</label>
						</div>
						<div id="allAttirbutes">
							<div class="input-field col s12 m12 l6" id="vendor_row">
								<?php
								$query="SELECT serial, name FROM vendor WHERE lock_vendor=0";
								$result=perform_query($connection, $query, "failed to get vendor");
								outputDropdown($result, "vendor", $vendor);
								?>
								<label>Vendor</label>
							</div>
							<div class="input-field col s12 m12 l6" id=date_row>
								<?php
								outputDateField('purchase_date',$purchase_date,1);
								?>
								<label>Date of Purchase</label>
							</div>
							<div class="file-field input-field col s12 m12 l6" id="upload_row">
								<div class="btn">
									<span>
										Browse
										<i class="material-icons right">cloud_upload</i>
									</span>
									<input type="file" name="bill" id=bill >
								</div>
								<div class="file-path-wrapper">
									<input type="text" class="file-path" placeholder="Upload Receipt" >
								</div>
							</div>
							<div class="col s12 m12 l6 input-field" id=amount_row>
								<?php 
								outputNumericField("amount", $amount, 1); ?>
								<label>Bill Amount </label>
							</div>
							<div class="input-field col s12" id=details_row>
								<?php
								outputTextField("details", $details, 120);
								?>
								<label>Details</label>
							</div>
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

		document.addEventListener('DOMContentLoaded', function() {
			var elems = document.querySelectorAll('.datepicker');
			var instances = M.Datepicker.init(elems, {
				format:'yyyy-mm-dd'
			});
		});
		<?php
			outputToast();
		?>
		function showDiv(elem){
			var name=elem.name;
			if(name=='operations'){
				switch(elem.value){
					case "1": 		//add
						addingBill();
						break;
					case "2":
						removingBill();
						break;
					case "3":
						updatingBill();
						break;
					case "4":
						viewingBill();
						break;
				}
			}
		}

		function addingBill(){
			document.getElementById('bill_row').style.display="none";		//dont display already existing
			document.getElementById('allAttirbutes').style.display='block';
			document.getElementById('bill').removeAttribute("required"); 	//remove it from required
			document.getElementById('amount').setAttribute('required', "");
			document.getElementById('purchase_date').setAttribute('required', "");
		}
		function removingBill(){
			document.getElementById('bill_row').style.display="block";	
			document.getElementById('allAttirbutes').style.display='none';
			document.getElementById('amount').removeAttribute("required");
			document.getElementById('details').removeAttribute('required');	
			document.getElementById('purchase_date').removeAttribute('required');
		}

		function updatingBill(){
			addingBill();
			document.getElementById('bill_row').style.display="block"; 	
		}

		function viewingBill(){
			removingBill();
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
	global $bill_serial, $vendor, $details, $purchase_date, $bill, $operations, $amount;
	$operations=-1;
	$bill_serial=-1;
	$vendor=-1;
	$details="";
	$purchase_date="";
	$bill="";
	$amount="";
}

function getPostData(){
	//category and manufacturer contains serial numbers and not text value
	global $bill_serial, $vendor, $details, $purchase_date, $bill, $operations, $amount;
	$operations=$_POST['operations'];
	$bill_serial=isset($_POST['billSerial'])?$_POST['billSerial']:-1;
	$vendor=isset($_POST['vendor'])?$_POST['vendor']:$vendor;
	$purchase_date=isset($_POST['purchase_date'])?$_POST['purchase_date']:"";
	$details=isset($_POST['details'])?$_POST['details']:"";
	$bill=isset($POST['bill'])?$_POST['bill']:"";
	$amount=isset($_POST['amount'])?$_POST['amount']:0;
}

function addBill(){
	global $bill_serial, $vendor, $details, $purchase_date, $bill, $operations, $amount, $connection;
	escapeChars();
	if($vendor!=-1){
		//first check if bill exists in system
		$query="select serial, lock_receipt from receipt where vendor_serial={$vendor} AND 
		purchase_date='{$purchase_date}' and amount={$amount}";
		$result=perform_query($connection, $query, "failed to fetch");
		if(mysqli_num_rows($result)){
			$row=mysqli_fetch_assoc($result);
			if($row['lock_receipt']==1){
				$query="UPDATE receipt set lock_receipt=0 where serial={$row['serial']}";
				if(perform_query($connection, $query, "failed to update lock"))
					addToToast("Bill is unlocked",0);
			}
			else
				addToToast("Bill already exists",0);
		}
		else{ //if everything is fine then add to database
			$query="SELECT MAX(serial) as bill_serial from receipt";
			$row=mysqli_fetch_assoc(perform_query($connection, $query, "failed to fetch bill_serial"));
			$bill_serial=$row['bill_serial'];
			//let name of the file be next serial number
			$bill_serial=uploadBill($bill_serial+1);  //arg is name of the final uploaded file
			if(!is_null($bill_serial))
				addToToast("Receipt is Created",1);
			else
				addToToast("Couldn't create Receipt",0);
		}
	}
	else{
		addToToast("Please select vendor",0);
	}
}

function removeBill(){
	global $bill_serial, $vendor, $details, $purchase_date, $bill, $operations, $amount, $connection;
	if($bill_serial==-1){
		addToToast("Bill doesnt exist",0);
	}
	else{
		$query="UPDATE receipt set lock_receipt=1 where serial={$bill_serial}";
		if(perform_query($connection, $query, "failed to remove bill"))
			addToToast("Receipt is Removed",0);
	}
}

function updateBill(){
	global $bill_serial, $vendor, $details, $purchase_date, $bill, $operations, $amount, $connection;
	escapeChars();
	if($bill_serial==-1){
		addToToast("Item doesnt exist",0);
	}
	else{
		$bill_serial=uploadBill($bill_serial, 1); //1-->update bill
		if(is_null(($bill_serial))){
			addToToast("Couldn't update the receipt",0);
		}
		else{
			addToToast("Receipt is updated",1);
		}
	}

}

function viewBill(){
	global $bill_serial, $vendor, $details, $purchase_date, $bill, $operations, $amount, $connection;
	$query="SELECT * from receipt where serial={$bill_serial} and lock_receipt=0";
	$result=perform_query($connection, $query, "failed to get receipt");
	$row=mysqli_fetch_assoc($result);
	$vendor=$row['vendor_serial'];
	$purchase_date=$row['purchase_date'];
	$details=$row['details'];
	$amount=$row['amount'];
}

function escapeChars(){
	global $details, $connection, $details_sql;
	$details_sql= mysqli_real_escape_string($connection,$details);
}


//returns the serial of the inserted bill
function addToReceipt($filepath){
	global  $connection, $amount, $vendor, $purchase_date, $details_sql;
	if(!is_null($filepath)){
		$query="INSERT INTO 
					receipt 
					(filepath, vendor_serial,purchase_date, amount, details) 
				values('{$filepath}', {$vendor}, '{$purchase_date}', {$amount}, '{$details_sql}' )";
	}
	else{
		$query="INSERT INTO 
					receipt 
					(vendor_serial,purchase_date, amount, details) 
				values({$vendor}, '{$purchase_date}', {$amount}, '{$details_sql}' )";
	}
	if(perform_query($connection, $query, "failed to insert into receipt")){
		return mysqli_insert_id($connection);
	}
	return NULL;
}

function updateReceipt($filePath){
	global  $connection, $amount, $vendor, $purchase_date, $bill_serial, $details_sql;
	escapeChars();
	$query="UPDATE 
				receipt
			SET
				vendor_serial={$vendor},
				purchase_date='{$purchase_date}',
				amount={$amount},
				details='{$details_sql}'";
	if(!is_null($filePath)){
		$query.=", filepath='{$filePath}' ";
	}
	$query.=" where serial={$bill_serial} and lock_receipt=0";
	if(perform_query($connection,$query, "failed to update bill")){
		return $bill_serial;
	}
	return NULL;
}

//pass in the destination file name
//returns the inserted bill serial
function uploadBill($fileName, $toUpdate=0){
	global $bill_serial;
	/*			Now adding receipt to the db 			*/
	$billPath = 'uploads/temp.pdf';
	$uploadedFile=uploadFile($billPath); //returns null if file isnt uploaded
	if(!is_null($uploadedFile)){
		$destinationPath='uploads/'.$fileName.'.pdf';
		copy($billPath, $destinationPath);
	}
	else{
		$destinationPath=NULL;
	}
	if($toUpdate){
		$billSerial=updateReceipt($destinationPath);
	}
	else
		$bill_serial=addToReceipt($destinationPath);
	return $bill_serial;
}

?>