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
  <link href="../../css/bootstrap-select.css" rel="stylesheet">
  <style type="text/css">
  </style>
</head>

<body>
  <div id="nav"></div>
  <div class="container">
    <h3 id="pageHeader">Create New Analysis</h3>

    <form action="../common/controller.php" method="post" class="form-horizontal" role="form"> 
      <input type="hidden" name="j" value="t_s" />

      <div class="form-group">
        <label for="name" class="col-sm-3 control-label">Analysis Name<strong style="color:darkred;">*</strong></label>
        <div class="col-sm-9">
          <input type="text" class="form-control" id="name" name="name" placeholder="analysis name">
        </div>
      </div>

      <div class="form-group">
        <label for="name" class="col-sm-3 control-label">Select Workflow Platform<strong style="color:darkred;">*</strong></label>
        <div class="col-sm-9">
          <select class="form-control" id="workflow" name="workflow">
            <option value=""></option>
            <option value="bioKepler">bioKepler</option>
            <option value="genePattern">GenePattern</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label for="name" class="col-sm-3 control-label">Applicable Pipeline<strong style="color:darkred;">*</strong></label>
        <div class="col-sm-9">
          <select class="form-control" id="lsid" name="lsid" disabled="true"></select>
        </div>
      </div>

      <h4 style="padding: 15px 0;">Pipeline Parameters</h4>

      <div class="form-group">
        <label for="name" class="col-sm-3 control-label">Filtering Step<strong style="color:darkred;">*</strong></label>
        <div class="col-sm-9">
          <label class="radio-inline"><input type="radio" name="filtering" id="filtering1" value="0" checked="true">No</label>
          <label class="radio-inline"><input type="radio" name="filtering" id="filtering2" value="1">Yes</label>
        </div>
      </div>

      <div class="form-group">
        <label for="name" class="col-sm-3 control-label">Parameters in FLOCK Step<strong style="color:darkred;">*</strong></label>
        <div class="col-sm-9">
          <label class="radio-inline"><input type="radio" name="manual" id="manual1" value="0" checked="true">Auto</label>
          <label class="radio-inline"><input type="radio" name="manual" id="manual2" value="1">Manual</label>
        </div>
      </div>

      <div class="well" style="display:none;" id="flockParamDiv">
        <div class="form-group">
          <label for="bins" class="col-sm-3 control-label">Bins<strong style="color:darkred;">*</strong></label>
          <div class="col-sm-9">
            <input type="text" class="form-control" id="bins" name="bins" placeholder="int or range[x-y]">
          </div>
        </div>
        <div class="form-group">
          <label for="density" class="col-sm-3 control-label">Density<strong style="color:darkred;">*</strong></label>
          <div class="col-sm-9">
            <input type="text" class="form-control" id="density" name="density" placeholder="int or range[x-y]">
          </div>
        </div>
        <div class="form-group">
          <label for="pop" class="col-sm-3 control-label">Maximum Number of Populations<strong style="color:darkred;">*</strong></label>
          <div class="col-sm-9">
            <input type="text" class="form-control" id="pop" name="pop" placeholder="int">
          </div>
        </div>
      </div>
      <br/>

      <div style="float:right;">
        <button type="button" class="btn btn-default" onclick="window.location='result.php';">Cancel</button>
        <button type="submit" class="btn btn-primary">Run</button>
      </div>
    </form>
  </div>

  <script src="../../js/jquery.min.js"></script>
  <script src="../../js/shared.js"></script>
  <script src="../../js/bootstrap.min.js"></script>
  <script>

    (function() {
      $("#nav").load("../common/nav.php");

      $('#workflow').change(function(){
        var selected = $('#workflow').val(), opts = '';
        $('#lsid').prop('disabled', true);

        if(selected) {
          if(selected === 'bio') {
            opts += '<option value="bio1">FLOCK_bioKepler (bioKID=1)</option>';
          } else {
            opts += '<option value="gp_txt">FLOCK_TXT_INPUT (LSID=FlockgateFLOCKTXT)</option>';
            opts += '<option value="gp_fcs">FLOCK_FCS_INPUT (LSID=FlockgateFLOCKFCS)</option>';     
          }
          $('#lsid').prop('disabled', false);
        }
        $('#lsid').html(opts);
      });
      $('input[name="manual"]').change(function() {
        $('#flockParamDiv').toggle();
      });

      common.p.toHeader(common.ss_g('fname'));

      $('<input>').attr({type: 'hidden', name: 'pid', value: common.ss_g(common.p.id) }).appendTo('form');
      $('<input>').attr({type: 'hidden', name: 'fid', value: common.ss_g('fid') }).appendTo('form');
    })();
  </script>
</body>
</html>