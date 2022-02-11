<?php
session_start();
require_once('php/functions.php');

$arch_year = date('Y');
$operation = '';

if (isset($_SESSION['user_id']) and isset($_SESSION['user_hash'])){
    if(usrchk($_SESSION['user_id'],$_SESSION['user_hash'])) {

	if (preg_match("/^(pko|rko)(_\d{4})?|kassa$/",$_GET['type'],$matches)) {
		$cy = date('Y');
		if (!empty($matches[2]))
			$y = (int)substr($matches[2],1);
		else
			$y = 0;
		// fix-0.0.18
		$operation = $matches[1];
		//------------------------
		switch ($operation) {
			case 'rko': if (!$y || ($y == $cy)) {
							$heading = 'РКО';
							$type = 'rko';
						}
						else {
							$heading = 'Архив РКО '.$y;
							$type = 'rko_'.$y;
							$arch_year = $y;
						}
						break;
			case 'pko': if (!$y || ($y == $cy)) {
							$heading = 'ПКО';
							$type = 'pko';
						}
						else {
							$heading = 'Архив ПКО '.$y;
							$type = 'pko_'.$y;
							$arch_year = $y;
						}
						break;
			case 'kassa': $heading='Касса';
							$type='kassa';
							break;
		}
	}
	else {
		$_SESSION["error"] = 'Выберите правильное действие'; setlog($_SESSION['user_id'],$_SESSION["error"],$page_);header("Location: actions.php"); exit();
	}
/*	========= fix-0.0.8
	switch ($_GET['type']){
			case 'pko': $heading='ПКО'; $type=$_GET['type']; break;
			case 'rko': $heading='РКО'; $type=$_GET['type']; break;
			case 'pko_2015': $heading='Архив ПКО 2015'; $type=$_GET['type']; $arch_year = '2015'; break;
			case 'pko_2016': $heading='Архив ПКО 2016'; $type=$_GET['type']; $arch_year = '2016'; break;
			case 'pko_2017': $heading='Архив ПКО 2017'; $type=$_GET['type']; $arch_year = '2017'; break;
			case 'pko_2018': $heading='Архив ПКО 2018'; $type=$_GET['type']; $arch_year = '2018'; break;
			case 'pko_2019': $heading='Архив ПКО 2019'; $type=$_GET['type']; $arch_year = '2019'; break;
			case 'rko_2015': $heading='Архив РКО 2015'; $type=$_GET['type']; $arch_year = '2015'; break;			
			case 'rko_2016': $heading='Архив РКО 2016'; $type=$_GET['type']; $arch_year = '2016'; break;			
			case 'rko_2017': $heading='Архив РКО 2017'; $type=$_GET['type']; $arch_year = '2017'; break;			
			case 'rko_2018': $heading='Архив РКО 2018'; $type=$_GET['type']; $arch_year = '2018'; break;			
			case 'rko_2019': $heading='Архив РКО 2019'; $type=$_GET['type']; $arch_year = '2019'; break;
			case 'kassa': $heading='Касса'; $type=$_GET['type']; break;
			default: $_SESSION["error"] = 'Выберите правильное действие'; setlog($_SESSION['user_id'],$_SESSION["error"],$page_);header("Location: actions.php"); exit();
		}
*/
		require_once('template/head.php');	

	if(isset($_GET['num']) && !empty($_GET['num'])){
		$number = $_GET['num'];

		if($_pdf = create_PDF($type, $number)){
			header('Content-Type: application/pdf');
			header("Location: {$_pdf}"); 
		}
		else{
			setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
			header("Location: actions.php?type={$type}"); 
			exit();						
		}	 	
	}
?>
	<div class="container">
		<?php
			require_once('template/exit.php');
		?>
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
		<div class="jumbotron">
			<h2><?php echo $heading;?></h2>  
			<a href="actions.php">назад</a>
			
				<form class="form-inline" action="action.php" method="GET">
					<input style="display:none" type="text" name="type" value="<?=$type?>">
					<div class="form-group">
					  <label for="datetime">Выбрать период (дд.мм.гггг)</label>
					  <div class="input-group date" id="datetimepicker3">
						<input name="start" id="start"  type="text" class="form-control" value="<?=date('d.m')?>.<?=$arch_year?>" />
						<span class="input-group-addon">
						  <span class="glyphicon glyphicon-calendar"></span>
						</span>
					  </div>
					  <div class="input-group date" id="datetimepicker4">
						<input name="end" id="end"  type="text" class="form-control" value="<?=date('d.m')?>.<?=$arch_year?>" />
						<span class="input-group-addon">
						  <span class="glyphicon glyphicon-calendar"></span>
						</span>
					  </div>
					</div>	

					 <button type="submit" class="btn btn-default">Применить</button>		
					
					
				</form>			
			
			<?php
			
			$filter = ' AND `date` > (CURRENT_TIMESTAMP - INTERVAL 7 DAY) ';
			
			if(isset($_GET['start']) && !empty($_GET['start']) && isset($_GET['end']) && !empty($_GET['end'])){
				$start = new datetime($_GET['start']);
				$end = new datetime($_GET['end']);
				
				$filter = ' AND `date` >= \''.$start->format('Y-m-d').'\' AND `date` <= \''.$end->format('Y-m-d').'\' ';
			}
			else{
				$start = new datetime(date('Y-m-d'));
				$end = new datetime((date('Y-m-d')));				
			}
			
			switch ($operation){
				// fix-0.0.18
				case 'pko':
/*				case 'pko_2019':
				case 'pko_2018':
				case 'pko_2017':
				case 'pko_2016':
				case 'pko_2015':
*/					
					
					$q = "SELECT * FROM `{$type}` WHERE `skladID` = ?".$filter."AND `status` = '1' ORDER BY date DESC, flag DESC, number_int DESC";
					
					$result = $mysqli->stmt_init();
					if ($result->prepare($q))
					{
						$result->bind_param("i",$_SESSION["skladID"]);
						$result->execute();
						$obj = $result->get_result();
			?>

				<table class="table table-hover">
					  <thead>
						<tr>
						  <th>Номер</th>
						  <th>Дата</th>
						  <th>Время</th>
						  <th>ККМ</th>
						  <th>Сумма</th>
						  <th>#</th>
						  <th>#</th>
						  <th>#</th>
						</tr>
					  </thead>		
					  <tbody>
			<?php			
						while ($data = $obj->fetch_object()){
							$date = new DateTime($data->datetime);
			?>
						<tr>
						  <th><?=$data->number?></th>
						  <td><?=$date->format('d.m.Y')?></td>
						  <td><?=$date->format('H:i:s')?></td>
						  <td><?=$data->osnov?></td>
						  <td><?=$data->sum + $data->sum10?></td>
						  <td><?if($_SESSION['role_id'] == 2 || $data->date == date("Y-m-d")){?><a title="Редактировать" href="edit.php?type=<?=$type?>&number=<?=$data->number?>">изменить</a><?}?></td>
						  <td><a title="Распечатать" href="action.php?type=<?=$type?>&num=<?=$data->number?>">распечатать</a></td>
						  <td><?=$data->upl_type?></td>

						</tr>
			<?php							
						}						
						$result->close();
			?>
					  </tbody>
					</table>	
			<?php						
					}					
			
					break;
				// fix-0.0.18
				case 'rko':				
/*				case 'rko_2019':				
				case 'rko_2018':				
				case 'rko_2017':				
				case 'rko_2016':				
				case 'rko_2015':				
*/				
					$q = "SELECT * FROM `{$type}` WHERE skladID=?".$filter."AND `status` = '1' ORDER BY datetime DESC";
					$result = $mysqli->stmt_init();
					if ($result->prepare($q))
					{
						$result->bind_param("i",$_SESSION["skladID"]);
						$result->execute();
						$obj = $result->get_result();
			?>
				<table class="table table-hover">
					  <thead>
						<tr>
						  <th>Номер</th>
						  <th>Дата</th>
						  <th>Время</th>
						  <th>Подразделение</th>
						  <th>Сумма</th>
						  <th>#</th>
						  <th>#</th>
						</tr>
					  </thead>		
					  <tbody>
			<?php			
						while ($data = $obj->fetch_object()){
							$date = new DateTime($data->datetime);
			?>
						<tr>
						  <th><?=$data->number?></th>
						  <td><?=$date->format('d.m.Y')?></td>
						  <td><?=$date->format('H:i:s')?></td>
						  <td><?=$data->azs?></td>
						  <td><?=$data->sum?></td>
						  <td><?if($_SESSION['role_id'] == 2 || $data->date == date("Y-m-d")){?><a title="Редактировать" href="edit.php?type=<?=$type?>&number=<?=$data->number?>">изменить</a><?}?></td>
						  <td><a title="Распечатать" href="action.php?type=<?=$type?>&num=<?=$data->number?>">распечатать</a></td>

						</tr>
			<?php							
						}						
						$result->close();
			?>
					  </tbody>
					</table>	
			<?php						
					}	
					break;
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
	setlog(isset($_SESSION['user_id'])?$_SESSION['user_id']:'',$_SESSION["error"],$page_);
	header("Location: index.php"); 
	exit();
}


?>