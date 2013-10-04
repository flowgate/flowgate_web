package org.immport.flock.utils;

import org.junit.Test;

import java.util.Arrays;

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
        int max = 10;
        for(int i=1;i<=max;i++) {
            print_nCr(max, i);
        }
        //runner.execute("overview_color", testDir + "output/test");
    }

    public static final void print_nCr(final int n, final int r) {
        int[] res = new int[r];
        for (int i = 0; i < res.length; i++) {
            res[i] = i + 1;
        }
        boolean done = false;
        while (!done) {
            System.out.println(Arrays.toString(res));
            done = getNext(res, n, r);
        }
    }

    public static final boolean getNext(final int[] num, final int n, final int r) {
        int target = r - 1;
        num[target]++;
        if (num[target] > ((n - (r - target)) + 1)) {
            // Carry the One
            while (num[target] > ((n - (r - target)))) {
                target--;
                if (target < 0) {
                    break;
                }
            }
            if (target < 0) {
                return true;
            }
            num[target]++;
            for (int i = target + 1; i < num.length; i++) {
                num[i] = num[i - 1] + 1;
            }
        }
        return false;
    }
}
