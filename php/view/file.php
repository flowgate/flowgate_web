<?php 
    include("../common/session.php"); 
    isSessionAlive();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="../../css/merged.css" rel="stylesheet">
  <style type="text/css">
  </style>
</head>

<body>
    <div id="nav"></div>
    <div class="container">
        <h3 id="pageHeader">Dataset</h3>
        <form id="fileForm" action="result.php" method="get" class="form-horizontal" role="form">
          <div>
            <div style="float:left;">
              <p class="text-muted">
                A dataset in FlowGate is defined to contain FCS files that can be directly compared. Therefore FCS files in a given dataset should have been generated using the same reagent staining panel.
                Click the dataset name to view the analysis results or create a new analysis.
              </p>
            </div>
            <div style="float:right;">
              <button id="addDatasetBtn" class="btn btn-primary" aria-hidden="true">Upload Dataset</button>
            </div>
          </div>
          <div id="fileTableDiv" style="padding-top:10px;clear:both;">
            <table id="fileTable" class="table table-bordered tablesorter">
              <thead>
                <tr>
                  <th width="5%">#</th>
                  <!-- <th width="8%">Dataset ID</th> -->
                  <th wiwdth="40%">Dataset Name</th>
                  <th>Description</th>
                  <th>Created</th>
                  <!--<th>Status</th>-->
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </form>
    </div>

    <script src="../../js/jquery.min.js"></script>
    <script src="../../js/shared.js"></script>
    <script src="../../js/bootstrap.min.js"></script>
    <script src="../../js/jquery.tablesorter.min.js"></script>
    <script>
      var _page = {
        getFiles: function() {
          var renderFiles = function(obj) {
            var $list = $('#fileTable tbody');
            $list.empty();
            if(obj && obj.files) {
              $.each(obj.files, function(i,f) {
                $list.append(
                  '<tr>' +
                    '<td>' + (i+1) + '</td>' +
                    // '<td>' + f.dataInputFileID + '</td>' +
                    '<td>' + 
                      '<a href="javascript:_page.viewResults(' + f.dataInputFileID + ',\'' + f.dataInputFileName + '\');">' + f.dataInputFileName + '</a>' + 
                      '<input type="button" class="btn btn-success btn-xs" value="New Analysis" style="margin-left:15px;" onclick="_page.createAnalysis(' + f.dataInputFileID + ',\'' + f.dataInputFileName + '\');"/>' + 
                      '<input type="button" class="btn btn-info btn-xs" value="View Result" style="margin-left:5px;" onclick="_page.viewResults(' + f.dataInputFileID + ',\'' + f.dataInputFileName + '\');"/>' + 
                    '</td>' +
                    '<td>' + f.dataInputFileDesc + '</td>' +
                    '<td>' + f.dataInputFileTime + '</td>' +
                  '</tr>'
                );
              });
              $("#fileTable").tablesorter({
                headers: { 0 : {sorter: false} }
              });
            }
          }
          makeAjaxCall('g', '../common/controller.php?j=f_u',{pid:sessionStorage.getItem("gofcm.pid")}, renderFiles);
          //this.fajax('u', {pid:sessionStorage.getItem("gofcm.pid")}, renderFiles);
        },
        viewResults: function(id, name) {
          $('<input>').attr({type: 'hidden', name: 'ds', value: id }).appendTo('form');
          this.setFileInfo(id, name);
          $('form').submit();
        },
        createAnalysis: function(id, name) {
          $('#fileForm').attr("action","run.php");
          this.setFileInfo(id, name);
          $('form').submit();
        },
        setFileInfo: function(id, name) {
          common.ss_s('fid', id);
          common.ss_s('fname', name);  
        }
      };


      (function() {
        $("#nav").load("../common/nav.php");
        common.p.toHeader();
        $('#addDatasetBtn').click(function() {
          $('form').attr('action', 'addFile.php');
          $('form').submit();
        });

        _page.getFiles();
      })();
    </script>
</body>
</html>