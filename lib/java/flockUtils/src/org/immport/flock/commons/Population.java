package org.immport.flock.commons;

import java.util.ArrayList;

public class Population {
	private Integer population;
	private Float percentage;
	public Float getPercentage() {
		return percentage;
	}

	public void setPercentage(Float percentage) {
		this.percentage = percentage;
	}

	private ArrayList <Integer> scores = null;
	private ArrayList <Float> centroids = null;
	private ArrayList <Float> mfis = null;
	
	public ArrayList<Float> getMfis() {
		return mfis;
	}

	public void setMfis(ArrayList<Float> mfis) {
		this.mfis = mfis;
	}

	public ArrayList<Float> getCentroids() {
		return centroids;
	}

	public void setCentroids(ArrayList<Float> centroids) {
		this.centroids = centroids;
	}

	public Population() {}

	public Integer getPopulation() {
		return population;
	}

	public void setPopulation(Integer population) {
		this.population = population;
	}

	public ArrayList<Integer> getScores() {
		return scores;
	}

	public void setScores(ArrayList<Integer> scores) {
		this.scores = scores;
	};
	
}
