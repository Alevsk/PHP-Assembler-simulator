<?php


class Parser {

	//public $assemblerRow = "/^(mov|jmp|add|inc|dec|jz|end)((\s)+(x|y|(\d)+)($|\s)+(x|y|(\d)+|(\d)+m)?)?(\s)*$/"; //simple assembler sintaxis support
	//public $assemblerRow = "/^(([a-z0-9])+:(\s)*)?(mov|jmp|add|inc|dec|jz|end)((\s)+(x|y|(\d)+)($|\s)+(x|y|(\d)+|(\d)+m)?)?(\s)*$/"; // ^ with tags support
	//public $assemblerRow = "/^(([a-z0-9])+:(\s)*)?(mov|jmp|add|inc|dec|jz|end)((\s)+(x|y|(\d)+|([a-z0-9])+)($|\s)+(x|y|(\d)+|(\d)+m|([a-z0-9])+)?)?(\s)*$/";
	public $assemblerRow = "/^(([a-z]+[a-z0-9]*)+:(\s)*)?(mov|jmp|add|inc|dec|jz|end)((\s)+(x|y|(\d)+|([a-z]+[a-z0-9]*)+)($|\s)+(x|y|(\d)+|(\d)+m|([a-z]+[a-z0-9]*)+)?)?(\s)*$/";
	public $assemblerRow = "/^(([a-z]+[a-z0-9]*)+:(\s)*)?(mov|jmp|add|inc|dec|jz|end)((\s)+(x|y|(\d)+|([a-z]+[a-z0-9]*)+)($|\s)+(x|y|(\d)+|(\d)+m|([a-z]+[a-z0-9]*)+|(\[(x|y)\])?)?)(\s)*$/";

	public $result;
	public $instructions = array(

		'end' => array('#' => array('#' => 0)),
		'jmp' => array('#' => array('#' => 1)),
		'inc' => array('x' => 12, 'y' => 13),
		'dec' => array('x' => 14, 'y' => 15),
		'mov' => array('x' => array('#' => 2), 'y' => array('#' => 3), '#' => array('x' => 4, 'y' => 5), 'm' => array('x' => 6, 'y' => 7)),
		'jz' => array('x' => array('#' => 16), 'y' => array('#' => 17)),
		'add' => array('x' => array('y' => 8, '#' => 10), 'y' => array('x' => 9, '#' => 11)),

		);

	public function isAValidRow($line){
		return preg_match($this->assemblerRow, $line, $this->result);
	}

}


if(count($argv) != 2) {
	print "Usage: php ensamblador.php assembler-code.txt\n"; 
	exit();
}

if(!file_exists($argv[1])) {
	print "file " . $argv[1] . " not found\n";
	exit();
}

$fileName = $argv[1];
$opCode = "";

$handle = fopen($fileName, "r");
if ($handle) {
	$lineN = 0;
	$program = array();
	$tags = array();
	$parser = new Parser();

	while (($line = fgets($handle)) !== false) {

		$lineN++;
		$line = trim($line);
		if($parser->isAValidRow($line)) {

	        	//1: instruction
	        	//4: register
	        	//7: value

			if(!isset($parser->result[7])) $parser->result[7] = "";
			if(!isset($parser->result[11])) $parser->result[11] = "";

			$execute = array('command' => $parser->result[4], 'op1' => $parser->result[7], 'op2' => $parser->result[11]);
			print_r($parser->result);
			
			if(empty($execute['op1'])) $execute['op1'] = '#';
			if(empty($execute['op2'])) $execute['op2'] = '#';

			//tag saving
			if(!empty($parser->result[1])) {
				$tags[trim(str_replace(':','',$parser->result[1]))] = count($program);
			}

			//check tag structure and symbol table
			if(preg_match("/^([a-wz]+[a-z0-9]*)$/", $execute['op1'])) {
				if(array_key_exists($execute['op1'], $tags))
					$execute['op1'] = $tags[$execute['op1']];
				else
					$tags[$execute['op1']] = null;
			}

			if(preg_match("/^([a-wz]+[a-z0-9]*)$/", $execute['op2'])) {
				if(array_key_exists($execute['op2'], $tags))
					$execute['op2'] = $tags[$execute['op2']];
				else
					$tags[$execute['op2']] = null;
			}

			if(strpos($execute['op2'], 'm') !== FALSE) {
				//$opCode .= $parser->instructions[$execute['command']]['m'][$execute['op1']] . "," . str_replace('m', '', $execute['op2']) . ",";
				array_push($program, $parser->instructions[$execute['command']]['m'][$execute['op1']], str_replace('m', '', $execute['op2']));
			} else if(is_numeric($execute['op1'])) {
				//$opCode .= $parser->instructions[$execute['command']]['#'][$execute['op2']] . "," . $execute['op1'] . ",";
				array_push($program, $parser->instructions[$execute['command']]['#'][$execute['op2']], $execute['op1']);
			} else {
				if(is_numeric($execute['op2'])) {
					//$opCode .= $parser->instructions[$execute['command']][$execute['op1']]['#'] . "," . $execute['op2'] . ",";
					array_push($program, $parser->instructions[$execute['command']][$execute['op1']]['#'], $execute['op2']);
				} else {
					//print_r($execute);

					if(is_numeric($parser->instructions[$execute['command']][$execute['op1']])) {

						//$opCode .= $parser->instructions[$execute['command']][$execute['op1']] . ",";
						array_push($program, $parser->instructions[$execute['command']][$execute['op1']]);

					} else {

						$tempTag1 = null;
						$tempTag2 = null;

						if($execute['op1'] != '#' && $execute['op1'] != 'x' && $execute['op1'] != 'y') {
							$tempTag1 = $execute['op1'];
							$execute['op1'] = '#';
						}
						
						if($execute['op2'] != '#' && $execute['op2'] != 'x' && $execute['op2'] != 'y') {
							$tempTag2 = $execute['op2'];
							$execute['op2'] = '#';
						}

						if($tempTag1) {

							//$opCode .= $parser->instructions[$execute['command']]['#'][$execute['op2']] . "," . $tempTag1 . ",";
							array_push($program, $parser->instructions[$execute['command']]['#'][$execute['op2']], $tempTag1);

						} else if($tempTag2) {
							
							//$opCode .= $parser->instructions[$execute['command']][$execute['op1']]['#'] . "," . $tempTag2 . ",";
							array_push($program, $parser->instructions[$execute['command']][$execute['op1']]['#'], $tempTag2);

						} else {
							//$opCode .= $parser->instructions[$execute['command']][$execute['op1']][$execute['op2']] . ",";
							array_push($program, $parser->instructions[$execute['command']][$execute['op1']][$execute['op2']]);
						}						
					}
				}
			}

			/*print_r($program);
			exit();*/

		} else {
			print "Sintax error in line  " . $lineN . ": " . $line . "\n";
			exit();
		}

	}

	//Segunda pasada
	for($i = 0; $i < count($program); $i++) {
		if(array_key_exists($program[$i], $tags)) $program[$i] = $tags[$program[$i]];
		$opCode .= $program[$i] . ",";
	}
		

	print_r($program);
	print_r($tags);
	echo $opCode . "\n";
	echo "\n\n";

} else {
	print "failed to open " . $argv[1] . " file\n";
	exit();
} 
fclose($handle);

?>