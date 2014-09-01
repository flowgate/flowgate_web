package org.immport.flock.commons;

import java.awt.Color;

public class ColorUtils {

	private static Color[] colors = new Color[41];
	private static String[] colorNames = new String[41];

	//http://www.w3.org/TR/css3-color/#svg-color
	static {
		{
			// also avoid lightGray, white and black
			colors[0] = Color.black;
			colors[1] = Color.red;
			colors[2] = Color.yellow;
			colors[3] = new Color(0, 128, 0);
			colors[4] = Color.blue;
			colors[5] = Color.orange;
			colors[6] = new Color(138, 43, 226);
			colors[7] = new Color(128, 128, 0);
			colors[8] = Color.cyan;
			colors[9] = Color.magenta;
			colors[10] = Color.green;
			colors[11] = new Color(0, 0, 128);
			colors[12] = new Color(240, 128, 128);
			colors[13] = new Color(128, 0, 128);
			colors[14] = new Color(240, 230, 140);
			colors[15] = new Color(143, 188, 143);
			colors[16] = Color.darkGray;
			colors[17] = new Color(0, 128, 128);
			colors[18] = new Color(153, 50, 204);
			colors[19] = new Color(255, 127, 80);
			colors[20] = new Color(255, 215, 0);
			colors[21] = new Color(0, 139, 139);
			colors[22] = new Color(128, 0, 0);
			colors[23] = new Color(95, 158, 160);
			colors[24] = Color.pink;		
			colors[25] = Color.gray;
			colors[26] = new Color(127, 255, 212);
			colors[27] = new Color(173, 216, 230);
			colors[28] = new Color(219, 112, 147);
			colors[29] = new Color(205, 133, 63);
			colors[30] = new Color(65, 105, 225);
			colors[31] = new Color(112, 128, 144);
			colors[32] = new Color(70, 130, 180);
			colors[33] = new Color(216, 191, 216);
			colors[34] = new Color(245, 222, 179);
			colors[35] = new Color(154, 205, 50);
			colors[36] = new Color(189, 183, 107);
			colors[37] = new Color(139, 0, 139);
			colors[38] = new Color(85, 107, 47);
			colors[39] = new Color(0, 206, 209);
			colors[40] = new Color(255, 20, 147);

			// also avoid lightGray, white and black
			colorNames[0] = "Black";
			colorNames[1] = "Red";
			colorNames[2] = "Yellow";
			colorNames[3] = "Green";
			colorNames[4] = "Blue";
			colorNames[5] = "Orange";
			colorNames[6] = "Blue Violet";
			colorNames[7] = "Olive";
			colorNames[8] = "Aqua";
			colorNames[9] = "Magenta";
			colorNames[10] = "Lime";
			colorNames[11] = "Navy";
			colorNames[12] = "Light Coral";
			colorNames[13] = "Purple";
			colorNames[14] = "Khaki";
			colorNames[15] = "Dark Seagreen";
			colorNames[16] = "Dark Grey";
			colorNames[17] = "Teal";
			colorNames[18] = "Dark Orchid";
			colorNames[19] = "Coral";
			colorNames[20] = "Gold";
			colorNames[21] = "Dark Cyan";
			colorNames[22] = "Maroon";
			colorNames[23] = "Cadet Blue";
			colorNames[24] = "Pink";
			colorNames[25] = "Grey";
			colorNames[26] = "Aquamarine";
			colorNames[27] = "Light Blue";
			colorNames[28] = "Pale Violet Red";
			colorNames[29] = "Peru";
			colorNames[30] = "Royal Blue";
			colorNames[31] = "Slate Gray";
			colorNames[32] = "Steel Blue";
			colorNames[33] = "Thistle";
			colorNames[34] = "Wheat";
			colorNames[35] = "Yellow Green";
			colorNames[36] = "Dark Khaki";
			colorNames[37] = "Dark Magenta";
			colorNames[38] = "Dark Olivegreen";
			colorNames[39] = "Dark Turquoise";
			colorNames[40] = "Deep Pink";
		}
	}

	public static String getColorCode(int color) {
		return Integer.toHexString(getColor(color).getRGB()).substring(2); // strip
		// alpha
	}

	public static String getColorName(int color) {
		return colorNames[color % 40];
	}

	public static Color getColor(int color) {
		return colors[color % 40];
	}
}
