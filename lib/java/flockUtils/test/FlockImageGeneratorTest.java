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
    public void run() throws Exception {
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
}
