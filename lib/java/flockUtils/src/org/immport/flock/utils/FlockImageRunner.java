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
    private static final int MAX_POOL_SIZE = 50;

    public static void main(String[] args) throws Exception {
        String errorMsg = "Usage: command <Type: all_images, overview(_color,_bw)> <input_zip_file>";

        if (args.length < 2) {
            throw new Exception(errorMsg);
        }

        String type = args[0];
        String inputPath = args[1];

        String results = "result";
        Zipper.extract(inputPath, results);

        FlockImageRunner runner = new FlockImageRunner();
        runner.execute(type, results);
        Zipper.buildNestedZip(results);
    }

    public void execute(String type, String workingPath) throws Exception {
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

    public static final boolean getNext(final int[] num, final int n, final int r) {
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
