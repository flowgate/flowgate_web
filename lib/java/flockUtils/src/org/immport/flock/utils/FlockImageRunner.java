package org.immport.flock.utils;

import org.immport.flock.commons.FlockAdapterFile;
import org.immport.flock.commons.ProcessParameter;
import org.immport.flock.commons.Zipper;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileReader;
import java.io.PrintWriter;
import java.util.*;
import java.util.concurrent.*;

/**
 * User: hkim
 * Date: 7/15/13
 * Time: 10:48 AM
 * org.immport.flock.utils
 */
public class FlockImageRunner {
    private static final String[] KEYS_FOR_PROP = {"bins", "density", "markers", "populations"};

    public static void main(String[] args) throws Exception {
        String errorMsg = "Usage: command <type: overview_color or overview_bw)> " +
                "<population: a for All, i for individual populations>" +
                "<input_zip_file> <job_id> <output_path[optional]>";

        if (args.length < 4) {
            throw new Exception(errorMsg);
        }

        int paramIndex = 0;
        String type = args[paramIndex++];
        String population = args[paramIndex++];
        String inputPath = args[paramIndex++];
        String jobId = args[paramIndex++];
        String outputPath = (args.length == 5 ? args[paramIndex] : null);

        if(!type.startsWith("overview_") || (!type.endsWith("color") && !type.endsWith("bw"))) {
            throw new Exception(errorMsg);
        }
        if(!population.equals("a") && !population.equals("i")) {
            throw new Exception(errorMsg);
        }

        FlockImageRunner runner = new FlockImageRunner();
        runner.begin(type, population, inputPath, jobId, outputPath);
    }

    public void begin(String type, String population, String inputPath, String jobId, String outputPath) throws Exception {
        try {
            boolean runInInputPath = false;

            File inputFile = new File(inputPath);
            if(!inputFile.exists() || !inputFile.canRead()) {
                throw new Exception("input file does not exist");
            }

            String destination = inputPath;
            File resultDir = new File(destination);

            if(inputFile.isDirectory() && outputPath == null) { //run image generator in current input directory
                runInInputPath = true;
            }

            if(!runInInputPath) {
                if(jobId.equals("_")) { //generate random ID
                    jobId = UUID.randomUUID().toString();
                }
                System.out.println("JOB ID: " + jobId);

                outputPath += outputPath.endsWith(File.separator) ? "" : File.separator;
                destination = outputPath + jobId;
                resultDir = new File(destination);
                if(resultDir.exists()) {
                    throw new Exception("Failed: Result directory already exists!");
                }

                inputFile = new File(inputPath);
                if(inputFile.exists() && inputFile.isDirectory()) { //build zip archive if it is a directory
                    Zipper.buildNestedZip(inputPath, true);
                    inputPath = inputPath + ".zip";
                }

                //extract flock result files
                Zipper.extract(inputPath, resultDir.getAbsolutePath());

                //FileUtils.copyFileToDirectory(inputFile, resultDir); //copy
            }

            Map<String, List<String>> info = new HashMap<String, List<String>>();

            this.execute(type, population, destination, info);

            //sort population values
            List<String> populations = info.get("populations");
            Collections.sort(populations, new Comparator<String>() {
                public int compare(String s1, String s2) {
                    return Integer.valueOf(s1).compareTo(Integer.valueOf(s2));
                }
            });

            //create json data file
            JSONObject jsonProp = new JSONObject();
            jsonProp.put("markers", info.get("markers"));
            jsonProp.put("populations", populations.get(populations.size()-1)); //records only the largest

            boolean isAutoMode = population.equals("-1");
            jsonProp.put("auto", isAutoMode); //auto mode

            if(!isAutoMode) {
                JSONObject jsonParam = new JSONObject();
                jsonParam.put("bins", info.get("bins"));
                jsonParam.put("density", info.get("density"));
                jsonProp.put("params", jsonParam);
            }
            PrintWriter writer = new PrintWriter(new File(resultDir.getAbsolutePath() + File.separator + "prop"));
            writer.println(jsonProp.toString());
            writer.close();

            Process chmodProcess = Runtime.getRuntime().exec("chmod -R a+rw " + destination);
            chmodProcess.waitFor();

            Zipper.buildOneLevelZip(destination);
        } catch(Exception ex) {
            throw ex;
        }finally {
            /*Database database = new Database();
            database.analysisStatusUpdate(jobId, success ? Database.ANALYSIS_STATUS_COMPLETED : Database.ANALYSIS_STATUS_FAILED);*/
        }

    }

