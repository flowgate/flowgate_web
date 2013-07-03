package org.immport.flock.commons;

public class MinMax {
	int maxX = Integer.MIN_VALUE;
	int maxY = Integer.MIN_VALUE;
	int minX = Integer.MAX_VALUE;
	int minY = Integer.MAX_VALUE;
	public int getMaxX() {
		return maxX;
	}
	public void setMaxX(int maxX) {
		this.maxX = maxX;
	}
	public int getMaxY() {
		return maxY;
	}
	public void setMaxY(int maxY) {
		this.maxY = maxY;
	}
	public int getMinX() {
		return minX;
	}
	public void setMinX(int minX) {
		this.minX = minX;
	}
	public int getMinY() {
		return minY;
	}
	public void setMinY(int minY) {
		this.minY = minY;
	}
	public String toString(){
		return("minX="+minX+" maxX="+maxX+" minY="+minY+" maxY="+maxY);
	}
}
