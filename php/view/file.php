<?php 
    require("../common/session.php"); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>mockup with bootstrap</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="../../css/merged.css" rel="stylesheet">
  <style type="text/css">
    .fileinput-button {
      position: relative;
      overflow: hidden;
    }
    .fileinput-button input {
      position: absolute;
      top: 0;
      right: 0;
      margin: 0;
      opacity: 0;
      filter: alpha(opacity=0);
      transform: translate(-300px, 0) scale(4);
      font-size: 23px;
      direction: ltr;
      cursor: pointer;
    }
    .fileupload-buttonbar .btn, .fileupload-buttonbar .toggle { margin-bottom: 5px; }
    .progress-animated .progress-bar,
    .progress-animated .bar {
      background: url(../../css/images/progressbar.gif) !important;
      filter: none;
    }
    .fileupload-loading {
      float: right;
      width: 32px;
      height: 32px;
      background: url(../../css/images/loading.gif) center no-repeat;
      background-size: contain;
      display: none;
    }
    .fileupload-processing .fileupload-loading { display: block; }
    .files audio, .files video { max-width: 300px; }

    @media (max-width: 767px) {
      .fileupload-buttonbar .toggle,
      .files .toggle,
      .files .btn span {
        display: none;
      }
      .files .name {
        width: 80px;
        word-wrap: break-word;
      }
      .files audio,
      .files video {
        max-width: 80px;
      }
    }
  </style>
</head>

