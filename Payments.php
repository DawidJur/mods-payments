<?php
require_once('Constants.php');

class Payments extends Contants {
	protected function returnPtsBasicOnDelay($delayInSec) {
		$delayValues = $this->delayValues;
		$delay = $delayInSec / 3600;
		foreach ($delayValues as $key => $value) {
			if($delay >= $value[0] && $delay < $value[1]) {
				return $value[2];
			}else {
				continue;
			}
		}
		exit('Something wrong in returnPtsBasicOnDelay');
	}

	protected function returnDelay($moderationDate) {
		$mysqli = $this->connectDatabase();
		$query = "SELECT * FROM ".$this->delayTable." WHERE TimeStat <= '".$moderationDate."' ORDER BY TimeStat DESC LIMIT 1";
		if($mysqli->real_query($query)) {
			$res = $mysqli->use_result();
			while ($row = $res->fetch_assoc()) {
				$TimeStat = $row['TimeStat'];
				$DGTime = $row['DGTime'];
				$GKTime = $row['GKTime'];
			}

			//choose bigger delay (smaller value)
			if(strtotime($DGTime) <= strtotime($GKTime)) {
				$delayDate = strtotime($DGTime);
			} else {
				$delayDate = strtotime($GKTime);
			}

			$delay = strtotime($moderationDate) - $delayDate;
			$mysqli->close();			
			return $delay;
		}else {
	    	echo $mysqli->error;
		}
		$mysqli->close();
		exit('returnDelay encountered a problem');
	}

	protected function isGamePremium($gameID) {
		//premium game -> game where we're checking progress in-game for additional points.
		if(in_array($gameID, $this->gamesWithBonus)) {
			return true;
		}else {
			return false;
		}
	}

	protected function getScreensHistory() {
		if(!empty($_GET['m']) && !empty($_GET['y'])) {
			$mysqli = $this->connectDatabase();
			$query = "SELECT * FROM ".$this->moderationsTable." WHERE MONTH(ModerationDate) = ".$_GET['m']." AND YEAR(ModerationDate) = ".$_GET['y'];
			$mysqli->real_query($query);
			$res = $mysqli->use_result();
			while ($row = $res->fetch_assoc()) {
				$moderations['ModerationDate'][] = $row['ModerationDate'];
				$moderations['ModeratorID'][] = $row['ModeratorID'];
				$moderations['GameID'][] = $row['GameID'];
			}
			return $moderations;
		} else {
			exit('Enter Month and Year');
		}
	}

	protected function returnNumberOfModerations() {
		$monthOfRaport = htmlspecialchars($_GET['m']);
		$yearOfRaport = htmlspecialchars($_GET['y']);
		$mysqli = $this->connectDatabase();
		$query = "SELECT count(ModeratorID) as NumberOfModerations FROM ".$this->moderationsTable." WHERE MONTH(ModerationDate) = ".$monthOfRaport." AND YEAR(ModerationDate) = ".$yearOfRaport;
		if($mysqli->real_query($query)) {
			$res = $mysqli->use_result();
			if($row = $res->fetch_assoc()) {
				$mysqli->close();
				return $row['NumberOfModerations'];
			}
		}
		$mysqli->close();
		exit('returnNumberOfModerations encountered a problem');
	}

	public function returnModeratorsPayments() {
		$this->start();
		$moderations = $this->getScreensHistory();
		for ($i=0; $i < count($moderations['GameID']); $i++) { 
			$moderationDate = $moderations['ModerationDate'][$i];
		 	$moderatorID = $moderations['ModeratorID'][$i];
		 	$gameID = $moderations['GameID'][$i];

			$moderatorNick = $this->returnModeratorByID($moderatorID);
			$delay = $this->returnDelay($moderationDate);
			$points[$moderatorNick] += $this->returnPtsBasicOnDelay($delay);

			if($this->isGamePremium($gameID)) {
				$points[$moderatorNick] += $this->bonusPtsAmount;
			}
		}
		if($i != $this->returnNumberOfModerations()) {
			exit('<h1>Something went wrong. Checked moderations: ".$i.", moderations in db: ".$this->returnNumberOfModerations()."</h1>');
		}else {
			return $points;
		}
	}

	public function printRaport() {
		echo "<pre>";
		print_r($this->returnModeratorsPayments());
		echo "</pre>";
	}

	public function printModeratorsPayments() {
		//table? to do
	}

	public function printWholeReportForEachModeration() {
		$countingExecutionTime = microtime(true);
		$this->start();
		$moderations = $this->getScreensHistory();
		$NumberOfModerations = $this->returnNumberOfModerations();
		echo '<table class="table">';
		echo "<tr><td>O.N.</td><td>Moderator ID</td><td>Moderation Nickname</td><td>Moderation Date</td><td>The oldest screen date</td><td>Delay (H)</td><td>Pts for moderation</td><td>Game ID</td><td>isPrem</td></tr>";
		for ($i=0; $i < count($moderations['GameID']); $i++) { 

			$moderationDate = $moderations['ModerationDate'][$i];
		 	$moderatorID = $moderations['ModeratorID'][$i];
		 	$gameID = $moderations['GameID'][$i];

		 	$mysqli = $this->connectDatabase();
			$query = "SELECT * FROM ".$this->delayTable." WHERE TimeStat <= '".$moderationDate."' ORDER BY TimeStat DESC LIMIT 1";
			if($mysqli->real_query($query)) {
				$res = $mysqli->use_result();
				while ($row = $res->fetch_assoc()) {
					$TimeStat = $row['TimeStat'];
					$DGTime = $row['DGTime'];
					$GKTime = $row['GKTime'];
				}
				//choose bigger delay (smaller value)
				if(strtotime($DGTime) <= strtotime($GKTime)) {
					$delayDate = $DGTime;
				} else {
					$delayDate = $GKTime;
				}
			}
			$mysqli->close();

		 	echo "<tr>";
		 	echo "<td>".($i+1)."</td>";
			echo "<td>".$moderatorID."</td>";
			echo "<td>".$this->returnModeratorByID($moderatorID)."</td>";
			echo "<td>".$moderationDate."</td>";
			echo "<td>".$delayDate."</td>";
			echo "<td>".round(((strtotime($moderationDate) - strtotime($delayDate)) / 3600),2)."</td>";
			echo "<td>".$this->returnPtsBasicOnDelay(strtotime($moderationDate) - strtotime($delayDate))."</td>";
			echo "<td>".$gameID."</td>";

			if($this->isGamePremium($gameID)) {
				echo "<td>Yes</td>";
			}else {
				echo "<td>No</td>";
			}
			echo "</tr>";
		}
		echo "</table>";
		$countingExecutionTimeStop = microtime(true);
		echo "Execution time ".bcsub($countingExecutionTimeStop, $countingExecutionTime, 4)."s";
	}
}
?>