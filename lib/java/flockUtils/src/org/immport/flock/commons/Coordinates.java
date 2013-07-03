package org.immport.flock.commons;

public class Coordinates {

	Coordinates(String _id1, String _id2) {
		id1 = _id1;
		id2 = _id2;
	}

	private String id1, id2;

	public String getId1() {
		return id1;
	}

	public String getId2() {
		return id2;
	}

}
