package org.immport.flock.utils;

import org.junit.Test;

import java.util.UUID;

/**
 * User: hkim
 * Date: 10/15/13
 * Time: 2:49 PM
 * org.immport.flock.utils
 */
public class GenePatternTest {
    GenePattern gp = new GenePattern();

    @Test
    public void main() throws Exception {
        String testDir = "/Users/hkim/Stuffs/test/gofcm/";
        String gpdev_export = "/export/data/results";

        gp.executePipeline(
                "hkim",
                testDir + "input36.zip",
                "10",
                "11",
                "10",
                null,
                UUID.randomUUID().toString()
        );
    }
}
