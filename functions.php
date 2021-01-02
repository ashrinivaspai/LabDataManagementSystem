<?php
/******************************************************************************
 * how to use this function: 
 * 1) a mysql result object must be used, result must have only two index
 *    first index is used as value of dropdown, second is used to display option
 * 2) name of dropdown is passed as second arg
 * 3) default value must be passed as third arg
 * 4) if to be used as multiple select pass 1, or leave it blank-> def arg 
 ******************************************************************************/
function outputDropdown($MySqlResult, $listName, $defaultSelection, $isMultiple=0){
	if(!$MySqlResult){
		die("failed to get {$listName}");
	}
	$data=mysqli_fetch_all($MySqlResult, MYSQLI_NUM);
	outputDropdownFromArray($data, $listName, $defaultSelection, $isMultiple);
	mysqli_free_result($MySqlResult);
}


//used to get html dropdown from an assoc array
function outputDropdownFromArray($arrayName, $listName, $defaultSelection, $isMultiple=0){
	echo "<select id='{$listName}' name={$listName}";
	if($isMultiple)
		echo "[] multiple";			//[] for multiple, google kari
	echo " onchange='showDiv(this)'  >";
	if(!$isMultiple){
		echo "<option value=-1 disabled ";
		if($defaultSelection==-1)
			echo "selected ";
		echo ">----</option>";
	}

	foreach ($arrayName as $key => $pair) {
		$value=$pair[0];
		$name=$pair[1];
		echo "<option value={$value}";
		if($defaultSelection==$value)
			echo " selected";
		echo ">{$name}</option>";
	}
	echo "</select>";
}

//outputs multiple select dropdown list, data is to be from sql
function outputMultipleSelect($MySqlResult, $listName){
	outputDropdown($MySqlResult, $listName, -1, 1);
}


/*
 * Useful for select menu which needs to have option groups
 * mysql result param must have 4 columns 
 *		1st->value of option
 *		2nd->string to be displayed
 *		3rd->value of group header
 *		4th-> name of the group header
*/

function outputDropdownOptGrp($MySqlResult, $listName, $defaultSelection=-1, $isMultiple=0){
	if(!$MySqlResult){
		die("failed to get {$listName}");
	}
	$data=mysqli_fetch_all($MySqlResult, MYSQLI_NUM);
	echo "<select id={$listName} name={$listName}";
	if($isMultiple)
		echo "[] multiple ";
	echo " onchange='showDiv(this)' >";
	if(!$isMultiple)
		echo "<option value=-1 selected disabled >----</option>";
	$previousGrp=-1;
	foreach ($data as $key => $pair) {
		$value=$pair[0];
		$name=$pair[1];
		$currentGrp=$pair[2];
		$labName=$pair[3];
		if($currentGrp!=$previousGrp){
			if($previousGrp!=-1){
				echo "</optgroup>";
			}
			echo "<optgroup label='{$labName}'>";
		}
		echo "<option value='{$value}' ";
		if($defaultSelection==$value)
			echo " selected";
		echo " >{$name}</option>";
		$previousGrp=$currentGrp;
	}
	echo "</select>";
}
/******************************************************************************
* same as above functions, first param sets the name of the input box, second 
* param sets default value, last param 1 means required, 0 means optional
******************************************************************************/
function outputTextField($fieldName, $defaultValue,$datalength=0, $required=0){
	echo "<input type=text name='{$fieldName}' id='{$fieldName}' value=\"{$defaultValue}\"";
	if($datalength>0)
		echo" data-length={$datalength}";
	if($required) echo " required";
	echo " />";
}


//forces user to insert number
function outputNumericField($fieldName, $defaultValue, $required=1, $minNumber=0){
	echo "<input type=number name='{$fieldName}' id='{$fieldName}' class='validate' value='{$defaultValue}' min='{$minNumber}' ";
	if($required) echo " required";
	echo "/>";
}

function outputDateField($fieldName,$defaultValue, $required=0){
	echo"<input type=text name={$fieldName} id={$fieldName} class='datepicker active' value='{$defaultValue}' ";
	if($required)
		echo " required";
	echo "/>";
}

function outputDataList($MySqlResult, $listName, $defaultSelection){
	if(!$MySqlResult){
		die("failed to get {$listName}");
	}
	echo "<input type=text list={$listName} class=''  required name='{$listName}'>";
	echo "<datalist id={$listName}>";
	echo "<option value=-1 disabled selected>---</option>";
	while($row=mysqli_fetch_row($MySqlResult)){
		$value=$row[0];
		echo "<option value='{$value}'";
		if($value==$defaultSelection)
			echo " selected";
		echo ">{$value}</option>";		
	}
	echo "</datalist>";
	mysqli_free_result($MySqlResult);
}

