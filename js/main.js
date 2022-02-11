function _chck_pko_sum_before_print() {
	if (parseInt($('#sum').val()) <= 0) {
		alert('Сумма выручки не может быть отрицательной либо нулевой!\nИсправьте сумму и повторите попытку.');
		return false;
	}
	else
		return true;
}
