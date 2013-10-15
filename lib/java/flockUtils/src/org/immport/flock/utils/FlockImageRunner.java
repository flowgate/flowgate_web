package org.immport.flock.utils;

import org.immport.flock.commons.FlockAdapterFile;
import org.immport.flock.commons.ProcessParameter;
import org.immport.flock.commons.Zipper;

import java.io.File;
import java.util.Arrays;
import java.util.List;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;

/**
 * User: hkim
 * Date: 7/15/13
 * Time: 10:48 AM
 * org.immport.flock.utils
 */
public class FlockImageRunner {
    private static final int MAX_POOL_SIZE = 20;

    public static void main(String[] args) throws Exception {
        String errorMsg = "Usage: command <Type: all_images, overview(_color,_bw)> <input_zip_file> <job_id> <output_path>";

        if (args.length < 4) {
            throw new Exception(errorMsg);
        }

        String type = args[0];
        String inputPath = args[1];
        String jobId = args[2];
        String outputPath = args[3];

        FlockImageRunner runner = new FlockImageRunner();
        runner.begin(type, inputPath, jobId, outputPath);
    }

    public void begin(String type, String inputPath, String jobId, String outputPath) throws Exception {
        outputPath += outputPath.endsWith(File.separator) ? "" : File.separator;

        //UUID uuid = UUID.randomUUID();
        String results = outputPath + jobId;

        File resultDir = new File(results);
        if(resultDir.exists()) {
            throw new Exception("Failed: Result directory already exists!");
        }

        //extract flock result files
        Zipper.extract(inputPath, results);

        this.execute(type, results);
        //Zipper.buildNestedZip(results);

    }

    private void execute(String type, String workingPath) throws Exception {
        String[] children = new File(workingPath).list();
        for(String childName : children) {
            File child = new File(workingPath + File.separator + childName);
            if(child.isDirectory()) {
                this.execute(type, child.getAbsolutePath());
            }
        }
        List<String> files = Arrays.asList(children);
        if(files.contains(FlockAdapterFile.INPUT_DATA)
                && files.contains(FlockAdapterFile.PERCENTAGE_TXT)
                && files.contains(FlockAdapterFile.PROFILE_TXT)
                && files.contains(FlockAdapterFile.POPULATION_ID_COL)
                && files.contains(FlockAdapterFile.POPULATION_CENTER)
                && files.contains(FlockAdapterFile.MFI)
                && files.contains(FlockAdapterFile.PARAMETERS)) {
            File workingDir = new File(workingPath + File.separator +"images");
            FlockImageGenerator fig = new FlockImageGenerator(0l, new File(workingPath), workingDir);
            fig.processFlockOutput();

            fig.generateGrey(); //generates single empty grey image

            int totalPopulation = fig.getPopulationSize();
            ExecutorService executor = Executors.newFixedThreadPool(MAX_POOL_SIZE);
            for(int i=1;i<=totalPopulation;i++) {
                int[] res = new int[i];
                for (int j = 0; j < res.length; j++) {
                    res[j] = j + 1;
                }
                boolean done = false;
                String populationParam = null;
                while (!done) {
                    populationParam = Arrays.toString(res).replaceAll("\\s+","");
                    populationParam = populationParam.substring(1, populationParam.length()-1);
                    ProcessParameter pp = new ProcessParameter("a", "m", true, false, populationParam);
                    Runnable worker = new ImageRunner(fig, pp);
                    executor.execute(worker);
                    //fig.generate(pp);
                    done = getNext(res, totalPopulation, i);
                }
            }
            executor.shutdown();
            while (!executor.isTerminated()) {}
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
