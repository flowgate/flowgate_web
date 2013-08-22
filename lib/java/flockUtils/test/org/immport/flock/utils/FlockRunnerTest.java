package org.immport.flock.utils;

import org.junit.Test;

import java.util.Arrays;

/**
 * User: hkim
 * Date: 8/22/13
 * Time: 3:50 PM
 * org.immport.flock.utils
 */
public class FlockRunnerTest {
    FlockRunner runner = new FlockRunner();

    @Test
    public void run() throws Exception {
        Integer[] bins = new Integer[] {10, 11, 12};
        Integer[] dens = new Integer[] {11, 12, 13};
        runner.execute(
                "/Users/hkim/Stuffs/test/gofcm/inputs1.zip",
                Arrays.asList(bins),
                Arrays.asList(dens),
                20,
                "/Users/hkim/Stuffs/test/gofcm/",
                "flock1_gp_osx");
    }
}
