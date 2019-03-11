<?php
require_once('Payments.php');

class Report extends Payments {

	public function returnHowManyModerationsPremium() {
		$this->start();
		$moderations = $this->getScreensHistory();
		for ($i=0; $i < count($moderations['GameID']); $i++) { 
			$gameID = $moderations['GameID'][$i];
			if($this->isGamePremium($gameID)) {
				$premiums[$gameID] += 1;
			}
		}
		return $premiums;
	}

	public function returnNumberOfModerationsPerDelay() {
		$this->start();
		$moderations = $this->getScreensHistory();
		for ($i=0; $i < count($moderations['ModerationDate']); $i++) { 
			$delay = $this->returnDelay($moderations['ModerationDate'][$i]);
			$pts = $this->returnPtsBasicOnDelay($delay);
			$moderationsPerDelay[$pts] += 1;
			if($pts == 0) {
				return $moderations['ModerationDate'][$i];
			}
		}
		return $moderationsPerDelay;
	}

	public function returnSumModerationsData() {
		
	}

	public function numberOfModerationsPerModerator() {

	} 
	public function numberOfDelayModerationsPerModerator() {
		
	}
}
?>