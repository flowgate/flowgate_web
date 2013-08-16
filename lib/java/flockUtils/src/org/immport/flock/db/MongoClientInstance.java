package org.immport.flock.db;

import com.mongodb.DB;
import com.mongodb.MongoClient;

import java.net.UnknownHostException;
import java.util.ResourceBundle;

/**
 * User: hkim
 * Date: 8/16/13
 * Time: 11:48 AM
 * org.immport.flock.db
 */
public class MongoClientInstance {
    public static MongoClientInstance clientInstance;

    private MongoClient client;
    private DB gofcmDB;

    private MongoClientInstance() {
        this.init();
    }

    private void init() {
        try {
            ResourceBundle rb = ResourceBundle.getBundle("flock");
            client = new MongoClient(rb.getString("mongo.address"), Integer.parseInt(rb.getString("mongo.port")));
            gofcmDB = client.getDB(rb.getString("mongo.db"));

            String user = rb.getString("mongo.db");
            String passwd = "!"+user;
            gofcmDB.authenticate(user, passwd.toCharArray());
        } catch(Exception ex) {
            System.err.println(ex.toString());
        }
    }

    public static MongoClientInstance getinstance() throws UnknownHostException {
        if(clientInstance==null) {
            clientInstance = new MongoClientInstance();
        }
        return clientInstance;
    }

    public MongoClient getClient() {
        return client;
    }

    public DB getGofcmDB() {
        return gofcmDB;
    }
}
