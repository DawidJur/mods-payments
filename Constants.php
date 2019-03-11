<?php
Class Contants {
	public $moderators;
	public $delayValues;
	public $gamesWithBonus;
	public $bonusPtsAmmount = 16;
	protected $delayTable = '';
	protected $moderationsTable = '';

	protected function connectDatabase() {
		$mysqli = new mysqli();
		if ($mysqli->connect_errno) {
    		echo "Błąd podczas łączenia z bazą danych. (".$mysqli->connect_errno.") ".$mysqli->connect_error;
    		return false;
		}
		return $mysqli;
	}

	private function getModerators() {
		$this->moderators = [];
	}

	protected function returnModeratorByID($moderatorID) {
		foreach ($this->moderators as $key => $value) {
			if($value['DG'] == $moderatorID || $value['GK'] == $moderatorID) {
				return $value['Nick'];
			}
		}
		return $moderatorID;
	}

	private function getDelayValue() {
		//data - H, H, pts
		//interval <a, b)
		$this->delayValues = [
			[0, (20/60), 6],
			[(20/60), 4, 5],
			[4, 72, 4],
			[72, INF, 3]
		];
	}

	private function getGamesWithBonus() {
		$this->gamesWithBonus = [];
	}

	public function start() {
		$this->getModerators();
		$this->getDelayValue();
		$this->getGamesWithBonus();
	}
}
?>