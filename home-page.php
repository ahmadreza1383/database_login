<?php

if(!isset($_SESSION['user_id']) or $_SESSION['user_id'] <= 0)
	exit('<h1>شما وارد نشده اید</h1>')

?>
	<h1> شما وارد شده اید</h1>
	<a href="change-password.php">تغیر پسورد</a>