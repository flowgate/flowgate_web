<?php
class RASModule {
	public static $SUCCESS = false;
	public static $RESULT = null;
	private static $TASKLOC = "./Tasks/";
	private $dbModule = null; 

	function dbm() {
		if(is_null($this->dbModule)) {
			require_once 'db_mongo.php';
			$this->dbModule = new DatabaseModule();
		}
	}

	function overview($_tid, $_type) {
		/*
	    if ($type == 'color') {
	        `./genOverviewColor ../Tasks/$taskId ../Tasks/$taskId`;
	    } else {
	        `./genOverviewBW ../Tasks/$taskId ../Tasks/$taskId`;
	    }
	    */

	    $pid = rand();

	    $fp = fopen("../Tasks/$taskId/profile.txt",'r');
	    if (!$fp) {
	    	echo "Unable to open file";
	    	exit;
	    }

	    // Read in the first line that has the Marker names
	    $line = rtrim(rtrim(fgets($fp,999),"\n"),"\r");
	    $markers = explode("\t",$line);
	    $markerLen = count($markers);
	    for ($i = 0; $i < $markerLen; $i++) {
	    	$markers[$i] = str_replace("<","",$markers[$i]);
	    	$markers[$i] = str_replace(">","",$markers[$i]);
	    }

	    // Read in the second line, to find the minimum population id
	    $line = rtrim(rtrim(fgets($fp,999),"\n"),"\r");
	    $populations = explode("\t",$line);
	    fclose($fp);

	    //read population percentage file
	    $fp = fopen("../Tasks/$taskId/percentage.txt", "r");
	    if (!$fp) {
	    	echo "Unable to open file";
	    	exit;
	    }
	    $pops = "{";
	    $totalPops = 0;
	    while(!feof($fp)){
	    	$line = rtrim(rtrim(fgets($fp),"\n"),"\r");
	    	if(strlen($line)>0 && $totalPops++ > 0) {
	    		$lineData = explode("\t",$line);
	    		$pops.=$lineData[0].":'".$lineData[1]."',";
	    	}
	    }
	    $pops.="}";
	    fclose($fp);

	    $renderCode =
	    "renderer: function(val) { ".
	    "var data = val.split(\";\"); ".
	    "return '<img height=\"85\" width=\"85\" ".
	    "src=\"./bin/displayFlockImage.php?taskId=$taskId&imageName='+encodeURIComponent(data[0])+'.'+encodeURIComponent(data[2])+'.all.$type.png&rr=$pid\">'".
	    "}";
	    $renderCodeSpan = "renderer: function(val) { return '<div style=\"height:85px;text-align:center;\">' + val + '</div>'}";

	    print "{\n";
	    print "  success: true,\n";
	    print "  popId: $populations[0],\n";
	    print "  totalPops:".($totalPops-1).", pops: ".$pops.", \n";
	    print "  columns: [\n";
	    $markerArray = array();
	    $markerArray[] = "    { header: ' ', dataIndex: 'm0', locked: true, $renderCodeSpan}";
	    for ($i = 1; $i < $markerLen; $i++) {
	    	$markerArray[] = "    { header: '$markers[$i]', dataIndex: 'm$i', locked: false, $renderCode}";
	    }

	    print join(",\n",$markerArray);
	    print "\n";
	    print "  ],\n";

	    print "  fields: [\n";
	    print "    'm0',\n";
	    for ($i = 1; $i < $markerLen; $i++) {
	    	print "    'm$i',\n";
	    }
	    print "  ],\n";

	    print "  rows: [\n";

	    for ($i = 1; $i < $markerLen; $i++) {
	    	$markerArray = array();
	    	$markerArray[] =  "      m0: '$markers[$i]'";
	    	for ($j = 1; $j < $markerLen; $j++) {
	    		$idx1 = $j -1;
	    		$idx2 = $i -1;
	    		$markerArray[] = "      m$j: '$markers[$j];$idx1;$markers[$i];$idx2'";
	    	}
	    	print "    {\n";
	    	print join(",\n",$markerArray);
	    	print "\n";
	    	if ($i < ($markerLen -1)) {
	    		print "    },\n";
	    	} else {
	    		print "    }\n";
	    	}
	    }

	    print "  ]\n";
	    print "}\n";
	}

