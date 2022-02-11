<?session_start();
define( '_JEXEC', 1 );
require_once('php/functions.php');

if (isset($_SESSION['user_id']) and isset($_SESSION['user_hash']) and $_SESSION['role_id'] == '2'){
    if(usrchk($_SESSION['user_id'],$_SESSION['user_hash'])) {

	if(isset($_GET['del']) && !empty($_GET['del'])){
		$del = htmlspecialchars($_GET['del']);
		
		$q = "
			DELETE FROM `users` WHERE user_login = '{$del}' AND azs_id = '{$_SESSION['skladID']}'
		";
		
		if ($result = $mysqli->query($q)){
			header("Location: logins.php"); 
			exit();					
		}
		else{
			$_SESSION["error"] = $mysqli->error;
			setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
			header("Location: logins.php?type={$type}"); 
			exit();					
		}
		
	}	
	
	if(isset($_REQUEST['edit']) && !empty($_REQUEST['edit'])){
		if(preg_match("/^[0-9]+$/",$_REQUEST['edit'])){
			$user_id = $_REQUEST['edit'];
			$position = $mysqli->real_escape_string($_REQUEST['position']);
			$pasport = $mysqli->real_escape_string($_REQUEST['pasport']);
			$fio = $mysqli->real_escape_string($_REQUEST['fio']);
			
			$q = "
				UPDATE 
					`users` 
				SET 
					`position` = '{$position}',
					`fio` = '{$fio}',
					`pasport`= '{$pasport}'
				WHERE `user_id` = '{$user_id}'
			";
			$mysqli->query($q);
			
		}
		
		header("Location: logins.php"); 
		exit;
	}
	
	

	if(isset($_REQUEST['new']) && !empty($_REQUEST['new'])){
		$heading='Добавление учетной записи';
		require_once('template/head.php');	
?>
		<div class="container">
<?php
	require_once('template/exit.php');
?>		
		  <div class="jumbotron">
			<h2><?php echo $heading;?></h2>  	
<?	
			switch ($_REQUEST['new']){
				case 'new': 		
					require_once('template/logins_new_form.php'); 
					break;
				case 'edit':
					if(preg_match("/^[0-9]+$/",$_REQUEST['item'])){
						$user_id = $_REQUEST['item'];
						$q = "SELECT * FROM `users` WHERE `user_id` = '{$user_id}' LIMIT 1";
						if ($result = $mysqli->query($q)){
							if($result->num_rows > 0){
								while ($data = $result->fetch_object()){
									$position = $data->position;
									$pasport = $data->pasport;
									$fio = $data->fio;
								}
							}
						}
						require_once('template/logins_edit_form.php'); 						
					}					
					break;					
				case 'save':
					$err = array();
					if(!preg_match("/^[a-zA-Z0-9]+$/",$_REQUEST['login'])){
						$err[] = "Логин может содержать только латинские буквы и цифры";
					}					 
					if(strlen($_REQUEST['login']) < 3 or strlen($_REQUEST['login']) > 30){
						$err[] = "Логин должен быть боль 3 и меньше 30 символов";
					}		
					$q = "
						SELECT 
							COUNT(user_id) as cnt
						FROM 
							users 
						WHERE 
							user_login='".$mysqli->real_escape_string($_REQUEST['login'])."'"
					;
					
					if ($result = $mysqli->query($q)){
						$data = $result->fetch_object();
						if($data->cnt > 0)
							$err[] = "Такой логин уже существует";
					}
					
					if(count($err) == 0){
						$login = $_REQUEST['login'];
						$fio = $_REQUEST['fio'];
						$position = $_REQUEST['position'];
						$open_pass = $_REQUEST['pass'];

						$password = md5(md5(trim($_REQUEST['pass'])));
						
						
						$mysqli->query("INSERT INTO users SET user_login='".$login."', user_password='".$password."', fio='".$fio."', position='".$position."', open_pass='".$open_pass."', azs_id='".$_SESSION['skladID']."', role_id='1'");

						header("Location: logins.php"); exit();
					}
					else{
						print "<b>Во время создания пользователя возникли следующие ошибки:</b><br>";
						foreach($err AS $error)
						{
							print $error."<br>";
						}
					}					
				
				
					break;
				default:	
					require_once('template/logins_edit_form.php'); 
					break;
			}
?>		
			<a href="logins.php">Назад</a>
		  </div>
		</div>
<?
	}
	else{
	$heading='Управление учетными записями (АЗС '.$_SESSION['skladID'].')';
	require_once('template/head.php');	
?>
	<div class="container">
		<?php
			require_once('template/exit.php');
		?>
		<div class="jumbotron">
			<h2><?php echo $heading;?></h2>  
			
			<?
			$q = "
				SELECT 
					* 
				FROM 
					`users` 
				WHERE 
					`azs_id`='{$_SESSION['skladID']}' 
				AND 
					(`role_id` = '1'
				OR
					`role_id` = '2')
				ORDER BY
					`fio`
			";
			
			if ($result = $mysqli->query($q)){

				echo '<table class="table table-hover">
					  <thead>
						<tr>
						  <th>Логин</th>
						  <th>ФИО</th>
						  <th>Должность</th>
						  <th>#</th>
						  <th>#</th>
						</tr>
					  </thead>';		

				if($result->num_rows > 0){

					echo '<tbody>';

					while ($data = $result->fetch_object()){
						echo '<tr>';
						
						echo "<td>{$data->user_login}</td>";	
						echo "<td>{$data->fio}</td>";	
						echo "<td>{$data->position}</td>";	
						echo '<td><a title="редактировать" href="logins.php?new=edit&item='.$data->user_id.'">редактировать</a></td>';
/* 26.02.2020 добавлено окно подтверждения на удаление пользователя ---- исп.m.sukhoivanenko */
						if ($data->role_id != 2):
							echo '<td><a title="удалить" href="logins.php?del='.$data->user_login.'" onclick="return confirm(\'Внимание!\nВы собираетесь удалить пользователя '.$data->fio.'.\nЕсли все верно, нажмите ОК\')">удалить</a></td>';
						endif;
						
						echo '</tr>';
					}
					
					echo '</tbody>';
			
				}
				echo '</table>';
			}
			else{
				echo $mysqli->error;
			}				
			
			?>	
			<p><a class="btn btn-primary" href="/logins.php?new=new" role="button">Добавить</a></p>	
			<a href="actions.php">Назад</a>
		 </div>		
		<?php 
			if(isset($_SESSION['error']))
			{
				echo '<div class="alert alert-danger" role="alert">'.$_SESSION['error'].'</div>';
				unset ($_SESSION['error']);
			}
		 ?>			 
	</div>
<?php
}
		
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