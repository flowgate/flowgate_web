package org.immport.flock.commons;
import java.io.BufferedOutputStream;
import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.ByteArrayOutputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.FileReader;
import java.io.FileWriter;
import java.io.IOException;
import java.io.InputStream;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

import org.apache.commons.io.IOUtils;

/**
 * wrap the accessing to the files and the functions to generate new files.
 * 
 * @author jchen
 *
 */
public class FlockAdapterFileNew {
	public static final String INPUT_DATA = "coordinates.txt";
	public static final  String PERCENTAGE_TXT = "percentage.txt";
	public static final  String PROFILE_TXT = "profile.txt";
	public static final  String POPULATION_ID_COL = "population_id.txt";
	public static final  String POPULATION_CENTER = "population_center.txt";
	public static final  String REVERT_INPUT_DATA = "coordinatesRev.txt";
	public static final  String REVERT_BINARY_INPUT_DATA = "coordinatesRev.dat";
	public static final  String MFI = "MFI.txt";
	public static final  String PARAMETERS = "parameters.txt";
	private File outputDir;
	private File inputDir;
	private PoorMansTable dbmock;
	
	//intermediate values
	private MinMax minMaxAll;
	private MinMax minMaxFile;
	private ProfileNew profile;
	private Long events;
	

	public FlockAdapterFileNew() {
	}

	public FlockAdapterFileNew(String inputDir, String outputDir)
			throws FlockAdapterException {
		super();

		this.inputDir = new File(inputDir);
		if (!this.inputDir.exists() || !this.inputDir.canRead()) {
			throw new FlockAdapterException("Input directory " + inputDir
					+ " is invalid");
		}

		this.outputDir = new File(outputDir);
		if (!this.outputDir.exists()) {
			boolean done = false;
			try {
				done = this.outputDir.mkdir();
			} catch (Exception e) {
			}
			if (!done) {
				throw new FlockAdapterException(
						"Cannot create output directory " + outputDir);
			}
		}
		dbmock = new PoorMansTable(this.outputDir);
	}

