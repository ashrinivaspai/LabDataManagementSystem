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
					variables used
					
	$staff_id			-> holds serial of currently logged in staff
	$reqest 			-> holds serial of row of transfer_item
	$response_message 	-> 
	$authorize_message 	->
	$operations 		-> serial of operation requested
	$response 			-> holds either 1-> approved, -1 ->reject, 0-> default no response 
	$addToLab 			-> holds serial of lab to which item needs to be transferred
	$addFromLab  		->  "" 				from  which 	"" 		""
	$request_message  	-> 
	$requiredItem 		-> serial of inventory, item which needs to be transferred
	$tableHead 			-> array of table heading
	$tableBody 			-> 2-D array of table body


*/
formInit();
$toastOutput=array();
if(isset($_POST['Submit'])){
	getPostData();
	$response_message=mysqli_real_escape_string($connection, $response_message);
	$request_message=mysqli_real_escape_string($connection, $request_message);
	$authorize_message=mysqli_real_escape_string($connection, $authorize_message);
	switch ($operations) {
		case 1:
			newRequest();
			break;
		case 2:
			$response=$_POST['Submit']; 		//1-->approve, -1-->reject
			respondToRequest();
			break;
		case 3:
			deleteRequest();
			break;
		case 4:
			viewRequest();
			break;
		case 5:
			$response=$_POST['Submit'];
			authorizeRequest();
			break;
		default:
			addToToast("Please select an operation",0);
			break;
	}
}

function newRequest(){
	global 	$operations,$addToLab, $requiredItem, $request_message, $addFromLab, 
			$connection, $staff_id;
	if(($requiredItem==-1)||($addToLab==-1)){
		addToToast("Please select from Dropdown",0);
	}
	else{
		$query="
			SELECT 
				lab_serial 
			from 
				inventory 
			where 
				serial={$requiredItem}";
		$temp=mysqli_fetch_assoc(perform_query($connection, $query, "failed to fetch item"));
		if($addToLab==$temp['lab_serial']){
			addToToast("Item exists in lab",0);
		}
		else{
			//check if the request is already open, i.e., dont let duplication
			$query="SELECT 
						serial 
					from 
						transfer_item 
					where 
						to_lab={$addToLab} and 
						inventory_serial={$requiredItem} AND
						((staff_approved=0 AND hod_approved=1) OR hod_approved=0) AND 
						lock_transfer=0";
			echo $query;
			$temp=mysqli_fetch_assoc(perform_query($connection, $query, "Failed to check the table"));
			if(!empty($temp)){
				addToToast("Request exists in system",0);
			}
			else{
				//first get the serial of the lab in which the item is currently located
				$query="SELECT lab_serial from inventory where serial={$requiredItem}";
				$result=perform_query($connection, $query, "Failed to get lab serial");
				$row=mysqli_fetch_assoc($result);
				$addFromLab=$row['lab_serial'];
				mysqli_free_result($result);
				$query="INSERT INTO transfer_item (
							inventory_serial, 
							to_lab, 
							from_lab,
							request_message,
							staff_requested
						)
						VALUES(
							{$requiredItem},
							{$addToLab},
							{$addFromLab},
							'{$request_message}',
							{$staff_id}
						)";
				if(perform_query($connection, $query, "failed to insert into db"))
					addToToast("Request is Created",1);
			}
		}
	}
}

function authorizeRequest(){
	global $connection, $request, $response;
	if(doesItemExist('transfer_item', 'serial', $request)){
		updateTransfer($response, 1);
		if($response==1)
			addToToast('Request is Authorized', 1);
		else
			addToToast('Request is Rejected', 0);
	}
	else{
		addToToast('Request Does not exist', 0);
	}

}

