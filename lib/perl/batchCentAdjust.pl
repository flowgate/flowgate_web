#!/usr/bin/perl

use strict;
my $SrcDir = "/opt/www/gofcm/htdocs/LyoPlateBcell";
my $OutDir = "/opt/www/gofcm/htdocs/FLOCK_Linux/server";
my $BinDir = "/opt/www/gofcm/htdocs/FLOCK_Linux/server/bin";
my $CentroidFile = "/opt/www/gofcm/htdocs/FLOCK_Linux/server/population_center.txt";

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
  my $cmd = "cp ../../Files/" . $seq . "/fcs.txt" . " coordinates.txt";
  system($cmd);
  my $cmd = $BinDir . "/cent_adjust " . $CentroidFile . " ./coordinates.txt";
  system($cmd);
  $cmd = "cp " . $CentroidFile . " population_center.txt";
  system($cmd); 
  $cmd = "cp population_center.txt population_center.txt.orig";
  system($cmd);

  #
  # Read in the coordinates.txt file to determin min and max
  # so we can write them out to the parameters.txt file
  #
  open(MinMax,"< coordinates.txt") ||
      die "Can't open coordinates.txt: $!\n";

  my $line1;
  my $min = 9999999;
  my $max = -9999999;
  $line1 = <MinMax>;
  while (defined ($line1 = <MinMax>)) {
    chomp($line1);
    my @values = split(/\t/,$line1);
    foreach my $value (@values) {
      if ($value > $max) {
        $max = $value;
      }
      if ($value < $min) {
        $min = $value;
      }
    }
  }
  close(MinMax);
  my $parName = $OutDir . "/Tasks/" . $seq . "/parameters.txt";
  open(PARAM,"> $parName") ||
       die "Can't open $parName: $!\n";
  print PARAM "Min\t",$min,"\n";
  print PARAM "Max\t",$max,"\n";
  close(PARAM);

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

