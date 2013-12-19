<?php
  include("../common/constants.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>mockup with bootstrap</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="../../css/jqueryFileTree.css" rel="stylesheet">
  <link href="../../css/bootstrap-select.css" rel="stylesheet">
  <link href="../../css/jquery.ui.css" rel="stylesheet">
  <link href="../../css/DT_bootstrap.css" rel="stylesheet">
  <style type="text/css">
    body, html {
      height: 100%;
    }
    #resultDiv {
      overflow: auto;
    }
    .centerSub {
      background-color:#eee; 
      border: 1px solid #888; 
      border-radius:3px;
    }

    table.DTFC_Cloned th { white-space: nowrap; }

    .caret { margin-top: 10px; }

    UL.jqueryFileTree LI.ext_cb { padding-left:5px; }

    .imageTable img {
      width: auto;
      height: auto;
      width: 100%;
      max-width: 150px;
    }

    .resultRow { margin: 0 !important;}
    .resultWell { 
      margin-bottom: 0 !important; 
      padding: 3px 0px !important; 
    }
    .selectorsCol { padding: 0 !important; }

    .markerSelect .bootstrap-select { width: 120px; }

    #filesContainer, #imagesContainer { padding: 0 !important; }

    .crossing { 
      background-color: #bbb; 
      max-width: 150px; 
    }

    #imageTable td { border: 1px solid black;}
    .imageTable thead tr th,
    #imageTable tbody tr td:first-child { 
      background-color: white !important;
      border: 1px solid black;
     }
  </style>

