package org.immport.flock.utils;

import org.genepattern.client.GPClient;
import org.genepattern.webservice.JobResult;
import org.genepattern.webservice.Parameter;
import org.genepattern.webservice.WebServiceException;
import org.immport.flock.db.Database;

import java.io.File;
import java.io.IOException;
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
        String flockLsid = args[7];
        String imageLsid = args[8];

        GenePattern gp = new GenePattern();
        gp.executePipeline(
                user, input, bins, density,
                population, type, jobId,//, gofcmPath
                flockLsid, imageLsid
        );
    }


    public void executePipeline(
            String user, String input, String bins, String density,
            String population, String type, String jobId,/*, String gofcmPath*/
            String flockLsid, String imageLsid) throws Exception {
        ResourceBundle rb = ResourceBundle.getBundle("flock");
        String gpAddress = rb.getString(this.GP_ADDRESS);
        if(user == null) {
            user = rb.getString(this.GP_USER);
        }

        String flockModule = null;
        if(flockLsid != null && !flockLsid.isEmpty() && flockLsid.startsWith("urn:lsid")) {
            flockModule = flockLsid;
        } else {
            flockModule = rb.getString(this.GP_FLOCK_MODULE);
            if(flockModule == null) {
                flockModule = "ImmPortFLOCK";
            }
        }
        String imageModule = null;
        if(imageLsid != null && !imageLsid.isEmpty() && imageLsid.startsWith("urn:lsid")) {
            imageModule = imageLsid;
        } else {
            imageModule = rb.getString(this.GP_IMAGE_MODULE);
            if(imageModule == null) {
                imageModule = "ImmPortImageGenerator";
            }
        }

        boolean successRun = false;

        Database database = new Database();
        database.analysisStatusUpdate(jobId, Database.ANALYSIS_STATUS_RUNNING);

        try {
            File inputFile = new File(input);
            if(inputFile.exists() || inputFile.canRead()) {

                GPClient gpClient = new GPClient(gpAddress, user);
                JobResult flockResults = gpClient.runAnalysis(
                        flockModule,
                        new Parameter[]{
                                new Parameter("input", inputFile),
                                new Parameter("bins", bins),
                                new Parameter("density", density),
                                new Parameter("population", population)
                        }
                );
                String outputUrl = flockResults.getURL("zip").toString();
                JobResult imageResult = gpClient.runAnalysis(
                        imageModule,
                        new Parameter[]{
                                new Parameter("input", "http://127.0.0.1:8080/gp" + outputUrl.substring(outputUrl.indexOf("gp") + 2)),
                                new Parameter("image_type", type == null?"color":type),
                                new Parameter("jobId", jobId),
                                new Parameter("population", "i"),
                                new Parameter("output_path", rb.getString(this.GP_GOFCM_RESULT_PATH))
                        }
                );

                if(!imageResult.hasStandardError()) {
                    successRun = true;
                }
            }
        } catch (WebServiceException e) {
            e.printStackTrace();
        } catch (IOException e) {
            e.printStackTrace();
        } catch(Exception e) {
            e.printStackTrace();
        } finally {
            try {
                database.analysisStatusUpdate(jobId, successRun ? Database.ANALYSIS_STATUS_COMPLETED : Database.ANALYSIS_STATUS_FAILED);
            } catch(Exception e) {
                e.printStackTrace();
            }
        }
    }

}
