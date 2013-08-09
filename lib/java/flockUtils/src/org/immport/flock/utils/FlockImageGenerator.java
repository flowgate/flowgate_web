package org.immport.flock.utils;

import org.apache.commons.io.IOUtils;
import org.immport.flock.commons.*;

import javax.imageio.ImageIO;
import java.awt.*;
import java.awt.image.BufferedImage;
import java.io.*;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

public class FlockImageGenerator {

    private long taskId;
    private File inputDir;
    private File outputDir;
    private FlockAdapter adapter;

    Profile profile = null;
    List<Marker> markers = null;
    List<Population> populations = null;
    MinMax minMax = null;
    byte[] events;
    int[][] coordinates;
    int[][] centroids;

    public FlockImageGenerator() { }

    public FlockImageGenerator(File outputDir) throws Exception {
        this(1, null, outputDir);
    }

    public FlockImageGenerator(long taskId, File inputDir, File outputDir) throws Exception {
        this.taskId = taskId;
        this.inputDir = inputDir;
        this.outputDir = outputDir;
        this.adapter = this.setFlockAdapter(inputDir, outputDir);
    }

    private FlockAdapter setFlockAdapter(File inputDir, File outputDir) throws Exception {
        FlockAdapter adapter = FlockFactory.getInstance();
        adapter.setInputDir(inputDir);
        adapter.setOutputDir(outputDir);
        PoorMansTable dbmock = new PoorMansTable(outputDir);
        adapter.setDbmock(dbmock);
        return adapter;
    }

    /*
      * Process the profile.txt, percentage.txt and population_center.txt files
      * The information in these files is transferred to the Profile data structure.
      *
      * Also calculates the min and max values in the coordinates.txt file. And
      * loads this file into the coordinates vector
      */
    public void processFlockOutput() throws FlockAdapterException {
        long startTime = System.currentTimeMillis();
        try {
            profile = adapter.getProfile();
            markers = profile.getMarkers();
            populations = profile.getPopulations();
            //minMax = adapter.getMinMaxAll();
            minMax = adapter.getMinMaxFile();
            events = adapter.getEvents();
            coordinates = adapter.getCoordinates(events.length, markers.size());

        } catch (Exception e) {
            System.out.println("E: " + e);
            throw new FlockAdapterException(e);
        }
        long stopTime = System.currentTimeMillis();
        System.out.println("ProcessFlockOutput: " + (stopTime - startTime) / 1000);
    }
    public void processFlockOutputFiles(
            String profilePath, String percentPath, String populcPath, String MFIPath,
            String populidPath, String paramPath, String coordPath) throws FlockAdapterException {
        try {
            profile = ((FlockAdapterFile)adapter).getProfile(profilePath,percentPath,populcPath,MFIPath);
            markers = profile.getMarkers();
            populations = profile.getPopulations();
            minMax = ((FlockAdapterFile)adapter).getMinMaxFile(paramPath);
            events = ((FlockAdapterFile)adapter).getEvents(populidPath);
            coordinates = ((FlockAdapterFile)adapter).getCoordinates(events.length, markers.size(), paramPath, coordPath);

        } catch (Exception e) {
            System.out.println("E: " + e);
            throw new FlockAdapterException(e);
        }
    }

    public void genPropsFile() throws FlockAdapterException {
        profile = adapter.getProfile();
        markers = profile.getMarkers();
        populations = profile.getPopulations();

        for (int i = 0; i < populations.size(); i++) {
            Population pop = populations.get(i);
            Byte popId = pop.getPopulation();
            ArrayList <Integer> scores = pop.getScores();

            StringBuffer sb = new StringBuffer();

            for (int j = 0; j < markers.size(); j++) {
                String markerName = markers.get(j).getName();
                int index = markers.get(j).getIndex();
                sb.append(markerName).append(trans.get(scores.get(index)));
            }
            adapter.savePopulationMarkerExpression(0, (int) popId, sb.toString());
        }
    }