	function centroid($_tid, $_idx1, $_idx2, $_pid) {
		`./genMarker2Pop ../Tasks/$taskId ../Tasks/$taskId $idx1 $idx2`;

		if(!file_exists("../Tasks/$taskId/history.txt")) { 
			$fp = fopen("your_filename_here","w");  
			fwrite($fp,"0");  
			fclose($fp); 
		} 

		$pid = rand();

		$fp = fopen("../Tasks/$taskId/profile.txt",'r');
		if(!$fp) {
			echo "Unable to open file";
			exit;
		}

	            // Read in the first line that has the Marker names
		$line = fgets($fp,999);
		$line = rtrim($line,"\n");
		$line = rtrim($line,"\r");
		$markers = explode("\t",$line);
		$markerLen = count($markers);
		for ($i = 0; $i < $markerLen; $i++) {
			$markers[$i] = str_replace("<","",$markers[$i]);
			$markers[$i] = str_replace(">","",$markers[$i]);
		}

		$populations = array();

		$popLen = 0;
		while(!feof($fp)) {
			$line = fgets($fp,999);
			if ($line) {
				$line = rtrim($line,"\n");
				$line = rtrim($line,"\r");
				$values = explode("\t",$line);
				$populations[$popLen] = $values[0];
				$popLen++;
			}
		} 

		fclose($fp);

		$Parameters = array();
		$fp = fopen("../Tasks/$taskId/parameters.txt",'r');
		if (!$fp) {
			echo "Unable to open file";
			exit;
		}

		while(!feof($fp)) {
			$line = fgets($fp,999);
			if ($line) {
				$line = rtrim($line,"\n");
				$line = rtrim($line,"\r");
				$values = explode("\t",$line);
				$Parameters[$values[0]] = $values[1];
			}
		} 
		fclose($fp);

	        // Remove the first element, the population_id column
		array_shift($markers);
		$markerLen = count($markers);

		$renderCode = "renderer: function(val) { ".
		"if (val == \"\") return \"\"; ".
		"var data = val.split(\";\"); ".
		"return  'Population: ' + data[4] + '<br><img height=\"90\" width=\"90\" ".
		"src=\"./bin/displayFlockImage.php?taskId=' + $taskId + '&imageName=' + data[0] + \".\" + data[2] + \".\" + data[4]  +".
		" \".color.highlighted.png&rr=$pid\" + '\">'}";


		print "{\n";
		print "  success: true,\n";

		print "  columns: [\n";
		for ($i = 0; $i < 5; $i++) {
			if ($i < 4) {
				print "    { header: '', dataIndex: 'p$i', width: 90, $renderCode},\n";
			} else {
				print "    { header: '', dataIndex: 'p$i', width: 90, $renderCode}\n";
			}
		}
		print "  ],\n";

		print "  fields: [\n";
		for ($i = 0; $i < 5; $i++) {
			if ($i < 4) {
				print "    'p$i',\n";
			} else {
				print "    'p$i'\n";
			}
		}
		print "  ],\n";

		print "  rows: [\n";
		$cnt = 0;
		for ($i = 0; $i < $popLen;) {
			print "    {\n";
			for ($j = 0; $j < 5; $j++) {
				$popCnt = $i + $j;
				if ($j < 4) {
					if ($popCnt < $popLen) {
						print "      p$j: '$markers[$idx1];$idx1;$markers[$idx2];$idx2;$populations[$popCnt]',\n";
					} else {
						print "      p$j: '',\n";
					}
				} else {
					if ($popCnt < $popLen) {
						print "      p$j: '$markers[$idx1];$idx1;$markers[$idx2];$idx2;$populations[$popCnt]'\n";
					} else {
						print "      p$j: ''\n";
					}
				}
			}

			$i += 5;
			if ($i < $popLen) {
				print "    },\n";
			} else {
				print "    }\n";
			}
		}

		print "  ],\n";

		$fp = fopen("../Tasks/$taskId/population_center.txt",'r');
		if (!$fp) {
			echo "Unable to open file";
			exit;
		}

		$xValue = 0;
		$yValue = 0;

		$cnt = 0;
		while(!feof($fp)) {
			$line = fgets($fp,999);
			if ($line) {
				$line = rtrim($line,"\n");
				$line = rtrim($line,"\r");
				$values = explode("\t",$line);
				if ($popIdx == $values[0]) {
					$xValue = (int)$values[$idx1+1];
					$yValue = (int)$values[$idx2+1];
					break;
				}
			}
		}
		fclose($fp);

		$min = $Parameters['Min'];
		$max = $Parameters['Max'];
		$range = $max - $min + 1;
		$xCoord = (int)((($xValue - $min)/$range) * 300);
		$yCoord = (int)(299 - ((($yValue - $min)/$range) * 300));


		print "  idx1Name: '$markers[$idx1]',\n";
		print "  idx2Name: '$markers[$idx2]',\n";
		print "  populations: '$popLen',\n";
		print "  data: {\n";
		print "    m1Idx: '$idx1',\n";
		print "    m2Idx: '$idx2',\n";
		print "    popIdx: '$popIdx',\n";
		print "    m1Name: '$markers[$idx1]',\n";
		print "    m2Name: '$markers[$idx2]',\n";
		print "    xValue: '$xValue',\n";
		print "    yValue: '$yValue',\n";
		print "    xCoord: '$xCoord',\n";
		print "    yCoord: '$yCoord',\n";
		print "    min: '$Parameters[Min]',\n";
		print "    max: '$Parameters[Max]',\n";
		print "    srcURL: './bin/displayFlockImage.php?taskId=$taskId&imageName='\n";
		print "  }\n";
		print "}\n";
	}
}
?>