	/**
	 * Try to collect all the meta information by reading each file only once.
	 * @return
	 * @throws FlockAdapterException
	 */
	public ProfileNew getProfile() throws FlockAdapterException {
		if( profile != null) return this.profile;
		BufferedReader br = null;
		String line;
		this.profile = new ProfileNew();
		boolean header = true;
		try {
			br = new BufferedReader(new FileReader(new File(inputDir,
					PROFILE_TXT)));
			HashMap<String, Marker> markers = new HashMap<String,Marker>();
			
			while ((line = br.readLine()) != null) {
				if (header) {
					String[] markerNames = line.split("\t");
					for (int i = 1; i < markerNames.length; i++) {
						markerNames[i] = markerNames[i].replace(">", "");
						markerNames[i] = markerNames[i].replace("<", "");
						Marker marker = new Marker();
						marker.setIndex(i - 1);
						marker.setName(markerNames[i]);
						markers.put(marker.getName(),marker);
					}
					header = false;
				} else {
					Population population = new Population();
					String[] populationValues = line.split("\t");
					population.setPopulation(Byte
							.parseByte(populationValues[0]));
					ArrayList<Integer> pops = new ArrayList<Integer>();
					for (int i = 1; i < populationValues.length; i++) {
						pops.add(Integer.parseInt(populationValues[i]));
					}
					population.setScores(pops);
					this.profile.getPopulations().put(population.getPopulation(),population);
				}
			}
			profile.setMarkers(markers);
		} catch (IOException e) {
			System.out.println("EX: " + e);
			throw new FlockAdapterException(e);
		} finally {
			try {
				if (br != null) {
					br.close();
				}
			} catch (IOException e) {
				throw new FlockAdapterException(e);
			}
		}
		
		this.readPercentageFile();
		this.readPolulationCenter();
		this.readMFI();
		
		//revert the input data file
		this.generateRevertCoordinateFile();
		this.generateRevertBinaryCoordinateFile();
		return this.profile;
	}
	
	
	private void readPercentageFile() throws FlockAdapterException{
		/*
		 * Read in the percentage file, to extract the population percentage
		 * information
		 */
		BufferedReader br=null;
		try {
			 br = new BufferedReader(new FileReader(new File(inputDir,
					PERCENTAGE_TXT)));
			// First line has header information and is not needed
			String line = br.readLine();
			while ((line = br.readLine()) != null) {
				String[] values = line.split("\t");
				Population pop = profile.findPopulation(Byte
						.parseByte(values[0]));
				pop.setPercentage(Float.parseFloat(values[1]));
			}
		} catch (IOException e) {
			System.out.println("EX: " + e);
			throw new FlockAdapterException(e);
		} finally {
			try {
				if (br != null) {
					br.close();
				}
			} catch (IOException e) {
				throw new FlockAdapterException(e);
			}
		}
	}
	
	
	private void readPolulationCenter() throws FlockAdapterException{
		/*
		 * Read in the population center file, to extract the centroid
		 * information
		 */
		BufferedReader br = null;
		try {
			br = new BufferedReader(new FileReader(new File(inputDir,
					POPULATION_CENTER)));
			String line=null;
			while ((line = br.readLine()) != null) {
				String[] values = line.split("\t");
				Population pop = profile.findPopulation(Byte
						.parseByte(values[0]));
				ArrayList<Float> centroids = new ArrayList<Float>();
				for (int i = 1; i < values.length; i++) {
					centroids.add(Float.parseFloat(values[i]));
				}
				pop.setCentroids(centroids);
			}
		} catch (IOException e) {
			System.out.println("EX: " + e);
			throw new FlockAdapterException(e);
		} finally {
			try {
				if (br != null) {
					br.close();
				}
			} catch (IOException e) {
				throw new FlockAdapterException(e);
			}
		}
	}
	
	
	private void readMFI() throws FlockAdapterException{
		/*
		 * Read in the MFI file, to extract the MFI
		 * information
		 */
		BufferedReader br = null;
		try {
			br = new BufferedReader(new FileReader(new File(inputDir,
					MFI)));
			String line = null;
			while ((line = br.readLine()) != null) {
				String[] values = line.split("\t");
				Population pop = profile.findPopulation(Byte
						.parseByte(values[0]));
				ArrayList<Float> mfis = new ArrayList<Float>();
				for (int i = 1; i < values.length; i++) {
					mfis.add(Float.parseFloat(values[i]));
				}
				pop.setMfis(mfis);
			}
		} catch (IOException e) {
			System.out.println("EX: " + e);
			throw new FlockAdapterException(e);
		} finally {
			try {
				if (br != null) {
					br.close();
				}
			} catch (IOException e) {
				throw new FlockAdapterException(e);
			}
		}

	}


	
	private void collectMinMaxAll(String tokenVal) {
		Float fx = Float.valueOf(tokenVal);
		Float fy = Float.valueOf(tokenVal);
		int x = fx.intValue();
		int y = fy.intValue();

		y = -1 * y;

		this.minMaxAll.maxX = Math.max(this.minMaxAll.maxX, x);
		this.minMaxAll.minX = Math.min(this.minMaxAll.minX, x);
		this.minMaxAll.maxY = Math.max(this.minMaxAll.maxY, y);
		this.minMaxAll.minY = Math.min(this.minMaxAll.minY, y);
		
	}
	
		
	/**
	 * revert the file: the row turns to be column, for more efficient reading when generating the images.
	 * The format of each line is:
	 * [markerName1]event1 event2 ...
	 * [markerName1]event1 event2 ....
	 * 
	 * Calcute the MinMaxAll by the way.
	 * @throws FlockAdapterException
	 */
	public void generateRevertCoordinateFile() throws FlockAdapterException {
		try {
			BufferedReader br  = new BufferedReader(new FileReader(new File(inputDir, INPUT_DATA)));
			BufferedWriter writer = new BufferedWriter(new FileWriter(new File(inputDir, REVERT_INPUT_DATA)));
			String line = br.readLine();
			int rows = line.split("\t").length;
			StringBuffer[] revert = new StringBuffer[rows];
			while( line!= null){
				String[] tokens = line.split("\t");
				for( int i=0; i<rows; ++i) {
					revert[i].append(tokens[i]+"\t");
					//update the MinMaxAll
					this.collectMinMaxAll(tokens[i].trim());
				}
				line = br.readLine();
			}
			//write to the file
			for( int i=0; i<rows; ++i) {
				writer.write(revert[i].append("\n").toString());
			}
			br.close();
			writer.close();
		}
		catch (IOException e){
			throw new FlockAdapterException(e);
		}
	}

