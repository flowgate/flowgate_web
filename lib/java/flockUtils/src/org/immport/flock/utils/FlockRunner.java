package org.immport.flock.utils;

import org.apache.commons.io.IOUtils;
import org.immport.flock.commons.Zipper;

import java.io.*;
import java.net.URI;
import java.net.URL;
import java.security.CodeSource;
import java.security.ProtectionDomain;
import java.util.ArrayList;
import java.util.List;
import java.util.zip.ZipEntry;
import java.util.zip.ZipFile;

/**
 * User: hkim
 * Date: 7/11/13
 * Time: 2:05 PM
 * org.immport.flock.utils
 */
public class FlockRunner {
    private final String flockName = "flock1"; //"flock1", "flock1_gp_osx";

    public static void main(final String[] args) throws Exception {
        String errorMsg = "Usage: (d:integer) command <zipFile> <# of bin(d) OR range of # of bins(d-d)> <density(d) OR range of density(d-d)>";
        String rangePattern = "\\d+-\\d+";

        if (args.length < 4) {
            throw new Exception(errorMsg);
        }

        String binStr = args[1];
        String densityStr = args[2];
        String population = args[3];

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

        String zipInput = args[0];
        if(zipInput!=null) {
            FlockRunner runner = new FlockRunner();
            runner.execute(zipInput, bins, densities, Integer.parseInt(population));
        }
    }

    public void execute(String zipInput, List<Integer> bins, List<Integer> densities, int population) throws Exception {
        URI flockUri = this.getFlockFile();

        File inputDir = new File("inputs");
        inputDir.mkdir();

        Zipper.extract(zipInput, inputDir.getAbsolutePath());

        //this.extract(zipInput, null, inputDir.getAbsolutePath());
        String[] inputArr = inputDir.list();

        File resultDir = new File("results");
        resultDir.mkdir();

        if(inputArr!=null && inputArr.length>0 && bins.size()>0 && densities.size()>0) {
            for(String inputName : inputArr) {
                String inputFileName = inputDir.getAbsolutePath() + File.separator + inputName;
                String fileOutName = inputName + "_out";

                for(int aBin : bins) {
                    for(int aDensity : densities) {
                        String currName = resultDir.getAbsolutePath() +
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
        Zipper.buildNestedZip(resultDir.getAbsolutePath());
    }

    public URI getFlockFile() throws Exception {
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
        }

        return fileURI;
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
