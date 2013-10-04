package org.immport.flock.utils;

import org.genepattern.client.GPClient;
import org.genepattern.webservice.JobResult;
import org.genepattern.webservice.Parameter;

import java.io.File;

/**
 * User: hkim
 * Date: 10/4/13
 * Time: 10:24 AM
 * org.immport.flock.utils
 */
public class GenePattern {
    public static void main(String[] args) throws Exception {
        GPClient gpClient = new GPClient("http://genepatt-dev.jcvi.org:8080/gp", "hkim");
        JobResult[] results = new JobResult[2];
        results[0] = gpClient.runAnalysis(
                "ImmPortFLOCK_new",
                new Parameter[]{
                        new Parameter("input.zip", new File("/Users/hkim/Stuffs/test/gofcm/input2.zip")),
                        new Parameter("bins", "10"),
                        new Parameter("density", "12"),
                        new Parameter("population", "12")
                }
        );
        results[1] = gpClient.runAnalysis(
                "ImmPortImageGenerator_new",
                new Parameter[]{
                        new Parameter("image.type", "color"),
                        new Parameter("input.zip", results[0].getURL("zip").toString())
                }
        );
    }

}
