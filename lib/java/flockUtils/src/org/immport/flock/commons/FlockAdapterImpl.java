package org.immport.flock.commons;

import java.io.BufferedReader;
import java.io.ByteArrayOutputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.FileReader;
import java.io.IOException;
import java.io.InputStream;
import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;

import org.apache.commons.io.IOUtils;

public class FlockAdapterImpl implements FlockAdapter {

	private String INPUT_DATA = "coordinates.txt";
	private String PERCENTAGE_TXT = "percentage.txt";
	private String PROFILE_TXT = "profile.txt";
	private String POPULATION_ID_COL = "population_id.txt";
	private String POPULATION_CENTER = "population_center.txt";
	private String PARAMETERS = "parameters.txt";
	private File outputDir;
	private File inputDir;
	private PoorMansTable dbmock;

	public FlockAdapterImpl() {
	}

	public FlockAdapterImpl(String inputDir, String outputDir)
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

	public Profile getProfile() throws FlockAdapterException {
		BufferedReader br;
		String line;
		Profile profile = new Profile();
		boolean header = true;
		try {
			br = new BufferedReader(new FileReader(new File(inputDir,
					PROFILE_TXT)));
			ArrayList<Marker> markers = new ArrayList<Marker>();
			ArrayList<Population> populations = new ArrayList<Population>();
			while ((line = br.readLine()) != null) {
				if (header) {
					String[] markerNames = line.split("\t");
					for (int i = 1; i < markerNames.length; i++) {
						markerNames[i] = markerNames[i].replace(">", "");
						markerNames[i] = markerNames[i].replace("<", "");
						Marker marker = new Marker();
						marker.setIndex(i - 1);
						marker.setName(markerNames[i]);
						markers.add(marker);
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
					populations.add(population);
				}
			}
			profile.setMarkers(markers);
			profile.setPopulations(populations);
		} catch (IOException e) {
			System.out.println("EX: " + e);
			throw new FlockAdapterException(e);
		} finally {
			br = null;
		}

		/*
		 * Read in the percentage file, to extract the population percentage
		 * information
		 */
		try {
			br = new BufferedReader(new FileReader(new File(inputDir,
					PERCENTAGE_TXT)));
			// First line has header information and is not needed
			line = br.readLine();
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
			br = null;
		}

		/*
		 * Read in the population center file, to extract the centroid
		 * information
		 */
		try {
			br = new BufferedReader(new FileReader(new File(inputDir,
					POPULATION_CENTER)));
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
			br = null;
		}

		return profile;

	}

	public MinMax getMinMaxAll() throws FlockAdapterException {
		BufferedReader br;
		String line;
		MinMax mm = new MinMax();
		try {
			br = new BufferedReader(new FileReader(new File(inputDir,
					INPUT_DATA)));
			br.readLine(); // header

			while ((line = br.readLine()) != null) {
				String[] tokens = line.split("\t");
				for (int i = 0; i < tokens.length; i++) {

					Float fx = Float.valueOf(tokens[i].trim());
					Float fy = Float.valueOf(tokens[i].trim());
					int x = fx.intValue();
					int y = fy.intValue();

					y = -1 * y;

					mm.maxX = Math.max(mm.maxX, x);
					mm.minX = Math.min(mm.minX, x);
					mm.maxY = Math.max(mm.maxY, y);
					mm.minY = Math.min(mm.minY, y);
				}
			}

			br.close();
		} catch (IOException e) {
			throw new FlockAdapterException(e);
		} finally {
			br = null;
		}
		return mm;

	}
	
	public MinMax getMinMaxFile() throws FlockAdapterException {
		BufferedReader br;
		String line;
		MinMax mm = new MinMax();
		try {
			br = new BufferedReader(new FileReader(new File(inputDir,
					PARAMETERS)));

			while ((line = br.readLine()) != null) {
				String[] tokens = line.split("\t");
				if (tokens[0].equals("Min")) {
					mm.minX = Integer.parseInt(tokens[1]);
					mm.maxY = Integer.parseInt(tokens[1]);
				}
				if (tokens[0].equals("Max")) {
					mm.maxX = Integer.parseInt(tokens[1]);
					mm.minY = -(Integer.parseInt(tokens[1]));
				}
			}

			br.close();
		} catch (IOException e) {
			throw new FlockAdapterException(e);
		} finally {
			br = null;
		}
		return mm;

	}

	public byte[] getEvents() throws FlockAdapterException {
		byte[] events = null;
		BufferedReader br;
		try {
			br = new BufferedReader(new FileReader(new File(inputDir,
					POPULATION_ID_COL)));
			String line = null;
			events = new byte[getEventsCount()];
			int count = 0;
			while ((line = br.readLine()) != null) {
				byte b = Byte.parseByte(line);
				events[count] = b;
				count++;
			}
			br.close();

		} catch (FileNotFoundException fe) {
			throw new FlockAdapterException(fe);
		} catch (IOException e) {
			throw new FlockAdapterException(e);
		} finally {
			br = null;
		}
		return events;
	}

	private int getEventsCount() throws NumberFormatException, IOException {
		BufferedReader br;
		br = new BufferedReader(new FileReader(new File(inputDir,
				POPULATION_ID_COL)));
		int count = 0;
		while (br.readLine() != null) {
			count++;
		}
		br.close();
		return count;
	}

	public int[][] getCoordinates(int x, int y) throws FlockAdapterException {
		int[][] coordinates = new int[x][y];
		BufferedReader br;
		String line;
		int count;
		try {
			MinMax minMax = getMinMaxFile();
			float range = (float)(minMax.getMaxX() - minMax.getMinX());
			br = new BufferedReader(new FileReader(new File(inputDir,
					INPUT_DATA)));
			// Read in the header line and discard
			br.readLine();
			count = 0;
			while ((line = br.readLine()) != null) {
				String[] tokens = line.split("\t");
				for (int i = 0; i < tokens.length; i++) {
					float value = Float.parseFloat(tokens[i]);
					coordinates[count][i] = (int) ((value/range) * 300);
				}
				count++;
			}
		} catch (FileNotFoundException fe) {
			throw new FlockAdapterException(fe);
		} catch (IOException e) {
			throw new FlockAdapterException(e);
		} finally {
			br = null;
		}

		return coordinates;
	}

	public int[][] getCentroids(int x, int y) throws FlockAdapterException {
		int[][] centroids = new int[x][y];
		BufferedReader br;
		String line;
		int count;
		try {
			br = new BufferedReader(new FileReader(new File(inputDir,
					POPULATION_CENTER)));
			// Read in the header line and discard
			count = 0;
			while ((line = br.readLine()) != null) {
				String[] tokens = line.split("\t");
				for (int i = 0; i < tokens.length; i++) {
					Float f = Float.parseFloat(tokens[i]);
					int value = f.intValue();
					// int value = Integer.parseInt(tokens[i]);
					centroids[count][i] = value;
				}
				count++;
			}
		} catch (FileNotFoundException fe) {
			throw new FlockAdapterException(fe);
		} catch (IOException e) {
			throw new FlockAdapterException(e);
		} finally {
			br = null;
		}

		return centroids;
	}

	public void saveDotPlotImage(String name, InputStream is)
			throws FlockAdapterException {
		FileOutputStream fos;
		try {
			fos = new FileOutputStream(new File(outputDir, name));
			IOUtils.copy(is, fos);
			fos.close();
		} catch (FileNotFoundException fe) {
			throw new FlockAdapterException(fe);
		} catch (IOException e) {
			throw new FlockAdapterException(e);
		} finally {
			fos = null;
		}

	}

	public String[] getDimensions(long pid) throws FlockAdapterException {

		BufferedReader br;
		String line;
		try {
			br = new BufferedReader(new FileReader(new File(inputDir,
					PROFILE_TXT)));
			line = br.readLine();
			br.close();
			String[] ss = line.split("\t");
			String[] result = ArrayUtils.copyOfRange(ss, 1, ss.length);
			for (int i = 0; i < result.length; i++) {
				result[i] = result[i].replace("<", "").replace(">", "");
			}
			return result;
		} catch (IOException e) {
			throw new FlockAdapterException(e);
		} finally {
			br = null;
		}
	}

	public List<Integer> getPopulations(long pid) throws FlockAdapterException {
		// int[] ids = { 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11 };
		List<Integer> list = new ArrayList<Integer>();
		BufferedReader br;
		String line;
		try {
			br = new BufferedReader(new FileReader(new File(inputDir,
					PROFILE_TXT)));
			br.readLine();// skip first line
			while ((line = br.readLine()) != null) {
				String[] ss = line.split("\t");
				if (ss[0] != null) {
					list.add(Integer.valueOf(ss[0]));
				}
			}
			br.close();
		} catch (IOException e) {
			throw new FlockAdapterException(e);
		} finally {
			br = null;
		}
		return list;
	}

	public boolean exists(long pid, String name) throws FlockAdapterException {
		File f = new File(outputDir, name);
		return (f.exists());
	}

	public byte[] loadDotPlotImage(long pid, String name)
			throws FlockAdapterException {
		FileInputStream fis;
		ByteArrayOutputStream baos;
		try {
			fis = new FileInputStream(new File(outputDir, name));

			baos = new ByteArrayOutputStream();
			IOUtils.copy(fis, baos);
			baos.close();
			fis.close();
			byte[] bb = baos.toByteArray();
			return bb;
		} catch (FileNotFoundException fe) {
			throw new FlockAdapterException(fe);
		} catch (IOException e) {
			throw new FlockAdapterException(e);
		} finally {
			fis = null;
			baos = null;
		}

	}

	public Map<Integer, Float> getPercentage(long pid)
			throws FlockAdapterException {
		BufferedReader br;
		String line;
		Map<Integer, Float> result = new LinkedHashMap<Integer, Float>();
		try {
			br = new BufferedReader(new FileReader(new File(inputDir,
					PERCENTAGE_TXT)));
			br.readLine();// skip first line
			while ((line = br.readLine()) != null) {
				String[] ss = line.split("\t");
				Integer pop = Integer.valueOf(ss[0]);
				Float perc = Float.valueOf(ss[1]);
				result.put(pop, perc);
			}
			br.close();
			return result;
		} catch (IOException e) {
			throw new FlockAdapterException(e);
		} finally {
			br = null;
		}
	}

	public List<String> getProfileList(long pid) throws FlockAdapterException {
		BufferedReader br;
		String line;
		List<String> result = new ArrayList<String>();
		try {
			br = new BufferedReader(new FileReader(new File(inputDir,
					PROFILE_TXT)));
			while ((line = br.readLine()) != null) {
				result.add(line);
			}
			br.close();
			return result;
		} catch (IOException e) {
			throw new FlockAdapterException(e);
		} finally {
			br = null;
		}
	}

    public void savePopulationMarkerExpression(long pid, int population,
            String markerExpression) throws FlockAdapterException {
        try {
            dbmock.savePopulationMarkerExpression(pid, population,
                    markerExpression);
        } catch (IOException e) {
            throw new FlockAdapterException(e);
        }
    }

    public void savePopulationCellType(long pid, int population, String cellType)
            throws FlockAdapterException {
        try {
            dbmock.savePopulationCellType(pid, population, cellType);
        } catch (IOException e) {
            throw new FlockAdapterException(e);
        }
    }

	public String loadPopulationMarkerExpression(long pid, int population)
			throws FlockAdapterException {
		try {
			return dbmock.loadPopulationMarkerExpression(pid, population);
		} catch (FileNotFoundException e) {
			throw new FlockAdapterException(e);
		} catch (IOException e) {
			throw new FlockAdapterException(e);
		}
	}

	public String loadPopulationCellType(long pid, int population)
			throws FlockAdapterException {
		try {
			return dbmock.loadPopulationCellType(pid, population);
		} catch (FileNotFoundException e) {
			throw new FlockAdapterException(e);
		} catch (IOException e) {
			throw new FlockAdapterException(e);
		}
	}

	public void setDbmock(PoorMansTable dbmock) {
		this.dbmock = dbmock;
	}

	public void setInputDir(File inputDir) {
		this.inputDir = inputDir;
	}

	public void setOutputDir(File outputDir) {
		this.outputDir = outputDir;
	}

	public void setInputDir(String inputDir) {
		this.inputDir = new File(inputDir);
	}

	public void setOutputDir(String outputDir) {
		this.outputDir = new File(outputDir);
	}

	public void setDbmock(String outputDir) {
		this.dbmock = new PoorMansTable(new File(outputDir));
	}

}
