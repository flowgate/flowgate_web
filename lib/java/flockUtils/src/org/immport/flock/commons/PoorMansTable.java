package org.immport.flock.commons;

import java.io.*;
import java.util.Date;
import java.util.Properties;

public class PoorMansTable {
	private File outputDir;

	public PoorMansTable(File outputDir) {
		this.outputDir = outputDir;
		try {
			if (!new File(outputDir, "props").exists()){
				props.store(new FileOutputStream(new File(outputDir, "props")),
						"last update at " + new Date());
			}
		} catch (FileNotFoundException e) {
			e.printStackTrace();
		} catch (IOException e) {
			e.printStackTrace();
		}
	}

	private Properties props = new Properties();

	public void savePopulationMarkerExpression(long pid, int population,
			String markerExpression) throws IOException {
		FileInputStream fisIn = null;
		FileOutputStream fisOut = null;
		try {
			fisIn = new FileInputStream(new File(outputDir,"props"));
			//props.load(/*new FileReader*/new FileInputStream(new File(outputDir, "props")));
			props.load(fisIn);
			fisIn.close();
			props.setProperty("markerExpression" + population, markerExpression);
			fisOut = new FileOutputStream(new File(outputDir, "props"));
			//props.store(/*new FileWriter*/new FileOutputStream(new File(outputDir, "props")),
			//	"last update at " + new Date());
			props.store(fisOut,"last update at " + new Date());
			fisOut.close();
		} catch (FileNotFoundException e) {
			e.printStackTrace();
		} catch (IOException e) {
			e.printStackTrace();
		} finally {
			if (fisIn != null) {
				fisIn.close();
			}
			if (fisOut != null) {
				fisOut.close();
			}
		}
	}

	public void savePopulationCellType(long pid, int population, String cellType) throws IOException {
		FileInputStream fisIn = null;
		FileOutputStream fisOut = null;
		try {
			fisIn = new FileInputStream(new File(outputDir,"props"));
			props.load(fisIn);
			//props.load(/*new FileReader*/new FileInputStream(new File(outputDir, "props")));
			fisIn.close();
			props.setProperty("cellType" + population, cellType);
			fisOut = new FileOutputStream(new File(outputDir, "props"));
			//props.store(/*new FileWriter*/new FileOutputStream(new File(outputDir, "props")),
			//		"last update at " + new Date());
			props.store(fisOut,"last update at " + new Date());
			fisOut.close();
		} catch (FileNotFoundException e) {
			e.printStackTrace();
		} catch (IOException e) {
			e.printStackTrace();
		} finally {
			if (fisIn != null) {
				fisIn.close();
			}
			if (fisOut != null) {
				fisOut.close();
			}
		}
	}

	public String loadPopulationMarkerExpression(long pid, int population) throws FileNotFoundException, IOException {
		FileInputStream fisIn = null;
		try {
			fisIn = new FileInputStream(new File(outputDir,"props"));
			props.load(fisIn);
			//props.load(/*new FileReader*/new FileInputStream(new File(outputDir, "props")));	
		} catch (FileNotFoundException e) {
			e.printStackTrace();
		} catch (IOException e) {
			e.printStackTrace();
		} finally {
			if (fisIn != null) {
				fisIn.close();
			}
		}
		return props.getProperty("markerExpression" + population);
	}

	public String loadPopulationCellType(long pid, int population) throws FileNotFoundException, IOException {
		FileInputStream fisIn = null;
		try {
			fisIn = new FileInputStream(new File(outputDir,"props"));
			props.load(fisIn);
			//props.load(/*new FileReader*/new FileInputStream(new File(outputDir, "props")));
		} catch (FileNotFoundException e) {
			e.printStackTrace();
		} catch (IOException e) {
			e.printStackTrace();
		} finally {
			if (fisIn != null) {
				fisIn.close();
			}
		}					
		return props.getProperty("cellType" + population);

	}
}