	/**
	 * revert the input_data file and save as a byte file.
	 * Since this is a binary file, the marker name is removed. 
	 * The format is:
	 *   [numOfBytesForMarker1][][][]...[numOfBytesForMarker2][][]...
	 * Calculate the MinMaxALL by the way.
	 * @throws FlockAdapterException
	 */
	public void generateRevertBinaryCoordinateFile() throws FlockAdapterException {
		try {
			BufferedReader br  = new BufferedReader(new FileReader(new File(inputDir, INPUT_DATA)));
			BufferedOutputStream writer = new BufferedOutputStream(new FileOutputStream(new File(inputDir, REVERT_BINARY_INPUT_DATA)));
			String line = br.readLine();
			int rows = line.split("\t").length;
			byte[][] revert = new byte[rows][];
			int lineNum =1;
			while( line!= null){
				String[] tokens = line.split("\t");
				for( int i=0; i<rows; ++i) {
					revert[i][0]= (byte)tokens.length;
					revert[i][lineNum] = Byte.parseByte(tokens[i]);
					this.collectMinMaxAll(tokens[i].trim());
				}
				line = br.readLine();
				++lineNum;
			}
			//write to the file
			for( int i=0; i<rows; ++i) {
				writer.write(revert[i]);
			}
			br.close();
			writer.close();
		}
		catch (IOException e){
			throw new FlockAdapterException(e);
		}
		
	}

	/**
	 * This function gets two lines of events by marker names from the revert text data file.
	 * @param markerX
	 * @param markerY
	 * @return
	 * @throws IOException
	 */
	public long[][] getTwoMarkersEventsFromTextRevert(String markerX, String markerY) throws IOException {
		
		int x = this.profile.findMarkerIndex(markerX);
		int y = this.profile.findMarkerIndex(markerY);
		return this.getTwoMarkersEventsFromTextRevert(x, y);
	}

	/**
	 * This function is used to get two lines(markers) of events from a revert input data file, the text file.
	 * @param markerX
	 * @param markerY
	 * @return
	 * @throws IOException 
	 */
	public long[][] getTwoMarkersEventsFromTextRevert(long xIndex, long yIndex) throws IOException {
		long[][] xyEvents = new long[2][];
		BufferedReader br = new BufferedReader(new FileReader(new File(inputDir, REVERT_INPUT_DATA)));
		
		int lineNum =0;
		String line = br.readLine();
		while( line!= null){
			if( xIndex== lineNum){
				String[] tokens = line.split("\t");
				for( int i=1; i<tokens.length; ++i) {
					xyEvents[0][i-1] = Long.parseLong(tokens[i]);
				}
			}
			else if( yIndex== lineNum){
				String[] tokens = line.split("\t");
				for( int i=1; i<tokens.length; ++i) {
					xyEvents[1][i-1] = Long.parseLong(tokens[i]);
				}				
			}
			line = br.readLine();
			++lineNum;
		}
	
		return xyEvents;
	}
	
	/**
	 * generate image files for all the marker combinations.
	 * @param width
	 * @param height
	 * @param isColor
	 * @throws IOException
	 */
	public void generateMarker2MarkerImages( int width,int height, boolean isColor) throws IOException {

		BufferedReader br = new BufferedReader(new FileReader(new File(inputDir, REVERT_INPUT_DATA)));
		String line = br.readLine();
		
		List<long[]> matrix = new ArrayList();
		List<String> markers = new ArrayList();
		while( line!= null){
			String[] x = line.split("\t");
			markers.add(x[0]);
			long[] values = new long[x.length-1];
			for ( int i=0; i<values.length; ) {
				values[i] = Long.parseLong(x[++i]);
			}
			matrix.add(values);
			line = br.readLine();
		} 
		
		//generating the files 
		for( int i=0; i<markers.size(); ++i){
			for( int j=0; j<markers.size(); ++j){
				StringBuffer imageName =new StringBuffer(markers.get(i) + "." + markers.get(j)) ;
				if (isColor) {
					imageName =  imageName.append(".all.color.png");
				} else {
					imageName = imageName.append( ".all.bw.png");
				}
				generateImageFile(imageName.toString(), matrix.get(i), matrix.get(j), width, height);
			}
		}
	}
	
	/**
	 * generate the two dimension image from two markers' events.
	 * @param imageName
	 * @param xDimension
	 * @param yDimension
	 * @throws FileNotFoundException
	 */
	public void generateImageFile(String imageName, long[]xEvents, long[]yEvents, int width, int height) throws FileNotFoundException{
		//TODO
		BufferedOutputStream writer = new BufferedOutputStream(new FileOutputStream(new File(inputDir, imageName)));
		
	}

}


