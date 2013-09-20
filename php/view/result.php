<?php
  
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>mockup with bootstrap</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="../../css/jqueryFileTree.css" rel="stylesheet">
  <link href="../../css/merged.css" rel="stylesheet">
  <link href="../../css/bootstrap-select.css" rel="stylesheet">
  <style type="text/css">
    .centerSub {
      background-color:#eee; 
      border: 1px solid #888; 
      border-radius:3px;
      padding: 15px;
    }

    UL.jqueryFileTree LI.ext_cb {
      padding-left:5px;
    }

    #overviewTable td { border: 3px #7f7f7f solid; }
    #overviewTable th { 
      font-family: Verdana,Arial,Helvetica,sans-serif;
      font-weight: normal;
      font-size: .9em;
      font-color: black;
      text-align: left;
      color: black;
      margin: 0;
      padding: 5px 12px 5px 12px;
      vertical-align: top;
      border: 3px solid #7f7f7f;
      background-color: #f5f5f5;
    }
    #overviewTable img {
      width: auto;
      height: auto;
      min-width: 85px;
      min-height: 85px;
    }

    .markerSelect .bootstrap-select {
      width: 150px;
    }
  </style>

</head>

  <body>
    <div id="nav"></div>
    <div class="row-fluid">
      <div class="span3" style="height:100%;">
        <div class="well" id="files" style="height:100%;overflow:auto;">
          <div class="row-fluid span12">
            <button class="btn btn-mini btn-warning" type="button" id="fileAllButton">Select All</button>
            <button class="btn btn-mini btn-inverse" type="button" id="fileNoneButton">Deselect All</button>
            <!--<p><span class="label label-important">Collapsing a directory deselects files under it!</span></p>-->
          </div>
          <div class="row-fluid span12" id="fileNav" style="overflow:auto;"></div>
        </div>
      </div>
      <div class="span9" style="">
        <div class="row-fluid">
          <div class="span12 centerSub">
            <div class="span11 offset1" id="details">
              <table>
                <tr>
                  <td><strong>Method Name</strong></td><td>FLOCK</td>
                </tr>
                <tr>
                  <td><strong>Method Version</strong></td><td>v0.1</td>
                </tr>
              </table>
            </div>
          </div>
        </div>
        <div class="row-fluid">
          <div class="span12 centerSub" id="overview">
            <div class="row-fluid" id="alert"></div>
            <div class="row-fluid">
              <div class="span12" style="margin-left:5px;">
                <div class="row-fluid">
                  <div class="span3">
                    <h6>Population</h6>
                    <select id="populselect" class="selectpicker" rel="populations" multiple data-selected-text-format="count>1" data-count-selected-text="{0} of {1} populations">
                    </select>
                  </div>
                  <div class="span2 markerSelect">
                    <h6>X-axis</h6>
                    <select id="xmarker" class="selectpicker"></select>
                  </div>
                  <div class="span2 markerSelect">
                    <h6>Y-axis</h6>
                    <select id="ymarker" class="selectpicker"></select>
                  </div>
                  <div class="span3">
                    <h6>Parameters</h6>
                    <select id="paramselect" class="selectpicker" multiple data-selected-text-format="count>1" data-count-selected-text="{0} of {1}"></select>
                  </div>
                </div>
              </div>
            </div>
            <div class="row-fluid">
              <div class="span2">
                <button class="btn btn-primary" type="button" id="updateButton">Show Result</button>
              </div>
              <div class="span10">
                <img src="../../images/ajax-loader.gif" id="loading-indicator" style="display:none;"/>
              </div>
            </div>
            <div class="row-fluid" style="margin-top:5px;">
              <div class="span12">
                <div id="flock_main">
                  <div id="flock_content">
                    <table id="overviewTable" style="width:100%;"></table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>  
    </div>

    <script src="../../js/jquery.min.js"></script>
    <script src="../../js/jqueryFileTree.js"></script>
    <script src="../../js/bootstrap-select.js"></script>
    <script>
      $(function(){
        $("#nav").load("../common/nav.php");

        var query = window.location.search.substring(1),
            vars = query.split("&"),
            taskId = ''; 
        for (var i=0;i<vars.length;i++) {
          var pair = vars[i].split("=");
          if(pair[0]==='taskId') {
            taskId = pair[1];
          }
        } 
        _data.meta(taskId);
        $('.selectpicker').selectpicker();
        _plugin.filetree.init('fileNav', taskId);
        _page.event.trigger();
      });

      var _page = {
        init: false, //initial load flag,
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
        event: {
          trigger: function() {
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
        alert: function(t) {
          $('#alert').append(
            '<div class="alert">'+
              '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
              '<strong>Warning!</strong> ' + t +
            '</div>');
        },
        validation: function() {
          var data = _page.data;
          data.populs = $('#populselect').val();
          data.xmarker = $('#xmarker').val();
          data.ymarker = $('#ymarker').val();
          data.params = $('#paramselect').val();
          if(data.populs!=null && data.populs.length>0 
            && data.xmarker!=null && data.xmarker.length>0
            && data.ymarker!=null && data.ymarker.length>0
            && data.files!=null && data.files.length>0
            && data.params!=null && data.params.length>0) {
            return true;
          } else {
            this.alert('Please select file(s) or Parameter(s).');
            return false;
          }
        }
      }; 
      var _data = {
        meta: function(tid) {
          var _this = this;
          $.ajax({
            url: '../bin/getTaskInfo.php?tid='+tid,
            async: false
          }).done(function(data) {
            if(data) {
              data = $.parseJSON(data);
              if(data.success==='true' && data.markers && data.populations) {
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
              }
            }
          });  
        },
        result: function() {
          var _this = this,
              _data = _page.data;
          $.ajax({
            url: '../bin/runFlock.php?tid='+_data.taskId+
                  '&f='+_data.files+'&ppl='+_data.ppl+'&pp='+_data.populs+
                  '&x='+_data.xmarker+'&y='+_data.ymarker+'&pr='+_data.params
          }).done(function(data) {
            if(data) {
              data = $.parseJSON(data);
              var imgHtml = "<img src='$imgPath$'/>";

              if(data.success==='true' && data.taskDir && data.type) {
                var taskId = data.taskId, 
                    taskDir = data.taskDir, 
                    type = data.type, 
                    popIds = data.popIds,
                    xcols = data.xmarker,
                    ycols = data.ymarker,
                    dirs = data.dirs,
                    m_f = data.m_f,
                    files = data.files,
                    params = data.params,
                    m_p = data.m_p,
                    imgSuffix = (type=='pop'?popIds.replace(/,/g,'.'):'all') + '.color.highlighted',
                    thead = '<thead><tr><th id="name" style="width:7%;"></th>', 
                    tbody = '<tbody>';

                xcols = (!xcols || xcols==='all')?_data.cols:[xcols];
                ycols = (!ycols || ycols==='all')?_data.cols:[ycols];

                //headers for x-axis
                for(var i=0;i<xcols.length;i++) {
                  thead+='<th id="'+xcols[i]+'_x">'+xcols[i]+'</th>';  
                }
                thead+='</tr></thead>';

                for(var i=0;i<ycols.length;i++) {
                  tbody+='<tr><th id="'+ycols[i]+'_y" headers="name">'+ycols[i]+'</th>';
                  for(var j=0;j<xcols.length;j++) {
                    tbody+='<td headers="'+xcols[j]+'_x">';
                    for(var f=0;f<files.length;f++) {
                      var divW = 100/params.length, filePath = files[f]+'_out';
                      for(var p=0;p<params.length;p++) {
                        var paramPath = filePath+'/'+filePath+'_'+params[p][0]+'_'+params[p][1]+'/'+type+'/';
                        tbody+=
                          '<div style="width:'+divW+'%;display:inline-block;">'
                          +'  <div>'
                          +'    <img src="../Tasks/'+taskId+'/'+paramPath+ycols[i]+'.'+xcols[j]+'.'+imgSuffix+'.png"/>'
                          +'  </div>'
                          +'  <div style="font-size:80%; text-align:center;">'
                          +     (xcols[j]!==ycols[i]?(m_f?files[f]:'')+(m_p?'['+params[p][0]+':'+params[p][1]+']':''):'&nbsp;')
                          +'  </div>'
                          +'</div>';
                      }  
                    }
                    tbody+='</td>';
                  }
                  tbody+='</tr>';
                }
                tbody+='</tbody>';
                $('#overviewTable').html(thead+tbody);

                $('#loading-indicator').hide();
              }
            }
          });
        },
        popul: function(cnt) {
          var $selecObj = $('#populselect'),
              count = 0;
          for(var i=1;i<=cnt;i++) {
            $selecObj.append(
              _page.html.opt_dc.replace("$c$", "popul"+(i>24?i%25+1:i))
                .replace("$t$", "Population "+i)
                .replace("$v$",i)
            );  
          }

          // $.each(pops, function(i,pop){
          //   $selecObj.append(
          //     _page.html.opt_dc.replace("$c$", "popul"+(i>24?i%25+1:i))
          //       .replace("$t$", "Population "+i+"("+pop+")")
          //       .replace("$v$",i)
          //     );
          //   count++;
          // });
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
          var opts = '', param_keys = Object.keys(params), param_vals = [], opts_arr=[];

          $.each(param_keys, function(i,v) { //range or single value
            var val = params[v], vals = [], tokens;
            if(val.indexOf("-")>0) {
              tokens = val.split("-");
              for(var i=parseInt(tokens[0]);i<=parseInt(tokens[1]);i++) {
                vals.push(i);
              }
            } else {
              vals.push(parseInt(val));
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
          $("#paramselect").append(opts).selectpicker();
        }
      };
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
              root: '../../Tasks/'+tid+'/', //'/Users/hkim/workspace/Workspace/gofcm/new/Tasks/'+tid+'/',//'../../Tasks/output/',
              script: '../fileTree/jqueryFileTree.php',
              expandSpeed: 300,
              collapseSpeed: 200,
              multiFolder: true
            }, function(file) {
            }); 
          }
        }
      };        

    //todo at submission("Show Result") - there should be a validation check on inputs (markers, files, populations and params)
    </script>
  </body>
</html>