    /*
      * Generates individual images for each Marker vs Marker combination.
      * The files can represent the all.color or all.bw images
      */
    public void genOverviewImages(int width, int height, boolean bw) throws FlockAdapterException {
        int numMarkers = markers.size();

        long startTime = System.currentTimeMillis();
        try {

            for (int i = 0; i < numMarkers; i++) {
                for (int j = 0; j < numMarkers; j++) {
                    int idx1 = markers.get(i).getIndex();
                    String name1 = markers.get(i).getName();
                    int idx2 = markers.get(j).getIndex();
                    String name2 = markers.get(j).getName();
                    String imageName = name1 + "." + name2 + ".all."+(bw?"bw":"color")+".png";

                    BufferedImage img = new BufferedImage(width, height,BufferedImage.TYPE_INT_RGB);
                    Graphics2D g2d = img.createGraphics();
                    g2d.setColor(Color.LIGHT_GRAY);
                    g2d.fillRect(0, 0, width, height);

                    FlockEvents flockEvents = genEventsMatrix(width, height,idx1, idx2);
                    Color[][] allEvents = flockEvents.getAllEvents();

                    for (int k = 0; k < height; k++) {
                        for (int l = 0; l < height; l++) {
                            if (allEvents[k][l] != null) {
                                if (bw) {
                                    g2d.setColor(Color.BLACK);
                                } else {
                                    g2d.setColor(allEvents[k][l]);
                                }
                                g2d.drawLine(k, l, k, l);
                            }
                        }
                    }

                    if (name1.equals(name2)) {
                        byte [] b4 = generateSolidImage(300,300);
                        adapter.saveDotPlotImage(imageName, new ByteArrayInputStream(b4));
                    } else {

                        //File outputfile = new File(outputDir,imageName);
                        //ImageIO.write(img, "png", outputfile);

                        byte[] allPopColor = getImageAsByteArray(img);
                        adapter.saveDotPlotImage(imageName, new ByteArrayInputStream(allPopColor));

                    }
                }
            }

        } catch (Exception e) {
            System.out.println("E: " + e);
            throw new FlockAdapterException(e);
        }
        long stopTime = System.currentTimeMillis();
        System.out.println("genOverviewImages " + (stopTime - startTime) / 1000);

    }

    /*
      * Generates individual images for each Marker vs Marker combination.
      * The files can represent the all.color or all.bw images
      */
    public void genMarkerByMarkerImages(int width, int height, boolean bw)
            throws FlockAdapterException {

        int numMarkers = markers.size();

        long startTime = System.currentTimeMillis();
        try {

            for (int i = 0; i < numMarkers; i++) {
                for (int j = 0; j < numMarkers; j++) {
                    long startMarkerTime = System.currentTimeMillis();
                    int idx1 = markers.get(i).getIndex();
                    String name1 = markers.get(i).getName();
                    int idx2 = markers.get(j).getIndex();
                    String name2 = markers.get(j).getName();
                    String imageName = null;
                    if (bw) {
                        imageName = name1 + "." + name2 + ".all.bw.png";
                    } else {
                        imageName = name1 + "." + name2 + ".all.color.png";
                    }
                    BufferedImage img = new BufferedImage(width, height, BufferedImage.TYPE_INT_RGB);
                    Graphics2D g2d = img.createGraphics();
                    g2d.setColor(Color.LIGHT_GRAY);
                    g2d.fillRect(0, 0, width, height);


                    FlockEvents flockEvents = genEventsMatrix(width, height, idx1, idx2);
                    Color[][] allEvents = flockEvents.getAllEvents();
                    //Map<Byte, boolean[][]> popEvents = flockEvents
                    //		.getPopEvents();

                    for (int k = 0; k < height; k++) {
                        for (int l = 0; l < height; l++) {
                            if (allEvents[k][l] != null) {
                                if (bw) {
                                    g2d.setColor(Color.BLACK);
                                } else {
                                    g2d.setColor(allEvents[k][l]);
                                }
                                g2d.drawLine(k, l, k, l);
                            }
                        }
                    }

                    if (name1.equals(name2)) {
                        byte [] b4 = generateSolidImage(300,300);
                        adapter.saveDotPlotImage(imageName, new ByteArrayInputStream(b4));
                    } else {
                        byte[] allPopColor = getImageAsByteArray(img);
                        adapter.saveDotPlotImage(imageName, new ByteArrayInputStream(allPopColor));

                    }
                    long stopMarkerTime = System.currentTimeMillis();
                    System.out.println("Marker " + i + " " + j + " " + (stopMarkerTime - startMarkerTime) / 1000);
                }
            }
        } catch (Exception e) {
            System.out.println("E: " + e);
            throw new FlockAdapterException(e);
        }
        long stopTime = System.currentTimeMillis();
        System.out.println("genMarkerByMarkerImages TEST " + (stopTime - startTime) / 1000);
    }

