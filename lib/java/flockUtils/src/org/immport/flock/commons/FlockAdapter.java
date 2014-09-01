package org.immport.flock.commons;

import java.awt.image.BufferedImage;
import java.io.File;
import java.io.InputStream;
import java.util.List;
import java.util.Map;

public interface FlockAdapter {
	
	public void setOutputDir(File outputDir);

	public void setInputDir(File inputDir);

	public void setDbmock(PoorMansTable dbmock);

	public void setOutputDir(String outputDir);

	public void setInputDir(String inputDir);

	public void setDbmock(String dbmock);
	
	public Profile getProfile() throws FlockAdapterException;
	public MinMax getMinMaxAll() throws FlockAdapterException;
	public MinMax getMinMaxFile() throws FlockAdapterException;
	public Integer [] getEvents() throws FlockAdapterException;
    public int [][] getCoordinates(int x,int y) throws FlockAdapterException;
    public int [][] getCentroids(int x, int y) throws FlockAdapterException;
    
    public void saveDotPlotImage(String dotPlotName, InputStream is) throws FlockAdapterException;

    public void saveDotPlotImage(String dotPlotName, BufferedImage imageBuffer) throws FlockAdapterException;
    
    /*
     * Used by first version, may not be necessary in the future
     */
    public String[] getDimensions(long pid) throws FlockAdapterException;  
    public List<Integer> getPopulations(long pid) throws FlockAdapterException;
    
    public boolean exists(long pid, String name) throws FlockAdapterException;
    
    public byte[] loadDotPlotImage(
            long pid,
            String name)
            throws FlockAdapterException;
    
    public Map<Integer,Float> getPercentage(
            long pid)
            throws FlockAdapterException;

    public List<String> getProfileList(long pid) throws FlockAdapterException;
 
    public void savePopulationMarkerExpression(long pid, int population,
            String markerExpression)
    	throws FlockAdapterException;
    
    public void savePopulationCellType(long pid, int population, String cellType)
    	throws FlockAdapterException;

    public String loadPopulationMarkerExpression(long pid, int population) throws FlockAdapterException ;
    public String loadPopulationCellType(long pid, int population) throws FlockAdapterException ;

}
