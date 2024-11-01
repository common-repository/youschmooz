<?php
	include "functions.php";
	if($_GET['page']=='schmooz.php'){
		include('dashboard.php');
	}

	if($_GET['page']=='schmooz-comment'){
		include('dashboard.php');
	}

	if($_GET['page']=='schmooz-setting'){
		include('schmooz-setting.php');
	}
?>