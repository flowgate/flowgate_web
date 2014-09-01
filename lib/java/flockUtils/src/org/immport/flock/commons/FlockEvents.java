package org.immport.flock.commons;

import java.awt.Color;
import java.util.Map;

public class FlockEvents {
	private Color[][] allEvents = null;
	private Map <Integer, boolean[][]> popEvents = null;
	
	public Color[][] getAllEvents() {
		return allEvents;
	}
	public void setAllEvents(Color[][] allEvents) {
		this.allEvents = allEvents;
	}
	public Map<Integer, boolean[][]> getPopEvents() {
		return popEvents;
	}
	public void setPopEvents(Map<Integer, boolean[][]> popEvents) {
		this.popEvents = popEvents;
	}
}