    /*
      * For a single population, generate images representing all the
      * marker combination.
      */
    public void genSinglePopulation(Byte popId, int width, int height,
                                    boolean bw, boolean highlighted) throws FlockAdapterException {

        int numMarkers = markers.size();

        long startTime = System.currentTimeMillis();
        try {

            for (int i = 0; i < numMarkers; i++) {
                for (int j = 0; j < numMarkers; j++) {

                    int idx1 = markers.get(i).getIndex();
                    String name1 = markers.get(i).getName();
                    int idx2 = markers.get(j).getIndex();
                    String name2 = markers.get(j).getName();

                    //FlockEvents flockEvents = genEventsMatrix(width, height, idx1, idx2);
                    FlockEvents flockEvents = genEventsMatrixPop(width, height, idx1, idx2);
                    Color[][] allEvents = flockEvents.getAllEvents();
                    Map<Byte, boolean[][]> popEvents = flockEvents.getPopEvents();

                    String imageName = "";
                    if (highlighted) {
                        imageName = name1 + "." + name2 + "." + popId + ".color.highlighted.png";

                        if (name1.equals(name2)) {
                            byte [] b4 = generateSolidImage(300,300);
                            adapter.saveDotPlotImage(imageName, new ByteArrayInputStream(b4));
                        } else {
                            byte[] b4 = generatePopHighlighted(allEvents, popEvents.get(popId), ColorUtils.getColor(popId), bw);

                            adapter.saveDotPlotImage(imageName,new ByteArrayInputStream(b4));
                        }

                    } else {
                        imageName = name1 + "." + name2 + "." + popId + ".color.only.png";

                        if (name1.equals(name2)){
                            byte [] b4 = generateSolidImage(300,300);
                            adapter.saveDotPlotImage(imageName, new ByteArrayInputStream(b4));
                        } else {
                            byte[] b4 = generatePopColorOnly(allEvents, popEvents.get(popId), ColorUtils.getColor(popId), bw);

                            adapter.saveDotPlotImage(imageName, new ByteArrayInputStream(b4));
                        }
                    }

                }
            }

        } catch (Exception e) {
            System.out.println("E: " + e);
            throw new FlockAdapterException(e);
        }
        long stopTime = System.currentTimeMillis();
        System.out.println("genSinglePopulation " + (stopTime - startTime) / 1000);

    }

    /*
      * For every population and every marker combination, create an image
      */
    public void genMarkerByMarkerPopulations(int width, int height, boolean bw, boolean highlighted) throws FlockAdapterException {
        int numMarkers = markers.size();
        long startTime = System.currentTimeMillis();
        try {

            for (int i = 0; i < numMarkers; i++) {
                for (int j = 0; j < numMarkers; j++) {

                    int idx1 = markers.get(i).getIndex();
                    String name1 = markers.get(i).getName();
                    int idx2 = markers.get(j).getIndex();
                    String name2 = markers.get(j).getName();

                    FlockEvents flockEvents = genEventsMatrix(width, height, idx1, idx2);
                    Color[][] allEvents = flockEvents.getAllEvents();
                    Map<Byte, boolean[][]> popEvents = flockEvents.getPopEvents();
                    for (Population population : populations) {
                        Byte popId = population.getPopulation();
                        String imageName = "";
                        if (highlighted) {
                            imageName = name1 + "." + name2 + "." + popId + ".color.highlighted.png";

                            if (name1.equals(name2)){
                                byte [] b4 = generateSolidImage(300,300);
                                adapter.saveDotPlotImage(imageName, new ByteArrayInputStream(b4));
                            } else {
                                byte[] b4 = generatePopHighlighted(allEvents, popEvents.get(popId), ColorUtils.getColor(popId), bw);
                                adapter.saveDotPlotImage(imageName,new ByteArrayInputStream(b4));
                            }

                        } else {
                            imageName = name1 + "." + name2 + "." + popId + ".color.only.png";

                            if (name1.equals(name2)){
                                byte [] b4 = generateSolidImage(300,300);
                                adapter.saveDotPlotImage(imageName, new ByteArrayInputStream(b4));
                            } else {
                                byte[] b4 = generatePopColorOnly(allEvents, popEvents.get(popId), ColorUtils.getColor(popId), bw);

                                adapter.saveDotPlotImage(imageName, new ByteArrayInputStream(b4));
                            }
                        }
                    }

                }
            }

        } catch (Exception e) {
            System.out.println("E: " + e);
            throw new FlockAdapterException(e);
        }
        long stopTime = System.currentTimeMillis();
        System.out.println("genMarkerByMarkerPopulations " + (stopTime - startTime) / 1000);

    }

