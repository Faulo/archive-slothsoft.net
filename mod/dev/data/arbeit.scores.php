<?php
use Slothsoft\Core\DOMHelper;
use Slothsoft\CMS\HTTPFile;

class CsvFile {
	public $path;
	public $rows;
	public $header;
	public function __construct(string $path) {
		$this->path = $path;
	}
	public function load() {
		$this->rows = [];
		if (($handle = fopen($this->path, 'r')) !== false) {
			while (($row = fgetcsv($handle)) !== false) {
				$this->rows[] = $row;
			}
			fclose($handle);
		}
		$this->header = array_shift($this->rows);
	}
	public function save() {
		if (($handle = fopen($this->path, 'w')) !== false) {
			fputcsv($handle, $this->header);
			foreach ($this->rows as $row) {
				$row = array_pad($row, count($this->header), '');
				fputcsv($handle, $row);
			}
			fclose($handle);
		}
	}
}

if (isset($_REQUEST['id'])) {
	$id = $_REQUEST['id'];
	$usersId = "$id.users";
	$scoresId = "$id.scores";
	$restoreId = "$id.users.restore";
	$csvDocs = $this->getResourceDir("/dev/arbeit", 'info');
	$xmlDocs = $this->getResourceDir("/dev/arbeit-xml", 'xml');
	if (isset($csvDocs[$usersId]) and isset($xmlDocs[$scoresId])) {
		$usersDoc = $csvDocs[$usersId];
		$scoresDoc = $xmlDocs[$scoresId];
		$usersPath = $usersDoc->documentElement->getAttribute('realpath');
		$directory = dirname($usersPath);
		$users = new CsvFile($usersPath);
		$users->load();
		
		$restore = null;
		if (isset($csvDocs[$restoreId])) {
			$restoreDoc = $csvDocs[$restoreId];
			$restorePath = $restoreDoc->documentElement->getAttribute('realpath');
			$restore = new CsvFile($restorePath);
			$restore->load();
		}
		
		foreach ($scoresDoc->getElementsByTagName('paper') as $paperNode) {
			$paperName = $paperNode->getAttribute('name');
			$paperId = "$id.$paperName";
			$path = $directory . DIRECTORY_SEPARATOR . "$paperId.csv";
			$paper = new CsvFile($path);
			if (file_exists($path)) {
				$paper->load();
			} else {
				$paper->header = array_slice($users->header, 0, 3);
				foreach ($paperNode->getElementsByTagName('task') as $taskNode) {
					$number = $taskNode->getAttribute('number');
					$points = $taskNode->getAttribute('points');
					$paper->header[] = "$number ($points)";
				}
				$paper->header[] = 'Kommentar';
				foreach ($users->rows as $row) {
					$paper->rows[] = array_slice($row, 0, 3);
				}
				$path = $directory . DIRECTORY_SEPARATOR . "$paperId.zip";
				if (file_exists($path)) {
					$names = [];
					$zip = new ZipArchive;
					if ($zip->open($path) === true) {
						for($i = 0; $i < $zip->numFiles; $i++) {
							$name = $zip->getNameIndex($i);
							if (preg_match('~(.+?)_~', $name, $match)) {
								$names[] = $match[1];
							}
						}
					}
					$names = array_unique($names);
					foreach ($paper->rows as &$row) {
						$name = "$row[1] $row[2]";
						$i = array_search($name, $names, true);
						if ($i === false) {
							$row = array_pad($row, count($paper->header) - 1, '0');
							$row[] = 'Keine Abgabe';
						} else {
							$row = array_pad($row, count($paper->header), '');
							unset($names[$i]);
						}
					}
					unset($row);
					if (count($names) > 0) {
						echo "ERROR! Not all names could be matched to users:" . PHP_EOL;
						my_dump($names);
						die;
					}
				}
				usort($paper->rows, function($a, $b) {
					return $a[1] <=> $b[1];
				});
				$paper->save();
			}
			$paperKey = array_search("Aufgabe: $paperName (Punkte)", $users->header, true);
			if ($paperKey === false) {
				echo "ERROR! Paper name not found:" . PHP_EOL;
				my_dump("Aufgabe: $paperName (Punkte)");
				my_dump($users->header);
				die;
			}
			foreach ($paper->rows as $row) {
				$userKey = false;
				$mail = null;
				foreach ($users->rows as $key => $user) {
					if ($row[0] === $user[0]) {
						$userKey = $key;
						$mail = $user[3];
						break;
					}
				}
				if ($userKey === false) {
					continue;
					echo "ERROR! User not found:" . PHP_EOL;
					my_dump($row);
					my_dump($users->rows);
					die;
				}
				$sum = 0;
				$comment = [];
				foreach ($paperNode->getElementsByTagName('task') as $i => $taskNode) {
					$number = $taskNode->getAttribute('number');
					$points = (int) $taskNode->getAttribute('points');
					$taskKey = $i + 3;
					if ($row[$taskKey] === '') {
						$sum = '';
						$comment = [];
						break;
					}
					$value = (float) str_replace(',', '.', $row[$taskKey]);
					$sum += $value;
					$comment[] = "$number: $value/$points";
				}
				$comment[] = end($row);
				if ($restore) {
					$mKey = array_search("E-Mail-Adresse", $restore->header, true);
					$pKey = array_search("Bewertungsaspekt", $restore->header, true);
					if ($mKey === false or $pKey === false) {
						echo "ERROR! Restore keys not found:" . PHP_EOL;
						my_dump($restore->header);
						die;
					}
					foreach ($restore->rows as $r) {
						if ($r[$mKey] === $mail and $r[$pKey] === $paperName) {
							if (strlen($r[4]) > 0) {
								$sum = $r[4];
								$comment = [];
							}
						}
					}
				}
				$users->rows[$userKey][$paperKey] = (string) $sum;
				$users->rows[$userKey][$paperKey + 1] = implode('; ', $comment);
			}
		}
		$users->save();
		
		return $usersDoc;
	}
}