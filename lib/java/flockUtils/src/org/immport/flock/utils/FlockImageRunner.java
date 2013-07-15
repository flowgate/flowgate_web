package org.immport.flock.utils;

import org.immport.flock.commons.FlockAdapterFile;
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

        List<String> childrenList = Arrays.asList(children);
        if(childrenList.contains(FlockAdapterFile.INPUT_DATA)
                && childrenList.contains(FlockAdapterFile.PERCENTAGE_TXT)
                && childrenList.contains(FlockAdapterFile.PROFILE_TXT)
                && childrenList.contains(FlockAdapterFile.POPULATION_ID_COL)
                && childrenList.contains(FlockAdapterFile.POPULATION_CENTER)
                && childrenList.contains(FlockAdapterFile.MFI)
                && childrenList.contains(FlockAdapterFile.PARAMETERS)) {
            String[] args = { type, workingPath, workingPath };
            FlockImageGenerator.main(args);
        }
    }
}
