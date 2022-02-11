<?
defined('_JEXEC') or die('Access denied');
?>


<form action="logins.php" method="POST">
		<input style="display:none" type="text" name="edit" value="<?=$user_id?>">
		<div class="form-group">
			<label for="fio">ФИО</label>
			<input type="text" class="form-control" id="fio" name="fio" placeholder=""  value="<?=$fio?>" required>
		</div>
		<div class="form-group">
			<label for="fio">Должность</label>
			<input type="text" class="form-control" id="position" name="position" placeholder=""  value="<?=$position?>" required>
		</div>
		<div class="form-group">
			<label for="fio">Паспорт</label>
			<input type="text" class="form-control" id="pasport" name="pasport" placeholder="" value="<?=$pasport?>" required>
		</div>
		<div class="form-group">
			<input type="submit" class="form-control" value="Сохранить">
		</div>	

	</form>
