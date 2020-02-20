<?php
	
	require 'database_handler.php';
	
	if(!isset($_SESSION['user_id']) or $_SESSION['user_id'] <= 0)
		exit('<h1>شما وارد نشده اید</h1>');
	
	if(isset($_POST['old_pass']) and isset($_POST['new_pass']))
	{
		$old_pass = $_POST['old_pass'];
		$new_pass = $_POST['new_pass'];
		
		$user = DatabaseHandler::GetRow("SELECT * FROM `users` WHERE `id` = ". $_SESSION['user_id']);
		
		if($user['pass'] != $_POST['old_pass']){
			$message = 'نام کاربری یا رمز عبور اشتباه است';
		} else {
			DatabaseHandler::Execute("UPDATE `users` SET `pass` = '$new_pass' WHERE `id` = ". $_SESSION['user_id']);
			$message = 'رمز بروز رسانی شد';
		}
	}
	
?>
<pre>
	<?= $message ?? '' ?>
	<form method="post">
		Old Pass: <input name="old_pass">
		<br>
		<br>
		New Pass: <input name="new_pass">
		<br>
		<br>
		<button type="submit">Change</button>
	</form>
</pre>