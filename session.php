<?php
   include_once('dbcon.php');
   include_once('functions.php');
   session_start();
   /***************************************************
    * permission 1 is lowest and 5 is most privilaged *
    **************************************************/
   
   $permission=-1;         //dummy values, if not php will show erros as this 
                           //variable will be used in other files without declaring it first
   $staff_serial=-1;       //same here
   $staff_id=-1;           //here too
   $ADMIN_LEVEL=2;         //admin level
   $MIN_ACCESS_LEVEL=1;    // min access level
   $staff_name=isset($_SESSION['name'])?$_SESSION['name']:"Guest";
   if(!isset($_SESSION['id'])){
      header("location:login.php");
   }
   else{
      //two variables for backward compatibility
      $staff_serial = $_SESSION['id'];
      $staff_id=$_SESSION['id'];
      $permission=$_SESSION['permission'];
   }
?>