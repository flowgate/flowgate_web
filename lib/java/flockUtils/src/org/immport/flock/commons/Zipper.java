package org.immport.flock.commons;

import net.lingala.zip4j.core.ZipFile;
import net.lingala.zip4j.model.ZipParameters;
import net.lingala.zip4j.util.Zip4jConstants;
import org.apache.commons.io.FileUtils;

import java.io.File;
import java.util.ArrayList;

/**
 * User: hkim
 * Date: 7/15/13
 * Time: 8:32 AM
 * org.immport.flock.commons
 * A helper class for compressing nested directories and extracting nested zip files
 * using zip4j and Apache's common-io
 */
public class Zipper {
    public Zipper() {}

    public static void extract(String inputFilePath, String outDirPath) throws Exception {
        ZipFile zipFile = new ZipFile(inputFilePath);
        zipFile.extractAll(outDirPath);
        Zipper.extractNestedZip(outDirPath);
    }
    public static void extractNestedZip(String dirPath) throws Exception {
        File outDir = new File(dirPath);
        String[] children = outDir.list();

        String currDir = dirPath + File.separator;
        if(children!=null && children.length>0) {
            for(String child : children) {
                File childFile = new File(currDir + child);
                if(childFile.isDirectory()) {
                    Zipper.extractNestedZip(childFile.getAbsolutePath());
                } else {
                    if(child.endsWith(".zip")) {
                        String nestedZipPath = currDir + child;
                        Zipper.extract(nestedZipPath, currDir + child.substring(0, child.lastIndexOf(".zip")));
                        File nestedZipFile = new File(nestedZipPath);
                        nestedZipFile.delete();
                    }
                }
            }
        }
    }

    public static void compress(String path) throws Exception {
        ZipFile zipFile = new ZipFile(path +".zip");

        ZipParameters parameters = new ZipParameters();
        parameters.setCompressionMethod(Zip4jConstants.COMP_DEFLATE);
        parameters.setCompressionLevel(Zip4jConstants.DEFLATE_LEVEL_NORMAL);

        File parent = new File(path);
        ArrayList<File> filesToAdd = new ArrayList<File>();
        String[] children = parent.list();
        for(String childName : children) {
            filesToAdd.add(new File(path + File.separator + childName));
        }

        // Add folder to the zip file
        //zipFile.addFolder(folderToAdd, parameters);

        if(filesToAdd.size()>0) {
            zipFile.addFiles(filesToAdd, parameters);

        }
        FileUtils.deleteDirectory(parent);
    }

    public static void buildNestedZip(String dirPath) throws Exception {
        File outDir = new File(dirPath);
        String[] children = outDir.list();

        String currDir = dirPath + File.separator;
        if(children!=null && children.length>0) {
            for(String child : children) {
                File childFile = new File(currDir + child);
                if(childFile.isDirectory()) {
                    Zipper.buildNestedZip(childFile.getAbsolutePath());
                }
            }
        }
        Zipper.compress(outDir.getAbsolutePath());
    }
}
