package org.immport.utils.flock;

import java.io.BufferedReader;
import java.io.DataOutputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.FileReader;
import java.io.FileWriter;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.PrintWriter;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Properties;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

import org.immport.domain.hibernate.dao.analysis.flow.FileManagementDAO;
import org.immport.domain.hibernate.mapping.analysis.flow.FlowFlockDataInputMarker;
import org.immport.struts.utils.flow.FlowImageGenerator;

/**
 * The Class FlowUtils.
 * 
 * @author BISC-Team
 */
public class FlowUtils {

    /*
     * Runs a system command. Looks for "Exception" in STDOUT or if anything is
     * written to STDERR, if either occurs, then and exception is generated
     */
    /**
     * Run system command.
     * 
     * @param taskPath
     *            the task path
     * @param command
     *            the command
     * @throws Exception
     *             the exception
     */
    public static void runSystemCommand(String taskPath, String command)
            throws Exception {

        Runtime runtime = Runtime.getRuntime();
        Process proc = null;
        StringBuffer status = new StringBuffer();
        BufferedReader inputReader = null;
        BufferedReader errorReader = null;

        try {
            System.out.println("COMMAND: " + command);
            String[] cmd = { "/bin/sh", "-c", command };
            proc = runtime.exec(cmd);

            // read the output from stdout and stderr, and catch any errors.
            inputReader = new BufferedReader(new InputStreamReader(proc
                    .getInputStream()));
            errorReader = new BufferedReader(new InputStreamReader(proc
                    .getErrorStream()));

            String line = null;
            while ((line = inputReader.readLine()) != null) {
                System.out.println("STDOUT: " + line);
                if (line.matches(".*Exception.*")) {
                    status.append(line + "\n");
                }
            }
            while ((line = errorReader.readLine()) != null) {
                System.out.println("STDERR: " + line);
                status.append("STDERR: " + line + "\n");
            }

            // wait for process to completete
            proc.waitFor();
            if (status.length() > 0) {
                System.out.println("*** Flock execution failed: "
                        + status.toString());
                throw new Exception(status.toString());
            }

        } catch (IOException e) {
            // throw new Exception(e);
            throw new Exception(e.toString());
        } catch (InterruptedException e) {
            throw new Exception(e);
        } catch (Exception e) { // any other exception
            throw new Exception(e);
        } finally {
            if (inputReader != null) {
                try {
                    inputReader.close();
                } catch (IOException e) {

                }
            }
            if (errorReader != null) {
                try {
                    errorReader.close();
                } catch (IOException e) {
                }
            }
        }
    }

