package org.immport.flock.utils;

import org.immport.flock.commons.Zipper;
import org.junit.Test;

/**
 * User: hkim
 * Date: 10/4/13
 * Time: 2:06 PM
 * org.immport.flock.utils
 */
public class FlockImageRunnerTest {
    FlockImageRunner runner = new FlockImageRunner();
    private final String testDir = "/Users/hkim/Stuffs/test/gofcm/";

    @Test
    public void run() throws Exception {
        String results = testDir + "result";
        Zipper.extract(testDir + "result.zip", results);

        runner.execute("overview_color", results);

        //print_nCr(5, 3);
        /*
        int max = 10;
        for(int i=1;i<=max;i++) {
            print_nCr(max, i);
        }*/
    }

    /*
    public static final void print_nCr(final int n, final int r) {
        int[] res = new int[r];
        for (int i = 0; i < res.length; i++) {
            res[i] = i + 1;
        }
        boolean done = false;
        while (!done) {
            System.err.println(Arrays.toString(res).replaceAll("\\s+",""));
            done = getNext(res, n, r);
            System.err.println("----------");
        }
    }

    public static final boolean getNext(final int[] num, final int n, final int r) {
        int target = r - 1;
        num[target]++;
        System.err.printf("1. target:%d, num[target]++:%d%n", num[target], target);
        System.err.printf("2. num[target]:%d, n:%d%n", num[target], n);
        if (num[target] > n){//((n - (r - target)) + 1)) { //check if the last element is still in the boundary
            // Carry the One
            while (num[target] > ((n - (r - target)))) {
                System.err.printf("3. num[target]:%d (target:%d), ((n - (r - target))):%d%n", num[target], target, ((n - (r - target))));
                target--;
                if (target < 0) {
                    break;
                }
            }
            if (target < 0) {
                return true;
            }
            System.err.printf("4. target:%d, num[target]:%d%n", target, num[target]);
            num[target]++;
            System.err.printf("5. target:%d, num[target]++:%d%n", target, num[target]);
            for (int i = target + 1; i < num.length; i++) {
                System.err.printf("6. num[i]:%d, num[i - 1] + 1:%d%n", num[i], num[i - 1] + 1);
                num[i] = num[i - 1] + 1;
            }
        }
        return false;
    }
    */
}
