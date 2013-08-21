package org.immport.flock.utils;

import org.junit.Before;
import org.junit.Test;

/**
 * User: hkim
 * Date: 7/3/13
 * Time: 2:41 PM
 * PACKAGE_NAME
 */
public class FlockImageGeneratorTest {
    FlockImageGenerator generator = new FlockImageGenerator();
    String testPath = "/Users/hkim/temp/flock/";
    String resultPath = testPath + "default_result/";
    String jobPath = testPath + "2";

    @Before
    public void set() {
    }

    @Test
    public void run() throws Exception  {
        //runOverview();
        runOverview1();

        runSingle("1");
        runSingle("2");
        runSingle("3");
        runSingle("4");
        runMultiPop();
    }

    public void runOverview() throws Exception {
        String[] args = {
                "overview_color",
                resultPath+"profile.txt",
                resultPath+"percentage.txt",
                resultPath+"population_center.txt",
                resultPath+"MFI.txt",
                resultPath+"population_id.txt",
                resultPath+"parameters.txt",
                resultPath+"coordinates.txt",
                jobPath
        };
        generator.main(args);
    }


    public void runOverview1() throws Exception {
        String[] args = {
                "overview_color",
                "/Users/hkim/Stuffs/test/gofcm/result",
                "/Users/hkim/Stuffs/test/gofcm/overview"
        };
        generator.main(args);
    }

    public void runSingle(String arg) throws Exception {
        String[] args = {
                "single_population",
                "/Users/hkim/Stuffs/test/gofcm/result",
                "/Users/hkim/Stuffs/test/gofcm/single"+arg,
                arg
        };
        generator.main(args);
    }

    public void runMultiPop() throws Exception {
        String[] args = {
                "multi_population",
                "/Users/hkim/Stuffs/test/gofcm/result",
                "/Users/hkim/Stuffs/test/gofcm/multip",
                "1,2,3,4"
        };
        generator.main(args);
    }
}