function respondToRequest(){
	global 	$operations,$addToLab, $requiredItem, $request_message, $addFromLab, 
			$connection, $staff_id,$response, $request, $response_message;
	//first check if the item exists
	if(doesItemExist('transfer_item', 'serial',$request)){
		//get the request details 
		$query="SELECT to_lab,from_lab, inventory_serial, staff_approved FROM transfer_item where serial={$request}";
		$result=perform_query($connection, $query, "failed to get details");
		$row=mysqli_fetch_assoc($result);
		if($row['staff_approved']==1||$row['staff_approved']==-1){
			addToToast('Request has already been responded',0);
		}
		else{
			$to_lab=$row['to_lab'];
			$from_lab=$row['from_lab'];
			$requiredItem=$row['inventory_serial'];
			//get the lab number of source lab
			$query="SELECT lab_number from lab where serial={$from_lab}";
			$temp=mysqli_fetch_assoc(perform_query($connection, $query, 'failed to get lab number'));
			//concat the response message(from) with details of the request
			$response_message_from=$response_message." | from lab:".$temp['lab_number'];
			$query="SELECT lab_number from lab where serial={$to_lab}";
			$temp=mysqli_fetch_assoc(perform_query($connection, $query, 'failed to get lab number'));
			//concating with query details (to)
			$response_message_to=$response_message." | to lab:".$temp['lab_number'];
			if($response==1){ 		//approve
				//first update in the inventory then update the transfer_item table
				$date= date("Y-m-d H:i:s");
				$query="UPDATE 
							inventory 
						SET
							lab_serial={$to_lab},
							updated='{$date}', 
							last_operation=5
						WHERE
							inventory.serial={$requiredItem}";
				perform_query($connection, $query, "failed to update inventory");
				$comments="item was transferred (serial={$request})";
				$query="
					INSERT INTO 
						inventory_log 
						(
							lab_serial, 
							inventory_serial, 
							status, 
							comments, 
							staff_id,
							operation_serial
						) 
						values
						(
							{$from_lab}, 
							'{$requiredItem}', 
							(SELECT status from inventory where serial={$requiredItem}), 
							'{$response_message_to}', 
							{$staff_id},
							5
						),
						(
							{$to_lab}, 
							'{$requiredItem}', 
							(SELECT status from inventory where serial={$requiredItem}), 
							'{$response_message_from}', 
							{$staff_id},
							1
						)";
				perform_query($connection, $query, "failed to insert into log");
				//now update transfer_item
				updateTransfer(1);
				addToToast("Apparatus is Transferred",1);
			}
			else if($response==-1){ 					//reject
				//here no need of updating the inventory and inventory log
				updateTransfer(-1);
				addToToast("Request is Denied",0);
			}
		}
	}
	else{
		addToToast('Please select an request',0);
	}
}


//function is used to make update to tranfer_item table either by appoving
//or rejecting request. here response is either 1 or -1
function updateTransfer($response, $isHOD=0){
	global $connection, $staff_id, $request, $response_message, $authorize_message;
	$date= date("Y-m-d H:i:s");
	$query="UPDATE 
				transfer_item 
			SET";
	if($isHOD){
		$query.="
			hod_approved={$response},
			authorize_message='{$authorize_message}',
			date_authorized='{$date}'";
	}
	else{
		$query.="
			staff_responded={$staff_id},
			response_message='{$response_message}',
			staff_approved={$response},
			date_responded='{$date}'";
	}

	$query.="WHERE
				serial={$request}";
	perform_query($connection, $query, "failed to update transfer_item");
}

function deleteRequest(){
	global $connection, $request;
	if(doesItemExist('transfer_item','serial',$request)){
		$query="UPDATE transfer_item set lock_transfer=1 WHERE serial={$request}";
		perform_query($connection, $query, "failed to delete");
		addToToast("Request is deleted",1);
	}
	else
		addToToast("Request doesnt exist",0);

}

