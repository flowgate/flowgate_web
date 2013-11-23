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
  <style type="text/css">
    .centerSub {
      background-color:#eee; 
      border: 1px solid #888; 
      border-radius:3px;
    }

    .caret { margin-top: 10px; }

    UL.jqueryFileTree LI.ext_cb { padding-left:5px; }

    .imageTable { table-layout: fixed; }
    .imageTable td { border: 3px #7f7f7f solid; }
    .imageTable th { 
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
    .imageTable img {
      width: auto;
      height: auto;
      max-width: 150px;
      /*min-width: 50px;
      min-width: 85px;
      min-height: 85px;*/
    }

    .resultRow { margin: 0 !important;}
    .resultWell { padding: 3px 0px !important; }
    .selectorsCol { padding: 0 !important; }

    .markerSelect .bootstrap-select { width: 120px; }

    #filesContainer, #imagesContainer { padding: 0 !important; }

    .scrollableX { overflow: auto; overflow-y:hidden; white-space:nowrap;}
    .scrollableY { height: 175px; overflow: auto; overflow-x:hidden;}
    .crossing { background-color: #bbb; }
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
        <div class="col-md-3" id="filesContainer">
          <div class="well resultWell" id="files" style="height:100%;overflow:auto;">
            <div class="row col-md-12" style="margin-top:5px;">
              <button class="btn btn-xs btn-primary" type="button" id="fileAllButton">Select All</button>
              <button class="btn btn-xs btn-warning" type="button" id="fileNoneButton">Deselect All</button>
              <!--<p><span class="label label-important">Collapsing a directory deselects files under it!</span></p>-->
            </div>
            <div class="row col-md-12" id="fileNav" style="overflow:auto;"></div>
          </div>
        </div>
        <div class="col-md-9" id="imagesContainer">
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
            <div class="col-md-12 centerSub" id="overview">
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
                  <img src="../../images/ajax-loader.gif" id="loading-indicator" style="display:none;"/>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <p class="text-info">Each table cell with images is scrollable!</p>
                </div>
              </div>
              <div class="row" style="margin-top:5px;">
                <div class="col-md-12">
                  <div class="row">
                      <table id="imageTable" class="imageTable" style="width:100%;"></table>
                  </div>
                  <br/><strong style="color:red;">####### VERTICAL SCROLL</strong><br/><br/>
                  <div class="row">
                      <table id="imageTable1" class="imageTable" style="width:100%;"></table>
                  </div>
                  <br/><strong style="color:red;">####### MOCK2</strong><br/><br/>
                  <div class="row">
                      <table id="imageTable2" class="imageTable" style="width:100%;"></table>
                  </div>
                  <br/><strong style="color:red;">####### MOCK3</strong><br/><br/>
                  <div class="row">
                      <table id="imageTable3" class="imageTable" style="width:100%;"></table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>  
    </div>

    <script src="../../js/shared.js"></script>
    <script src="../../js/jquery.min.js"></script>
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.0.2/js/bootstrap.min.js"></script>
    <script src="../../js/jqueryFileTree.js"></script>
    <script src="../../js/bootstrap-select.js"></script>
    <script src="../../js/jquery.ui.min.js"></script>
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
        _data.results(); 
        $('#imagesContainer').resizable();
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
          data.ymarker = $('#ymarker').val();
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
          /*renderResults(
            {results:[
              {input:'input2_1.zip',par:'bins:8-11, density:4-6',p:'SomeProject',id:'3671b885-6e8d-4274-b510-33b8d8f2f480'},
              {input:'input2_2.zip',par:'bins:8-11, density:4-6',p:'SomeProject',id:'f25c08f4-a36b-4309-8692-2e67c9f64b0c'}
            ]}
          );*/
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
              var imgHtml = "<img src='$imgPath$'/>";

              if(data.success==='true' && data.taskDir && data.type) {
                var taskId = data.taskId, 
                    taskDir = data.taskDir, 
                    type = data.type, 
                    popIds = data.popIds,
                    xcols = data.xmarker,
                    ycols = data.ymarker,
                    dirs = data.dirs,
                    m_f = data.m_f, //multi-file?
                    files = data.files,
                    params = data.params,
                    m_p = data.m_p, //multi-parameter?
                    imgSuffix = popIds.replace(/,/g,'.') + '.color.highlighted',
                    thead = '<thead><tr><th id="name" style="width:7%;"></th>', 
                    tbody = '<tbody>';

                xcols = (!xcols || xcols==='all')?_data.cols:[xcols];
                ycols = (!ycols || ycols==='all')?_data.cols:[ycols];

                //original table
                //headers for x-axis
                for(var x=0;x<xcols.length;x++) {
                  thead+='<th>'+xcols[x]+'</th>';  
                }
                thead+='</tr></thead>';

                var rows = '';
                for(var y=0;y<ycols.length;y++) {
                  rows += '<tr><th headers="name">'+ycols[y]+'</th>';
                  var cells = '';
                  for(var x=0;x<xcols.length;x++) {
                    if(xcols[x] !== ycols[y]) {
                      cells += '<td><div class="scrollableX">';

                      for(var f=0;f<files.length;f++) {
                        var param_l = params.length;
                        var divW = (param_l>1?(100/param_l)*10:0), filePath = files[f]+'_out';

                        for(var p=0;p<param_l;p++) {
                          var paramPath = filePath+'/'+filePath+'_'+params[p][0]+'_'+params[p][1]+'/images/';
                          cells +=
                            '<div style="display:inline-block; text-align:center; border-right:1px solid;">'
                            +'  <div><img style="width:100%;" src="../../results/'+taskId+'/'+paramPath+ycols[y]+'.'+xcols[x]+'.'+imgSuffix+'.png"/></div>'
                            +'  <div>' + (m_f?files[f]:'')+(m_p?'['+params[p][0]+':'+params[p][1]+']':'') + '</div>'
                            +'</div>';
                        }  
                      }
                    } else { //put grey background for crossing X & Y markers
                      cells += '<td class="crossing"><div>';
                    }
                    cells+='</div></td>';
                  }
                  rows+=cells+'</tr>';
                }

                tbody+=rows + '</tbody>';
                $('#imageTable').html(thead+tbody);

                //vertical scroll
                thead = '<thead><tr><th id="name" style="width:7%;"></th>';
                tbody = '<tbody>';
                for(var x=0;x<xcols.length;x++) {
                  thead+='<th>'+xcols[x]+'</th>';  
                }
                thead+='</tr></thead>';

                var rows = '';
                for(var y=0;y<ycols.length;y++) {
                  rows += '<tr><th headers="name">'+ycols[y]+'</th>';
                  var cells = '';
                  for(var x=0;x<xcols.length;x++) {
                    if(xcols[x] !== ycols[y]) {
                      cells += '<td><div class="scrollableY">';

                      for(var f=0;f<files.length;f++) {
                        var param_l = params.length;
                        var divW = (param_l>1?(100/param_l)*10:0), filePath = files[f]+'_out';

                        for(var p=0;p<param_l;p++) {
                          var paramPath = filePath+'/'+filePath+'_'+params[p][0]+'_'+params[p][1]+'/images/';
                          cells +=
                            '<div style="text-align:center; border-right:1px solid;">'
                            +'  <div><img style="width:100%;" src="../../results/'+taskId+'/'+paramPath+ycols[y]+'.'+xcols[x]+'.'+imgSuffix+'.png"/></div>'
                            +'  <div>' + (m_f?files[f]:'')+(m_p?'['+params[p][0]+':'+params[p][1]+']':'') + '</div>'
                            +'</div>';
                        }  
                      }
                    } else { //put grey background for crossing X & Y markers
                      cells += '<td class="crossing"><div>';
                    }
                    cells+='</div></td>';
                  }
                  rows+=cells+'</tr>';
                }

                tbody+=rows + '</tbody>';
                $('#imageTable1').html(thead+tbody);

                //table1 with file header
                if
                tbody='';
                for(var f=0;f<files.length;f++) {
                  tbody+='<tr><th headers="name">'+files[f]+'</th>';
                  if(xcols[x] !== ycols[y]) {
                    tbody += '<div class="scrollable">';
                    var param_l = params.length;
                    var divW = (param_l>1?(100/param_l)*10:0), filePath = files[f]+'_out';

                    for(var p=0;p<param_l;p++) {
                      var paramPath = filePath+'/'+filePath+'_'+params[p][0]+'_'+params[p][1]+'/images/';
                      tbody+=
                        '<div style="display:inline-block; text-align:center; border-right:1px solid;">'
                        +'  <div><img style="width:100%;" src="../../results/'+taskId+'/'+paramPath+ycols[y]+'.'+xcols[x]+'.'+imgSuffix+'.png"/></div>'
                        +'  <div>' + (m_p?'['+params[p][0]+':'+params[p][1]+']':'') + '</div>'
                        +'</div>';
                    }  
                    tbody+='</div>';
                  }
                  tbody+='</tr>';
                }

                tbody+='</tbody>';
                $('#imageTable2').html(tbody);

                // for(var i=0;i<ycols.length;i++) {

                //   for(var j=0;j<xcols.length;j++) {
                //     tbody+='<td headers="'+xcols[j]+'_x">';

                $('#loading-indicator').hide();
                fluidImage();
              }
            }
          })
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

      /*
        fluid-images
        http://unstoppablerobotninja.com/entry/fluid-images/
      */
      var imgSizer = {
        Config : {
          imgCache : []
          ,spacer : "/path/to/your/spacer.gif"
        }
        ,collate : function(aScope) {
          var isOldIE = (document.all && !window.opera && !window.XDomainRequest) ? 1 : 0;
          if (isOldIE && document.getElementsByTagName) {
            var c = imgSizer;
            var imgCache = c.Config.imgCache;
            var images = (aScope && aScope.length) ? aScope : document.getElementsByTagName("img");
            for (var i = 0; i < images.length; i++) {
              images.origWidth = images.offsetWidth;
              images.origHeight = images.offsetHeight;
              imgCache.push(images);
              c.ieAlpha(images);
              images.style.width = "100%";
            }
            if (imgCache.length) {
              c.resize(function() {
                for (var i = 0; i < imgCache.length; i++) {
                  var ratio = (imgCache.offsetWidth / imgCache.origWidth);
                  imgCache.style.height = (imgCache.origHeight * ratio) + "px";
                }
              });
            }
          }
        }
        ,ieAlpha : function(img) {
          var c = imgSizer;
          if (img.oldSrc) {
            img.src = img.oldSrc;
          }
          var src = img.src;
          img.style.width = img.offsetWidth + "px";
          img.style.height = img.offsetHeight + "px";
          img.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" + src + "', sizingMethod='scale')"
          img.oldSrc = src;
          img.src = c.Config.spacer;
        }
       // Ghettomodified version of Simon Willison's addLoadEvent() -- http://simonwillison.net/2004/May/26/addLoadEvent/
        ,resize : function(func) {
          var oldonresize = window.onresize;
          if (typeof window.onresize != 'function') {
            window.onresize = func;
          } else {
            window.onresize = function() {
              if (oldonresize) {
                oldonresize();
              }
              func();
            }
          }
        }
      };
      var fluidImage = function() {
        if (document.getElementById && document.getElementsByTagName) {
          var aImgs = document.getElementById("imageTable").getElementsByTagName("img");
          imgSizer.collate(aImgs);
        }
      };
    //todo at submission("Show Result") - there should be a validation check on inputs (markers, files, populations and params)
    </script>
  </body>
</html>