    private void execute(String type, String population, String workingPath, Map<String, List<String>> info) throws Exception {
        String[] children = new File(workingPath).list();
        for(String childName : children) {
            File child = new File(workingPath + File.separator + childName);
            if(child.isDirectory()) {
                this.execute(type, population, child.getAbsolutePath(), info);
            }
        }

        System.out.println("Processing image generation: " + workingPath);

        //checks if current directory has all flock result files
        List<String> files = Arrays.asList(children);
        boolean hasAllFlockResults = true;
        for(String output : FlockAdapterFile.FLOCK_RESULTS) {
            if(!files.contains(output)) {
                hasAllFlockResults = false;
                break;
            }

            //read flock results to generate prop file
            if(output.equals(FlockAdapterFile.FCS)) {
                BufferedReader br = new BufferedReader(new FileReader(workingPath + File.separator + FlockAdapterFile.FCS));
                String line = null;
                while((line = br.readLine()) != null) {
                    if(line.startsWith("Bins") || line.startsWith("Density") || line.startsWith("Populations") || line.startsWith("Markers")) {
                        String[] tempArr = line.split("=");

                        if(tempArr.length == 2 && tempArr[0] != null && tempArr[1] != null) {
                            String key = tempArr[0].toLowerCase();
                            String value = tempArr[1];

                            List<String> values = null;
                            if(info.containsKey(key)) {
                                values = info.get(key);
                            } else {
                                values = new ArrayList<String>();
                                info.put(key, values);
                            }

                            if(key.equals("markers")) {
                                if(value.startsWith("[") && value.endsWith("]")) {
                                    String onlyMarkers = value.substring(value.indexOf("[") + 1, value.indexOf("]"));
                                    String[] markers = onlyMarkers.split("\\t");
                                    for(String marker : markers) {
                                        if(!values.contains(marker)) {
                                            values.add(marker);
                                        }
                                    }
                                }
                            } else {
                                if(!values.contains(value)) {
                                    values.add(value);
                                }
                            }
                        }
                    }
                }
                br.close();
            }
        }

        if(hasAllFlockResults) {
            File workingDir = new File(workingPath + File.separator + "images");

            FlockImageGenerator fig = new FlockImageGenerator(0l, new File(workingPath), workingDir);
            fig.processFlockOutput();
            fig.generateGrey(); //generates single empty grey image

            int totalPopulation = fig.getPopulationSize();
            boolean isBW = type.endsWith("bw");

            //ExecutorService executor = Executors.newFixedThreadPool(Runtime.getRuntime().availableProcessors());

            int availableProcessors = Runtime.getRuntime().availableProcessors();
            BlockingQueue<Runnable> linkedBlockingDeque = new LinkedBlockingDeque<Runnable>(50);
            ExecutorService executor = new ThreadPoolExecutor(availableProcessors, availableProcessors, 15, TimeUnit.SECONDS, linkedBlockingDeque, new ThreadPoolExecutor.CallerRunsPolicy());

            String allPopulationString = "";
            for(int i = 1;i <= totalPopulation;i++) {
                allPopulationString += i + ",";
                ProcessParameter pp = new ProcessParameter("a", "s", true, isBW, Integer.toString(i));
                Runnable worker = new ImageRunner(fig, pp);
                executor.execute(worker);
            }
            //overview
            ProcessParameter opp = new ProcessParameter("a", "o", true, isBW, allPopulationString);
            Runnable oworker = new ImageRunner(fig, opp);
            executor.execute(oworker);

            if(population.equals("a")) { //combinations of population
                int maxPopulationCombination = 3; //totalPopulation
                for(int i = 2; i <= maxPopulationCombination; i++) {
                    int[] res = new int[i];
                    for (int j = 0; j < res.length; j++) {
                        res[j] = j + 1;
                    }
                    boolean done = false;
                    String populationParam = null;
                    while (!done) {
                        populationParam = Arrays.toString(res).replaceAll("\\s+", "");
                        populationParam = populationParam.substring(1, populationParam.length() - 1);
                        ProcessParameter cpp = new ProcessParameter("a", "m", true, isBW, populationParam);
                        Runnable cworker = new ImageRunner(fig, cpp);
                        executor.execute(cworker);
                        //fig.generate(pp);
                        done = getNext(res, totalPopulation, i);
                    }
                }
            }
            executor.shutdown();
            while (!executor.isTerminated()) {} //waits for child threads to end
        }
    }

    private final boolean getNext(final int[] num, final int n, final int r) {
        int target = r - 1;
        num[target]++;
        if (num[target] > ((n - (r - target)) + 1)) {
            // Carry the One
            while (num[target] > ((n - (r - target)))) {
                target--;
                if (target < 0) {
                    break;
                }
            }
            if (target < 0) {
                return true;
            }
            num[target]++;
            for (int i = target + 1; i < num.length; i++) {
                num[i] = num[i - 1] + 1;
            }
        }
        return false;
    }

    public class ImageRunner implements Runnable {
        FlockImageGenerator generator;
        ProcessParameter pp;
        public ImageRunner(FlockImageGenerator generator, ProcessParameter pp) {
            this.generator = generator;
            this.pp = pp;
        }

        public void run() {
            try {
                generator.generate(pp);
            } catch (Exception ex) {
                ex.printStackTrace();
            }
        }
    }
}
