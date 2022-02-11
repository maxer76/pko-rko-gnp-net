<?php 

session_start();
$heading = 'Авторизация';
require_once('php/functions.php');

if(isset($_POST['submit']))
{
	if($objUser = passchk($_POST['login'],$_POST['password']))
    {	
        $hash = md5(generateCode(10));
		$ip = $_SERVER['REMOTE_ADDR'];
		$last_login = date('Y-m-d H:m:s');
		
		
		if ($result = $mysqli->prepare("UPDATE `users` SET `user_hash`=?, `user_ip`=?, `last_login`=?  WHERE `user_id`=?"))
		{
			$result->bind_param("ssss",$hash,$ip,$last_login,$objUser->user_id);
			$result->execute();
			$result->close();
		}		
		

				
		
		if($objUser->user_login != 'it'){
			$_SESSION["user_id"] = $objUser->user_id;
			chkadm($_SESSION['user_id']);
			$_SESSION["user_hash"] = $hash;
			$_SESSION["azs_name"] = $objUser->azs_name;
			$_SESSION["ink"] = $objUser->ink;
			$_SESSION["ragion_name"] = $objUser->ragion_name;
			$_SESSION["address"] = $objUser->address;
			$_SESSION["region"] = $objUser->region;
			$_SESSION["skladID"] = $objUser->skladID;
			$_SESSION["buh"] = $objUser->buh;
			$_SESSION["position"] = $objUser->position;
			$_SESSION["pasport"] = $objUser->pasport;
			if($_SESSION['role_id'] == '2')
				$_SESSION["fio"] = $_SESSION["buh"];
			else
				$_SESSION["fio"] = $objUser->fio;
		
			header("Location: actions.php"); 
			exit();
		}
		else{
			$_SESSION["user_it"] = $objUser->user_id;
			$_SESSION["user_hash"] = $hash;	
			header("Location: kkm.php"); 
			exit();			
		}

    }
    else
    {
		$_SESSION['error'] = 'Неверный логин или пароль';
        header("Location: index.php"); 
		exit();
    }
}
if (isset($_SESSION['user_id']) and isset($_SESSION['user_hash'])){
    if(usrchk($_SESSION['user_id'],$_SESSION['user_hash'])) {
		header("Location: actions.php"); 
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
