<?php
    
	$fp = fopen("../Files/files.txt",'r');
    if (!$fp) {
        echo "Unable to open file";
        exit;
    }
	
    $numFiles = 0;
    while(!feof($fp)) {
        $line = fgets($fp,999);
        if ($line) {
            $numFiles++;
        }
    }
    fclose($fp);
	
    $numFiles--;

    print "{\n";
    print "  success: true,\n";
    print "  columns: [\n";
    print "    {header: 'Sequence', dataIndex: 'c1', width: 15},\n";
    print "    {header: 'Name', dataIndex: 'c2', flex: 1},\n";
    print "    {header: 'File Name', dataIndex: 'c3', flex:1},\n";
    print "    {header: 'Status', dataIndex: 'c4'}\n";
    print "  ],\n";
    print "  fields: [\n";
    print "    'c1',\n";
    print "    'c2',\n";
    print "    'c3',\n";
    print "    'c4'\n";
    print "  ],\n";
    print "  rows: [\n";

    $fp = fopen("../Files/files.txt",'r');
    if (!$fp) {
        echo "Unable to open file";
        exit;
    }

    $cnt = 0;
    while (!feof($fp)) {
        $line = fgets($fp,999);
        if ($line) {
            $line = rtrim($line,"\n");
			$line = rtrim($line,"\r");
            $values = explode("\t",$line);
            print "    {\n";
            print "      c1: '$values[0]',\n";
            print "      c2: '$values[1]',\n";
            print "      c3: '$values[2]',\n";
            print "      c4: '$values[3]'\n";
            if ($cnt < $numFiles) {
                print "    },\n";
            } else {
                print "    }\n";
            }
            $cnt++;
        }
    }

    print "  ]\n";
    print "}\n";
?>
