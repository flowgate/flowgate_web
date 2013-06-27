<?php
    $fp = fopen("../Tasks/tasks.txt",'r');
    if (!$fp) {
        echo "Unable to open file";
        exit;
    }

    $numTasks = 0;
    while(!feof($fp)) {
        $line = fgets($fp,999);
        if ($line) {
            $numTasks++;
        }
    }
    fclose($fp);

    $numTasks--;

    print "{\n";
    print "  success: true,\n";
    print "  columns: [\n";
    print "    {header: 'Sequence', dataIndex: 'c1'},\n";
    print "    {header: 'Name', dataIndex: 'c2'},\n";
    print "    {header: 'Input File', dataIndex: 'c3'},\n";
    print "    {header: 'Bins', dataIndex: 'c4'},\n";
    print "    {header: 'Density', dataIndex: 'c5'},\n";
    print "    {header: 'Status', dataIndex: 'c6'}\n";
    print "  ],\n";
    print "  fields: [\n";
    print "    'c1',\n";
    print "    'c2',\n";
    print "    'c3',\n";
    print "    'c4',\n";
    print "    'c5',\n";
    print "    'c6'\n";
    print "  ],\n";
    print "  rows: [\n";

    $fp = fopen("../Tasks/tasks.txt",'r');
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
            print "      c4: '$values[3]',\n";
            print "      c5: '$values[4]',\n";
            print "      c6: '$values[5]'\n";
            if ($cnt < $numTasks) {
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
