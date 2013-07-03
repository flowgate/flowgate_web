package org.immport.flock.commons;

import java.awt.Color;
import java.util.Map;

public class FlockEvents {
	private Color[][] allEvents = null;
	private Map <Byte, boolean[][]> popEvents = null;
	
	public Color[][] getAllEvents() {
		return allEvents;
	}
	public void setAllEvents(Color[][] allEvents) {
		this.allEvents = allEvents;
	}
	public Map<Byte, boolean[][]> getPopEvents() {
		return popEvents;
	}
	public void setPopEvents(Map<Byte, boolean[][]> popEvents) {
		this.popEvents = popEvents;
	}
}
