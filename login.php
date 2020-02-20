<?php
	require('database_handler.php');
	
	if(isset($_POST['email']) and isset($_POST['pass'])){
		
		$email = $_POST['email'];
		$pass = $_POST['pass']; 
		
		$user = DatabaseHandler::GetRow("SELECT * FROM `users` WHERE `email` LIKE '$email' ");
		
		if($user and ($user['pass'] == $_POST['pass']) ){
			$_SESSION['user_id'] = $user['id'];
			header('location: home-page.php');
		} else {
			$_SESSION['user_id'] = 0;
			$error = 'نام کاربری یا رمز عبور اشتباه است';
		}
			
			
		}
		
		
	
	
	






?>
<pre>

	<?= $error ?? '' ?>
	<form method="post">
	
	email:<input name="email">
	<br>
	<br>
	pass:<input name="pass">
	<br>
	<br>
	<button type="submit">login</button>
	
	</form>

</pre>

