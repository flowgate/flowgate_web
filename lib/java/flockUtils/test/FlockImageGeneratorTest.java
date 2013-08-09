import org.immport.flock.utils.FlockImageGenerator;
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
        //runFiles();
        runSingle();
    }

    public void runFiles() throws Exception {
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

    public void runSingle() throws Exception {
        String[] args = {
                "single_population",
                "/Users/hkim/workspace/Workspace/gofcm/Tasks/2",
                "/Users/hkim/workspace/Workspace/gofcm/Tasks/2/pop",
                "1"
        };
        generator.main(args);
    }
}
