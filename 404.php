<?php
	require_once('functions.php');
	session_start();
	$staff_name=isset($_SESSION['name'])?$_SESSION['name']:"Guest";
	header("HTTP/1.0 404 Not Found");
?>
<!DOCTYPE html>
<html>
<head>
	<title>404</title>
	<link rel="icon" href="favicon.ico" type="image/x-icon" />
		<link rel = "stylesheet" 
		href = "/ctc/iconfont/material-icons.css">
		<link rel="stylesheet" href="/ctc/materialize/css/materialize.css">
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
	<header><?php displayNavbar('');?></header>
	<main>
		<div class="container left-align">
			<div class="row ">
				<div class="col s12 m6 l6 center-align">
					<img src="/ctc/404.png"  style="height: 500px;">
				</div>
				<div class="col s12 m6 l6  ">
					<p class="flow-text grey-text darken-4 left-align">
						<h1 class="grey-text text-darken-3" style="font-size: 140px; font-weight: 700">Oops!</h1>
						<h5 class="grey-text text-darken-3" style="font-size: 40px;">
							We can't seem to find the page you are looking for.
						</h5>
						<br/>
						<br/>
					</p>
				</div>
			</div>
		</div>
	</main>
	<?php displayFooter();?>
	<script type = "text/javascript"
		src = "/ctc/jquery-2.1.1.min.js"></script>
		<!-- Compiled and minified JavaScript -->
	<script src="/ctc/materialize/js/materialize.js"></script>
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
	</script>
</body>
</html>