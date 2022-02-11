<?
session_start();
define( '_JEXEC', 1 ); 
require_once('php/functions.php');

if (isset($_SESSION['user_id']) and isset($_SESSION['user_hash'])){

					$arch = '';
					
					$doc_num = '';
					$q = "
						SELECT 
							*
						FROM
							`{$_REQUEST['type']}`
						WHERE
							`number` = '{$_REQUEST['number']}'
						AND
							`skladID` = '{$_SESSION['skladID']}'
						LIMIT
							1
					";
					if ($result = $mysqli->query($q)){
						if($result->num_rows > 0){
							while ($data = $result->fetch_object()){
								$doc_num = $data->date;
							}
						}
						else {
							$_SESSION["error"] = 'Документ на изменение не найден';
							setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
							header("Location: action.php?type={$type}"); 
							exit();
						}  						
					}
					else {
						$_SESSION["error"] = 'Ошибка при поиске документа на изменение '.$mysqli->error;
						setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
						header("Location: action.php?type={$type}"); 
						exit();
					}  					

 if(chk_close_period($doc_num))
  if ($_SESSION['role_id'] == 2 || $doc_num == date("Y-m-d")){
    if(usrchk($_SESSION['user_id'],$_SESSION['user_hash'])) {

if(isset($_POST['edit']) && $_POST['edit'] == 'edit'){

	switch (substr($_POST['type'],0,3)){
/*	==== fix-0.0.8
			case 'pko_2015': $arch = '_2015';
			case 'pko_2016': $arch = '_2016';
			case 'pko_2017': $arch = '_2017';
			case 'pko_2018': $arch = '_2018';
			case 'pko_2019': $arch = '_2019';
*/			case 'pko':
				if (($y = (int)substr($_POST['type'],4)) && ($y != date('Y'))) {
					$arch = '_'.$y;
				}
				$type=$_POST['type']; 
				if(
					!isset($_POST['number']) and
					empty($_POST['number'])	and	
					!isset($_POST['ot']) and
					empty($_POST['ot'])	and	
					!isset($_POST['sum']) and
					empty($_POST['sum']) and	
					!isset($_POST['osnov']) and
					empty($_POST['osnov'])	and	
					!isset($_POST['pril']) and
					empty($_POST['pril'])
				){
					$_SESSION["error"] = 'Не заполнены обязательные поля';
					setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
					header("Location: action.php?type={$type}"); 
					exit();	
				}
				else{
					$q = "
						SELECT 
							*
						FROM
							`{$type}`
						WHERE
							`number` = '{$_POST['number']}'
						AND
							`skladID` = '{$_SESSION['skladID']}'
						LIMIT
							1
					";
					if ($result = $mysqli->query($q)){
						if($result->num_rows > 0){
							while ($data = $result->fetch_object()){
								$sum_10 = $data->sum10;
								$date = $data->date;
								$datetime = new Datetime(Date($data->datetime));
								$nds10 = $data->nds10;
								
								
								$old_sum = $data->sum;
								$old_ot = $data->ot;
								
							}
						}
						else{
							$_SESSION["error"] = 'Ошибка при поиске документа в базе. Ошибка редактирования.';
							setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
							header("Location: action.php?type={$type}"); 
							exit();							
						}
					}
					else{
						$_SESSION["error"] = 'Ошибка при запросе к базе данных. Ошибка редактирования. '.$mysqli->error;
						setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
						header("Location: action.php?type={$type}"); 
						exit();								
					}
				
				
				
					$number = $_POST['number'];
					$sum = amount_summ($_POST['sum']);
					$sum = $sum - $sum_10;
					$schet = $_POST['schet'];	
					
					if($schet == '91.01')
						$nds = 0;
					else
						$nds = round($sum * 20 / 120, 2);		

					//НДС 20%
					//$nds = round($sum * 18 / 118, 2);		
					
					
					if($schet == '91.01'){
						$osnov = 'Излишки наличных денежных средств в кассе АЗС №'.$_SESSION['skladID'];
					}	
					else{
						$osnov = $_POST['osnov'];
					}
					$pril = $_POST['pril'];		
					$ot = $_POST['ot'];		
						
				}

				
			

				if($sum >= 0){
			
					$q = "UPDATE `{$type}` SET `sum`=?, `nds`=?, `ot`=?, `osnov`=?, `pril`=?, `edit_time`=? WHERE `number`=? AND `skladID`=?";
					$result = $mysqli->stmt_init();
					if ($result = $mysqli->prepare($q))
					{
						$result->bind_param("ssssssss",$sum,$nds,$ot,$osnov,$pril,time(),$number,$_SESSION['skladID']);
						if($result->execute()) {
						
							if($sum != $old_sum || $ot != $old_ot){
							
								$qq = "
									DELETE FROM 
										`kassa`.`book{$arch}` 
									WHERE 
										`book{$arch}`.`date` >= '{$date}'
									AND
										`book{$arch}`.`skladID` = '{$_SESSION['skladID']}'								
								";
								if ($result1 = $mysqli->query($qq)){
									
								}
								else{
									$_SESSION["error"] = 'Ошибка при удалении старых листов кассовой книги. '.$mysqli->error;
									setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
									header("Location: action.php?type={$type}"); 
									exit();								
								}	
							
							}

							if(!empty($arch)){
								transport($_SESSION['skladID']);
							}								

							$params['number'] = $number;
							$params['skladID'] = $_SESSION['skladID'];
							$params['sum'] = $sum;
							$params['sum10'] = $sum_10;
							$params['nds'] = $nds;
							$params['nds10'] = $nds10;
							$params['ot'] = $ot;
							$params['datetime'] = $datetime->format('Y-m-d H:i:s');
							$params['oper'] = $schet;
							
							if($schet == '50.02'){
								$params['osnov'] = 'Розничная выручка (ККТ №'.$osnov.')';
							}
							if($schet == '91.01'){
								$params['osnov'] = 'Излишки наличных денежных средств в кассе АЗС №'.$_SESSION['skladID'];
							}
							if($schet == '50.02')
								$params['pril'] = 'Отчет о закрытии смены  №'.$pril;
							
							if($schet == '91.01')	
								$params['pril'] =  $pril;						
							
							toXML($params,$type);	
							
							$result->close();
							header("Location: action.php?type={$type}"); 
							exit();						
						}
						else{
							$_SESSION["error"] = 'Ошибка в запросе '.$mysqli->error;
							setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
							header("Location: action.php?type={$type}"); 
							exit();						
						}
					
					}
					else{
						$_SESSION["error"] = 'ошибка при сохранении '.$mysqli->error;
						setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
						header("Location: action.php?type={$type}"); 
						exit();	
					}
				}
				else{
					$_SESSION["error"] = 'Сумма документа меньше суммы продаж по 10% НДС';
					setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
					header("Location: action.php?type={$type}"); 
					exit();					
				}
				
				
				break;
				
/* ===== fix-0.0.8				
			case 'rko_2015': $arch = '_2015';	
			case 'rko_2016': $arch = '_2016';	
			case 'rko_2017': $arch = '_2017';	
			case 'rko_2018': $arch = '_2018';	
			case 'rko_2019': $arch = '_2019';	
*/
			case 'rko': 
				if (($y = (int)substr($_POST['type'],4)) && ($y != date('Y'))) {
					$arch = '_'.$y;
				}
				$type=$_POST['type']; 
				if(
					!isset($_POST['number']) and
					empty($_POST['number'])	and	
					!isset($_POST['sum']) and
					empty($_POST['sum'])	and	
					!isset($_POST['vidat']) and
					empty($_POST['vidat'])	and	
					!isset($_POST['pril']) and
					empty($_POST['pril']) 
				){
					$_SESSION["error"] = 'Не заполнены обязательные поля';
					setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
					header("Location: action.php?type={$type}"); 
					exit();	
				}
				else{
				
					$q = "
						SELECT 
							*
						FROM
							`{$type}`
						WHERE
							`number` = '{$_POST['number']}'
						AND
							`skladID` = '{$_SESSION['skladID']}'
						LIMIT
							1
					";
					if ($result = $mysqli->query($q)){
						if($result->num_rows > 0){
							while ($data = $result->fetch_object()){
								$date = $data->date;
								$datetime = new DateTime(date($data->datetime));
								
								$old_sum = $data->sum;
								$old_vidat = $data->vidat;
								$old_osnov = $data->osnov;
							}
						}
						else{
							$_SESSION["error"] = 'Ошибка при поиске документа в базе. Ошибка редактирования.';
							setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
							header("Location: action.php?type={$type}"); 
							exit();							
						}
					}
					else{
						$_SESSION["error"] = 'Ошибка при запросе к базе данных. Ошибка редактирования. '.$mysqli->error;
						setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
						header("Location: action.php?type={$type}"); 
						exit();								
					}				
				
					$number = $_POST['number'];
					$sum = amount_summ($_POST['sum']);
					$vidat = $_POST['vidat'];
					$pril = $_POST['pril'];
					if(isset($_POST['po'])) $po = $_POST['po']; else $po = '';
					$pasport = getPasport($_POST['vidat']);
					$oper = $_POST['oper'];
					$kassir = $_SESSION['fio'];
					if(isset($_POST['osnov'])) $osnov = $_POST['osnov']; else $osnov = $old_osnov;
				}

				$q = "UPDATE `{$type}` SET `sum`=?, `vidat`=?, `pril`=?, `osnov`=?, `po`=?, `pasport`=?, `edit_time`=? WHERE `number`=? AND `skladID`=?";
				$result = $mysqli->stmt_init();
				if ($result = $mysqli->prepare($q))
				{
					$result->bind_param("sssssssss",$sum,$vidat,$pril,$osnov,$po,$pasport,time(),$number,$_SESSION['skladID']);
					if($result->execute()) {

						if($sum != $old_sum || $vidat != $old_vidat){
						
							$qq = "
								DELETE FROM 
									`kassa`.`book{$arch}` 
								WHERE 
									`book{$arch}`.`date` >= '{$date}'
								AND
									`book{$arch}`.`skladID` = '{$_SESSION['skladID']}'
							";
							if ($result1 = $mysqli->query($qq)){	
								

								
							}
							else{
								$_SESSION["error"] = 'Ошибка при удалении старых листов кассовой книги. '.$mysqli->error;
								setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
								header("Location: action.php?type={$type}"); 
								exit();								
							}
						
						}

						$params['number'] = $number;																	// НОМЕР ДОКУМЕНТА
						$params['skladID'] = $_SESSION['skladID'];														// НОМЕР АЗС
						$params['sum'] = $sum;																			// СУММА
						$params['oper'] = $oper;																		// КОРР. СЧЕТ
						$params['datetime'] = $datetime->format('Y-m-d H:i:s');											// ДАТА ДОКУМЕНТА

						// ********************************************* ИНКАССАЦИЯ *******************************************
						if($oper == '57.3'){
							$flag_date = new DateTime('2018-05-01');
							if($datetime < $flag_date){
								$params['vidat'] = $_SESSION["ink"];													// ВЫДАТЬ 			= Банк
								$params['osnov'] = $osnov;																// ОСНОВАНИЕ		= Сдача выручки в банк 
								$params['pril']	 = 'Квитанция к сумке №'.$pril;											// ПРИЛОЖЕНИЕ		= Квитанция к сумке №
							}
							else{
								$params['vidat'] = $vidat;																// ВЫДАТЬ 			= Кассир
								if($osnov == 'Сдача выручки в банк'){
									$params['osnov'] = $osnov.' ('.$_SESSION["ink"].')';								// ОСНОВАНИЕ		= Сдача выручки в банк (Банк)
									$params['pril']	 = 'Квитанция к сумке №'.$pril.' от '.$datetime->format("d.m.Y");	// ПРИЛОЖЕНИЕ		= Квитанция к сумке №_____ (от ДАТА)
								}
								else{
									$params['osnov'] = $osnov;															// ОСНОВАНИЕ		= Пополнение основной кассы офиса из кассы АЗС
									$params['pril']	 = $pril;															// ПРИЛОЖЕНИЕ		= Приложение
								}
								
								$params['po']	 = $pasport;															// ПО				= Паспорт кассира						
							}
						}
						// ****************************************************************************************************						
						
						// *****************************************  Возврат покупателю **************************************
						if($oper == '62.01,62.02'){
							$params['vidat'] = $vidat;																	// ВЫДАТЬ 			= ФИО клиента
							$params['osnov'] = $osnov;																	// ОСНОВАНИЕ		= Возврат оплаты за товар покупателю
							$params['pril']	 = $pril;																	// ПРИЛОЖЕНИЕ		= Поле приложение заполняется вручную
							$params['po']	 = $po;																		// ПО				= Паспортные данные клиента							
						}
						// ****************************************************************************************************
						
						// *********************************************** Недостача ******************************************
						if($oper == '94.05.1'){
							$params['vidat'] = $vidat;																	// ВЫДАТЬ 			= ФИО кассира
							$params['osnov'] = $osnov;																	// ОСНОВАНИЕ		= Недостача наличных денежных средств в кассе АЗС №___
							$params['pril']	 = $pril;																	// ПРИЛОЖЕНИЕ		= Поле приложение заполняется вручную
						}	
						// ****************************************************************************************************
					

						toXML($params,$type);						
					
						$result->close();
						header("Location: action.php?type={$type}"); 
						exit();						
					}
					else{
						$_SESSION["error"] = 'Ошибка в запросе 2 '.$mysqli->error;
						setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
						header("Location: action.php?type={$type}"); 
						exit();						
					}
					$result->close();
					header("Location: action.php?type={$type}"); 
					exit();					
				}
				break;
			default: 
				$_SESSION["error"] = 'Не передан тип таблицы'; 
				setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
				header("Location: actions.php"); 
				exit();
	}
}
else
{
	if (preg_match("/^(pko|rko)(_\d{4})?|kassa$/",$_GET['type'],$matches)) {
		$cy = date('Y');
		if (!empty($matches[2]))
			$y = (int)substr($matches[2],1);
		else
			$y = 0;
		switch ($matches[1]) {
			case 'rko': if (!$y || ($y == $cy)) {
							$heading = 'Редактировать РКО';
							$type = 'rko';
						}
						else {
							$heading = 'Редактировать РКО('.$y.')';
							$type = 'rko_'.$y;
							$arch_year = $y;
						}
						break;
			case 'pko': if (!$y || ($y == $cy)) {
							$heading = 'Редактировать ПКО';
							$type = 'pko';
						}
						else {
							$heading = 'Редактировать ПКО('.$y.')';
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
		$_SESSION["error"] = 'Не передан тип таблицы'; setlog($_SESSION['user_id'],$_SESSION["error"],$page_);header("Location: actions.php"); exit();
	}
/*	======== fix-0.0.8
		switch ($_GET['type']){
				case 'pko': $heading='Редактировать ПКО'; $type=$_GET['type']; break;
				case 'rko': $heading='Редактировать РКО'; $type=$_GET['type']; break;
				case 'pko_2015': $heading='Редактировать ПКО(2015) '; $type=$_GET['type']; break;
				case 'pko_2016': $heading='Редактировать ПКО(2016) '; $type=$_GET['type']; break;
				case 'pko_2017': $heading='Редактировать ПКО(2017) '; $type=$_GET['type']; break;
				case 'pko_2018': $heading='Редактировать ПКО(2018) '; $type=$_GET['type']; break;
				case 'pko_2019': $heading='Редактировать ПКО(2019) '; $type=$_GET['type']; break;
				case 'rko_2015': $heading='Редактировать РКО(2015) '; $type=$_GET['type']; break;					
				case 'rko_2016': $heading='Редактировать РКО(2016) '; $type=$_GET['type']; break;					
				case 'rko_2017': $heading='Редактировать РКО(2017) '; $type=$_GET['type']; break;					
				case 'rko_2018': $heading='Редактировать РКО(2018) '; $type=$_GET['type']; break;					
				case 'rko_2019': $heading='Редактировать РКО(2019) '; $type=$_GET['type']; break;					
				case 'kassa': $heading='Касса'; $type=$_GET['type']; break;
				default: $_SESSION["error"] = 'Не передан тип таблицы'; setlog($_SESSION['user_id'],$_SESSION["error"],$page_);header("Location: actions.php"); exit();
			}
*/
		require_once('template/head.php');
	?>
	<div class="container">
			<?php
				require_once('template/exit.php');
			?>
		<div class="jumbotron">
		<h2><?php echo $heading.' '.$_GET['number'];?></h2>  

	<?php
// ========= fix-0.0.8
	switch (substr($type,0,3)){
		case 'pko': 	
//		case 'pko_2015': 	
//		case 'pko_2016': 	
//		case 'pko_2017': 	
//		case 'pko_2018': 	
//		case 'pko_2019': 	
			/*if($_SESSION['user_id'] != '1451'){
					$_SESSION["error"] = 'В данный момент производится доработка формы редактирования ПКО. Попробуйте позже.';
					setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
					header("Location: action.php?type={$type}"); 
					exit();	
			}			*/	
			require_once('template/pko_edit_form.php'); 
			break;
		case 'rko':	
//		case 'rko_2015':	
//		case 'rko_2016':	
//		case 'rko_2017':	
//		case 'rko_2018':	
//		case 'rko_2019':	
			require_once('template/rko_edit_form.php'); break;
	}
}
?> 
	
	<a href="action.php?type=<?php echo $type?>">Назад</a>
	</div>
</div>

<?php
require_once('template/bottom.php');				
    }
    else {
		$_SESSION["error"] = 'Ошибка авторизации';
		setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
        header("Location: index.php"); 
		exit();	
    }
  }
else {
	$_SESSION["error"] = 'Нет прав доступа на редактирование';
	setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
	header("Location: action.php"); 
	exit();
}  
  
else {
	$_SESSION["error"] = 'Период закрыт';
	setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
	header("Location: action.php?type={$_GET['type']}"); 
	exit();
}  
  
}
else {
	$_SESSION["error"] = 'Ошибка авторизации';
	setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
	header("Location: index.php"); 
	exit();
}


?>