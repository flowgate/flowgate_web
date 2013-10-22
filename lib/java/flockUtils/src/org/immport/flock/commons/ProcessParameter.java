package org.immport.flock.commons;

/**
 * User: hkim
 * Date: 8/14/13
 * Time: 12:27 PM
 * org.immport.flock.commons
 */
public class ProcessParameter {
    //marker selection (a - all marker pairs, m - multiple pairs of markers, s - single pair of markers)
    private String marker;
    //population selection (a - all populations[overview], m - multiple populations[subset], s - single population)
    private String population;
    //flag for highlighted images (T, F)
    private boolean highlighted;
    //flag for black & white images (T, F)
    private boolean bw;
    private int width;
    private int height;
    //any additional parameters in string
    private String params;

    public ProcessParameter() {}

    public ProcessParameter(String marker, String population, boolean highlighted, boolean bw, String params) {
        this(marker, population, highlighted, bw, 300, 300, params);
    }

    public ProcessParameter(String marker, String population, boolean highlighted, boolean bw, int width, int height, String params) {
        this.marker = marker;
        this.population = population;
        this.highlighted = highlighted;
        this.bw = bw;
        this.width = width;
        this.height = height;
        this.params = params;
    }

    public String getImageName() {
        String imageName = ".";
        if(population.equals("o")) {
            imageName += "all.";
        } else {
            if(population.equals("m")) {
                String[] pops = params.split(",");
                for(String pop : pops) {
                    imageName += pop +".";
                }
            } else {
                imageName += params + ".";
            }
        }

        if(bw) {
            imageName += "bw.";
        } else {
            imageName += "color.";
        }

        if(highlighted) {
            imageName += "highlighted.";
        } else {
            imageName += "only.";
        }

        return imageName + "png";
    }

    public String getMarker() {
        return marker;
    }

    public void setMarker(String marker) {
        this.marker = marker;
    }

    public String getPopulation() {
        return population;
    }

    public void setPopulation(String population) {
        this.population = population;
    }

    public boolean isHighlighted() {
        return highlighted;
    }

    public void setHighlighted(boolean highlighted) {
        this.highlighted = highlighted;
    }

    public boolean isBw() {
        return bw;
    }

    public void setBw(boolean bw) {
        this.bw = bw;
    }

    public int getWidth() {
        return width;
    }

    public void setWidth(int width) {
        this.width = width;
    }

    public int getHeight() {
        return height;
    }

    public void setHeight(int height) {
        this.height = height;
    }

    public String getParams() {
        return params;
    }

    public void setParams(String params) {
        this.params = params;
    }
}