<body>
    <div id="nav"></div>
    <div class="container">
        <h3>File</h3>
        <div id="fileAlert"></div>
        <div>
          <a href="#fileUploadModal" role="button" class="btn btn-info" data-toggle="modal">Upload file</a>
          <select id="projectFilter" style="margin-top:10px;"></select>
        </div>
        <div id="fileTableDiv" style="padding-top:10px;">
          <table id="fileTable" class="table table-bordered tablesorter">
            <thead>
              <tr>
                <th>#</th>
                <th>File Name</th>
                <th>Project</th>
                <!--<th>Status</th>-->
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
    </div>

    <!-- upload modal -->
    <div class="modal fade" id="fileUploadModal" tabindex="-1" role="dialog">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="prompt">File Upload</h4>
          </div>
          <div class="modal-body">
            <div class="divDialogElements">
              <div class="divPopupMenu">
                <div>
                  <span class="btn btn-success fileinput-button">
                    <i class="glyphicon glyphicon-plus"></i>
                    <span>Select files...</span>
                    <input id="fileupload" type="file" name="files[]" multiple>
                  </span>
                  <p style="padding-top: 15px;" class="text-info">OR drag & drop files into this popup.</p>  
                </div>
                <div id="progress" class="progress">
                  <div class="progress-bar progress-bar-success"></div>
                </div>
                <div id="files" class="files"></div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
          </div>
        </div>
      </div>
    </div>
    <!-- job submission modal -->
    <div class="modal fade" id="runModal" tabindex="-1" role="dialog">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="prompt">Run FLOCK</h4>
          </div>
          <div class="modal-body">
            <div class="divDialogElements">
              <h4 class="muted">Pipeline Parameters</h4>
              <input type="hidden" name="rid" id="rid" />
              <label for="rbin" class="control-label">Bins</label>
              <div><input type="text" class="form-control" id="rbin" placeholder="int or range[x-y]"></div>
              <label for="rden" class="control-label">Density</label>
              <div><input type="text" class="form-control" id="rden" placeholder="int or range[x-y]"></div>
              <label for="rpop" class="control-label">Population</label>
              <div><input type="text" class="form-control" id="rpop" placeholder="int"></div>
            </div>
          </div>
          <div class="modal-footer">
            <img src="../../css/images/ajax-loader.gif" id="loading-indicator" style="display:none;"/>
            <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
            <a href="#" class="btn btn-primary" onclick="_page.run();">Run</a>
          </div>
        </div>
      </div>
    </div>

    <script src="../../js/jquery.min.js"></script>
    <script src="../../js/shared.js"></script>
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.0.2/js/bootstrap.min.js"></script>
    <!--<script src="../../js/jquery.ui.widget.js"></script>-->
    <script src="../../js/jquery.ui.min.js"></script>
    <script src="../../js/jquery.fileupload.min.js"></script>
    <script src="../../js/jquery.tablesorter.min.js"></script>
    <script>

      $(function(){
        $("#nav").load("../common/nav.php");

        $('#fileupload').fileupload({
            url: '../bin/upload.php',
            dataType: 'json',
            done: function (e, data) {
              var fileMsg;
              $.each(data.result.files, function (index, file) {
                  if(file && file.size>0 && !file.error) {
                    _page.uploadDone(file);
                    fileMsg=file.name;
                  } else {
                    fileMsg = file.error;
                  }
                  $('<p/>').text(fileMsg + ' is uploaded').appendTo('#files');
              });
              _page.getFiles();
            },
            progressall: function (e, data) {
              var progress = parseInt(data.loaded / data.total * 100, 10);
              $('#progress .progress-bar').css('width', progress + '%');
            }
        }).prop('disabled', !$.support.fileInput).parent().addClass($.support.fileInput ? undefined : 'disabled');

        _page.getFiles();
      });

      var _page = {
        f_contr: "../common/controller.php?j=f_",
        fajax: function(t,d,cb) {
          $.ajax({
            type: "POST",
            async: true,
            url: "../common/controller.php?j=f_"+t,
            dataType: 'json',
            data: d,
            success: function (obj, ts) {
              if(cb) {
                cb(obj);
              }
            },
            error: function() {}
          });
        },
        getFiles: function() {
          var renderFiles = function(obj) {
            var $list = $('#fileTable tbody');
            $list.empty();
            if(obj && obj.files) {
              $.each(obj.files, function(i,f) {
                $list.append(
                  '<tr>' +
                    '<td>'+(i+1)+'</td>' +
                    '<td><a href="#" onclick="_page.fileClick(\'' + f.dataInputFileID+ '\');">'+f.dataInputFileName+'</a></td>' +
                    '<td>'+f.datasetName+'</td>' +
                    //'<td>'+f.f_status+'</td>' +
                  '</tr>'
                );
              });
              $("#fileTable").tablesorter();
            }
          }
          makeAjaxCall('g', '../common/controller.php?j=f_u',{pid:sessionStorage.getItem("gofcm.pid")}, renderFiles);
          //this.fajax('u', {pid:sessionStorage.getItem("gofcm.pid")}, renderFiles);
        },
        uploadDone: function(file) {
          this.fajax('a', {fname:file.name, pid:sessionStorage.getItem("gofcm.pid")}, null);  
        },
        fileClick: function(id) {
          $('#rid').val(id);
          common.modal.open('runModal');
        },
        run: function() {
          $('#loading-indicator').show();

          var fileId = $('#rid').val();
          var bins = $('#rbin').val();
          var density = $('#rden').val();
          var pop = $('#rpop').val();
          var pid = common.ss_g(common.p.id); //get current project id from sessionStorage
          var runCB = function(obj) {
            var isError = true;
            if(obj) {
              if(obj.success && obj.jid) {
                isError = false;
              } 
            }
            $('#loading-indicator').hide();
            common.modal.hide('runModal');
            common.alert(
              'fileAlert', 
              isError?'e':'s',
              isError?'Job submission failed! Please try it again.':'Job "' + obj.jid+ '" has been submitted successfully!'
            );
          };

          var jobParam = {
            pid: pid,
            fid: fileId,
            bins: bins,
            density: density,
            pop: pop
          }
          makeAjaxCall('p', '../common/controller.php?j=t_s', jobParam, runCB);
        }
      };
    </script>
</body>
</html>