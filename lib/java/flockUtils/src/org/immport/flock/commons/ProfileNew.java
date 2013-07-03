package org.immport.flock.commons;

import java.util.ArrayList;
import java.util.HashMap;

/**
 * 
 * @author jchen
 *
 */
public class ProfileNew {
	private HashMap <Byte, Population> populations;
	private HashMap <String,Marker> markers;
	
	public HashMap<Byte,Population> getPopulations() {
		return populations;
	}
	public void setPopulations(HashMap<Byte,Population> populations) {
		this.populations = populations;
	}
	public HashMap<String, Marker> getMarkers() {
		return markers;
	}
	public void setMarkers(HashMap<String,Marker> markers) {
		this.markers = markers;
	}

	public Population findPopulation(Byte popId) {
		
		return populations.get(popId);
	}

	public int findMarkerIndex(String markerName) {
		return this.markers.get(markerName).getIndex();
	}
}
