<?
defined('_JEXEC') or die('Access denied');

$q = "SELECT * FROM `{$type}` WHERE number=? AND skladID=? LIMIT 1";
$result = $mysqli->stmt_init();
if ($result->prepare($q))
{
	$result->bind_param("ss",$_GET['number'],$_SESSION['skladID']);
	$result->execute();
	if($obj = $result->get_result()->fetch_object()){

?>	
<form class="forms" action="edit.php" method="POST">
		<input style="display:none" type="text" name="type" value="<?=$type?>">
		<input style="display:none" type="text" name="edit" value="edit">
		<input style="display:none" type="text" name="number" value="<?=$_GET['number']?>">
		<div class="form-group">
			<label for="sum">Сумма</label>
			<input type="number" step="0.01" class="form-control" id="sum" name="sum" placeholder="Сумма" value="<?=$obj->sum?>" required>
		</div>
		<div class="form-group">
			<label for="oper">Вид Операции (определяется по корсчету)</label>
			<input readonly="" type="text" class="form-control" id="oper" name="oper" placeholder="Вид Операции" required="" value="<?=$obj->oper?>">
		</div>		
<?
// *********************************************** ИНКАССАЦИЯ ********************************************************
	if($obj->oper == '57.3'){
			$q1 = "
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

			
			if ($result1 = $mysqli->query($q1)){
				$serials = '';
				if($result1->num_rows > 0){
					$serials.='<div class="form-group">';
					$serials.='<label for="vidat">Выдать кому</label>';
					$serials.='<select class="form-control" id="vidat" name="vidat">';
					while ($data = $result1->fetch_object()){
						$sel = '';
						if($data->fio == $obj->vidat){
							$sel = ' selected ';
						}
						if(empty(trim($data->fio))){
							$serials.='<option '.$sel.' value="'.$_SESSION['buh'].'">'.$_SESSION['buh'].'</option>';
						}
						else
							$serials.='<option '.$sel.' value="'.$data->fio.'">'.$data->fio.'</option>';
					}
					$serials.='</select>';		
					$serials.='</div>';		
					echo $serials;
				}
			}	

			$value1 = 'Сдача выручки в банк';
			$value2 = 'Пополнение основной кассы офиса из кассы АЗС №'.$_SESSION['skladID'];
?>
		<div class="form-group">
			<label for="osnov">Основание</label>
			<select name="osnov" class="form-control" required>	
				<option <?if($value1 == $obj->osnov) echo 'selected'?> value="<?=$value1?>"><?=$value1?></option>
				<option <?if($value2 == $obj->osnov) echo 'selected'?> value="<?=$value2?>"><?=$value2?></option>
			</select>
		</div>	

		<div class="form-group">
			<label for="pril">Приложение. Квитанция к сумке №</label>
			<input type="text" class="form-control" id="pril" name="pril" placeholder="Приложение" value="<?=$obj->pril?>" >
		</div>
	<?}
// ****************************************************************************************************************	
	
// *********************************************** ВОЗВРАТ ********************************************************	
	if($obj->oper == '62.01,62.02'){
?>
		<div class="form-group">
			<label for="oper">Выдать кому</label>
			<input type="text" class="form-control" id="vidat" name="vidat" required="" value="<?=$obj->vidat?>">
		</div>	
		<div class="form-group">
			<label for="oper">Паспорт</label>
			<input type="text" class="form-control" id="po" name="po" required="" value="<?=$obj->po?>">
		</div>	
		<div class="form-group">
			<label for="oper">Приложение</label>
			<input type="text" class="form-control" id="pril" name="pril" required="" value="<?=$obj->pril?>">
		</div>	
<?	}
// ****************************************************************************************************************

// *********************************************** НЕДОСТАЧА ******************************************************	
	if($obj->oper == '94.05.1'){
		$q1 = "
			SELECT 
				`fio`, 
				`user_id`,
				`role_id`
			FROM 
				`users` 
			WHERE 
				`azs_id` = '{$_SESSION['skladID']}'
		";	

		
		if ($result1 = $mysqli->query($q1)){
			$serials = '';
			if($result1->num_rows > 0){
				$serials.='<div class="form-group">';
				$serials.='<label for="vidat">Выдать кому</label>';
				$serials.='<select class="form-control" id="vidat" name="vidat">';
				while ($data = $result1->fetch_object()){
					$sel = '';
					if($data->fio == $obj->vidat){
						$sel = ' selected ';
					}
					if(empty(trim($data->fio))){
						$serials.='<option '.$sel.' value="'.$_SESSION['buh'].'">'.$_SESSION['buh'].'</option>';
					}
					else
						$serials.='<option '.$sel.' value="'.$data->fio.'">'.$data->fio.'</option>';
				}
				$serials.='</select>';		
				$serials.='</div>';		
				echo $serials;
			}
		}	
?>
		<div class="form-group">
			<label for="oper">Приложение</label>
			<input type="text" class="form-control" id="pril" name="pril" required="" value="<?=$obj->pril?>">
		</div>	
<?		
	}
// ****************************************************************************************************************
?>
	
	

		<div class="form-group">
			<input type="submit" class="form-control" value="Сохранить">
		</div>	

	</form>
<?
	}
	else{
		echo "<p>Документ {$_GET['num']} не найден или у вас нет к нему доступа</p>";
	}
	$result->close();
}
?>