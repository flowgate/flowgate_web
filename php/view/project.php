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
        <h3>Project</h3>
        <div id="projectAlert"></div>
        <div>
          <div style="float:left;">
            <p class="text-muted">
              A project in FlowGate is a container defined by the user to contain datasets from the same experiment or clinical study. It is designed to be a flexible way to organize datasets into user-defined packages.
              Click the project name to view or upload datasets.
            </p>
          </div>
          <div style="float:right;">
            <button id="addProjectBtn" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Add Project</button>
          </div>
        </div>
        
        <div id="projectTableDiv" style="padding-top:10px;clear:both;">
          <table id="projectTable" class="table table-bordered tablesorter">
            <thead>
              <tr>
                <th width="5%">#</th>
                <!-- <th width="8%">Project ID</th> -->
                <th>Project Name</th>
                <th>Description</th>
                <th width="15%">Created</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
        <form id="projectForm" action="../common/controller.php?j=p_s" method="post" role="form"></form>
    </div>

    <script src="../../js/jquery.min.js"></script>
    <script src="../../js/shared.js"></script>
    <script src="../../js/bootstrap.min.js"></script>
    <script src="../../js/jquery.ui.min.js"></script>
    <script src="../../js/jquery.tablesorter.min.js"></script>
    <script>

      var _page = {
        getProjects: function() {
          var renderProjects = function(obj) {
            var $list = $('#projectTable tbody');
            $list.empty();
            if(obj && obj.projects) {
              $.each(obj.projects, function(i,p) {
                $list.append(
                  '<tr>' +
                    '<td>'+(i+1)+'</td>' +
                    // '<td>'+p.datasetID+'</td>' +
                    '<td><a href="#" onclick="_page.select(\''+p.datasetID+'\', \''+p.datasetName+'\');">'+p.datasetName+'</a></td>' +
                    '<td>'+p.datasetDesc+'</td>' +
                    '<td>'+p.datasetTime+'</td>' +
                  '</tr>'
                );
              });
              $("#projectTable").tablesorter({
                headers: { 0 : {sorter: false} }
              });
            }
          }
          makeAjaxCall('g', '../common/controller.php?j=p_u',{}, renderProjects);
        },
        select: function(pid, pname) {
          $('<input>').attr({type: 'hidden', name: 'pid', value: pid }).appendTo('form');
          $('<input>').attr({type: 'hidden', name: 'pname', value: pid }).appendTo('form');
          common.p.set(pid, pname);
          $('form').submit();
        }
      };


      (function(){
        $("#nav").load("../common/nav.php");
        _page.getProjects();
        $('#addProjectBtn').click(function() {
          window.location = "addProject.php";
        });
      })();
    </script>
</body>
</html>