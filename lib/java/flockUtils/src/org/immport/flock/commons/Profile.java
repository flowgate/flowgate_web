package org.immport.flock.commons;

import java.util.ArrayList;

public class Profile {
	private ArrayList <Population> populations = null;
	private ArrayList <Marker> markers = null;
	
	public ArrayList<Population> getPopulations() {
		return populations;
	}
	public void setPopulations(ArrayList<Population> populations) {
		this.populations = populations;
	}
	public ArrayList<Marker> getMarkers() {
		return markers;
	}
	public void setMarkers(ArrayList<Marker> markers) {
		this.markers = markers;
	}

	public Population findPopulation(Byte popId) {
		for (Population pop: populations) {
			if (popId == pop.getPopulation()) {
				return pop;
			}
		}
		return null;
	}
}