    /*
      * For 2 markers, create all the images for every population
      */
    public void genMarker2MarkerPopulations(int index1, int index2, int width,
                                            int height, boolean bw, boolean highlighted) throws FlockAdapterException {

        long startTime = System.currentTimeMillis();
        try {

            int idx1 = markers.get(index1).getIndex();
            String name1 = markers.get(index1).getName();
            int idx2 = markers.get(index2).getIndex();
            String name2 = markers.get(index2).getName();

            FlockEvents flockEvents = genEventsMatrixPop(width, height, idx1, idx2);
            Color[][] allEvents = flockEvents.getAllEvents();
            Map<Byte, boolean[][]> popEvents = flockEvents.getPopEvents();
            for (Population population : populations) {
                Byte popId = population.getPopulation();
                String imageName = "";
                if (highlighted) {
                    imageName = name1 + "." + name2 + "." + popId + ".color.highlighted.png";

                    if (name1.equals(name2)){
                        byte [] b4 = generateSolidImage(300,300);
                        adapter.saveDotPlotImage(imageName, new ByteArrayInputStream(b4));
                    } else {
                        byte[] b4 = generatePopHighlighted(allEvents, popEvents.get(popId), ColorUtils.getColor(popId), bw);

                        adapter.saveDotPlotImage(imageName, new ByteArrayInputStream(b4));
                    }

                } else {
                    imageName = name1 + "." + name2 + "." + popId + ".color.only.png";

                    if (name1.equals(name2)){
                        byte [] b4 = generateSolidImage(300,300);
                        adapter.saveDotPlotImage(imageName, new ByteArrayInputStream(b4));
                    } else {
                        byte[] b4 = generatePopColorOnly(allEvents, popEvents.get(popId), ColorUtils.getColor(popId), bw);

                        adapter.saveDotPlotImage(imageName, new ByteArrayInputStream(b4));
                    }
                }

            }

        } catch (Exception e) {
            System.out.println("E: " + e);
            throw new FlockAdapterException(e);
        }
        long stopTime = System.currentTimeMillis();
        System.out.println("genMarker2MarkerPopulations " + (stopTime - startTime) / 1000);

    }

    private FlockEvents genEventsMatrix(int width, int height, int idx1, int idx2) {

        FlockEvents flockEvents = new FlockEvents();
        Color[][] allEvents = new Color[width][height];
        //Map<Byte, boolean[][]> popEvents = new HashMap<Byte, boolean[][]>();
        //for (Population population : populations) {
        //	popEvents.put(population.getPopulation(),
        //			new boolean[width][height]);
        //}

        /*
          float minX = minMax.getMinX();
          float maxX = minMax.getMaxX();
          float range = maxX - minX;

          int max = (int) ((maxX/range) * 300);
          */
        int max = 299;


        for (int k = 0; k < events.length; k++) {
            int x = coordinates[k][idx1];
            int y = max - coordinates[k][idx2];

            Color c = ColorUtils.getColor(events[k]);

            if (x == width) {
                x = width - 1;
            }
            if (y == height) {
                y = height - 1;
            }

            allEvents[x][y] = c;
            //popEvents.get(events[k])[x][y] = true;
        }
        flockEvents.setAllEvents(allEvents);
        //flockEvents.setPopEvents(popEvents);

        return flockEvents;
    }

    private FlockEvents genEventsMatrixPop(int width, int height, int idx1, int idx2) {
        FlockEvents flockEvents = new FlockEvents();
        Color[][] allEvents = new Color[width][height];
        Map<Byte, boolean[][]> popEvents = new HashMap<Byte, boolean[][]>();
        for (Population population : populations) {
            popEvents.put(population.getPopulation(), new boolean[width][height]);
        }

        /*
          float minX = minMax.getMinX();
          float maxX = minMax.getMaxX();
          float range = maxX - minX;

          int max = (int) ((maxX/range) * 300);
          */

        int max = 299;

        for (int k = 0; k < events.length; k++) {
            int x = coordinates[k][idx1];
            int y = max - coordinates[k][idx2];

            Color c = ColorUtils.getColor(events[k]);

            if (x == width) {
                x = width - 1;
            }
            if (y == height) {
                y = height - 1;
            }

            allEvents[x][y] = c;
            popEvents.get(events[k])[x][y] = true;
        }
        flockEvents.setAllEvents(allEvents);
        flockEvents.setPopEvents(popEvents);

        return flockEvents;
    }

