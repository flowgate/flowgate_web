<?php 
    require("../common/common.php"); 
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
    <div class="container hero-unit">
        <h3>File</h3>
        <a href="#fileUploadModal" role="button" class="btn" data-toggle="modal">Upload file</a>
    </div>

    <!-- upload modal -->
    <div id="fileUploadModal" class="modal hide fade">
      <div class="modal-header">
        <a href="#" class="close" data-dismiss="modal">&times;</a>
        <h3 id="prompt">File Upload</h3>
      </div>
      <div class="modal-body">
        <div class="divDialogElements">
          <div class="divPopupMenu">
            <span class="btn btn-success fileinput-button">
              <i class="glyphicon glyphicon-plus"></i>
              <span>Select files...</span>
              <input id="fileupload" type="file" name="files[]" multiple>
            </span>
            <br>
            <br>
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

    <script src="../../js/jquery.min.js"></script>
    <script src="../../js/jquery.ui.widget.js"></script>
    <script src="../../js/jquery.iframe-transport.js"></script>
    <script src="../../js/jquery.fileupload.js"></script>
    <script>
      var uploadDone = function(file) {
        $.ajax({
          type: "POST",
          async: false,
          url: "../common/controller.php?j=f_a",
          dataType: 'json',
          data: {fname:file.name, pid:sessionStorage.getItem("gofcm.pid")},
          success: function (obj, ts) {},
          error: function() {}
        });
      };

      $(function(){
        $("#nav").load("../common/nav.php");
      });
      $(function () {
        'use strict';
        $('#fileupload').fileupload({
            url: '../bin/upload.php',
            dataType: 'json',
            done: function (e, data) {
              var fileMsg;
              $.each(data.result.files, function (index, file) {
                  if(file && file.size>0 && !file.error) {
                    uploadDone(file);
                    fileMsg=file.name;
                  } else {
                    fileMsg = file.error;
                  }
                  $('<p/>').text(fileMsg).appendTo('#files');
              });
            },
            progressall: function (e, data) {
              var progress = parseInt(data.loaded / data.total * 100, 10);
              $('#progress .progress-bar').css(
                  'width',
                  progress + '%'
              );
            }
        }).prop('disabled', !$.support.fileInput)
            .parent().addClass($.support.fileInput ? undefined : 'disabled');
      });
    </script>
</body>
</html>