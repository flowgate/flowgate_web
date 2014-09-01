package org.immport.flock.commons;
import java.util.*;
public class FlockFactory {
	private static FlockAdapter instance;

	public static FlockAdapter getInstance() throws InstantiationException, IllegalAccessException, ClassNotFoundException{
		if (instance == null){
			ResourceBundle rb = ResourceBundle.getBundle("flock");
			String classname = rb.getString("adapter");
			instance = (FlockAdapter)Class.forName(classname).newInstance();
		}
		return instance;
	}
	
	public static void main(String[] args) throws Exception{
		System.out.print(FlockFactory.getInstance());
	}
}