    private byte[] getImageAsByteArray(BufferedImage img) throws IOException {
        // save as a GIF file
        ByteArrayOutputStream image = new ByteArrayOutputStream();
        ImageIO.write(img, "png", image);
        image.close();
        return image.toByteArray();
    }

    private byte[] generateSolidImage(int width, int height) throws Exception {
        BufferedImage img = new BufferedImage(width, height, BufferedImage.TYPE_INT_RGB);

        Graphics2D g2d = img.createGraphics();
        g2d.setColor(Color.GRAY);
        g2d.fillRect(0, 0, width, height);

        return getImageAsByteArray(img);
    }

    private byte[] generatePopHighlighted(Color[][] allEvents, boolean[][] popEvents, Color c, boolean bw) throws Exception {
        int width = popEvents.length;
        int height = popEvents[0].length;

        BufferedImage img = new BufferedImage(width, height, BufferedImage.TYPE_INT_RGB);

        Graphics2D g2d = img.createGraphics();
        g2d.setColor(Color.LIGHT_GRAY);
        g2d.fillRect(0, 0, width, height);
        g2d.setColor(Color.WHITE);
        // draw background
        for (int i = 0; i < width; i++) {
            for (int j = 0; j < height; j++) {
                if (allEvents[i][j] != null) {
                    g2d.drawLine(i, j, i, j);
                }
            }
        }

        for (int i = 0; i < width; i++) {
            for (int j = 0; j < height; j++) {
                if (popEvents[i][j]) {
                    if (bw) {
                        g2d.setColor(Color.BLACK);
                    } else {
                        g2d.setColor(c);
                    }
                    g2d.drawLine(i, j, i, j);
                }
            }
        }

        return getImageAsByteArray(img);
    }

    private byte[] generatePopColorOnly(Color[][] allEvents, boolean[][] popEvents, Color c, boolean bw) throws Exception {

        int width = popEvents.length;
        int height = popEvents[0].length;

        BufferedImage img = new BufferedImage(width, height, BufferedImage.TYPE_INT_RGB);

        Graphics2D g2d = img.createGraphics();
        g2d.setColor(Color.LIGHT_GRAY);
        g2d.fillRect(0, 0, width, height);

        for (int i = 0; i < width; i++) {
            for (int j = 0; j < height; j++) {
                if (popEvents[i][j]) {
                    if (bw) {
                        g2d.setColor(Color.BLACK);
                    } else {
                        g2d.setColor(c);
                    }
                    g2d.drawLine(i, j, i, j);
                }
            }
        }

        return getImageAsByteArray(img);
    }
    /*
      * THIS IS PROTOTYPE CODE AND NOT CURRENTLY USED
      * Tries to generate one image that contains all marker combinations
      * May be more effiecent than generating many images, but needs to use
      * SPRITE technology on UI size to extract individual images.
      */
    public void genMarkerByMarkerImage(String fileName, int width, int height, boolean bw) throws FlockAdapterException {

        int numMarkers = markers.size();
        int rectSize = numMarkers * height;
        BufferedImage img = new BufferedImage(rectSize, rectSize, BufferedImage.TYPE_INT_RGB);
        Graphics2D g2d = img.createGraphics();
        g2d.setColor(Color.LIGHT_GRAY);
        g2d.fillRect(0, 0, rectSize, rectSize);

        long startTime = System.currentTimeMillis();
        try {
            FileOutputStream fos = new FileOutputStream(new File(outputDir, fileName));

            for (int i = 0; i < numMarkers; i++) {
                for (int j = 0; j < numMarkers; j++) {

                    int idx1 = markers.get(i).getIndex();
                    int idx2 = markers.get(j).getIndex();

                    FlockEvents flockEvents = genEventsMatrix(width, height, idx1, idx2);
                    Color[][] allEvents = flockEvents.getAllEvents();
                    Map<Byte, boolean[][]> popEvents = flockEvents.getPopEvents();

                    for (int k = 0; k < height; k++) {
                        for (int l = 0; l < height; l++) {
                            if (allEvents[k][l] != null) {
                                if (bw) {
                                    g2d.setColor(Color.BLACK);
                                } else {
                                    g2d.setColor(allEvents[k][l]);
                                }
                                g2d.drawLine(i * height + k, j * height + l, i * height + k, j * height + l);
                            }
                        }
                    }
                }
            }

            byte[] allPopColor = getImageAsByteArray(img);
            IOUtils.copy(new ByteArrayInputStream(allPopColor), fos);
            fos.close();

        } catch (Exception e) {
            System.out.println("E: " + e);
            throw new FlockAdapterException(e);
        }
        long stopTime = System.currentTimeMillis();
        System.out.println("genMarkerByMarkerImage " + (stopTime - startTime) / 1000);

    }