//outputs radio button based on data array
function outputRadioButton($dataArray, $listName, $defaultSelection){
	foreach ($dataArray as $value => $name) {
		echo "<label><input type=radio name={$listName} value={$value} onclick=showDiv(this) id='{$name}{$value}'";
		if($defaultSelection==$value)
			echo " checked";
		echo "/><span>{$name}</span></label><Br>"; 
	}
}

//use this instead of mysqli_query, error checking is done here
function perform_query($connection, $query, $errorMessage="failed"){
	$result=mysqli_query($connection, $query);
	if(!$result){
		addToToast($errorMessage,0);
	}
	return $result;
}

//mostly a useless function
function doesItemExist($table, $column, $value){
	global $connection, $result;
	if(is_numeric($value))
		$query="select * from `{$table}` where `{$column}`={$value}";
	else
		$query="select * from `{$table}` where `{$column}`='{$value}'"; 
	$result=perform_query($connection, $query, "failed to retrive");
	return mysqli_num_rows($result);
}


//checks both char and numeric values
function checkValue($table, $requiredColumn, $basedOnColumn, $condition){
	global $connection, $result;
	$query="SELECT `{$requiredColumn}` from `{$table}` where `{$basedOnColumn}`=";
	if(is_numeric($condition))
		$query.="{$condition}";
	else
		$query.="'{$condition}'";
	$result=perform_query($connection, $query, "failed to check");
	$row=mysqli_fetch_assoc($result);
	mysqli_free_result($result);
	return $row[$requiredColumn];

}

function getValue($table, $requiredColumn, $basedOnColumn, $condition){
	global $connection, $result;
	$query="SELECT `{$requiredColumn}` from `{$table}` where `{$basedOnColumn}`=";
	if(is_numeric($condition))
		$query.="{$condition}";
	else
		$query.="'{$condition}'";
	$result=mysqli_fetch_assoc(perform_query($connection, $query, "failed to query"));
	return $result[$requiredColumn];
}

//sets only numeric values
function setValue($table, $columnToUpdate,$value, $basedOnColumn, $condition){
	global $connection, $result;
	$query="UPDATE `{$table}` set `{$columnToUpdate}`={$value} where `{$basedOnColumn}`=";
	if(is_numeric($condition))
		$query.="{$condition}";
	else
		$query.="'{$condition}'";
	perform_query($connection, $query, "failed to update value");
	return 1;
}

//checks file extension basically
function getMime($file) {
	if (function_exists("finfo_file")) {
		$finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
		$mime = finfo_file($finfo, $file);
		finfo_close($finfo);
		return $mime;
	} 
	else if (function_exists("mime_content_type")) {
		return mime_content_type($file);
  	} 
  	else if (!stristr(ini_get("disable_functions"), "shell_exec")) {
		$file = escapeshellarg($file);
		$mime = shell_exec("file -bi " . $file);
		return $mime;
	} 
  	else {
	return false;
  }
}


function addToToast($message,$isSuccess){
	global $toastOutput;
	$html_message=addslashes($message);
	if($isSuccess)
		array_push($toastOutput,"window.M.toast({html:'{$html_message}', classes:'green'});");
	else
		array_push($toastOutput,"window.M.toast({html:'{$html_message}', classes:'red'});");
}

function outputToast(){
	global $toastOutput;
	if(!empty($toastOutput)){
		foreach ($toastOutput as $key => $value) {
			echo $value;
		}
		$toastOutput=array();
	}
}

//table header is 1D array, tablebody must be 2D array
function outputTable($tableHeader, $TableBody){
	global $toastOutput;
	if(empty($TableBody)){
		addToToast("Sorry,no records found",0);
		return;
	}
	echo "<table class='centered striped bordered responsive-table'>";
		echo "<thead>";
			echo "<tr>";
				foreach ($tableHeader as $key => $value) {
					echo "<th>{$value}</th>";
				}
			echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
			foreach ($TableBody as $key => $row) {
				echo "<tr>";
					foreach ($row as $key => $value) {
						echo "<td>";
							echo $value;
						echo "</td>";
					}
				echo "</tr>";
			}
		echo "</tbody>";
	echo "</table>";
}


//project specific function
function getLabForStaff($staff_id, $permission){
	global $connection, $ADMIN_LEVEL;
	if($permission>=$ADMIN_LEVEL){
		$query="SELECT serial from lab where lock_lab=0";
	}
	else{
		$query="SELECT 
					serial 
				from 
					lab 
				where 
					(
						staff_incharge={$staff_id} 
							or 
						lab_incharge={$staff_id}
					) AND 
					lock_lab=0";
	}
	$result=perform_query($connection, $query, "failed to get lab");
	$lab_serial=array();
	while($row=mysqli_fetch_assoc($result)){
		array_push($lab_serial, $row['serial']);
	}
	if(empty($lab_serial))
		array_push($lab_serial, -1);
	return $lab_serial;
}


