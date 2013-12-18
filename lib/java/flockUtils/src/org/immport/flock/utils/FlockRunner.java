package org.immport.flock.utils;

import org.apache.commons.io.FileUtils;
import org.apache.commons.io.LineIterator;
import org.immport.flock.commons.FlockAdapterFile;
import org.immport.flock.commons.Zipper;

import java.io.BufferedReader;
import java.io.File;
import java.io.InputStreamReader;
import java.net.URI;
import java.net.URLDecoder;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.List;

/**
 * User: hkim
 * Date: 7/11/13
 * Time: 2:05 PM
 * org.immport.flock.utils
 */
public class FlockRunner {
    private final String flockName = "flock1_gp";
    public final String errorMsg = "Usage: (d:integer) command <zipFile> <# of bin(d) OR range of # of bins(d-d)> <density(d) OR range of density(d-d)>";

    public static void main(final String[] args) throws Exception {
        FlockRunner runner = new FlockRunner();

        if (args.length < 4) {
            throw new Exception(runner.errorMsg);
        }

        String binStr = args[1];
        String densityStr = args[2];
        String population = args[3];

        String zipInput = args[0];
        if(zipInput!=null) {
            runner.execute(zipInput, binStr, densityStr, Integer.parseInt(population), null, null);
        }
    }

    public void execute(String zipInput, String binStr, String densityStr, int population, String workDir, String flock) throws Exception {
        URI flockUri = null;
        if(workDir == null) {
            workDir = "";
        } else {
            if(!workDir.endsWith(File.separator)) {
                workDir += File.separator;
            }
        }

        if(flock==null) {
            flockUri = this.getFlockFile();
        } else {
            File flockFile = new File(workDir + flock);
            flockUri = flockFile.toURI();
        }

        if(binStr != null && densityStr != null){
            String rangePattern = "\\d+-\\d+";

            //parse integer or range parameters
            List<Integer> bins = new ArrayList<Integer>();
            if(binStr!=null && binStr.length()>0) {
                if(!binStr.matches("\\d+") && !binStr.matches(rangePattern)) { //validation
                    throw new Exception(errorMsg);
                }
                if(binStr.contains("-")) { //range
                    String[] binsArr = binStr.split("-");
                    int binLow = Integer.parseInt(binsArr[0]);
                    int binHigh = Integer.parseInt(binsArr[1]);
                    for(int i=binLow;i<=binHigh;i++) {
                        bins.add(i);
                    }
                } else { //single integer value
                    bins.add(Integer.parseInt(binStr));
                }
            }
            List<Integer> densities = new ArrayList<Integer>();
            if(densityStr!=null && densityStr.length()>0) {
                if(!densityStr.matches("\\d+") && !densityStr.matches(rangePattern)) { //validation
                    throw new Exception(errorMsg);
                }
                if(densityStr.contains("-")) {
                    String[] densitiesArr = densityStr.split("-");
                    int densityLow = Integer.parseInt(densitiesArr[0]);
                    int densityHigh = Integer.parseInt(densitiesArr[1]);
                    for(int i=densityLow;i<=densityHigh;i++) {
                        densities.add(i);
                    }
                } else {
                    densities.add(Integer.parseInt(densityStr));
                }
            }

            File inputDir = new File(workDir + "input");
            inputDir.mkdir();
            Zipper.extract(zipInput, inputDir.getAbsolutePath());
            File resultDir = new File(workDir + "result");
            resultDir.mkdir();

            this.executeHelper(flockUri, inputDir, resultDir, bins, densities, population);

            //build final zip output
            Zipper.buildNestedZip(resultDir.getAbsolutePath());
            //delete input directory
            FileUtils.deleteDirectory(inputDir);
        }
    }

