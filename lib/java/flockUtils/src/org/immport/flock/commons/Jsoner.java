package org.immport.flock.commons;


import java.io.File;
import java.io.IOException;
import java.util.Map;

/**
 * User: hkim
 * Date: 8/23/13
 * Time: 9:13 AM
 * org.immport.flock.commons
 */
public class Jsoner {

    public void write(String filePath, Map data) throws Exception {
        try {

            JsonFactory jfactory = new JsonFactory();

            JsonGenerator jGenerator = jfactory.createJsonGenerator(new File("c:\\user.json"), JsonEncoding.UTF8);
            jGenerator.writeStartObject();

            jGenerator.writeStringField("name", "mkyong");
            jGenerator.writeNumberField("age", 29);

            jGenerator.writeFieldName("messages");
            jGenerator.writeStartArray();

            jGenerator.writeString("msg 1");
            jGenerator.writeString("msg 2");
            jGenerator.writeString("msg 3");

            jGenerator.writeEndArray();
            jGenerator.writeEndObject();
            jGenerator.close();
        } catch (JsonGenerationException e) {
            e.printStackTrace();
        } catch (IOException e) {
            e.printStackTrace();

        }
    }

    public void read(String filePath, Map data) throws Exception {
        try {

            JsonFactory jfactory = new JsonFactory();
            JsonParser jParser = jfactory.createJsonParser(new File("c:\\user.json"));

            while (jParser.nextToken() != JsonToken.END_OBJECT) {
                String fieldname = jParser.getCurrentName();
                if ("name".equals(fieldname)) {
                    jParser.nextToken();
                    System.out.println(jParser.getText());
                }

                if ("age".equals(fieldname)) {
                    jParser.nextToken();
                    System.out.println(jParser.getIntValue());
                }

                if ("messages".equals(fieldname)) {
                    jParser.nextToken();
                    while (jParser.nextToken() != JsonToken.END_ARRAY) {
                        System.out.println(jParser.getText());
                    }
                }
            }
            jParser.close();
        } catch (JsonGenerationException e) {
            e.printStackTrace();
        } catch (IOException e) {
            e.printStackTrace();
        }
    }
}
