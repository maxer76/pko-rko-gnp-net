<?php
session_start();
$heading = 'Меню';
require_once('php/bookPageClass.php');

if (isset($_SESSION['user_id']) and isset($_SESSION['user_hash']))
{   
    if(buh_chk($_SESSION['user_id'],$_SESSION['user_hash']))
    {
		if(isset($_REQUEST['azs_id']) && !empty($_REQUEST['azs_id'])){
			
			if($azs_id = azs_exist($_REQUEST['azs_id'])) {
			

				$_SESSION["buh"] = get_buh_fio($azs_id);									// Получаем имя бухгалтера АЗС (!!!требуется доработка так как неучитывается замещение!!!)
				$_SESSION["skladID"] = $azs_id;
				$_SESSION['azs_name'] = 'АЗС '.$azs_id;
				$_SESSION['fio'] = $_SESSION["buh"];
			
/************************************************************************************************************/
				if(isset($_POST['manual']) && !empty($_POST['manual'])){
					
					$date = new datetime($_POST['date']);									// Дата с которой переформировываем книгу (из формы)
					$today = new DateTime(date('Y-m-d'));									// Текущая дата
					$start_date = new DateTime('2019-01-01');								// Минимальная дата
					$interval = $date->diff($today);										// Количество обрабатываемых дней
					$block = 10 / (int)$interval->format('%a');								// Количество шагов для ProgressBar 
					$total = 0;																// Общий счётчик
					$old_total = 0; 														// Предыдущий шаг счётчика
					
					
					if($date >= $start_date && $date < $today) {								// Проверяем дату. Должна быть больше минимальной и меньше текущей
					}
					else{
						$_SESSION["error"] = 'Неверная дата для пересоздания. Выберите дату между 01-01-2019 и '.date("d-m-Y", strtotime("-1 day"));
						setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
						header("Location: buh_console.php"); 
						exit();	
					}
					
					$book = new Book($_SESSION['skladID']);								
					$buh = $_SESSION["buh"];												
					
					$date->modify('-1 day');												// Уменьшаю выбранную дату на 1 день, что бы получить данные из предыдущего дня
					if($pagePrevDayData = $book->getPageData($date->format('Y-m-d')))		// Получаю данные предыдущего дня
					{
					}
					else
					{
						$_SESSION["error"] = 'Неверная дата для пересоздания. Остатки на конец дня '.$date->format('d-m-Y').' отсутствуют';
						setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
						header("Location: buh_console.php"); 
						exit();							
					}
					$date->modify('+1 day');												// Возвращаем дату к исходному значению
					
					echo '<div style="width:800px;text-align:center"><h2>Пересоздаю листы кассовой книги для АЗС '.$_SESSION['skladID'].'</h2>';
					echo '<div style="position:absolute;left:20px;top:60px;width:80px">'.$date->format('d-m-Y').'</div>';
					echo '<div style="position:absolute;left:720px;top:60px;width:80px">'.$today->format('d-m-Y').'</div>';					
					while($date < $today)
					{
						if($pageData = $book->getPageData($date->format('Y-m-d')))
						{
							$pagePrevDayData = $book->updatePageData($pagePrevDayData, $pageData); 						// Получаем данные для обновления страницы. Передаём предыдущий день и текущий день.
							$book->updatePage($pagePrevDayData);														// Обновляем страницу в базе
						}
						else
						{
							$pagePrevDayData = $book->newPageData($pagePrevDayData, '', $buh, $date->format('Y-m-d'));	// Получаем данные для новой страницы на основании данных последней страницы (последний параметр - это дата для новой страницы)
							$book->savePage($pagePrevDayData);															// Сохраняем страницу в базу
						}
						$total += $block;																				// Увеличиваем общий счётчик на размер блока
						if(floor($total) != floor($old_total))															// Если целая часть счётчика измениласть, то выводим блок
						{
							$width = $total*60;																			// Ширина блока
							echo '<div style="position:absolute;left:100px;top:60px;background-color:#999;width:'.$width.'px">&nbsp;</div>';
							$width = 0;
						}
						
						$old_total = $total;
						
						flush();					
						ob_flush();											
						$date->modify('+1 day');
					}
					
					echo '<h2 style="margin-top:70px">Готово!</h2>';
					echo '<a href="/buh_book.php?azs_id='.$azs_id.'">Вернуться назад</a></div>';

					exit;
				}

/************************************************************************************************************/			
			
			
			
			
			
			
				require_once('template/head.php');
		
	?>

				<div class="container">	
					<p><a href="exit_buh.php" role="button">Выйти</a></p>
					<div class="jumbotron ">
						<h3>Регион <?=$_SESSION['region']?> - АЗС <?=$_REQUEST['azs_id']?></h3>
						<?php 
							if(isset($_SESSION['error']))
							{
								echo '<div class="alert alert-danger" role="alert">'.$_SESSION['error'].'</div>';
								unset ($_SESSION['error']);
							}
							if(isset($_GET['suc']))
							{
								echo '<div class="alert alert-success" role="alert">Успешно выполенено</div>';
								unset ($_SESSION['error']);
							}
						?>					

						

<?
				if(!isset($_REQUEST['type']) && empty($_REQUEST['type']))
					echo '
						<a href="buh_console.php">назад</a>
						<br>
						<br>
						<br>					
							<form action="buh_book.php" method="POST">
							
									<input style="display:none" type="text" name="manual" value="manual">
									
									<input style="display:none" type="text" name="azs_id" value="'.$azs_id.'">
					';				

					echo '				
									<div class="form-group">
										<label for="datetime">С какой даты пересоздать</label>
									  <!-- Элемент HTML с id равным datetimepicker1 -->
									  <div class="input-group date" id="datetimepicker2">
										<input name="date" id="datetime"  type="text" class="form-control" />
										<span class="input-group-addon">
										  <span class="glyphicon glyphicon-calendar"></span>
										</span>
									  </div>
									</div>		
								
									<div class="form-group">
										<input type="submit" class="form-control btn btn-primary" value="Пересоздать">
									</div>										
						</form>					
					';


					$q = "
						SELECT 
							`book`.`list`,
							`book`.`date`,
							`book`.`start`,
							`book`.`end`,
							`book`.`kassir`,
							`status`.`name`,
							`status`.`system`
						FROM 
							`book` 
						INNER JOIN
							`status`
						ON
							`book`.`status` = `status`.`id`
						WHERE 
							skladID=? 
						AND
							`book`.`status` != '10'
						ORDER BY 
							`date`
						DESC	
					";
					
					$result = $mysqli->stmt_init();
					if ($result->prepare($q))
					{
						$result->bind_param("i",$azs_id);
						$result->execute();
						$obj = $result->get_result();
			?>
			<div class="col-md-10 col-md-offset-1">
				<h3>Листы</h3>
				<table class="table table-hover">
					  <thead>
						<tr>
						  <th>Номер</th>
						  <th>Дата</th>
						  <th>Начало дня</th>
						  <th>Конец дня</th>
						  <th>Кассир</th>
						  <th>Статус</th>
						</tr>
					  </thead>		
					  <tbody>
			<?php			
						while ($data = $obj->fetch_object()){
							$date = new DateTime($data->date);
			?>
						<tr>
						  <th><?=$data->list?></th>
						  <td><?=$date->format('d.m.Y')?></td>
						  <td><?=str_replace("-", "", $data->start)?></td>
						  <td><?=str_replace("-", "", $data->end)?></td>
						  <td><?=$data->kassir?></td>
						  <td class="<?=$data->system?>" ><?=$data->name?></td>

						</tr>
			<?php							
						}						
						$result->close();
			?>
					  </tbody>
					</table>
			</div>
			<?php						
					}
					else
						echo $mysqli->error;
			?>				
							
							
					</div>
					
		 
				  </div>

			<?php

				require_once('template/bottom.php');
			}
			else{
				$_SESSION["error"] = 'АЗС не найдена';
				setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
				header("Location: buh_console.php"); 
				exit();				
			}
		}
    }
    else
    {
		$_SESSION["error"] = 'Неверный логин или пароль';
		setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
        header("Location: buh_login.php"); 
		exit();	
    }
}
else
{
	$_SESSION["error"] = 'Неверный логин или пароль1';
	setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
	header("Location: buh_login.php"); 
	exit();
}

?>
