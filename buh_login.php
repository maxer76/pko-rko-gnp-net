<?php 
session_start();
$heading = 'Авторизация';
require_once('php/functions.php');

if(isset($_POST['submit']))
{
	if($objUser = buhchk($_POST['login'],$_POST['password']))
    {
        $hash = md5(generateCode(10));

		if ($result = $mysqli->prepare("UPDATE `buh` SET `user_hash`=? WHERE `user_id`=?"))
		{
			$result->bind_param("si",$hash,$objUser->user_id);
			$result->execute();
			$result->close();
		}	
		
		$_SESSION["user_id"] = $objUser->user_id;
		$_SESSION["user_hash"] = $hash;		
		$_SESSION["region"] = $objUser->region_id;
		
		header("Location: buh_console.php"); 
		exit();		
		
    }
    else
    {
		$_SESSION['error'] = 'Неверный логин или пароль';
        header("Location: buh_login.php"); 
		exit();
    }
}
if (isset($_SESSION['user_id']) and isset($_SESSION['user_hash'])){
    if(buh_chk($_SESSION['user_id'],$_SESSION['user_hash'])) {
		header("Location: buh_console.php"); 
    }
}


require_once('template/head.php');
?>
		
		<div class="container">

		  <form method="POST" class="form-signin">
			<h2 class="form-signin-heading">Авторизация</h2>
			<label for="inputEmail" class="sr-only">Имя пользователя</label>
			<input name="login" type="text" id="inputEmail" class="form-control" placeholder="Имя пользователя" required autofocus>
			<label for="inputPassword" class="sr-only">Пароль</label>
			<input name="password" type="password" id="inputPassword" class="form-control" placeholder="Пароль" required>
			<input name="submit" class="btn btn-lg btn-primary btn-block" type="submit" value="Войти">
		  </form>
		  
		  <?php 
			if(isset($_SESSION['error']))
			{
				echo '<div class="alert alert-danger" role="alert">'.$_SESSION['error'].'</div>';
				unset ($_SESSION['error']);
			}
		  ?>
		  
		</div> <!-- /container -->		
		
<?php
require_once('template/bottom.php');
?>
