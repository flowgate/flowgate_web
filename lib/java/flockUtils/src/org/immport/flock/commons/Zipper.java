package org.immport.flock.commons;

import net.lingala.zip4j.core.ZipFile;
import net.lingala.zip4j.model.ZipParameters;
import net.lingala.zip4j.util.Zip4jConstants;
import org.apache.commons.io.FileUtils;

import java.io.File;

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

    public void extract(String inputFilePath, String outDirPath) throws Exception {
        ZipFile zipFile = new ZipFile(inputFilePath);
        zipFile.extractAll(outDirPath);
        this.findNestedZip(outDirPath);
    }
    public void findNestedZip(String dirPath) throws Exception {
        File outDir = new File(dirPath);
        String[] children = outDir.list();

        String currDir = dirPath + File.separator;
        if(children!=null && children.length>0) {
            for(String child : children) {
                File childFile = new File(currDir + child);
                if(childFile.isDirectory()) {
                    this.findNestedZip(childFile.getAbsolutePath());
                } else {
                    if(child.endsWith(".zip")) {
                        String nestedZipPath = currDir + child;
                        this.extract(nestedZipPath, currDir + child.substring(0, child.lastIndexOf(".zip")));
                        File nestedZipFile = new File(nestedZipPath);
                        nestedZipFile.delete();
                    }
                }
            }
        }
    }

    public void compress(String path) throws Exception {
        ZipFile zipFile = new ZipFile(path +".zip");
        String folderToAdd = path;
        ZipParameters parameters = new ZipParameters();
        parameters.setCompressionMethod(Zip4jConstants.COMP_DEFLATE);
        parameters.setCompressionLevel(Zip4jConstants.DEFLATE_LEVEL_NORMAL);

        // Add folder to the zip file
        zipFile.addFolder(folderToAdd, parameters);

        File addedFolder = new File(folderToAdd);
        FileUtils.deleteDirectory(addedFolder);
    }

    public void buildNestedZip(String dirPath) throws Exception {
        File outDir = new File(dirPath);
        String[] children = outDir.list();

        String currDir = dirPath + File.separator;
        if(children!=null && children.length>0) {
            for(String child : children) {
                File childFile = new File(currDir + child);
                if(childFile.isDirectory()) {
                    this.buildNestedZip(childFile.getAbsolutePath());
                }
            }
        }
        this.compress(outDir.getAbsolutePath());
    }
}
