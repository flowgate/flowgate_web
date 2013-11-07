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
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;

/**
 * User: hkim
 * Date: 7/15/13
 * Time: 10:48 AM
 * org.immport.flock.utils
 */
public class FlockImageRunner {
    private static final int MAX_POOL_SIZE = 15;
    private static final String[] KEYS_FOR_PROP = {"bins", "density", "markers", "populations"};

    public static void main(String[] args) throws Exception {
        String errorMsg = "Usage: command <type: overview_color or overview_bw)> " +
                "<population: a for All, i for individual populations>" +
                "<input_zip_file> <job_id> <output_path>";

        if (args.length < 5) {
            throw new Exception(errorMsg);
        }

        int paramIndex = 0;
        String type = args[paramIndex++];
        String population = args[paramIndex++];
        String inputPath = args[paramIndex++];
        String jobId = args[paramIndex++];
        String outputPath = args[paramIndex];

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
        outputPath += outputPath.endsWith(File.separator) ? "" : File.separator;

        if(jobId.equals("_")) {
            jobId = UUID.randomUUID().toString();
        }
        System.out.println("JOB ID: " + jobId);

        String results = outputPath + jobId;

        File resultDir = new File(results);
        if(resultDir.exists()) {
            throw new Exception("Failed: Result directory already exists!");
        }

        //extract flock result files
        Zipper.extract(inputPath, results);

        Map<String, List<String>> info = new HashMap<String, List<String>>();

        this.execute(type, population, results, info);

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
        JSONObject jsonParam = new JSONObject();
        jsonParam.put("bins", info.get("bins"));
        jsonParam.put("density", info.get("density"));
        jsonProp.put("params", jsonParam);
        PrintWriter writer = new PrintWriter(new File(resultDir.getAbsolutePath() + File.separator + "prop"));
        writer.println(jsonProp.toString());
        writer.close();

        Process chmodProcess = Runtime.getRuntime().exec("chmod -R a+rw " + results);
        chmodProcess.waitFor();

        //no need to build zip since all outputs now go to common result area
        //Zipper.buildNestedZip(results);

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
                                String onlyMarkers = value.substring(value.indexOf("[") + 1, value.indexOf("]"));
                                String[] markers = onlyMarkers.split("\\t");
                                for(String marker : markers) {
                                    if(!values.contains(marker)) {
                                        values.add(marker);
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
            File workingDir = new File(workingPath + File.separator +"images");
            FlockImageGenerator fig = new FlockImageGenerator(0l, new File(workingPath), workingDir);
            fig.processFlockOutput();

            fig.generateGrey(); //generates single empty grey image

            int totalPopulation = fig.getPopulationSize();
            ExecutorService executor = Executors.newFixedThreadPool(MAX_POOL_SIZE);
            boolean isBW = type.endsWith("bw");

            if(population.equals("a")) { //all combinations of population
                for(int i=1;i<=totalPopulation;i++) {
                    int[] res = new int[i];
                    for (int j = 0; j < res.length; j++) {
                        res[j] = j + 1;
                    }
                    boolean done = false;
                    String populationParam = null;
                    while (!done) {
                        populationParam = Arrays.toString(res).replaceAll("\\s+","");
                        populationParam = populationParam.substring(1, populationParam.length() - 1);
                        ProcessParameter pp = new ProcessParameter("a", "m", true, isBW, populationParam);
                        Runnable worker = new ImageRunner(fig, pp);
                        executor.execute(worker);
                        //fig.generate(pp);
                        done = getNext(res, totalPopulation, i);
                    }
                }
            } else { //individual populations
                String allPopulationString = "";
                for(int i=1;i<=totalPopulation;i++) {
                    allPopulationString += i + ",";
                    ProcessParameter pp = new ProcessParameter("a", "s", true, isBW, Integer.toString(i));
                    Runnable worker = new ImageRunner(fig, pp);
                    executor.execute(worker);
                }
                //overview
                ProcessParameter pp = new ProcessParameter("a", "o", true, isBW, allPopulationString);
                Runnable worker = new ImageRunner(fig, pp);
                executor.execute(worker);

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
