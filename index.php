<?php 
require_once('Payments.php'); $Payments = new Payments; 
require_once('Tests.php'); $Test = new Test; 
require_once('Report.php'); $Report = new Report; 
?>

<!DOCTYPE html>
<html>
<head>
	<title>Wypłaty moderatorów</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>
<body>
<section class="jumbotron">
	<form action=<?='"?'.$_GET['site'].'"';?> method="get">
		<select name="site">
			<option value="payments">Payments</option>
			<option value="tests">Tests</option>
			<option value="report">Report</option>
		</select>
		Miesiac: <input <? if(!empty($_GET['m']) && !empty($_GET['y'])) echo 'value="'.$_GET['m'].'"'; ?> type="number" name="m"> 
		Rok: <input <? if(!empty($_GET['m']) && !empty($_GET['y'])) echo 'value="'.$_GET['y'].'"'; ?> type="number" name="y"> 
		<input type="submit" value="Sprawdź">
	</form>
</section>	
<section>
<?
if(!empty($_GET['m']) && !empty($_GET['y']) && $_GET['site'] == 'payments') {
	$Payments->printRaport(); 
}

if(!empty($_GET['m']) && !empty($_GET['y']) && $_GET['site'] == 'report') {
	$Payments->printWholeReportForEachModeration(); 
}

if(!empty($_GET['m']) && !empty($_GET['y']) && $_GET['site'] == 'tests') {
	$Test->printLostDelayData(); 
}
?>
</section>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>
</html>