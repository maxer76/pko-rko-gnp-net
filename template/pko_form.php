<? 
defined('_JEXEC') or die('Access denied');

if(isset($_GET['smena']) && $_GET['smena'] == 'close'){

				if (is_dir("export/import/{$_SESSION['skladID']}")){
					
					$uploaddir = "export/import/{$_SESSION['skladID']}/";
					
					$out = "out{$_SESSION['skladID']}-";
					$out_file = '';
					$filter = $uploaddir.'{'.$out.'*.zip}';

					foreach(glob($uploaddir.'*.XML') as $file){
						unlink($file);
					}
					
					$ftime = 0;
					foreach(glob($filter, GLOB_BRACE) as $file){
						$pos = strripos($file, $out);
						if($pos !== false) {
							if ($ftime < filectime($file)) {
								$ftime = filectime($file);
								if ($out_file)
									@unlink($out_file);
								$out_file = $file;
							}
							else
								@unlink($file);
						}
					}

					if(!empty($out_file)){
						echo '<div class="alert alert-success" role="alert">Получены данные за смену</div>';
?>
						<form class="forms" id=""  action="/print.php" method="POST">
							<input style="display:none" type="text" name="form_id" value="<?=$_SESSION['form_id']?>">	
							<input style="display:none" name="type" value="pko">
							<input style="display:none" name="schet" value="50.02">
<?

?>	
							<input style="display:none" name="filename" value="<?=$file?>">
							
<?
							$q = "
								SELECT
									`serial`
								FROM
									`kkm`
								WHERE
									`azs` = '{$_SESSION["skladID"]}'
								ORDER BY
									`serial`
							";	
						
							if ($result = $mysqli->query($q)){	
								if($result->num_rows > 0){
																	
									while ($data = $result->fetch_object()){
										$serials.='<div class="form-group">';
											$serials.='<label for="pril">Z-отчёт для ККМ <b style="color:red"> '.trim($data->serial).'</b> <br>Суточный отчет с гашением №</label>';
											$serials.='<input type="number" class="form-control" id="pril" name="kkm__'.trim($data->serial).'" placeholder="" required>';
										$serials.='</div>';
										$q2 = "
											SELECT 
												`fio`, 
												`user_id`,
												`role_id`
											FROM 
												`users` 
											WHERE 
												`azs_id` = '{$_SESSION['skladID']}'
										";
										if ($result1 = $mysqli->query($q2)){
											if($result1->num_rows > 0){
												$serials.='
												  <div class="form-group">
													<label for="ot__'.trim($data->serial).'">Принято от:</label>
													<select name="ot__'.trim($data->serial).'" id="ot__'.trim($data->serial).'" class="form-control" required>
														<option></option>
												';
												while ($data1 = $result1->fetch_object()){
													if(!empty(trim($data1->fio))){
														$serials.= '<option value="'.$data1->fio.'">'.$data1->fio.'</option>';		
													}
												}
												$serials.= '
													</select>
													<br>
													<br>
												  </div>			
												';
											}
											else{
												$_SESSION["error"] = 'Для АЗС не созданы пользователи';
												setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
												header("Location: editkkm.php"); 
												exit();		
											}		
										}
										else
										{
											$_SESSION["error"] = 'Ошибка при получении списка пользователей '.$mysqli->error;
											setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
											header("Location: editkkm.php"); 
											exit();	
										}											
									}
									echo $serials;
								}
								else{
									$_SESSION["error"] = 'Для АЗС с №'.$_SESSION["skladID"].' не добавлены ККМ';
									setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
									header("Location: action.php?type={$type}"); 
									exit();		
								}
							}	
							
?>
							<p><input type="submit" class="btn btn-primary" role="button" value="Закрытие смены"></p>
						</form>							
							
<?					
					}
					else{
						echo '<div class="alert alert-danger" role="alert">Отсутствует файл с даными о закрытии смены. Попробуйте позже. <br> Время появления данных после закрытия смены в АЗС+ зависит от скорости работы Интернет. Продполагаемое время < 5 минут.</div>';
					}
					
				} 
				else{
					echo '<div class="alert alert-danger" role="alert">Отсутствует файл с даными о закрытии смены. Попробуйте позже. <br> Время появления данных после закрытия смены в АЗС+ зависит от скорости работы Интернет. Продполагаемое время < 5 минут.</div>';
				}
?>

				
		
<?			
}
else{
			if(isset($_SESSION['error']))
			{
				echo '<div class="alert alert-danger" role="alert">'.$_SESSION['error'].'</div>';
				unset ($_SESSION['error']);
			}	
?>	

<form class="forms" id="" action="print.php" method="POST">
		<input style="display:none" type="text" name="form_id" value="<?=$_SESSION['form_id']?>">	
		<input style="display:none" type="text" name="type" value="pko">
		<input style="display:none" type="text" name="manual" value="manual">
		<input type="hidden" name="ret" value="0<?=''//!empty($_GET['ret'])?1:0?>">
		
		
<?
if(isset($_GET['ned']) && $_GET['ned'] == 'ned')
	echo '
		<input style="display:none" name="schet" value="91.01">
		<div class="form-group">	
			<label for="sum">Сумма документа</label>
			<input type="number" step="0.01" class="form-control" id="sum" name="sum" placeholder="Сумма" required>
		</div>
	';
else
	echo '
		<input style="display:none" name="schet" value="50.02">
		<div class="form-group">	
			<label for="sum">Сумма выручки (ПРОДАЖА)</label>
			<input type="number" step="0.01" class="form-control" id="sum" name="sum" placeholder="Сумма" required>
			<div class="alert alert-danger" role="alert">
				<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
				<span class="sr-only">!!!</span>
				<b>Внимание!</b> Сверьте сумму с Z-отчётом!
			</div>	
		</div>	
	
	';

	$q = "
		SELECT 
			`fio`, 
			`user_id`,
			`role_id`
		FROM 
			`users` 
		WHERE 
			`azs_id` = '{$_SESSION['skladID']}'
	";
	if ($result = $mysqli->query($q)){
		if($result->num_rows > 0){
			echo '
			  <div class="form-group">
				<label for="azs_id">Принято от:</label>
				<select name="ot" class="form-control" required>	
					<option></option>
			';
			while ($data = $result->fetch_object()){
				if(!empty(trim($data->fio))){
					echo '<option value="'.$data->fio.'">'.$data->fio.'</option>';	
				}
			}
			echo '
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
	if(isset($_GET['ned']) && $_GET['ned'] == 'ned'){
		echo '
		<div class="form-group">
			<label for="pril">Основание</label>
			<input readonly type="text" class="form-control" id="osnov" name="osnov" value="Излишки наличных денежных средств в кассе АЗС №'.$_SESSION['skladID'].'">
		</div>';
	}
	else
	{
							$q = "
								SELECT
									`serial`
								FROM
									`kkm`
								WHERE
									`azs` = '{$_SESSION["skladID"]}'
								ORDER BY
									`serial`
							";	
						
							if ($result = $mysqli->query($q)){	
								if($result->num_rows > 0){
									$serials.='<div class="form-group">';
									$serials.='<label for="osnov">Выберите ККМ</label>';
									$serials.='<select class="form-control" name="osnov">';
									while ($data = $result->fetch_object()){
											$serials.='<option value="'.trim($data->serial).'">'.trim($data->serial).'</option>';
									}
									$serials.='</select>';
									$serials.='</div>';
									echo $serials;
								}
								else{
									$_SESSION["error"] = 'Для АЗС с №'.$_SESSION["skladID"].' не добавлены ККМ';
									setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
									header("Location: action.php?type={$type}"); 
									exit();		
								}
							}
}							
?>
		<div class="form-group">
<?
if(isset($_GET['ned']) && $_GET['ned'] == 'ned')
	echo '<label for="pril">Приложение</label>';
else
	echo '<label for="pril">Суточный отчет с гашением №</label>';
?>		
			
			<input type="text" class="form-control" id="pril" name="pril" placeholder="Приложение" required>
		</div>
		
			<input type="submit" class="form-control" value="Сохранить" onclick="return _chck_pko_sum_before_print()" />
		

	</form>
<?}?>