    private void executeHelper(URI flockUri, File in, File out, List<Integer> bins, List<Integer> densities, int population) throws Exception {
        String[] files = in.list();

        if(files!=null && files.length>0) {
            //check if it is a flock result set already
            boolean hasAllFlockResults = true;
            List<String> filesList = Arrays.asList(files);
            for(String output : FlockAdapterFile.FLOCK_RESULTS) {
                if(!filesList.contains(output)) {
                    hasAllFlockResults = false;
                    break;
                }
            }

            if(hasAllFlockResults) {
                String binRead = null;
                String densityRead = null;
                File fcsPropertiesFiles = new File(in.getAbsolutePath() + File.separator + FlockAdapterFile.FCS);
                LineIterator lineIterator = FileUtils.lineIterator(fcsPropertiesFiles);
                try {
                    while (lineIterator.hasNext() && (binRead == null || densityRead == null)) {
                        String line = lineIterator.nextLine();
                        if(line.toLowerCase().startsWith("bin")) {
                            binRead = line.substring(line.lastIndexOf("=") + 1);
                        } else if(line.toLowerCase().startsWith("density")) {
                            densityRead = line.substring(line.lastIndexOf("=") + 1);
                        }
                    }
                } finally {
                    LineIterator.closeQuietly(lineIterator);
                }

                boolean outDirectoryEndingOut = out.getAbsolutePath().endsWith("_out");
                String fileOutName = (out.getName().equals("result") ? "user_result" : out.getName()) + (outDirectoryEndingOut ? "" : "_out");

                String currName = out.getAbsolutePath() + (outDirectoryEndingOut ? "" : "_out") +
                        File.separator +
                        fileOutName + "_" + binRead + "_" + densityRead;
                File outputDir = new File(currName);
                outputDir.mkdirs();

                FileUtils.copyDirectory(in, outputDir);
            } else {
                for(String file : files) {
                    String inputFileName = in.getAbsolutePath() + File.separator + file;
                    File inputFile = new File(inputFileName);
                    if(inputFile.isDirectory()) {
                        File newOut = new File(out.getAbsolutePath() + File.separator + file);
                        this.executeHelper(flockUri, inputFile, newOut, bins, densities, population);
                    } else {

                        if(!inputFile.isHidden()) { //skips hidden files: UNIX(.), Windows(check file property)
                            String fileOutName = file + "_out";

                            for(int aBin : bins) {
                                for(int aDensity : densities) {
                                    String currName = out.getAbsolutePath() +
                                            File.separator +
                                            fileOutName +
                                            File.separator +
                                            fileOutName + "_" + aBin + "_" + aDensity;
                                    File outputDir = new File(currName);
                                    outputDir.mkdirs();

                                    this.runFlock(flockUri, outputDir, inputFileName, aBin, aDensity, population);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public URI getFlockFile() throws Exception {

        String jarPath = this.getClass().getProtectionDomain().getCodeSource().getLocation().getPath();
        String decodedPath = URLDecoder.decode(jarPath, "UTF-8");
        String directory = decodedPath.substring(0, decodedPath.lastIndexOf(File.separator) + 1);
        File flock = new File(directory + flockName);


        /*
        * This block is used only when flock binary is included within that jar
        *
        URI fileURI = null;

        ProtectionDomain domain = FlockRunner.class.getProtectionDomain();
        CodeSource source = domain.getCodeSource();
        URL sourceLocation = source.getLocation();

        File jarFile = new File(sourceLocation.toURI());
        if (jarFile.isDirectory()) {
            fileURI = URI.create(jarFile.toString() + flockName);
        } else {
            ZipFile zipFile = new ZipFile(jarFile.getAbsolutePath());

            File tempFile = File.createTempFile(flockName, Long.toString(System.currentTimeMillis()));
            tempFile.deleteOnExit();
            ZipEntry entry = zipFile.getEntry(flockName);

            if (entry == null) {
                throw new FileNotFoundException("cannot find file: " + flockName + " in archive: " + zipFile.getName());
            }

            OutputStream outs = new FileOutputStream(tempFile);
            InputStream ins = zipFile.getInputStream(entry);
            IOUtils.copy(ins, outs);
            IOUtils.closeQuietly(outs);
            IOUtils.closeQuietly(ins);

            fileURI = tempFile.toURI();
        }*/

        return flock.toURI();
    }

    private void runFlock(URI flockUri, File workingDir, String dataFile, int bin, int density, int population) throws Exception {
        if(flockUri!=null) {
            File flockFile = new File(flockUri);
            if(flockFile!=null && flockFile.canRead()) {
                Process p = Runtime.getRuntime().exec("chmod u+x " + flockFile.toString());
                p.waitFor();
                p = Runtime.getRuntime().exec(
                        String.format("%s %s %d %d %d", flockFile.toString(), dataFile, bin, density, population),
                        null,
                        workingDir
                );

                String line;
                BufferedReader in = new BufferedReader(new InputStreamReader(p.getInputStream()));
                while ((line = in.readLine()) != null) {
                    System.out.println(line);
                }
                in.close();
                BufferedReader err = new BufferedReader(new InputStreamReader(p.getErrorStream()));
                while ((line = err.readLine()) != null) {
                    System.err.println(line);
                }
                err.close();

            }
        }
    }
}
