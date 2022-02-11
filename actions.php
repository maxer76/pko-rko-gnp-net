<?php
session_start();
$heading = 'Меню';
require_once('php/functions.php');

if (isset($_SESSION['user_id']) and isset($_SESSION['user_hash']))
{
    if(usrchk($_SESSION['user_id'],$_SESSION['user_hash']))
    {
		require_once('template/head.php');
?>

	  <div class="container">	
		<?php
			require_once('template/exit.php');
		?>
		<div class="jumbotron ">	
		
			<h2>Меню</h2>   
		<?php 
			if(isset($_SESSION['error']))
			{
				echo '<div class="alert alert-danger" role="alert">'.$_SESSION['error'].'</div>';
				unset ($_SESSION['error']);
			}
			if(isset($_SESSION['success']))
			{
				echo '<div class="alert alert-success" role="alert">'.$_SESSION['success'].'</div>';
				unset ($_SESSION['success']);
			}
		 ?>					
<?php
if(isset($_GET['type']) && !empty($_GET['type'])) $type = $_GET['type']; else $type='';

switch ($type) {
	case 'vozvrat': 
		echo '
			<div class="">
				<!-- <p><a class="btn btn-primary" href="/create.php?type=pko&ret=1" role="button">Шаг1. Создать ПКО</a></p> -->
				<p><a class="btn btn-primary" href="/create.php?type=rko" role="button">Шаг1. Создать РКО</a></p>
			</div>		
			<hr>
			
		';	
		break;
	case 'inkassaciya': 
		echo '
			<div class="">
				<p><a class="btn btn-primary" href="/create.php?type=rko&ink=yes" role="button">Шаг1. Создать РКО</a></p>
			</div>		
			<hr>
		';		
		break;
	case 'zakritie': 
	
		echo '
			<div class="">
				<p><a class="btn btn-primary" href="/create.php?type=pko&smena=close" role="button">Шаг1. Создать ПКО</a></p>
			</div>		
			<hr>
		';	
		break;
	default:
		if($_SESSION['role_id'] != '2')
			echo '
				<div class="mymenu">
					<p><a class="btn btn-primary" href="/actions.php?type=zakritie" role="button">Закрытие смены</a></p>
					<p><a class="btn btn-primary" href="/create.php?type=pko&empty=yes" role="button">ПКО без закрытия смены</a></p>			
					<p><a class="btn btn-primary" href="/actions.php?type=inkassaciya" role="button">Инкассация</a></p>
					<p><a class="btn btn-primary" href="/actions.php?type=vozvrat" role="button">Возврат</a></p>
					<p><a class="btn btn-primary" href="/create.php?type=rko&ned2=ned2" role="button">Недостача</a></p>
					<p><a class="btn btn-primary" href="/create.php?type=pko&ned=ned" role="button">Излишки</a></p>
					<p><a class="btn btn-primary" href="/kassa.php" role="button">Кассовая книга '.date('Y').'</a></p>
					<p><a class="btn btn-primary" href="/kassa.php?arch=_'.((int)date('Y')-1).'" role="button">Кассовая книга '.((int)date('Y')-1).'</a></p>'.
/*'					<p><a class="btn btn-primary" href="/kassa.php" role="button">Кассовая книга 2020</a></p>
=== fix-0.0.8		<p><a class="btn btn-primary" href="/kassa.php?arch=_2019" role="button">Кассовая книга 2019</a></p>'.
*/				'</div>';
		else{
			echo '
				<div class="mymenu">
					<p><a class="btn btn-primary" href="/kassa.php" role="button">Кассовая книга '.date('Y').'</a></p>
					<p><a class="btn btn-primary" href="/kassa.php?arch=_'.((int)date('Y')-1).'" role="button">Кассовая книга '.((int)date('Y')-1).'</a></p>
					<p><a class="btn btn-primary" href="/kassa.php?arch=_'.((int)date('Y')-2).'" role="button">Кассовая книга '.((int)date('Y')-2).'</a></p>
				</div>	 	
			';			
		}
		
		//
?>
			<hr>

<?php }?>			
			<p><a class="btn btn-default" href="/action.php?type=pko" role="button">Список всех ПКО</a> <a class="btn btn-default" href="/action.php?type=rko" role="button">Список всех РКО</a></p>
			<p><a class="btn btn-default" href="/action.php?type=pko_<?=((int)date('Y')-1)?>" role="button">ПКО <?=((int)date('Y')-1)?></a> <a class="btn btn-default" href="/action.php?type=rko_<?=((int)date('Y')-1)?>" role="button">РКО <?=((int)date('Y')-1)?></a></p>
			<p><a class="btn btn-default" href="/action.php?type=pko_<?=((int)date('Y')-2)?>" role="button">ПКО <?=((int)date('Y')-2)?></a> <a class="btn btn-default" href="/action.php?type=rko_<?=((int)date('Y')-2)?>" role="button">РКО <?=((int)date('Y')-2)?></a></p>
<?php if($_SESSION['role_id'] == '2'){?>			
			<p><a class="btn btn-default" href="/logins.php" role="button">Кассиры</a></p>
			<p><a class="btn btn-default" href="/azssettings.php" role="button">Параметры АЗС</a></p>
<?php }?>		
			<p></p>
			 <a href="actions.php">назад</a>
		 </div>
		
 
	  </div>

<?php

		require_once('template/bottom.php');
	
    }
    else
    {
		$_SESSION["error"] = 'Неверный логин или пароль';
		setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
        header("Location: index.php"); 
		exit();	
    }
}
else
{
	$_SESSION["error"] = 'Неверный логин или пароль';
	setlog(isset($_SESSION['user_id'])?$_SESSION['user_id']:'',$_SESSION["error"],$page_);
	header("Location: index.php"); 
	exit();
}

?>
