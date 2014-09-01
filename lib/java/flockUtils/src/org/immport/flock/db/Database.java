package org.immport.flock.db;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.SQLException;
import java.util.ResourceBundle;

/**
 * User: hkim
 * Date: 3/13/14
 * Time: 3:02 PM
 * org.immport.flock.db
 */
public class Database {
    public static final Integer ANALYSIS_STATUS_RUNNING = 1;
    public static final Integer ANALYSIS_STATUS_COMPLETED = 2;
    public static final Integer ANALYSIS_STATUS_FAILED = 3;

    Connection conn = null;

    public Database() {}

    private void getConnection() {
        if(conn == null) {
            ResourceBundle rb = ResourceBundle.getBundle("flock");
            String addr = rb.getString("mysql.address");
            String port = rb.getString("mysql.port");
            String user = rb.getString("mysql.user");
            String pass = rb.getString("mysql.password");

            try {
                conn = DriverManager.getConnection("jdbc:mysql://" + addr + ":" + port + "/" + "cyberflow", user, pass);
            } catch (SQLException e) {
                e.printStackTrace();
            }
        }
    }

    public boolean analysisStatusUpdate(String jid, int status) throws Exception {
        boolean rtnVal = false;

        String sql = "UPDATE analysis SET analysisStatus = ? WHERE analysisID = ? ;";
        if(conn == null) {
            this.getConnection();
        }

        try {
            conn.setAutoCommit(false);

            PreparedStatement prep = conn.prepareStatement(sql);
            prep.setInt(1, status);
            prep.setString(2, jid);
            rtnVal = prep.executeUpdate() == 1;
        } catch (SQLException e) {
            e.printStackTrace();
            try {
                conn.rollback();
            } catch(SQLException e1) {
                e1.printStackTrace();
            }
        } finally {
            try {
                conn.commit();
                conn.setAutoCommit(true);
                conn.close();
            } catch(SQLException e) {
                e.printStackTrace();
            }
            conn = null;
        }

        return rtnVal;
    }

    public static void main(String[] args) throws Exception {
        if(args.length < 2) {
            throw new Exception("Usage: jobId status[running - 1, completed - 2, failed - 3] " + "(" + args.length + ")");
        }

        String jobId = args[0];
        String status = args[1];

        Database database = new Database();
        boolean updated = database.analysisStatusUpdate(jobId, Integer.parseInt(status));

        System.out.println(updated ? "Status updated" : "Status update failed");
    }
}