//takes page link as parameter and displays the navBar, change the links and dropdown variable
//according to ur needs
function displayNavbar($activeLink){
	global $staff_name;
	$links=array(
			array('item.php', 'Apparatus'), 
			array('inventory.php', 'Inventory'), 
			array('receipt.php', 'Receipt'),
			array('search_item.php', 'Search'), 
			array('snapshot.php', 'Snapshot'));

	$dropdown=array(
			array('transfer_item.php', 'Transfer Item'),
			array('lab.php', 'Lab'),
			array('staff.php', 'Create Staff'),
			array('man_cat_ven.php', 'New Entity'),
			array('permission.php', 'Change Access Level'),
			array('report.php', 'Reports')
			);
	echo "<ul id=navDropdown class=dropdown-content>";
	navLinks($dropdown, $activeLink);
	echo "</ul>";
	echo "<div class='row '>
		<div class='navbar-fixed'>
			<nav >
				<div class='nav-wrapper'>
					<a href='inventory.php' class='brand-logo'><img src='/ctc/nitte.png' style='height: 50px;' alt='LOGO'></a>
					<a href='#' data-target='mobile-demo' class='sidenav-trigger'><i class='material-icons'>menu</i></a>
					<ul class='right hide-on-med-and-down'>";
	navLinks($links, $activeLink);
	echo "	
						<li>
							<a class='dropdown-trigger' href='#!' data-target='navDropdown'>
								<i class='material-icons right'> arrow_drop_down</i>
							</a>
						</li>
						<li class='left'><a>Hello, {$staff_name}</a></li>
						<li><a href='/ctc/logout.php'><i class='material-icons left'>power_settings_new</i></a></li>
					</ul>
				</div>
			</nav>
		</div>";
	echo "
		<ul class='sidenav' id='mobile-demo'>
			<li><a><h6>Hello, {$staff_name}</h6></a></li>";
	navLinks($links, $activeLink);
	navLinks($dropdown, $activeLink);
	echo "		
			<li><a href='/ctc/logout.php'><i class='material-icons center-align '>power_settings_new</i></a></li>
		</ul>
	</div>";
}

function navLinks($links, $activeLink){
	foreach ($links as $i => $link) {
		echo "<li";
		if($activeLink==$link[0])
			echo " class='active' ";
		echo "><a href='/ctc/{$link[0]}'>{$link[1]}</a></li>";
	}
}

function displayFooter(){
	echo "
	<footer class='page-footer'>
		<div class=container>
			<div class=row>
				<div class='col s4 m4 l2'>
					<img src='nitteimg-footer.png' style='width:100%; align:left'></img>
				</div>
				<div class='col s12 m l7'>
					<h5>Lab Database Management System </h5>
					Designed for Dept. of ECE, NMAMIT by the members of Circuit Tinkerers Club.
				</div>
				<div class='col s12 m6 l3'>
					<h5>Links</h5>
					<ul>
						<li><a href='http://www.nmamit.nitte.edu.in'>NMAMIT Website</a></li>
						<li><a href='http://172.168.32.6'>Moodle</a></li>
					</ul>
				</div>
			</div> 
		</div>
		<div class='footer-copyright'>
			<div class=container>
				<h6 class='center-align '>Copyright 2018-";echo date('Y'); 
	echo " Department Of Electronics & Communication-NMAMIT</h6> 
			</div>
		</div>
	</footer>";
}

/**
			Hashing functions
*/

function encryptPassword($password){
	global $connection;
	$hashFormat="$2y$10$";
	$saltLength=22;
	$salt=generateSalt($saltLength);
	$formatAndSalt=$hashFormat.$salt;
	return crypt($password, $formatAndSalt);
}

function generateSalt($length){
	$uniqueString=md5((uniqid(mt_rand(),true)));
	$base64String=base64_encode($uniqueString);
	$finalBase64String=str_replace('+', '.', $base64String);
	$salt=substr($finalBase64String, 0,$length);
	return $salt;
}

function passwordCheck($password, $existingHash){
	$hash=crypt($password, $existingHash);
	if($hash==$existingHash)
		return true;
	else
		return false;
}


function uploadFile($filePath){
	if(!empty($_FILES['bill']['name'])){
		if(getMime($_FILES['bill']['tmp_name'])!='application/pdf'){
			addToToast("Upload file type must be PDF",0);
		}
		else{
			if(!move_uploaded_file($_FILES['bill']['tmp_name'], $filePath)){
				addToToast("You Pressed Refresh",0);
				return NULL;
			}
		}
		return 1;
	}
	else
		return NULL;
}

function calculateFileHash($filepath){
	return hash_file('md5', $filepath);
}
?>