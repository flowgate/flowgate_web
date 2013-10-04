package org.immport.flock.utils;

import org.immport.flock.commons.FlockAdapterFile;
import org.immport.flock.commons.ProcessParameter;
import org.immport.flock.commons.Zipper;

import java.io.File;
import java.util.Arrays;
import java.util.List;

/**
 * User: hkim
 * Date: 7/15/13
 * Time: 10:48 AM
 * org.immport.flock.utils
 */
public class FlockImageRunner {
    public static void main(String[] args) throws Exception {
        String errorMsg = "Usage: command <Type: all_images, overview(_color,_bw)> <input_zip_file> <OUTPUT_DIR>";

        if (args.length < 3) {
            throw new Exception(errorMsg);
        }

        String type = args[0];
        String inputPath = args[1];
        String outputPath = args[2];

        String results = outputPath + File.separator + "results";
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
            for(int i=1;i<=fig.getPopulationSize();i++) {
                ProcessParameter pp = new ProcessParameter("a", "m", true, false, null);
                fig.generate(pp);
            }
            this.helper(workingDir.getAbsolutePath());
        }
    }


    private void helper(String workingPath) throws Exception {
        File workingDir = new File(workingPath);
        List<String> files = Arrays.asList(workingDir.list());

        File outputDir = new File(workingPath + File.separator +"images");
        if(!outputDir.exists()) {
            outputDir.mkdirs();
        }
        FlockImageGenerator fig = null;

        fig = new FlockImageGenerator(0l, new File(workingPath), outputDir);
        fig.processFlockOutput();

    }
}
