#!/bin/bash

JobId=$1
InputFile=$2
ScriptOut=$3
Output=$4
ResultDir=$5
CP=/export/cyberflow/lib/java

/export/scripts/flowGate_script-gordon-full.sh $InputFile $ScriptOut False

java -classpath $CP/axis.jar:$CP/mail.jar:$CP/activation.jar:$CP/flockUtils.jar -Djava.awt.headless=true org.immport.flock.utils.FlockImageRunner overview_color a $Output $JobId $ResultDir

