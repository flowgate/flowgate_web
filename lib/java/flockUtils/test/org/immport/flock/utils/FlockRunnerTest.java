package org.immport.flock.utils;

import org.junit.Test;

/**
 * User: hkim
 * Date: 8/22/13
 * Time: 3:50 PM
 * org.immport.flock.utils
 */
public class FlockRunnerTest {
    FlockRunner runner = new FlockRunner();
    private final String testDir = "/Users/hkim/Stuffs/test/gofcm/";// /Users/hkim/Stuffs/test/gofcm/, /Users/movence/works/test/gofcm/

    @Test
    public void run() throws Exception {
        Integer[] bins = new Integer[] {10};
        Integer[] dens = new Integer[] {11};
        runner.execute(
                testDir + "flockResult3.zip",
                "10",
                "11",
                12,
                testDir,
                "flock1_gp_osx");

        //Zipper.extract(testDir + "result.zip", testDir + "output");
    }
}
