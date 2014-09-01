package org.immport.flock.commons;

public class ArrayUtils {

	public static String[] copyOfRange(String[] ss, int firstIndex,
			int lastIndex) throws java.lang.IllegalArgumentException {
		if (ss == null) {
			return null;
		} else if (lastIndex == firstIndex) {
			return new String[0];
		} else if (lastIndex < firstIndex) {
			throw new java.lang.IllegalArgumentException(firstIndex + " > "
					+ lastIndex);
		} else {
			String[] result = new String[lastIndex - firstIndex];
			for (int i = 0; i < lastIndex - firstIndex; i++) {
				result[i] = ss[firstIndex + i];
			}
			return result;
		}
	}

}