function viewRequest(){
	global $connection, $view_options, $staff_id, $tableBody, $tableHead, $permission;
	$lab_serial=getLabForStaff($staff_id, $permission);
	$query="SELECT 
				CONCAT(	'Transfer ',
						iv.item_code,
						' from ',
						(SELECT l.lab_number from lab l where l.serial=t.from_lab),
						' to ', 
						(SELECT l.lab_number from lab l where l.serial=t.to_lab)
				) as requests,
				(CASE 
					WHEN t.hod_approved=0 then 'Pending'
					WHEN t.hod_approved=-1 then 'Not Authorized'
					WHEN ( t.hod_approved=1 AND t.staff_approved=0 ) then 'Authorized'
					WHEN t.staff_approved=-1 then 'Rejected'
					WHEN (t.hod_approved=1 AND t.staff_approved=1) then 'Approved'
					ELSE 'ERROR'
				END) AS status,
				date_requested,
				date_authorized,
				date_responded,
				response_message
			FROM
				transfer_item as t,
				inventory as iv 
			WHERE
				t.inventory_serial=iv.serial AND  
				t.lock_transfer=0 AND ";
	$query.="t.to_lab in(";
	$query.=implode(',',$lab_serial);
	$query.=') ';
	if(!empty($view_options)){
		if(in_array(1, $view_options))
			$query.="AND t.staff_approved=1 ";
		else if(in_array(-1, $view_options))
			$query.="AND (t.hod_approved=-1 OR t.staff_approved=-1) ";
		else if(in_array(0, $view_options))
			$query.="AND (t.hod_approved=0 OR (t.hod_approved=1 AND t.staff_approved=0)) ";
	}
	$query.=" ORDER BY t.serial DESC";
	$result=perform_query($connection, $query, "failed to get requests");
	$tableBody=mysqli_fetch_all($result);
	$tableHead=array('Request Details','Status','Date Requested','HOD Responded on','Lab Incharge Responded on','Response Comments');
}

?>

