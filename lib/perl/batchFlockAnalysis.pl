#!/usr/bin/perl

use strict;
my $SrcDir = "/opt/www/gofcm/htdocs/LyoPlateBcell";
my $OutDir = "/opt/www/gofcm/htdocs/FLOCK_Linux/server";
my $BinDir = "/opt/www/gofcm/htdocs/FLOCK_Linux/server/bin";

#
# Make directories for output files
#
mkdir("$OutDir/Files");
mkdir("$OutDir/Tasks");

my $outName = $OutDir . "/Files/files.txt";
open(OUT,"> $outName") ||
    die "Can't open $outName: $!\n";

#
# Change to the source directory and retrieve a list of file names
#
chdir($SrcDir);
my $seq = 0;
my @files = `ls *.txt`;

#
# Create a directory, and copy the fcs text file
#
foreach my $file (@files) {
  $seq++;
  chomp($file);
  my $fileName = $file;
  $fileName =~ s/\.txt//;
  my $dirName = $OutDir . "/Files/" . $seq;
  mkdir($dirName);
  my $cmd = "cp " . $SrcDir . "/" . $file . " " . $dirName . "/fcs.txt";
  
  system($cmd);
  print OUT $seq,"\t",$fileName,"\t",$file,"\tLoaded\n";
}
close(OUT);

$outName = $OutDir . "/Files/file_sequence.txt";
open(OUT,"> $outName") ||
    die "Can't open $outName: $!\n";
print OUT $seq,"\n";
close(OUT);

#
# Now run FLOCK on all the files
#
my $inName = $OutDir . "/Files/files.txt";
open(IN,"< $inName") ||
    die "Can't open $inName: $!\n";
$outName = $OutDir . "/Tasks/tasks.txt";
open(OUT,"> $outName") ||
    die "Can't open $outName: $!\n";

my $line;
while (defined ($line = <IN>)) {
  chomp($line);
  my ($seq,$name,$fileName,$status) = split(/\t/,$line);
  my $dirName = $OutDir . "/Tasks/" . $seq;
  mkdir($dirName);
  chdir($dirName);
  my $cmd = $BinDir . "/flock1 ../../Files/" . $seq . "/fcs.txt";
  
  system($cmd);
  $cmd = "cp population_center.txt population_center.txt.orig";
  system($cmd);
  print OUT $seq,"\t",$name,"\t",$seq,"\t0\t0\tCompleted\n";
}
close(OUT);
close(IN);

$outName = $OutDir . "/Tasks/task_sequence.txt";
open(OUT,"> $outName") ||
    die "Can't open $outName: $!\n";
print OUT $seq,"\n";
close(OUT);

exit(0);