    /**
     * Get the FCS file from the database, and print out only the columns
     * selected for analysis
     * @param taskDir
     *            the task directory
     * @param flockInputId
     *            the flock input id
     * @param columnsUsedForAnalysis
     *            the columns used for analysis
     * @return the fcs file
     * @throws Exception
     *             the exception
     */
    public static void getFcsFile(File taskDir, Long flockInputId,
            String columnsUsedForAnalysis) throws Exception {

        FileManagementDAO fileManagementDAO = FileManagementDAO.getInstance();

        String[] columnNames = columnsUsedForAnalysis.split(",");
        int[] columnPositions = new int[columnNames.length];
        // Column positions start with 1, not 0, so we need to make
        // an adjustment by subtracting 1.
        for (int i = 0; i < columnNames.length; i++) {
            columnPositions[i] = Integer.parseInt(columnNames[i]) - 1;
        }

        try {
            BufferedReader reader = fileManagementDAO
                    .getFcsTextFile(flockInputId);
            PrintWriter pw = new PrintWriter(new FileWriter(new File(taskDir,
                    "fcs.txt")));

            /*
             * Read in the first line which contains the marker names, We will
             * replace them with the marker assigned names
             */
            String line = reader.readLine(); // first line contains marker names
            String replacementStr = "_";
            /*
             * Remove tab
             */
            String patternStr = "\t";
            // Compile regular expression
            Pattern pattern = Pattern.compile(patternStr);

            StringBuffer markerNames = new StringBuffer();
            for (int i = 0; i < columnNames.length; i++) {
                int columnPosition = Integer.parseInt(columnNames[i]);
                FlowFlockDataInputMarker ffdim = fileManagementDAO
                        .getFlowFlockDataInputMarker(flockInputId,
                                columnPosition);
                /*
                 * Clean up the marker names so that tab characters do not mess
                 * up the first line
                 */
                String markerName = ffdim.getNameAssigned();
                Matcher matcher = pattern.matcher(markerName);
                matcher.replaceAll(replacementStr);
                markerNames.append(matcher.replaceAll(replacementStr) + "\t");
            }
            markerNames.deleteCharAt(markerNames.length() - 1); // remove ending
                                                                // \t
            pw.print(markerNames.toString() + "\n");

            while ((line = reader.readLine()) != null) {
                line.replace("\r", "");
                if (!line.trim().equals("")) {
                    // use columnsForAnalysis to select columns for output
                    String[] values = line.split("\t");
                    StringBuffer outputLine = new StringBuffer();
                    for (int i = 0; i < columnPositions.length; i++) {
                        outputLine.append(values[columnPositions[i]] + "\t");
                    }
                    outputLine.deleteCharAt(outputLine.length() - 1); // remove
                                                                      // ending
                                                                      // \t
                    pw.print(outputLine.toString() + "\n");
                }
            }

            reader.close();
            pw.close();
        } catch (Exception ex) {
            System.out.println("EX: " + ex);
            throw new Exception("Problem generating the FCS file");
        }
    }