<!DOCTYPE html>
<html>
<head>
	<title>Transfer Item</title>
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
	<header><?php displayNavbar('transfer_item.php');?></header>
	<main>
		<div class="container grey lighten-5">
			<form class="col s12 z-depth-2" action="transfer_item.php" method="POST">
				<div class="container">
					<div class="row">
						<Br/><span class="center"><h5 class="grey-text text-darken-2 ">Transfer Apparatus</h5></span>
						<div class="col s12 input-field" id=operations>
							<?php
							$dropDownOptions=array(
									array(1,'Create New Request'), 
									array(2,'Respond to Requests'),
									array(3,'Delete Sent Request'),
									array(4,'View Sent Requests'),
								);
							if($permission>=2)
								array_push($dropDownOptions, array(5, 'Authorize'));
							outputDropdownFromArray($dropDownOptions, "operations", 1);
							?>
							<label>Operation</label>
						</div>
						<div id='new_request'>
							<div class="col s12 m6 l6 input-field">
								<?php
								$lab_serial=getLabForStaff($staff_id, $permission);
								$query="SELECT 
											serial, 
											CONCAT(lab_name, ' | ', lab_number) as name 
										from lab
										WHERE ";
								$query.="serial in(";
								$query.=implode(',',$lab_serial);
								$query.=')';
								$result=perform_query($connection, $query, "failed to get lab");
								outputDropdown($result, "addToLab", $addToLab);
								?>
								<label>Add to this Lab</label>
							</div>
							<div class="col s12 m6 l6 input-field">
								<?php
								//select all current items
								$query="SELECT 
											iv.serial,
											CONCAT(iv.item_code,' | ',i.name,' | ', m.name) as item,
											l.serial, 
											CONCAT(l.lab_name, ' | ', l.lab_number) as lab_name 
										from
											inventory as iv,
											item as i,
											manufacturer as m,
											lab as l
										where
											i.serial=iv.serial_item AND
											m.serial=i.manufacturer_serial AND
											l.serial=iv.lab_serial AND
											iv.lock_item=0
										ORDER BY 
											l.serial";
								$result=perform_query($connection, $query, "failed to get items");
								outputDropdownOptGrp($result, "requiredItem",$requiredItem );
								?>
								<label>Required Item</label>
							</div>
							<div class="col s12 input-field">
								 <?php
								 outputTextField("request_message", $request_message, 100,1);
								 ?>
								 <label>Request Message</label>
							</div>
							<div class="center-align" id="submit_row">
								<button type="submit" name="Submit" value="Submit" class="btn waves-effect waves-light green">
									Create Request
									<i class="material-icons right">done</i>
								</button>
							</div>
						</div>
						<div id="delete" style="display: none;">
							<div class="col s12 input-field">
								<?php
								$lab_serial=getLabForStaff($staff_id, $permission);
								$query="SELECT 
											t.serial, 
											CONCAT(	'Transfer ',
													iv.item_code,
													' from ',
													(SELECT l.lab_number from lab l where l.serial=t.from_lab),
													' to ', 
													(SELECT l.lab_number from lab l where l.serial=t.to_lab)
											) as item
										FROM
											transfer_item as t,
											inventory as iv
										WHERE
											t.inventory_serial=iv.serial AND 
											t.hod_approved=0 AND 
											t.lock_transfer=0 AND ";
								$query.="t.to_lab in(";
								$query.=implode(',',$lab_serial);
								$query.=')';
								$result=perform_query($connection, $query, "failed to get requests");
								outputDropdown($result, "request",$request);
								?>
								<label>Select Transfer request</label>
							</div>
							<div class="center-align">
								<button class="btn waves-effect waves-light red" type="submit" name="Submit" value="-1">
									Delete Request
									<i class="material-icons right">delete</i>
								</button>
							</div>
						</div>
						<div id="view"  style="display: none;">
							<div class="col s12 input-field">
								<?php
								$options=array(array(0,'Pending'), array(1,'Approved'),array(-1,'Rejected'));
								outputDropdownFromArray($options, "view_options", 2,1);
								?>
								<label>Select Filters</label>
							</div>
							<div class="center-align" >
								<button type="submit" name="Submit" value="Submit" class="btn waves-effect waves-light">View Requests</button>
							</div>
						</div>
						<div id=response style="display: none;">
							<div class="col s12 input-field">
								<?php
								$lab_serial=getLabForStaff($staff_id, $permission);
								$query="SELECT 
											t.serial, 
											CONCAT(	'Transfer ',
													iv.item_code,
													' from ',
													(SELECT l.lab_number from lab l where l.serial=t.from_lab),
													' to ', 
													(SELECT l.lab_number from lab l where l.serial=t.to_lab),
													' (', t.request_message,' )'
											) as item
										FROM
											transfer_item as t,
											inventory as iv 
										WHERE
											t.inventory_serial=iv.serial AND 
											t.staff_approved=0 AND 
											t.hod_approved=1 AND
											t.lock_transfer=0 AND ";
								$query.="t.from_lab in(";
								$query.=implode(',',$lab_serial);
								$query.=')';
								$result=perform_query($connection, $query, "failed to get requests");
								outputDropdown($result, "request",$request);
								?>
								<label>Select Transfer request</label>
							</div>
							<div class="col s12 input-field" id=response_comments>
								 <?php
								 outputTextField("response_message", $response_message,100,0);
								 ?>
								 <label>Remarks</label>
							</div>
							<div class="center-align" >
								<button class="btn waves-light waves-effect green" type="submit" name="Submit" value='1'>
									Accept
									<i class="material-icons right">check</i>
								</button>
								<button class="btn waves-effect waves-light red" type="submit" name="Submit" value="-1">
									Reject
									<i class="material-icons right">delete</i>
								</button>
							</div>
						</div>
						<div id=authorize style="display: none;">
							<div class="col s12 input-field">
								<?php
								$lab_serial=getLabForStaff($staff_id, $permission);
								$query="SELECT 
											t.serial, 
											CONCAT(	'Transfer ',
													iv.item_code,
													' from ',
													(SELECT l.lab_number from lab l where l.serial=t.from_lab),
													' to ', 
													(SELECT l.lab_number from lab l where l.serial=t.to_lab),
													' (', t.request_message,' )'
											) as item
										FROM
											transfer_item as t,
											inventory as iv 
										WHERE
											t.inventory_serial=iv.serial AND  
											t.hod_approved=0 AND
											t.lock_transfer=0 AND ";
								$query.="t.from_lab in(";
								$query.=implode(',',$lab_serial);
								$query.=')';
								$result=perform_query($connection, $query, "failed to get requests");
								outputDropdown($result, "request",$request);
								?>
								<label>Select Transfer request</label>
							</div>
							<div class="col s12 input-field" id=authorize_comments>
								 <?php
								 outputTextField("authorize_message", $authorize_message,100,0);
								 ?>
								 <label>Remarks</label>
							</div>
							<div class="center-align" >
								<button class="btn waves-light waves-effect green" type="submit" name="Submit" value='1'>
									Authorize
									<i class="material-icons right">check</i>
								</button>
								<button class="btn waves-effect waves-light red" type="submit" name="Submit" value="-1">
									Reject
									<i class="material-icons right">delete</i>
								</button>
							</div>
						</div>
					</div>
				</div>	
				<br/><br/>
			</form>
				<?php
				if(!empty($tableBody)){
					echo "<Br/><Br/>";
				}
				echo "<div class=' z-depth-3 white'>";
				if(isset($_POST['Submit'])&&($operations==4)){
					//start displaying table
					outputTable($tableHead, $tableBody);
				}
				?>
			</div>
		</div>
		<Br/><Br/>
	</main>
	<?php displayFooter();?>
	<script type = "text/javascript"
	src = "jquery-2.1.1.min.js"></script>    
	<script src="materialize/js/materialize.js"></script>
	<script>
		$(document).ready(function() {
			$('input, textarea').characterCounter();
			$('.sidenav').sidenav();
			$(".dropdown-trigger").dropdown({constrainWidth: false});
		});  

		document.addEventListener('DOMContentLoaded', function() {
			var elems = document.querySelectorAll('select');
			var instances = M.FormSelect.init(elems, {});
		});
		<?php
			outputToast();
		?>

		function showDiv(elem){
			var name=elem.name;
			if(name=='operations'){
				switch(elem.value){
					case '1':
						createRequest();
						break;
					case '2':
						respondToRequest();
						break
					case '3':
						deleteRequest();
						break;
					case '4':
						viewRequest();
						break;
					case '5':
						authorizeRequest();
						break;
					default:
						break;
				}
			}
		}

		function hideAll(){
			document.getElementById('new_request').style.display="none";
			document.getElementById('response').style.display="none";
			document.getElementById('delete').style.display="none";
			document.getElementById('view').style.display="none";
			document.getElementById('authorize').style.display="none";	
		}

		function removeAttribute(){
			document.getElementById('response_message').removeAttribute('required');
			document.getElementById('request_message').removeAttribute('required');
			document.getElementById('authorize_message').removeAttribute('required');
		}
		function createRequest(){
			hideAll();
			removeAttribute();
			document.getElementById('new_request').style.display="block";
			document.getElementById('request_message').setAttribute('required',"");

		}

		function respondToRequest(){
			hideAll();
			removeAttribute();
			document.getElementById('response').style.display="block";
			document.getElementById('response_message').setAttribute('required',"");
		}
		function deleteRequest(){
			hideAll();
			removeAttribute();
			document.getElementById('delete').style.display="block";
		}

		function viewRequest(){
			hideAll();
			removeAttribute();
			document.getElementById('view').style.display="block";
		}

		function authorizeRequest(){
			hideAll();
			removeAttribute();
			document.getElementById('authorize').style.display="block";
			document.getElementById('authorize_message').setAttribute('required',"");
		}
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
	global $operations,$addToLab, $requiredItem, $request_message, $authorize_message,
	$request, $response_message, $view_options;
	//iniitalize things
	$operations=1;
	$addToLab=-1;
	$requiredItem=-1;
	$request_message="";
	$request=-1;
	$response_message="";
	$authorize_message="";
	$view_options=array();

}

function getPostData(){
	global $operations,$addToLab, $requiredItem, $request_message,$authorize_message, 
	$response_message, $request, $view_options;
	//get form data;
	$operations=isset($_POST['operations'])?$_POST['operations']:-1;
	$addToLab=isset($_POST['addToLab'])?$_POST['addToLab']:-1;
	$requiredItem=isset($_POST['requiredItem'])?$_POST['requiredItem']:-1;
	$request_message=isset($_POST['request_message'])?$_POST['request_message']:"";
	$response_message=isset($_POST['response_message'])?$_POST['response_message']:"";
	$authorize_message=isset($_POST['authorize_message'])?$_POST['authorize_message']:"";
	$request=isset($_POST['request'])?$_POST['request']:-1;
	$view_options=isset($_POST['view_options'])?$_POST['view_options']:array();
}

?>