</head>

  <body>
    <div id="nav"></div>
    <div id="tableDiv" class="container">
        <h3>Result</h3>
        <div class="row" id="alert"></div>
        <div>
          Filter by Project: <select id="projectFilter" style="margin-top:10px;"></select>
        </div>
        <div id="resultsTableDiv" style="padding-top:10px;">
          <table id="resultsTable" class="table table-bordered tablesorter">
            <thead>
              <tr>
                <th>#</th>
                <th>ID</th>
                <th>Input Name</th>
                <th>Project</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
    </div>
    <div id="resultDiv" class="row resultRow" style="display:none;">
      <div style="padding: 5px 15px;">
        <button class="btn btn-sm" type="button" onclick="_page.toggle();">back to results</button>
      </div>
      <div>
        <div class="col-md-2" id="filesContainer">
          <div class="well resultWell" id="files" style="height:100%;overflow:auto;">
            <div class="row col-md-12" style="margin-top:5px;">
              <button class="btn btn-xs btn-primary" type="button" id="fileAllButton">Select All</button>
              <button class="btn btn-xs btn-warning" type="button" id="fileNoneButton">Deselect All</button>
              <!--<p><span class="label label-important">Collapsing a directory deselects files under it!</span></p>-->
            </div>
            <div class="row col-md-12" id="fileNav" style="overflow:auto;"></div>
          </div>
        </div>
        <div class="col-md-10" id="imagesContainer" style="height:100%;padding-left:5px;">
          <div class="row resultRow">
            <div class="col-md-12 centerSub">
              <div class="row">
                <div class="col-md-11 col-md-offset-1" id="details">
                  <table>
                    <tr><td><strong>Method Name</strong></td><td>FLOCK</td></tr>
                    <tr><td><strong>Method Version</strong></td><td>v0.1</td></tr>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <div class="row resultRow">
            <div class="col-md-12 centerSub" id="overview" style="height:100%;">
              <div class="row" id="ralert"></div>
              <div class="row resultRow">
                <div class="col-md-12 selectorsCol">
                  <div class="row resultRow">
                    <div class="col-md-3 selectorsCol">
                      <h6>Population</h6>
                      <select id="populselect" class="selectpicker" rel="populations" multiple data-selected-text-format="count>1" data-count-selected-text="{0} of {1} populations">
                      </select>
                    </div>
                    <div class="col-md-2 markerSelect selectorsCol">
                      <h6>X-axis</h6>
                      <select id="xmarker" class="selectpicker"></select>
                    </div>
                    <div class="col-md-2 markerSelect selectorsCol">
                      <h6>Y-axis</h6>
                      <select id="ymarker" class="selectpicker"></select>
                    </div>
                    <div class="col-md-5 selectorsCol">
                      <h6>Parameters</h6>
                      <select id="paramselect" class="selectpicker" multiple data-selected-text-format="count>1" data-count-selected-text="{0} of {1}"></select>
                    </div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-3">
                  <button class="btn btn-primary" type="button" id="updateButton">Show Result</button>
                </div>
                <div class="col-md-9">
                  <img src="../../css/images/ajax-loader.gif" id="loading-indicator" style="display:none;"/>
                </div>
              </div>
              <div class="row" style="margin-top:5px;">
                <div class="col-md-12">
                  <div class="row" id="imageTableRow"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>  
    </div>

    <script src="../../js/jquery.min.js"></script>
    <script src="../../js/shared.js"></script>
    <script src="../../js/jqueryFileTree.js"></script>
    <script src="../../js/bootstrap-select.min.js"></script>
    <script src="../../js/jquery.ui.min.js"></script>
    <script src="../../js/jquery.floatThead.min.js"></script>
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.0.2/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.5.2/underscore-min.js"></script>
    <script>
      var _page = {
        init: false, //initial load flag,
        imageTable: null,
        data: {
          taskId: '',
          files: '', //comma separated list of selected files,
          populs: '',
          xmarker: '',
          ymarker: '',
          params: '',
          cols: null,
          ppl: 0
        },
        html: {
          opt_dc: "<option data-content='<span class=\"$c$\">&nbsp;&nbsp;&nbsp;</span>$t$'>$v$</option>",
          opt_vt: "<option value='$v$'>$t$</option>",
          opt_vv: "<option value='$v$'>$v$</option>"
        },
        toggle: function() {
          $('#tableDiv, #resultDiv').toggle();
        },
        view: function(taskId) {
          _data.meta(taskId);
          $('.selectpicker').selectpicker();
          _plugin.filetree.init('fileNav', taskId);
          _page.event.init();
        },
        event: {
          init: function() {
            this.button.file();
            this.button.update();
            this.file.checkbox();
          },
          button: {
            file: function() {
              $("#fileAllButton, #fileNoneButton").click(function() { //select all files or none 
                var ischeck = $(this).attr("id")==="fileAllButton",
                    firstFile = null;
                $('[name^="fcb_"]').each(function(i, v) {
                  if(i===0) {
                    firstFile = v;
                  }
                  $(v).prop("checked", ischeck);
                });
                if(firstFile) { //fires change event to update files variable at page level
                  $(firstFile).change();
                }
                if(ischeck) {
                  _plugin.selectpicker.multifile();
                  
                } else {
                  _plugin.selectpicker.singlefile();
                }
              });
            },
            update: function() {
              $("#updateButton").click(function() {
                if(_page.validation()) {
                  $('#loading-indicator').show();
                  _data.result();
                }
              });
            }
          },
          file: {
            checkbox: function() {
              $('#fileNav').on('change', '[name^="fcb_"]', function(event){ //keeps currently selected files at page level
                var selectedFiles = '', 
                    attrName,
                    count = 0;
                $('[name^="fcb_"]:checked').each(function(i, v) {
                  attrName = $(v).attr('name');
                  selectedFiles += attrName.substring(attrName.indexOf("_")+1) + ",";
                  count++;
                });
                _page.data.files = (count==0||count==1)?selectedFiles:selectedFiles.substring(0, selectedFiles.length-1);
                if(count>1) {
                  _plugin.selectpicker.multifile();
                } else {
                  _plugin.selectpicker.singlefile();
                }
              });
            }
          }
        },
        alert: function(w,e,m) { //w-where(page, result), t-message, e-boolean error or alert
          $('#'+(w?'r':'')+'alert').html(
            '<div class="alert '+(e?'alert-error':'')+'">'+
              '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
              '<strong>Warning!</strong> ' + m +
            '</div>');
        },
        validation: function() {
          var data = _page.data;
          data.populs = $('#populselect').val();
          data.xmarker = $('#xmarker').val();
          if(data.xmarker === 'all') {
            data.xmarker = $.map($('#xmarker option:not(:first)') ,function(option) {
              return option.value;
            });
          }
          data.ymarker = $('#ymarker').val();
          if(data.ymarker === 'all') {
            data.ymarker = $.map($('#ymarker option:not(:first)') ,function(option) {
              return option.value;
            });
          }
          data.params = $('#paramselect').val();
          if(data.populs!=null && data.populs.length>0 
            && data.xmarker!=null && data.xmarker.length>0
            && data.ymarker!=null && data.ymarker.length>0
            && data.files!=null && data.files.length>0
            && data.params!=null && data.params.length>0) {
            return true;
          } else {
            common.alert('ralert', 'a', 'Please select file(s) or Parameter(s).');
            return false;
          }
        }
      }; 
      var _data = {
        results: function(obj) {
          var renderResults = function(obj) {
            var $table = $('#resultsTable');
            if(obj && obj.results) {
              $.each(obj.results, function(i,r) {
                $table.find('tbody').append(
                  '<tr>' +
                    '<td>'+(i+1)+'</td>'+
                    '<td><a href="javascript:_page.view(\''+r.analysisName+'\');">'+r.analysisName+'</a></td>' +
                    '<td>'+r.dataInputFileName+'</td><td>'+r.datasetName+'</td><td>unknown</td>' + //'<td>'+r.par+'</td><td>'+r.p+'</td>' +
                  '</tr>'
                );
              });
              //$table.tablesorter();
            }
          };
          makeAjaxCall('g', '../common/controller.php', {'j':'t_u', 'pid':common.ss_g(common.p.id)}, renderResults);
        },
        meta: function(tid) {
          var _this = this;
          $.ajax({
            url: '../bin/resultMetadata.php?tid='+tid,
            async: false
          }).done(function(data) {
            if(data) {
              data = $.parseJSON(data);
              if(data.success===true && data.markers && data.populations) {
                _page.toggle();
                var markers = data.markers, 
                    taskId = data.taskId,
                    pops = data.populations;
                if(!_page.init) {
                  _page.data.taskId = taskId;
                  _this.popul(pops);
                  _this.markers(markers);
                  _this.params(data.params);

                  _page.init = true;
                }
              } else {
                common.alert('alert', 'e', data.err);
              }
            }
          });  
        },
        result: function() {
          var _this = this,
              _data = _page.data;
          $.ajax({
            url: '../bin/imageGenerator.php?tid='+_data.taskId+
                  '&f='+_data.files+'&ppl='+_data.ppl+'&pp='+_data.populs+
                  '&x='+_data.xmarker+'&y='+_data.ymarker+'&pr='+_data.params
          }).done(function(data) {
            if(data) {
              data = $.parseJSON(data);
              if(data.success==='true' && data.result) {
                var taskId = data.result.taskId, 
                    imageDir = data.result.imageDir, 
                    type = data.result.type, 
                    popIds = data.result.popIds,
                    xcols = data.result.xmarker,
                    ycols = data.result.ymarker,
                    files = data.result.files,
                    params = data.result.params,
                    m_f = data.result.multiFile,
                    m_p = data.result.multiParam,
                    m_m = data.result.multiMarker,
                    markerToImage = data.result.markerToImage,
                    fileMap = data.result.fileMap;

                //headers (always paramet combinations)
                var thead = '<thead><tr>';
                thead += '<th id="name">'+(m_f?'File':'Marker')+'</th>'; //row header
                for(var p=0;p<params.length;p++) {
                  thead+='<th>['+params[p][0]+':'+params[p][1]+']</th>';  
                }
                thead+='</tr></thead>';

                var columnsForDatatable = [];
                var imagesTotalWidth = 0; //total image width

                var rows = '';
                for(var f=0;f<files.length;f++) {
                  var fileData = fileMap[files[f]];
                  var row = '';

                  for(var x=0;x<xcols.length;x++) {
                    for(var y=0;y<ycols.length;y++) {

                      if(xcols[x]!==ycols[y]) {
                        row = '<td>' + (m_m ? xcols[x]+':'+ycols[y] : files[f]) + '</td>';
                        for(var p=0;p<params.length;p++) {
                          var mergedParam = params[p][0]+':'+params[p][1];
                          var paramMap = fileData[mergedParam];
                          if(paramMap.has) {
                            row += '<td><img src="' + imageDir + paramMap.dir + 'images/' + markerToImage[(xcols[x] + ':' + ycols[y])] + '"/></td>'; 
                          } else {
                            row += '<td class="crossing"></td>';
                          }

                          if(f === 0) {
                            columnsForDatatable.push(p+1);
                            imagesTotalWidth += 150;
                          }
                        }

                        rows += '<tr>' + row + '</tr>';
                      }
                    }
                  }
                }

                if(_page.imageTable) {
                  _page.imageTable.fnDestroy();
                }
                var tbody = '<tbody>' + rows + '</tbody>';
                $('#imageTableRow').html('<table id="imageTable" class="imageTable">'+thead+tbody+'</table>');

                $('#imageTable').imagesLoaded().then(function() {

                  $('#imageTable').floatThead()
                  //hide loading bar
                  $('#loading-indicator').hide();  
                });
              }
            }
          });
        },
        popul: function(cnt) {
          var $selecObj = $('#populselect'),
              count = 0;
          for(var i=1;i<=parseInt(cnt);i++) {
            $selecObj.append(
              _page.html.opt_dc.replace("$c$", "popul"+(i>24?i%25+1:i))
                .replace("$t$", "Population "+i)
                .replace("$v$",i)
            );  
          }
          $selecObj.selectpicker('selectAll');
          _page.data.ppl = cnt;
        },
        markers: function(markers) {
          var opts = "<option value='all' id='markerall'>All</option>";
          for(var i=0;i<markers.length;i++) {
            opts+=_page.html.opt_vv.replace(/\$v\$/g, markers[i]);    
          }
          $("#xmarker, #ymarker").append(opts).selectpicker();
          _page.data.cols = markers;
        },
        params: function(params) {
          var opts = ''; //, param_keys = Object.keys(params), param_vals = [], opts_arr=[];
          var bins = params.bins;
          var density = params.density;
          $.each(bins, function(bi, bv) {
            $.each(density, function(di, dv) {
              opts+=_page.html.opt_vt.replace('$v$', bv+':'+dv).replace('$t$', 'bins['+bv+']:density['+dv+']');  
            })
          });

          /* 
          * use code below for arbitrary parameter pairs other than bins and density

          $.each(param_keys, function(i,v) { //range or single value
            var val = params[v], vals = [], tokens;
            if(val.indexOf("-")>0) {
              tokens = val.split("-");
              for(var i=parseInt(tokens[0]);i<=parseInt(tokens[1]);i++) {
                vals.push(i);
              }
            } else {
              $.each(val, function(i,v) {
                vals.push(parseInt(v));
              });
            }
            param_vals.push(vals);
          });

          $.each(param_vals, function(i1,v1) { //build option values for parameter select box
            var currKey = param_keys[i1], tempArr=[];
            $.each(v1, function(i2, v2) {
              if(opts_arr.length>0) {
                $.each(opts_arr, function(i3, v3) {
                  tempArr.push(v3+':'+v2);
                })
              } else {
                tempArr.push(v2);
              } 
            })
            opts_arr = tempArr;
          })

          $.each(opts_arr, function(i,v) { //creates options
            var t='', t_arr = v.split(':');
            $.each(t_arr, function(i1, v1) {
              t += (i1>0?':':'') + param_keys[i1]+'['+v1+']';
            })
            opts+=_page.html.opt_vt.replace('$v$', opts_arr[i]).replace('$t$', t);    
          });
          */

          $("#paramselect").append(opts).selectpicker();
        }
      };

      //functions to handle behaviors of plugins
      var _plugin = {
        selectpicker: {
          multifile: function() {
            this.disable('xmarker');
            this.disable('ymarker');
            //this.makeSingle('paramselect');
          },
          singlefile: function() {
            this.enable('xmarker');
            this.enable('ymarker');
            //this.makeMulti('paramselect');
          },
          disable: function(id) {
            var $_sel = $('#'+id),
                $_option = $_sel.find('#markerall');
            $_option.prop('disabled',true);
            $_option.next().prop('selected', true); //select a marker next to overview
            this.refresh(id);
          },
          enable: function(id) {
            var $_sel = $('#'+id),
                $_option = $_sel.find('#markerall');
            $_option.prop('disabled',false);
            this.refresh(id);
          },
          makeSingle: function(id) {
            $('#'+id).removeAttr('multiple')
              .removeAttr('data-selected-text-format')
              .removeAttr('data-count-selected-text');
            this.refresh(id);
          },
          makeMulti: function(id) {
            var $_sel = $('#'+id);
            $_sel.attr('multiple', '')
              .attr('data-selected-text-format', 'count>1')
              .attr('data-count-selected-text', '{0} of {1}');
            this.refresh(id);    
          },
          refresh: function(id) {
            $('#'+id).selectpicker('refresh', 'deselectAll');
          }
        },
        filetree: {
          init: function(id, tid) {
            $('#'+id).fileTree({
              root: '<?php echo $RESULT_DIR;?>/'+tid+'/', //'../../Tasks/'+tid+'/', //'/Users/hkim/workspace/Workspace/gofcm/new/Tasks/'+tid+'/',//'../../Tasks/output/',
              script: '../bin/jqueryFileTree.php',
              expandSpeed: 300,
              collapseSpeed: 200,
              multiFolder: true
            }, function(file) {
            }); 
            $('#filesContainer').resizable();
          }
        }
      };
    //todo at submission("Show Result") - there should be a validation check on inputs (markers, files, populations and params)


      $(function(){
        $("#nav").load("../common/nav.php");
        _data.results();
        $('#imagesContainer').resizable();

        //manually calls DataTables' draw function for window resize
        $( window ).resize(function() { 
          if(_page.imageTable) {
            _page.imageTable.fnDraw();
          }
        });
      });
    </script>
  </body>
</html>
