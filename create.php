<?php 
session_start();
define( '_JEXEC', 1 );
$serials = '';
require_once('php/functions.php');

if (isset($_SESSION['user_id']) and isset($_SESSION['user_hash'])){
    if(usrchk($_SESSION['user_id'],$_SESSION['user_hash'])) {
	
		if ($_SESSION['role_id'] == '2'){
			$_SESSION["error"] = 'Директорам запрещено создавать ПКО и РКО';
			setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
			header("Location: index.php"); 
			exit();	
		}
	
	$form_id = rand(1000000, 9999999);
	
	$_SESSION['form_id'] = $form_id;
	
	switch ($_GET['type']){
			case 'pko': 
				if(isset($_GET['smena']) && $_GET['smena'] == 'close'){
					$heading='Создать ПКО закрытие смены';
				}
				elseif(isset($_GET['empty']) && $_GET['empty'] == 'yes'){
					$heading='ПКО без закрытия смены';
				}  
				elseif(isset($_GET['ned']) && $_GET['ned'] == 'ned'){
					$heading='ПКО на сумму выявленного излишка';
				} else 
					$heading='Создать ПКО для возврата'; 
				
				$type=$_GET['type']; 
				break;
			case 'rko': 
				if(isset($_GET['ink']) and  $_GET['ink'] == 'yes'){
					$heading='Создать РКО для инкассации';
				} 
				elseif(isset($_GET['ned2']) and  $_GET['ned2'] == 'ned2'){
					$heading='РКО на сумму выявленной недостачи';
				} 
				else 
					$heading='Создать РКО для возврата'; 
					
				$type=$_GET['type']; 
				break;
			case 'kassa': $heading='Касса'; $type=$_GET['type']; break;
			default: $_SESSION["error"] = 'Выберите правильное действие'; setlog($_SESSION['user_id'],$_SESSION["error"],$page_);header("Location: actions.php"); exit();
		}

	require_once('template/head.php');																																								if (!empty($progstat['status'])) {header('Location: actions.php');exit();}
?>

<div class="container">
		<?php
			require_once('template/exit.php');
		?>
	<div class="jumbotron">
	<h2><?php echo $heading;?></h2>  

<?php
switch ($type){
	case 'pko': require_once('template/pko_form.php'); break;
	case 'rko':	require_once('template/rko_form.php'); break;
}
?> 
	
	<a href="actions.php">назад</a>
	</div>
</div>



<?php
require_once('template/bottom.php');				
    }
    else {
		$_SESSION["error"] = 'Неверный логин или пароль';
		setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
        header("Location: index.php"); 
		exit();	
    }
}
else {
	$_SESSION["error"] = 'Неверный логин или пароль';
	setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
	header("Location: index.php"); 
	exit();
}


?>