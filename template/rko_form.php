<?
defined('_JEXEC') or die('Access denied');
?>	
<form class="forms" id="" action="print.php" method="POST">
		<input style="display:none" type="text" name="form_id" value="<?=$_SESSION['form_id']?>">	
		<input style="display:none" type="text" name="type" value="rko">	
		<div class="form-group">
			<label for="sum">Сумма</label>
			<input type="number" step="0.01" class="form-control" id="sum" name="sum" placeholder="Сумма" required>
		</div>		


<?php

// **************************************************** ИНКАССАЦИЯ ***********************************************
if(isset($_GET['ink']) and  $_GET['ink'] == 'yes'){
	
	
	
?>
		<div class="form-group">
			<label for="oper">Вид Операции (определяется по корсчету)</label>
			<input readonly type="text" class="form-control" id="oper" name="oper" placeholder="Вид Операции" required value="57.3">
		</div>
<?

	$q = "
		SELECT 
			`fio`, 
			`user_id`,
			`role_id`
		FROM 
			`users` 
		WHERE 
			`azs_id` = '{$_SESSION['skladID']}'
		ORDER BY
			`fio`
	";
	if ($result = $mysqli->query($q)){
		if($result->num_rows > 0){
			echo '
			  <div class="form-group">
				<label for="vidat">Выдать кому:</label>
				<select name="vidat" class="form-control" required>	
			';
			while ($data = $result->fetch_object()){
				$sel = '';
				if($data->fio == $_SESSION['fio']) $sel = 'selected'; 
				if($data->role_id != '10')
					if(empty(trim($data->fio))){
						echo '<option '.$sel.' value="'.$_SESSION['buh'].'">'.$_SESSION['buh'].'</option>';	
					}
					else
						echo '<option '.$sel.' value="'.$data->fio.'">'.$data->fio.'</option>';		
			}
			echo '
				</select>
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

?>
		<div class="form-group">
			<label for="osnov">Основание</label>
			<select name="osnov" class="form-control" required>	
				<?php if ($_SESSION['skladID'] == 368):?>
				<option value="Сдача выручки в банк (<?=$_SESSION["ink"]?>)">Сдача выручки в банк (<?=$_SESSION["ink"]?>)</option>
				<option value="Сдача выручки в банк (ОСБ Брянское №8605)">Сдача выручки в банк (ОСБ Брянское №8605)</option>
				<?php else:?>
				<option value="Сдача выручки в банк">Сдача выручки в банк</option>
				<?php endif;?>
				<option value="Пополнение основной кассы офиса из кассы АЗС №<?=$_SESSION['skladID']?>">Пополнение основной кассы офиса из кассы АЗС №<?=$_SESSION['skladID']?></option>
			</select>
		</div>	
		<div class="form-group">
			<label for="pril">Приложение. Квитанция к сумке №</label>
			<input type="text" class="form-control" id="pril" name="pril" placeholder="Квитанция к сумке №" required>
		</div>		

		
<?php
//**************************************************************************************************************************
}
elseif(isset($_GET['ned2']) and  $_GET['ned2'] == 'ned2'){
	
	if(date('Y') >= 2017) $o = '94.05.1'; 
	else $o = '94';	
	
?>		
		<div class="form-group">
			<label for="oper">Вид Операции (определяется по корсчету)</label>
			<input readonly type="text" class="form-control" id="oper" name="oper" placeholder="Вид Операции" required value="<?=$o?>">
		</div>		
<?

	$q = "
		SELECT 
			`fio`, 
			`user_id`,
			`role_id`
		FROM 
			`users` 
		WHERE 
			`azs_id` = '{$_SESSION['skladID']}'
		ORDER BY
			`fio`			
	";
	if ($result = $mysqli->query($q)){
		if($result->num_rows > 0){
			echo '
			  <div class="form-group">
				<label for="vidat">Выдать кому:</label>
				<select name="vidat" class="form-control" required>	
					<option></option>
			';
			while ($data = $result->fetch_object()){
				if($data->role_id != '10')
					if(empty(trim($data->fio))){
						echo '<option value="'.$_SESSION['buh'].'">'.$_SESSION['buh'].'</option>';	
					}
					else
						echo '<option value="'.$data->fio.'">'.$data->fio.'</option>';		
			}
			echo '
				</select>
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

?>
		<div class="form-group">
			<label for="osnov">Основание</label>
			<input readonly type="text" class="form-control" id="osnov" name="osnov" placeholder="Основание" required value="Недостача наличных денежных средств в кассе АЗС №<?=$_SESSION['skladID']?>">
		</div>
		<div class="form-group">
			<label for="pril">Приложение</label>
			<input type="text" class="form-control" id="pril" name="pril" placeholder="" required>
		</div>			
		
<?php
}
else{
	//******************************************************* ВОЗВРАТ ****************************************************************************
?>		
		<div class="form-group">
			<label for="oper">Вид Операции (определяется по корсчету)</label>
			<input readonly type="text" class="form-control" id="oper" name="oper" placeholder="Вид Операции" required value="62.01,62.02">
		</div>		
		<div class="form-group">
			<label for="vidat">Выдать кому(ФИО)</label>
			<input type="text" class="form-control" id="vidat" name="vidat" placeholder="Выдать" required>
		</div>
		<div class="form-group">
			<label for="osnov">Основание</label>
			<input readonly type="text" class="form-control" id="osnov" name="osnov" placeholder="Основание" required value="Возврат оплаты за товар покупателю">
		</div>
		<div class="form-group">
			<label for="po">Выдать по</label>
			<input type="text" class="form-control" id="po" name="po" placeholder="Выдать по" required>
		</div>		
<?php
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
									$serials.='<label for="pril">Приложение (выберите ККМ)</label>';
									$serials.='<select class="form-control" name="pril">';
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
?>					
<!--		<div class="form-group">
			<label for="pril">Приложение</label>
			<input type="text" class="form-control" id="pril" name="pril" placeholder="" required>
		</div>			-->
<?php
	//**********************************************************************************************************************************************
}
?>	


		
			<input type="submit" class="form-control" value="Сохранить">
		

	</form>