<title>Missing records</title>
<?php 
require_once('Constants.php');

class Test extends Contants {

	private $month;
	private $year;

	public function print_pre($obj) {
		echo "<pre>";
		print_r($obj);
		echo "</pre>";
	}

	private function getDate() {
		$this->month = htmlspecialchars($_GET['m']);
		$this->year = htmlspecialchars($_GET['y']);
	}

	public function returnCorrectNumberOfRows() {
		$numberOfDays = cal_days_in_month(CAL_GREGORIAN,$this->month,$this->year);
		$numberOfRows = $numberOfDays * 24 * 60;
		return $numberOfRows;
	}

	public function returnHowManyRows() {
		$mysqli = $this->connectDatabase();
		$query = "SELECT count(TimeStat) as rows FROM ".$this->delayTable." WHERE MONTH(TimeStat) = $this->month AND YEAR(TimeStat) = $this->year";
		$mysqli->real_query($query);
		$res = $mysqli->use_result();
		if($row = $res->fetch_assoc()) {
			$return['month'] = $row['rows'];
		}
		$res->close();
		for($i=1;$i<=cal_days_in_month(CAL_GREGORIAN,$this->month,$this->year);$i++) { 
			$query = "SELECT count(TimeStat) as rows FROM ".$this->delayTable." WHERE MONTH(TimeStat) = $this->month AND YEAR(TimeStat) = $this->year AND DAY(TimeStat) = $i";
			$mysqli->real_query($query);
			$res = $mysqli->use_result();
			while ($row = $res->fetch_assoc()) {
				$return['day'][$i] = $row['rows'];
			}
			$res->close();
		}
		return $return;
	}

	public function printLostDelayData() {
		$this->getDate();
		if(!empty($this->year) && !empty($this->month)) {
			$howManyRows = $this->returnHowManyRows();
			echo "<p>Number of rows in DB: ".$howManyRows['month']."</p>";
			echo "<p>Should be: ".$this->returnCorrectNumberOfRows()."</p>";
			echo "<p>missing: ".($this->returnCorrectNumberOfRows() - $howManyRows['month']).'</p>';
			echo "<p>Days: Should be 1440.</p>";
			for ($i=1; $i <= count($howManyRows['day']); $i++) { 
				echo '<p>Day '.$i.'.'.$this->month.'.'.$this->year.': '.$howManyRows['day'][$i].' missing: '.(1440 - $howManyRows['day'][$i]).'</p>';
			}
		}
	}

	public function addRandomRecords($numerOfRowsToAdd) {
		$this->start();
		$mysqli = $this->connectDatabase();
		foreach($this->moderators as $key => $value) {
			$modsIds[] = $value['DG'];
			$modsIds[] = $value['GK'];
		}
		$timeStamp = strtotime($_GET['y'].'-'.$_GET['m'].'-01 00:00:00'); //from when it has to add new records
		for($i=0;$i<$numerOfRowsToAdd;$i++) { 
			$date = date('Y-m-d H:i:s', ($timeStamp + rand(0, 2592000)));
			$modId = $modsIds[rand(0,(count($modsIds) - 1))];
			$game = rand(1,10);
			$query = "INSERT INTO ".$this->moderationsTable." VALUES('','$modId','$game','$date');";
			$mysqli->real_query($query);
		}
		$mysqli->close();
	}
}
?>