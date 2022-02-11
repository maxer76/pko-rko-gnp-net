<?
defined('_JEXEC') or die('Access denied');
?>


<form action="logins.php" method="POST">
		<input style="display:none" type="text" name="new" value="save">
		<div class="form-group">
			<label for="login">Логин</label>
			<input type="text" class="form-control" id="login" name="login" placeholder="" required>
		</div>
		<div class="form-group">
			<label for="pass">Пароль</label>
			<input type="text" class="form-control" id="pass" name="pass" placeholder="" required>
		</div>
		<div class="form-group">
			<label for="fio">ФИО</label>
			<input type="text" class="form-control" id="fio" name="fio" placeholder="" required>
		</div>
		<div class="form-group">
			<label for="fio">Должность</label>
			<input type="text" class="form-control" id="position" name="position" placeholder="" required>
		</div>
		<div class="form-group">
			<input type="submit" class="form-control" value="Сохранить">
		</div>	

	</form>
