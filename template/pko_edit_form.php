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
		<input style="display:none" name="schet" value="<?=$obj->oper?>">
		<div class="form-group">
			<label for="sum">Сумма</label>
			<input type="number" step="0.01" class="form-control" id="sum" name="sum" placeholder="Сумма" value="<?echo $obj->sum + $obj->sum10?>" required>	
			<label style="font-weight:normal">
			<?
				$datetime = new DateTime($obj->date);
				if($datetime->format('Y') < 2019){
			?>
					18%: <?=$obj->sum?>; 10%: <?=$obj->sum10?> 
				<?}else{?>	
				20%: <?=$obj->sum?>; 10%: <?=$obj->sum10?> 
			<?}?>
			</label>
		</div>
<?			
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
					$serials.='<label for="ot">Принято от:</label>';
					$serials.='<select class="form-control" id="ot" name="ot">';
					while ($data = $result1->fetch_object()){
						$sel = '';
						if($data->fio == $obj->ot){
							$sel = ' selected ';
						}
						if(!empty(trim($data->fio))){
							$serials.='<option '.$sel.' value="'.$data->fio.'">'.$data->fio.'</option>';
						}
					}
					$serials.='</select>';		
					$serials.='</div>';		
					echo $serials;
				}
			}	


			
	if($obj->schet == '91.01'){
		echo '
		<div class="form-group">
			<label for="pril">Основание</label>
			<input type="text" class="form-control" id="osnov" name="osnov" value="Излишки наличных денежных средств в кассе АЗС №'.$_SESSION['skladID'].'">
		</div>';
	}
	else
	{			
			$q1 = "
				SELECT
					`serial`
				FROM
					`kkm`
				WHERE
					`azs` = '{$_SESSION["skladID"]}'
				ORDER BY
					`serial`
			";	
			
			$reserved = true;

			
			if ($result1 = $mysqli->query($q1)){
				$serials = '';
				if($result1->num_rows > 0){
					$serials.='<div class="form-group">';
					$serials.='<label for="osnov">ККТ</label>';
					$serials.='<select class="form-control" name="osnov">';
					while ($data = $result1->fetch_object()){
						$sel = '';
						
						if($data->serial == $obj->osnov){
							$sel = ' selected ';
							$reserved = false;
						}					
						$serials.='<option '.$sel.' value="'.$data->serial.'">'.$data->serial.'</option>';
					}
					if($reserved)
						$serials.='<option selected value="'.$obj->osnov.'">'.$obj->osnov.'</option>';					
					$serials.='</select>';		
					$serials.='</div>';		
					echo $serials;
				}
			}	
	}
?>
		<div class="form-group">
		<?
		if($obj->schet == '91.01')
			echo '<label for="pril">Приложение</label>';
		else
			echo '<label for="pril">Отчет о закрытии смены №</label>';
		?>	
			<input type="text" class="form-control" id="pril" name="pril" placeholder="Приложение" value="<?=$obj->pril?>" required>
		</div>
		<div class="form-group">
			<input type="submit" class="form-control" value="Сохранить">
		</div>	

	</form>
<?
	}
	else{
		$_SESSION["error"] = "<p>Документ {$_GET['number']} не найден или у вас нет к нему доступа</p>";
		setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
		header("Location: action.php"); 
		exit();	
	}
	$result->close();
}
else{
	$_SESSION["error"] = "<p>Ошибка в запросе: </p>".$q;
	setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
	header("Location: action.php"); 
	exit();	
}
?>