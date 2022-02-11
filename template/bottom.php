		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.11.1.min.js"><\/script>')</script>

		<script type="text/javascript" src="js/vendor/moment-with-locales.min.js"></script>		
		
		<script type="text/javascript" src="js/vendor/bootstrap.min.js"></script>
		
		<script type="text/javascript" src="js/vendor/bootstrap-datetimepicker.min.js"></script>		

        <script src="js/main.js"></script>
		
		<!-- Инициализация виджета "Bootstrap datetimepicker" -->
		<script type="text/javascript">
		  $(function () {
			//Идентификатор элемента HTML (например: #datetimepicker1), для которого необходимо инициализировать виджет "Bootstrap datetimepicker"
			$("#datetimepicker1").datetimepicker(
				{locale: 'ru'}
			);
		  });
		</script>	
		
		<script type="text/javascript">
		  $(function () {
			//Идентификатор элемента HTML (например: #datetimepicker1), для которого необходимо инициализировать виджет "Bootstrap datetimepicker"
			$("#datetimepicker2").datetimepicker({
				locale: 'ru',
				format: 'DD.MM.YYYY',
				allowInputToggle: true,
				disabledTimeIntervals: false
			});
		  });
		</script>	

		<script>
			$(function () {
				<?
				if(!isset($start) || !isset($end)){
					$start = new datetime(date('F j, Y H:i:s'));
					$end = new datetime(date('F j, Y H:i:s'));
				}
				?>
			
				var date1 = new Date('<?if($from = $start->format('F j, Y H:i:s')) echo $from;?>');
				var date2 = new Date('<?if($to = $end->format('F j, Y H:i:s')) echo $to;?>');			
				
				$('#datetimepicker3').datetimepicker({
					locale: 'ru',
					 format: 'DD.MM.YYYY',
					 allowInputToggle: true,
					 defaultDate: date1
				});
				$('#datetimepicker4').datetimepicker({
					locale: 'ru',
					format: 'DD.MM.YYYY',
					allowInputToggle: true,
					defaultDate: date2
				});
				$("#datetimepicker3").on("dp.change", function (e) {
					$('#datetimepicker4').data("DateTimePicker").minDate(e.date);
				});
				$("#datetimepicker4").on("dp.change", function (e) {
					$('#datetimepicker3').data("DateTimePicker").maxDate(e.date);
				});		
			});
		</script>	

		<script>
			$(document).ready(function(){
			
				$('.forms').submit(function(){
					// Блокируем кнопки при отправке формы
					
					$('.form-group', $(this)).hide();
					$('input[type=submit]', $(this)).attr('disabled', 'disabled');
					$('input[type=submit]', $(this)).attr('value', 'Ожидайте...');
				});			
				

				
			}); 		
	</script>	
	
<?
/*
				$("#create_rko").submit(function(e) {
					$('#bg_layer').show();
					e.preventDefault();
					var form_data = $(this).serialize(); 
					$.ajax({
						type: "POST", 
						cache: false,
						dataType: 'json',
						url: "print.php", 
						data: form_data,
						success: function() {
						},
						error: function(){
								location.href = 'actions.php';	
						},
						complete: function(){
								location.href = 'actions.php';						
						}
				   });
				});
				
				$("#create_pko").submit(function(e) {
					$('#bg_layer').show();
					e.preventDefault();
					var form_data = $(this).serialize(); 
					$.ajax({
						type: "POST", 
						cache: false,
						dataType: 'json',
						url: "print.php", 
						data: form_data,
						success: function() {
						},
						error: function(){
								location.href = 'actions.php';	
						},
						complete: function(){
								location.href = 'actions.php';						
						}
				   });
				});
*/
?>	
		
    </body>
</html>