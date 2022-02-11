<?
session_start();
require_once('php/bookPageClass.php');

if (isset($_SESSION['user_id']) and isset($_SESSION['user_hash']))
{   
    if(usrchk($_SESSION['user_id'],$_SESSION['user_hash']))
    {
/********************************************** Отображение титульной страницы ********************************************/
if(isset($_GET['arch']) && !empty($_GET['arch'])) {
    $arch = $_GET['arch'];
    $start = new datetime(str_replace('_','',$arch).'-12-01');
    $end = new datetime(str_replace('_','',$arch).'-12-31');
}
else {
    $arch = '';
    $start = new datetime(date('Y-m-d'));
    $end = new datetime((date('Y-m-d')));
}
/**************************************************************************************************************************/


/********************************************** Отображение титульной страницы ********************************************/

/*		if(isset($_GET['title']) && !empty($_GET['title'])){
			$date  = $mysqli->real_escape_string($_GET['title']);
			if($r = create_title($arch)){
				header("Location: {$r}"); 	
				exit;
			}
		}	
*/
/**************************************************************************************************************************/
		
		
/************************************************ Смена кассира для страницы **********************************************/		
		
		if(isset($_GET['id']) && !empty($_GET['id']) && isset($_GET['kassir']) && !empty($_GET['kassir'])){
			$id 	 = $mysqli->real_escape_string($_GET['id']);												// ID страницы
			$kassir  = $mysqli->real_escape_string($_GET['kassir']);											// Кассир из формы
			Page::setKassir($kassir, $id, $arch);																		// Устанавливаем кассира для страницы
			header("Location: kassa.php?arch=".$arch);
		}
		
/**************************************************************************************************************************/				

/********************************************** Удаление листов кассовой книги ********************************************/

		if(!empty($_GET['del']) && ($_SESSION['role_id'] == 2)){
			$q = "
				DELETE FROM 
					`book{$arch}`
				WHERE 
					date>='".$_GET['del']."'
				AND
					`skladID` = '{$_SESSION['skladID']}'			
			";
			$mysqli->query($q);
		}
		
/**************************************************************************************************************************/	

/********************************************** Отображение печатной форма PDF ********************************************/

		if(isset($_GET['id_pdf']) && !empty($_GET['id_pdf'])){
			$id  = $mysqli->real_escape_string($_GET['id_pdf']);												// ID страницы
			$book = new Book($_SESSION['skladID']);											
			$pageDataPDF = $book->getPageDataPDF($id, $arch);														// Объект с данными из таблиц book и azs для печатной формы PDF
			
			if($r = $book->createPagePDF($pageDataPDF, $arch)){
				header("Location: {$r}"); 	
				exit;
			}

		}
		
/**************************************************************************************************************************/	

/************************************************* Создание новой страницы ************************************************/	
		
		if(isset($_GET['new'])){
			
			$book	= new Book($_SESSION['skladID']);
		
			$kassir = $mysqli->real_escape_string($_GET['new']);
			$buh	= $_SESSION['buh'];
			
			if($lastPageData = $book->getLastPageData($arch))
			{
				$lastDate = new DateTime($lastPageData->date);
				$today = new DateTime(date('Y-m-d'));

				if($lastDate->modify('+1 day') < $today)
				{
					$newPageData = $book->newPageData($lastPageData, $kassir, $buh, $lastDate->format('Y-m-d'), $arch);
					$book->savePage($newPageData, $arch);
					header("Location: kassa.php?arch=".$arch);
				}
				else{
					$_SESSION["error"] = "Нельзя сформировать лист кассовой	 книги день в день";
					setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
                    header("Location: kassa.php?arch=".$arch);
					exit();					
				}				
			}
			else{

				$_SESSION["error"] = "Нет первоначальных данных";
				setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
                header("Location: kassa.php?arch=".$arch);
				exit();					
			}			

			exit;
		}
		
/**************************************************************************************************************************/		
		
		$heading = "Кассовая книга{$arch}";
		
		require_once('template/head.php');
?>
<div class="container">
<?require_once('template/exit.php');?>
	<div class="jumbotron">
		<h2><?php echo $heading;?></h2>
		<?
		
			
			$q = "
				SELECT 
					`book{$arch}`.*,
					`book{$arch}`.`date` as last_date
				FROM 
					`book{$arch}`
				WHERE 
					date=(
						SELECT 
							MAX(`date`) 
						FROM 
							`book{$arch}`
						WHERE 
							`skladID` = '{$_SESSION['skladID']}'					
					)
				AND
					`skladID` = '{$_SESSION['skladID']}'			
			";

			$date ='';
			
			if ($result = $mysqli->query($q)){
				if($result->num_rows > 0){
					if($obj = $result->fetch_object()){
						$date = new DateTime($obj->last_date);
						$date->modify('+1 Day');
					}				
				}
				else{
					$_SESSION["error"] = "Нет первоначальных данных";
					setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
					header("Location: kassa.php"); 
					exit();					
				}				
			}

		
		
			$serials = '';
			$q2 = "
				SELECT 
					`fio`, 
					`user_id`
				FROM 
					`users` 
				WHERE 
					`azs_id` = '{$_SESSION['skladID']}'
				AND
					`role_id` = '1'
			";


			if ($result1 = $mysqli->query($q2)){
				if($result1->num_rows > 0){
					$serials.='
					  <div class="form-group">
						<label for="new">Выберите кассира для листа кассовой книги:</label>
						<select name="new" id="new" class="form-control" required>
							<option></option>
					';
					while ($data1 = $result1->fetch_object()){
						if(!empty(trim($data1->fio))){
							$serials.= '<option value="'.$data1->fio.'">'.$data1->fio.'</option>';		
						}
					}
					$serials.= '
						</select>
					  </div>			
					';
				}
				else{
					$_SESSION["error"] = 'Для АЗС не созданы пользователи';
					setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
					header("Location: actions.php"); 
					exit();		
				}		
			}
			else
			{
				$_SESSION["error"] = 'Ошибка при получении списка пользователей '.$mysqli->error;
				setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
				header("Location: actions.php"); 
				exit();	
			}	
		
		?>
		<?
		if(empty($arch)){?>
		<form class="forms" id=""  action="/kassa.php" method="GET">
			<?=$serials;?>
			<p><input type="submit" class="btn btn-primary" role="button" value="Создать лист для <?echo $date->format('d-m-Y')?>"></p>
		</form>
		
		<?
			//echo '<p><a class="btn btn-primary" href="/kassa.php?title=create" role="button">Создать титульный лист за 2 полугодие 2017</a></p>';
		}?>
		<br>
		<?
		
		if(!empty($arch)){			

//			$tt = new DateTime('2020-01-01');
//			$ttt = new DateTime('2021-01-04');
//			if($date>=$tt && $date<=$ttt){?>
			<?php if (substr($arch,1) == $date->format('Y')):?>
			<form class="forms" id=""  action="/kassa.php" method="GET">
				<?=$serials;?>
				<input style="display:none" name="arch" value="<?=$arch?>">
				<p><input type="submit" class="btn btn-primary" role="button" value="Создать лист для <?=$date->format('d-m-Y')?>"></p>
			</form>
			<?php endif;?>
			
			<?
				
//			}
			//echo '<p><a class="btn btn-primary" href="/kassa.php?title=create&arch=_2018" role="button">Создать титульный лист</a></p>';
		}
		
		?>
				<form class="form-inline" action="kassa.php" method="GET">
					<input style="display:none" type="text" name="arch" value="<? echo $arch;?>">
					<div class="form-group">
					  <label for="datetime">Выбрать период (дд.мм.гггг)</label>
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

					 <button type="submit" class="btn btn-default">Применить</button>		
					
					
				</form>			
		
		<?php 
		
			$filter = ' AND `date` > (CURRENT_TIMESTAMP - INTERVAL 10 DAY) ';	

			if(isset($_GET['start']) && !empty($_GET['start']) && isset($_GET['end']) && !empty($_GET['end'])){
				$start = new datetime($_GET['start']);
				$end = new datetime($_GET['end']);
				
				$filter = ' AND `date` >= \''.$start->format('Y-m-d').'\' AND `date` <= \''.$end->format('Y-m-d').'\' ';
			}

		
			if(isset($_SESSION['error']))
			{
				echo '<div class="alert alert-danger" role="alert">'.$_SESSION['error'].'</div>';
				unset ($_SESSION['error']);
			}
		 ?>			
<?php
	
	$q = "
		SELECT 
			`book{$arch}`.*,
			`status`.`name`,
			`status`.`system`
		FROM 
			`book{$arch}` 
		INNER JOIN
			`status`
		ON
			`book{$arch}`.`status` = `status`.`id`
		WHERE 
			skladID=? 
			".$filter."
			AND
			`book{$arch}`.`status` != '10'
		ORDER BY 
			date 
		DESC
	";
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
						  <th>Дата</th>
						  <th>АЗС</th>
						  <th>#</th>
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
						  <td><?=$date->format('d.m.Y')?></td>
						  <td><?=$data->azs?></td>
						  <td><a title="Распечатать" href="kassa.php?id_pdf=<?=$data->id?><?php echo (!empty($arch)) ? '&arch='.$arch : '' ?>">распечатать</a></td>
						  <td>
							<?
										$serials = '';
										$selected = '';
										$q2 = "
											SELECT 
												`fio`, 
												`user_id`
											FROM 
												`users` 
											WHERE 
												`azs_id` = '{$_SESSION['skladID']}'
											AND
												`role_id` = '1'
										";
										if ($result1 = $mysqli->query($q2)){
											if($result1->num_rows > 0){
												$serials.='
												  <div class="form-group">						
													<select name="kassir" id="kassir" class="form-control" required>
														<option></option>
												';
												while ($data1 = $result1->fetch_object()){
													if(!empty(trim($data1->fio))){
														if($data1->fio == $data->kassir) $selected = 'selected';
														$serials.= '<option '.$selected.' value="'.$data1->fio.'">'.$data1->fio.'</option>';	
														$selected = '';
													}
												}
												$serials.= '
													</select>
												  </div>			
												';
											}
											else{
												$_SESSION["error"] = 'Для АЗС не созданы пользователи';
												setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
												header("Location: actions.php"); 
												exit();		
											}		
										}
										else
										{
											$_SESSION["error"] = 'Ошибка при получении списка пользователей '.$mysqli->error;
											setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
											header("Location: actions.php"); 
											exit();	
										}	
							?>			

							<form class="form-inline" action="/kassa.php" method="GET">
								<?=$serials;?>
								<input style="display:none" name="id" value="<?=$data->id?>">
                                <?php echo (!empty($arch)) ? '<input style="display:none" name="arch" value="'.$arch.'">' : '' ?>
								<input type="submit" class="btn btn-default" role="button" value="OK">
							</form>	
						  </td>
						  <td class="<?=$data->system?>" >
							<?=$data->name?>
							<?php if ($_SESSION['role_id'] == 2):?>
							<br /><a title="Удалить лист" href="kassa.php?del=<?=$data->date?><?php echo (!empty($arch)) ? '&arch='.$arch : '' ?>" onclick="return confirm('Внимание!!! Будет удален этот лист и ВСЕ листы за последующие даты до сегодняшнего числа. Подтвердите удаление.')">удалить</a>
							<?php endif;?>
						  </td>
						</tr>		
			<?php							
						}						
						$result->close();
			?>
					  </tbody>
				</table>	
			<a href="actions.php">назад</a>

	<?}?>
	</div>		
</div>		 
<?
require_once('template/bottom.php');
		
	
//*************************************************************************************************
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
	setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
	header("Location: index.php"); 
	exit();
}

?>