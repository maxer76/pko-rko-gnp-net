<?
session_start();
define( '_JEXEC', 1 );
require_once('php/functions.php');

if (isset($_SESSION['user_id']) and isset($_SESSION['user_hash']) and $_SESSION['role_id'] == '2'){
    if(usrchk($_SESSION['user_id'],$_SESSION['user_hash'])) {
		
		// delete replacement record
		if(
			isset($_GET['del']) and
			!empty(trim($_GET['del']))
		){
			$q = "
				DELETE FROM `replacements` WHERE `id` = '{$_GET['del']}'		
			";
			$result = $mysqli->query($q);
			
			$q1 = "
				UPDATE 
					`azs` 
				SET 
					`replacement`='0'
				WHERE 
					`skladID` = '{$_SESSION['skladID']}'
				LIMIT
					1					
			";
			if ($result1 = $mysqli->query($q1)){
				
			}			
			
			header("Location: azssettings.php"); 
			exit();				
			
		}
		
		// edit AZS chief record
		if(
			isset($_POST['buh']) and
			!empty(trim($_POST['buh'])) and	
			isset($_POST['ink']) and
			!empty(trim($_POST['ink'])) and
			isset($_POST['address']) and
			!empty(trim($_POST['address']))
		)
		{
			$buh = $mysqli->real_escape_string($_POST['buh']);
			$ink = $mysqli->real_escape_string($_POST['ink']);
			$address = $mysqli->real_escape_string($_POST['address']);
			$position = $mysqli->real_escape_string($_POST['position']);
			
			
			
			$q = "
				UPDATE 
					`azs` 
				SET 
					`ink`='{$ink}',
					`address`='{$address}',
					`buh`='{$buh}',
					`position`='{$position}'
				WHERE 
					`skladID` = '{$_SESSION['skladID']}'
				LIMIT
					1
			";
			if ($result = $mysqli->query($q)){
				$_SESSION['buh'] = $buh;
				$_SESSION['address'] = $address;
				$_SESSION['fio'] = $buh;
				$_SESSION['ink'] = $ink;
				header("Location: actions.php"); 
				exit();						
			}
			else{
				$_SESSION["error"] = 'Ошибка в запросе при изменении параметров АЗС '.$mysqli->error;
				setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
				header("Location: azssettings.php"); 
				exit();					
			}
		}
		
		// new replacement record
		if(
			isset($_POST['rep_name']) and
			isset($_POST['rep_pos']) and
			isset($_POST['start']) and
			isset($_POST['end']) 
		){
			if(!empty(trim($_POST['rep_name'])) and	!empty(trim($_POST['rep_pos'])) and !empty(trim($_POST['start'])) and !empty(trim($_POST['end'])) ){

				$rep_name = $mysqli->real_escape_string($_POST['rep_name']);
				$rep_pos = $mysqli->real_escape_string($_POST['rep_pos']);
				$start = new datetime($_POST['start']);
				$start = $start->format('Y-m-d');
				$end = new datetime($_POST['end']);	
				$end = $end->format('Y-m-d');
				
				// check replacement interval in date 'from-to'
				$q = "
					SELECT * FROM `replacements`
						WHERE `skladID`='{$_SESSION['skladID']}'
							AND ('{$start}' between `from` and `to` OR '{$end}' between `from` and `to`)
						LIMIT 1
				";
				if (($result = $mysqli->query($q))
					&& !$mysqli->errno
					&& $result->num_rows) {
						echo 'in';
					$result = $result->fetch_assoc();
					$_SESSION["error"] = 'Невозможно добавить замещение. В период с <b>'.$result['from'].'</b> по <b>'.$result['to'].'</b> замещающим был(-а) <b>'.$result['fio'].'</b><br />Укажите другой диапазон дат, либо удалите замещение с ID <b>'.$result['id'].'</b>';
					header("Location: azssettings.php"); 
					exit();					
				}
				// check date interval is good
				$q = "
					INSERT INTO
						`replacements`
						(`skladID`, `fio`, `position`, `from`, `to`)
					VALUES
						('{$_SESSION['skladID']}', '{$rep_name}', '{$rep_pos}', '{$start}', '{$end}')
				";
				
				if ($result = $mysqli->query($q)){
					$q1 = "
						UPDATE 
							`azs` 
						SET 
							`replacement`='{$mysqli->insert_id}'
						WHERE 
							`skladID` = '{$_SESSION['skladID']}'
						LIMIT
							1					
					";
					if ($result1 = $mysqli->query($q1)){
					
						$q2 = "
							UPDATE 
								`pko` 
							SET 
								`buh`='{$rep_name}',
								`dir`='{$rep_name}',
								`dol`='{$rep_pos}',
								`status`='1'
							WHERE 
								`skladID` = '{$_SESSION['skladID']}'
							AND
								`date` >= '{$start}' 
							AND
								`date` <= '{$end}'
						";
						
						$mysqli->query($q2);
						
						$q2 = "
							UPDATE 
								`rko` 
							SET 
								`buh`='{$rep_name}',
								`dir`='{$rep_name}',
								`dol`='{$rep_pos}',
								`status`='1'
							WHERE 
								`skladID` = '{$_SESSION['skladID']}'
							AND
								`date` >= '{$start}' 
							AND
								`date` <= '{$end}'
						";		

						$mysqli->query($q2);
						
						
//						delete_book_from_date($start->format('Y-m-d'),$_SESSION["skladID"],'');

					}
						
					header("Location: azssettings.php"); 
					exit();						
				}
				else{
					$_SESSION["error"] = 'Ошибка в запросе к БД при получении параметров АЗС '.$mysqli->error;
					setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
					header("Location: azssettings.php"); 
					exit();						
				}
				
				

				
			}
			else{
				$_SESSION["error"] = 'Замещение не установлено. Некоторые поля заполнены не верно';
				setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
				header("Location: azssettings.php"); 
				exit();					
			}
			
		}
	

	$heading='Управление параметрами (АЗС '.$_SESSION['skladID'].')';
	require_once('template/head.php');	
?>
	<div class="container">
		<?php
			require_once('template/exit.php');
		?>
		<div class="jumbotron">
			<h2><?php echo $heading;?></h2>  
			<?php 
				if(isset($_SESSION['error']))
				{
					echo '<div class="alert alert-danger" role="alert">'.$_SESSION['error'].'</div>';
					unset ($_SESSION['error']);
				}
			 ?>					
<?
			$q1 = "
				SELECT 
					`buh`,
					`ink`,
					`address`,
					`position`
				FROM 
					`azs` 
				WHERE 
					`skladID` = '{$_SESSION['skladID']}'
				LIMIT 
					1
			";	
			if ($result1 = $mysqli->query($q1)){
				if($result1->num_rows > 0){
					while ($data1 = $result1->fetch_object()){
						echo '
						<form action="azssettings.php" method="POST">
							<div class="form-group">
								<label for="buh">ФИО директора</label>
								<input type="text" class="form-control" id="buh" name="buh" placeholder="ФИО" value="'.$data1->buh.'" required>
							</div>			
							<div class="form-group">
								<label for="ink">Наименование банка</label>
								<input type="text" class="form-control" id="ink" name="ink" placeholder="Банк" value="'.htmlspecialchars($data1->ink).'" required>
							</div>	
							<div class="form-group">
								<label for="address">Адрес</label>
								<input type="text" class="form-control" id="address" name="address" placeholder="Адрес" value="'.htmlspecialchars($data1->address).'" required>
							</div>	
							<div class="form-group">
								<label for="position">Должность</label>
								<input type="text" class="form-control" id="position" name="position" placeholder="Должность" value="'.$data1->position.'" required>
							</div>	
							<div class="form-group">
								<input type="submit" class="form-control" value="Сохранить">
							</div>								
						</form>
						';
					}
				}
				else{
					$_SESSION["error"] = 'В базе данных не найдена АЗС с таким номером';
					setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
					header("Location: actions.php"); 
					exit();					
				}
			}
			else{
				$_SESSION["error"] = 'Ошибка в запросе к БД при получении параметров АЗС '.$mysqli->error;
				setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
				header("Location: actions.php"); 
				exit();				
			}
	?>
	</div>
	<div class="jumbotron">
	<h3>Новое замещение</h3>
			
			
					<form action="azssettings.php" method="POST">
						<div class="form-group">
							<label for="rep_name">ФИО заместителя</label>
							<input type="text" class="form-control" id="rep_name" name="rep_name" placeholder="ФИО заместителя" value="">
						</div>			
						<div class="form-group">
							<label for="rep_pos">Должность заместителя</label>
							<input type="text" class="form-control" id="rep_pos" name="rep_pos" placeholder="Должность заместителя" value="">
						</div>	
						<div class="form-group">
						  <label for="datetime">Выбрать период замещения (дд.мм.гггг) </label>
						  <div class="form-inline">
							  <div class="input-group date" id="datetimepicker3">
								<input name="start" id="start"  type="text" class="form-control" />
								<span class="input-group-addon">
								  <span class="glyphicon glyphicon-calendar"></span>
								</span>
							  </div>
							  <div class="input-group date" id="datetimepicker4">
								<input name="end" id="end"  type="text" class="form-control" />
								<span class="input-group-addon">
								  <span class="glyphicon glyphicon-calendar"></span>
								</span>
							  </div>
						  </div>
						</div>										
						<div class="form-group">
							<input type="submit" class="form-control" value="Добавить замещение">
						</div>								
					</form>
	<h3>Предыдущие замещения</h3>
<?				$q = "
					SELECT
						*
					FROM	
						`replacements`
					WHERE
						`replacements`.`skladID` = {$_SESSION['skladID']}
					ORDER BY `from` DESC
					";
				
				if ($result1 = $mysqli->query($q)){
					if($result1->num_rows > 0){
						echo '<table class="table table-hover">
							  <thead>
								<tr>
								  <th>ID</th>
								  <th>ФИО</th>
								  <th>Должность</th>
								  <th>Дата начала</th>
								  <th>Дата окончания</th>
								  <th>#</th>
								</tr>
							  </thead>';		
						while ($data1 = $result1->fetch_object()){



									echo '<tr>';
									
									echo "<td><b>{$data1->id}</b></td>";	
									echo "<td>{$data1->fio}</td>";	
									echo "<td>{$data1->position}</td>";	
									echo "<td>{$data1->from}</td>";	
									echo "<td>{$data1->to}</td>";	
									echo '<td><a title="удалить" href="?del='.$data1->id.'" onclick="return confirm(\'Подтвердите удаление\')">удалить</a></td>';
									
									echo '</tr>';
								
						
						}
						echo '</table>';							
					}
				}				
				
			
?>			


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

		
require_once('template/bottom.php');
				
    }
    else {
		$_SESSION["error"] = 'РќРµРІРµСЂРЅС‹Р№ Р»РѕРіРёРЅ РёР»Рё РїР°СЂРѕР»СЊ';
		setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
        header("Location: index.php"); 
		exit();	
    }
}
else {
	$_SESSION["error"] = 'РќРµРІРµСЂРЅС‹Р№ Р»РѕРіРёРЅ РёР»Рё РїР°СЂРѕР»СЊ';
	setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
	header("Location: index.php"); 
	exit();
}