    /**
     * Generate the binary versions of the FCS and Population files, so they can
     * be used by the image generation software. Also update the fcs.properties
     * file with min, max and markerNames information.
     * 
     * @param taskDir
     *            the task directory
     * @param flockInputId
     *            the flock input id
     * @param columnsUsedForAnalysis
     *            the columns used for analysis
     * @throws Exception
     *             the exception
     */
    public static void generateImageSourceFiles(File taskDir,
            Long flockInputId, String columnsUsedForAnalysis) throws Exception {

        Properties parameters;

        BufferedReader fcsReader = null;
        BufferedReader popReader = null;
        DataOutputStream binFile = null;

        System.out.println("taskDir" + taskDir);
        try {
            String line;
            parameters = new Properties();
            parameters.load(new FileInputStream(taskDir + "/fcs.properties"));

            int events = Integer.parseInt(parameters.getProperty("Events"));
            int markers = Integer.parseInt(parameters.getProperty("Markers"));
            int populations = Integer.parseInt(parameters
                    .getProperty("Populations"));
            int[][] values = new int[markers][events];
            int min = 999999999;
            int max = -999999999;
            int count = 0;

            // Determine the number of populations, the count of events per
            // population
            // and list the events per population so we can create output files
            // ordered
            // by population
            ArrayList[] pops = new ArrayList[populations + 1];
            HashMap<String, Integer> popcount = new HashMap<String, Integer>();
            binFile = new DataOutputStream(new FileOutputStream(taskDir
                    + "/population.bin"));
            popReader = new BufferedReader(new FileReader(taskDir
                    + "/population_id.txt"));
            while ((line = popReader.readLine()) != null) {
                Integer v = Integer.valueOf(line);
                if (pops[v] == null) {
                    pops[v] = new ArrayList<Integer>();
                }
                pops[v].add(count);
                count++;
            }

            // Write out the binary file of populations in order
            StringBuffer popcountString = new StringBuffer();
            for (int i = 1; i <= populations; i++) {
                if (pops[i] == null) {
                    continue;
                }
                for (int j = 0; j < pops[i].size(); j++) {
                    binFile.writeInt(i);
                }
                if (i != populations) {
                    popcountString.append("P" + i + ":" + pops[i].size() + ",");
                } else {
                    popcountString.append("P" + i + ":" + pops[i].size());
                }
            }

            binFile.close();

            count = 0;
            fcsReader = new BufferedReader(new FileReader(taskDir + "/fcs.txt"));
            fcsReader.readLine();
            while ((line = fcsReader.readLine()) != null) {
                String[] data = line.split("\t");
                for (int i = 0; i < markers; i++) {
                    String value = data[i];
                    int v = (int) Float.parseFloat(value);
                    if (v > max) {
                        max = v;
                    }
                    if (v < min) {
                        min = v;
                    }
                    values[i][count] = v;
                }
                count++;
            }

            binFile = new DataOutputStream(new FileOutputStream(taskDir
                    + "/fcs.bin"));
            for (int i = 0; i < markers; i++) {
                for (int j = 1; j <= populations; j++) {
                    if (pops[j] == null) {
                        continue;
                    }
                    for (int k = 0; k < pops[j].size(); k++) {
                        int c = (Integer) pops[j].get(k);
                        binFile.writeInt(values[i][c]);
                    }

                }
            }

            binFile.close();

            /*
             * Now add in the columns used for analysis into the properties
             * files
             */
            FileManagementDAO fileManagementDAO = FileManagementDAO
                    .getInstance();
            String[] columnNames = columnsUsedForAnalysis.split(",");

            String replacementStr = "_";
            /*
             * Remove ",',period,comma,spaces,tab,\,/,|
             */
            String patternStr = "\"|\'|\\.|,|\\s|\t|\\\\|\\||/";
            // Compile regular expression
            Pattern pattern = Pattern.compile(patternStr);

            StringBuffer markerNames = new StringBuffer();
            for (int i = 0; i < columnNames.length; i++) {
                int columnPosition = Integer.parseInt(columnNames[i]);
                FlowFlockDataInputMarker ffdim = fileManagementDAO
                        .getFlowFlockDataInputMarker(flockInputId,
                                columnPosition);
                /*
                 * Clean up the marker names so that special characters do not
                 * mess up the file names
                 */
                String markerName = ffdim.getNameAssigned();
                Matcher matcher = pattern.matcher(markerName);
                matcher.replaceAll(replacementStr);
                markerNames.append(matcher.replaceAll(replacementStr) + "|");
            }
            markerNames.deleteCharAt(markerNames.length() - 1); // remove ending
                                                                // \t

            parameters.put("Min", String.valueOf(min));
            parameters.put("Max", String.valueOf(max));
            parameters.put("MarkerNames", markerNames.toString());
            parameters.put("PopulationCount", popcountString.toString());
            parameters.put("PopulationsOrig", String.valueOf(populations));
            parameters.store(new FileOutputStream(taskDir + "/fcs.properties"),
                    "");
            parameters.store(new FileOutputStream(taskDir
                    + "/fcs.properties.orig"), "");
        } catch (Exception ex) {
            System.out.println("EX: " + ex);
            throw new Exception("Problem generating the Image Source Files");
        } finally {
            if (fcsReader != null) {
                try {
                    fcsReader.close();
                } catch (IOException e) {
                }
            }
            if (popReader != null) {
                try {
                    popReader.close();
                } catch (IOException e) {

                }
            }
            if (binFile != null) {
                try {
                    binFile.close();
                } catch (IOException e) {

                }
            }
        }
    }

    /**
     * Generate the overview color and black and white images.
     * 
     * @param taskDir
     *            the task directory
     * @throws Exception
     *             the exception
     */
    public static void generateImageFiles(File taskDir) throws Exception {
        FlowImageGenerator fig = new FlowImageGenerator(0l, taskDir, taskDir);
        String parametersFile = taskDir + "/fcs.properties";
        fig.processParameters(parametersFile);
        // 300X300 images
        fig.getAllCoordinatesFast(300);
        fig.getPopulationsFast();
        // color
        fig.genOverviewImages(300, false);
        // black and white
        fig.genOverviewImages(300, true);
    }
}