    private static final Map<Integer,String> trans = new HashMap();
    static{
        {
            trans.put(1, "-");
            trans.put(2, "lo");
            trans.put(3, "+");
            trans.put(4, "hi");
        }
    }


    public static void main(String[] args) throws Exception {
        String errorMsg = "Usage: command \n"
                +"<Type: all_images, overview(_color,_bw), all_markers, all_populations, marker_populations, single_population, gen_propsfile> \n"
                +"<INPUT_DIR or File...> <OUTPUT_DIR> [Index 1] [Index 2]";

        if (args.length < 3) {
            throw new Exception(errorMsg);
        }

        int argIndex = 0;
        String type = args[argIndex++];
        File inputDir = new File(args[argIndex++]);
        boolean isDirectory = inputDir.isDirectory();

        String profilePath=null;
        String percentPath=null;
        String populcPath=null;
        String MFIPath=null;
        String populidPath=null;
        String paramPath=null;
        String coordPath=null;
        if(!isDirectory) {
            --argIndex;
            profilePath = args[argIndex++];
            percentPath = args[argIndex++];
            populcPath = args[argIndex++];
            MFIPath = args[argIndex++];
            populidPath = args[argIndex++];
            paramPath = args[argIndex++];
            coordPath = args[argIndex++];
        }

        File outputDir = new File(args[argIndex++]);
        FlockImageGenerator fig = null;

        if(isDirectory) {
            fig = new FlockImageGenerator(0l, inputDir, outputDir);
            fig.processFlockOutput();
        } else {
            fig = new FlockImageGenerator(outputDir);
            fig.processFlockOutputFiles(profilePath,percentPath,populcPath,MFIPath,populidPath,paramPath,coordPath);
        }

        if (type.equals("all_images")) {
            //fig.genMarkerByMarkerImages(300, 300, false);
            //fig.genMarkerByMarkerImages(300, 300, true);
            //fig.genMarkerByMarkerPopulations(300, 300, false,true);
            //fig.genMarkerByMarkerPopulations(300, 300, false,false);
        } else if (type.startsWith("overview")) {
            if(!type.equals("overview_bw")) {
                fig.genOverviewImages(300,300,false);
            }
            if(!type.equals("overview_color")) {
                fig.genOverviewImages(300,300,true);
            }
        } else if (type.equals("all_markers")) {
            fig.genMarkerByMarkerImages(300, 300, false);
        } else if (type.equals("all_populations")) {
            fig.genMarkerByMarkerPopulations(300, 300, false,true);
        } else if (type.equals("marker_populations")) {
            if (args.length < 5) {
                System.err.println("For type 'marker_populations, you must provide index1 and index2");
                throw new Exception(errorMsg);
            }
            int index1 = Integer.parseInt(args[argIndex++]);
            int index2 = Integer.parseInt(args[argIndex]);

            fig.genMarker2MarkerPopulations(index1, index2, 300, 300, false,true);
        } else if (type.equals("single_population")) {
            if (args.length < 4) {
                System.err.println("For type 'single_population, you must provide popIdx");
                throw new Exception(errorMsg);
            }
            Byte index1 = Byte.parseByte(args[argIndex]);

            fig.genSinglePopulation(index1, 300, 300, false,true);
        } else if (type.equals("gen_propsfile")) {
            fig.genPropsFile();
        } else {
            System.err.println("Not a valid type");
            throw new Exception(errorMsg);
        }

    }

}
