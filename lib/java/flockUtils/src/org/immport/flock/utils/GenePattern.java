package org.immport.flock.utils;

import org.genepattern.client.GPClient;
import org.genepattern.webservice.JobResult;
import org.genepattern.webservice.Parameter;

import java.io.File;
import java.util.ResourceBundle;

/**
 * User: hkim
 * Date: 10/4/13
 * Time: 10:24 AM
 * org.immport.flock.utils
 */
public class GenePattern {
    private final String GP_ADDRESS = "genepattern.address";
    private final String GP_USER = "genepattern.user";
    private final String GP_FLOCK_MODULE = "genepattern.flock";
    private final String GP_IMAGE_MODULE = "genepattern.image";
    private final String GP_GOFCM_RESULT_PATH = "genepattern.gofcm";

    public static void main(String[] args) throws Exception {
        if (args.length < 7) {
            throw new Exception("Failed: (" + GenePattern.class + ") missing input parameters!");
        }

        String user = args[0];

        String input = args[1];
        String bins = args[2];
        String density = args[3];
        String population = args[4];

        String type = args[5];
        String jobId = args[6];
        //String gofcmPath = args[7];

        GenePattern gp = new GenePattern();
        gp.executePipeline(
                user, input, bins, density,
                population, type, jobId//, gofcmPath
        );
    }


    public void executePipeline(
            String user, String input, String bins, String density,
            String population, String type, String jobId/*, String gofcmPath*/) throws Exception {
        ResourceBundle rb = ResourceBundle.getBundle("flock");
        String gpAddress = rb.getString(this.GP_ADDRESS);
        if(user == null) {
            user = rb.getString(this.GP_USER);
        }

        String flockModule = rb.getString(this.GP_FLOCK_MODULE);
        if(flockModule == null) {
            flockModule = "ImmPortFLOCK";
        }
        String imageModule = rb.getString(this.GP_IMAGE_MODULE);
        if(imageModule == null) {
            imageModule = "ImmPortImageGenerator";
        }

        GPClient gpClient = new GPClient(gpAddress, user);
        JobResult[] results = new JobResult[2];
        results[0] = gpClient.runAnalysis(
                flockModule,
                new Parameter[]{
                        new Parameter("input", new File(input)),
                        new Parameter("bins", bins),
                        new Parameter("density", density),
                        new Parameter("population", population)
                }
        );
        results[1] = gpClient.runAnalysis(
                imageModule,
                new Parameter[]{
                        new Parameter("input", results[0].getURL("zip").toString()),
                        new Parameter("image_type", type == null?"color":type),
                        new Parameter("jobId", jobId),
                        new Parameter("population", "i"),
                        new Parameter("output_path", rb.getString(this.GP_GOFCM_RESULT_PATH))
                }
        );
    }

}
