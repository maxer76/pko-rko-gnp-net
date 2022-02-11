<?php
session_start();
$heading = 'Меню';
require_once('php/functions.php');

if (isset($_SESSION['user_id']) and isset($_SESSION['user_hash']))
{   
    if(usradmchk($_SESSION['user_id'],$_SESSION['user_hash']))
    {
		if(isset($_GET['del']) && !empty($_GET['del']) && isset($_GET['azs']) && !empty($_GET['azs'])){
				$serial = mb_strtolower(trim($mysqli->real_escape_string($_GET['del'])), 'UTF-8');
				$azs = mb_strtolower(trim($mysqli->real_escape_string($_GET['azs'])), 'UTF-8');
				$pk = isset($_GET['pk'])?trim($mysqli->real_escape_string($_GET['pk'])):'';
				
				$q = "
					DELETE FROM 
						`kkm` 
					WHERE 
						`kkm`.`serial` = '{$serial}'
					LIMIT
						1
				";
				if ($result = $mysqli->query($q)){
					// update LOG KKM on monitoring server 172.16.251.159
					file_get_contents("http://172.16.251.159/monitoring/kkm_upload.php?data=" . 
										base64_encode(json_encode(['kkm_num'=>$serial,
																	'host'=>$pk,
																	'azs_id'=>$azs,
																	'user_id'=>$_SESSION['user_id'],
																	'user_name'=>$_SESSION['user_login'],
																	'oper'=>'delete',
																	'from_ip'=>$_SERVER['REMOTE_ADDR']])));
					header("Location: editkkm.php?azs_id={$azs}"); 
					exit();						
				}
				else{
					$_SESSION["error"] = 'Ошибка при удалении ККМ '.$mysqli->error;
					setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
					header("Location: editkkm.php"); 
					exit();		
				}			
			
		}
	
		require_once('template/head.php');
		
		
		
		
		if(isset($_POST['serial']) && !empty($_POST['serial']) && isset($_POST['pk']) && !empty($_POST['pk'])  && isset($_POST['azs']) && !empty($_POST['azs']) ){
			$serial = mb_strtolower(trim($mysqli->real_escape_string($_POST['serial'])), 'UTF-8');
			$azs = mb_strtolower(trim($mysqli->real_escape_string($_POST['azs'])), 'UTF-8');
			$pk = mb_strtolower(trim($mysqli->real_escape_string($_POST['pk'])), 'UTF-8');
            $osn = isset($_POST['serial']) ? $_POST['osn'] : '';
			$q = "
				INSERT INTO 
					`kkm`
							(`serial`, `azs`, `pse`, `osn`) 
				VALUES 
							('{$serial}','{$azs}','{$pk}', '{$osn}')			
			";

			if ($result = $mysqli->query($q)){
				// update LOG KKM on monitoring server 172.16.251.159
				file_get_contents("http://172.16.251.159/monitoring/kkm_upload.php?data=" . 
									base64_encode(json_encode(['kkm_num'=>$serial,
																'host'=>$pk,
																'azs_id'=>$azs,
																'user_id'=>$_SESSION['user_id'],
																'user_name'=>$_SESSION['user_login'],
																'oper'=>'create',
																'from_ip'=>$_SERVER['REMOTE_ADDR']])));
				header("Location: editkkm.php?azs_id={$azs}"); 
				exit();					
			}
			else{
				if($mysqli->errno == '1062'){
					$q = "
						SELECT 
							`azs`
						FROM 
							`kkm` 
						WHERE 
							`serial` = '{$serial}'
						LIMIT 
							1
					";
					if ($result = $mysqli->query($q)){
						$_SESSION["error"] = '<p>ККМ с таким серийным номером привязан к АЗС №'.$azs.'</p>';
					}
					else{
						$_SESSION["error"] = '<p>ККМ с таким серийным номером уже существует.</p><p>Произошла ошибка при определении АЗС к которой он привязан'.$mysqli->error.'</p>';
					}
					
				}
				
				setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
				header("Location: editkkm.php"); 
				exit();					
			}
			
		}
		
?>

	  <div class="container">	
		<p><a href="exit_adm.php" role="button">Выйти</a></p>
		<div class="jumbotron ">
			<h3>Регион <?php echo $_SESSION['region']?></h3>
<?php
	$q = "
		SELECT 
			`name`, 
			`skladID`
		FROM 
			`azs` 
		WHERE 
			`region` = '{$_SESSION['region']}'
	";
	if ($result = $mysqli->query($q)){
		if($result->num_rows > 0){
			echo '
			<form method="GET">
			  <div class="form-group">
				<label for="azs_id">Выберите АЗС</label>
				<select name="azs_id" class="form-control"  onchange="this.form.submit();">
					<option></option>
			';
			while ($data = $result->fetch_object()){
				$sel = ($_GET['azs_id'] == $data->skladID) ? 'selected' : '';
				echo '<option '.$sel.' value="'.$data->skladID.'">'.$data->name.'</option>';		
			}
			echo '
				</select>
			  </div>			
			</form>			
			';
		}
// fix permanent redirect 26.02.2020 m.sukhoivanenko
		elseif (empty($_SESSION['error'])){
			$_SESSION["error"] = 'В регионе '.$_SESSION['region'].' не найдены АЗС';
			setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
			header("Location: editkkm.php"); 
			exit();		
		}		
	}
// fix permanent redirect 26.02.2020 m.sukhoivanenko
	elseif (empty($_SESSION['error']))
	{
		$_SESSION["error"] = 'Ошибка при получении списка АЗС '.$mysqli->error;
		setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
		header("Location: editkkm.php"); 
		exit();	
	}	

	
	if(isset($_GET['azs_id']) && !empty($_GET['azs_id'])){
	
		$azs_id = $mysqli->real_escape_string($_GET['azs_id']);
	
		$q = "
			SELECT 
				`kkm`.`id`, 
				`kkm`.`serial`, 
				`kkm`.`azs`, 
				`kkm`.`pse`,
				`kkm`.`osn`,
				`azs`.`region`
			FROM 
				`kkm` 
			INNER JOIN 
				`azs`
			ON
				`kkm`.`azs` = `azs`.`skladID`
			WHERE 
				`kkm`.`azs` = '{$azs_id}'
			AND
				`azs`.`region` = '{$_SESSION["region"]}'
							
		";
		
	if ($result = $mysqli->query($q)){
		if($result->num_rows > 0){
			echo '
				<table class="table table-hover">
					  <thead>
						<tr>
						  <th>Имя компьютера</th>
						  <th>Серийный номер</th>
						  <th>Основание</th>
						  <th>#</th>
						</tr>
					  </thead>	
					  <tbody>
			';
			while ($data = $result->fetch_object()){
				  echo '
					<tr>
						<td>'.$data->pse.'</td>
						<td>'.$data->serial.'</td>
						<td>'.$data->osn.'</td>
						<td><a href="editkkm.php?azs='.$data->azs.'&del='.$data->serial.'&pk='.$data->pse.'">удалить</a></td>
					</tr>
					';		
			}
			echo '
					  </tbody>
					</table>	
			';
			
		}

?>
	<h3>Добавить ККМ</h3>
    <form method="POST" >
	<!-- fix on 26.02.2020 m.sukhoivanenko -->
        <input name="azs" type="hidden" class="form-control" id="azs" value="<?php echo $_GET['azs_id']?>">
        <div class="col-xs-4">
            <div class="form-group">
                <label for="pk">Имя компьютера</label>
                <input name="pk" type="text" class="form-control" id="pk" required>
            </div>
        </div>
        <div class="col-xs-4">
            <div class="form-group">
                <label for="serial">Серийный номер</label>
                <input name="serial" type="text" class="form-control" id="serial" required>
            </div>
        </div>
        <div class="col-xs-4">
            <div class="form-group">
                <label for="serial">Основание</label>
                <input name="osn" type="number" class="form-control" id="serial">
            </div>
        </div>
        <button type="submit" class="btn btn-default">Добавить</button>
    </form>

<?php
		
		
	}
// fix permanent redirect 26.02.2020 m.sukhoivanenko
	elseif (empty($_SESSION['error']))
	{
		$_SESSION["error"] = 'Ошибка при получении списка ККМ на АЗС '.$mysqli->error;
		setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
		header("Location: editkkm.php"); 
		exit();	
	}			
		
		
	}
?>		
			



			
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

		require_once('template/bottom.php');
	
    }
    else
    {
		$_SESSION["error"] = 'Неверный логин или пароль';
		setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
        header("Location: kkm.php"); 
		exit();	
    }
}
else
{
	$_SESSION["error"] = 'Неверный логин или пароль1';
	setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
	header("Location: kkm.php"); 
	exit();
}

?>
