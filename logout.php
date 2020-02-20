<?php

	$_SESSION['user_id'] = 0;
	unset($_SESSION['user_id']);
	header('location: login.php')

?>