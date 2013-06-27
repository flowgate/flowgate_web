// Global Variables
var taskId = 0;
var fileInputId = 0;
var taskName = "FLOCK Task";
var editConfig;
var overViewConfig;
var tableConfig;
var markerByMarkerConfig;


var loadOverview = function(type) {
    sessionCheck();
    Ext.get('flock_main').mask("Loading ...",'x-mask-loading');
    var conn = new Ext.data.Connection({timeout: 120000});
    //var url = '/flockWeb/loadFlockOverview.do?taskId=' + taskId +
    // 			'&fileInputId=' + fileInputId + "&type=" + type;
    var url = './bin/loadOverview.php?taskId=' + taskId +'&fileInputId=' + fileInputId + "&type=" + type;

    conn.request( {
        url: url,
        success: function(resp,opt) {
            overviewConfig = Ext.util.JSON.decode(resp.responseText);
            buildOverviewGrid();
            Ext.get('flock_main').unmask();
        },
        failure: function(resp,opt) {
            Ext.get('flock_main').dom.innerHTML = '<span style="color:red;">Failure to Load the Overview Screen</span>';
            Ext.get('flock_main').unmask();
        }
    });
};

/*
 * Build the grid to display the marker by marker images
*/
var buildOverviewGrid = function() {
    var store = new Ext.data.JsonStore({
        data: overviewConfig,
        root: 'rows',
        fields: overviewConfig.fields
    });

    var overviewGrid = new Ext.grid.LockingGridPanel({
        title: 'Click on any thumbnail image to view and adjust populations',
        store: store,
        frame:false,
        height: 600,
        enableColumnMove: false,
//        width: 800,
        viewConfig: {
            forceFit: false
        },
        columns: overviewConfig.columns,
        sm: new Ext.grid.CellSelectionModel({singleSelect:true,
            listeners: {
                cellselect: {
                    fn: function(sm,rowIndex,colIndex) {
                        var cell = sm.getSelectedCell();
                        var record = store.getAt(cell[0]);
                        var fieldName = overviewGrid.getColumnModel().getDataIndex(cell[1]);
                        var data = record.get(fieldName).split(";");
                        //loadMarkerByMarkerView(data[1],data[3]);
                        // Make sure a valid cell was clicked.
                        if (data[1] == undefined) {
        					return;
        				}
        				if (data[1] == data[3]) {
        					return;
        				}
                        loadAdjustCentroid(data[1],data[3],overviewConfig.popId);
                    }
                }
            }
        })
    });

    Ext.get('flock_content').dom.innerHTML = "";
    overviewGrid.render("flock_content");
};

var loadTableView = function(type) {
	resetSessionTimeout();
    Ext.get('flock_main').mask("Loading ...",'x-mask-loading');
    var conn = new Ext.data.Connection({timeout: 120000});
//    var url = '/flockWeb/loadFlockTable.do?taskId=' + taskId +
//    			'&fileInputId=' + fileInputId + "&type=" + type;
    var url = './bin/loadFlockTable.php?taskId=' + taskId +
    			'&fileInputId=' + fileInputId + "&type=" + type;
    conn.request( {
        url: url,
        success: function(resp,opt) {
            tableConfig = Ext.util.JSON.decode(resp.responseText);
            buildTableGrid();
            Ext.get('flock_main').unmask();
        },
        failure: function(resp,opt) {
            Ext.get('flock_main').dom.innerHTML = '<span style="color:red;">Failure to Load the Summary Screen</span>';
            Ext.get('flock_main').unmask();
        }
    });
};

/*
 * Build the grid to display the Summary information
*/
var buildTableGrid = function() {
    var store = new Ext.data.JsonStore({
        data: tableConfig,
        root: 'tableRows',
        fields: tableConfig.tableFields
    });


    var tableGrid = new Ext.grid.LockingEditorGridPanel({
        title: 'Key: 1=negative, 2=low, 3=positive, 4=high',
        store: store,
        frame:false,
        height: 600,
        clicksToEdit: 1,
//        width: 800,
        viewConfig: {
            forceFit: false
        },
        columns: tableConfig.tableColumns,
        listeners: {
        	/*afteredit: function(e) {
        		var conn = new Ext.data.Connection();
        		var url = "/flockWeb/updateFlockCellType.do";
        		if (e.field == "m3") {
        			url = "/flockWeb/updateFlockDescription.do";
        		}
        			
        		conn.request({
        			url: url,
        			params: {
        				taskId: taskId,
        				fileInputId: fileInputId,
        				popId: e.record.data.m0,
        				value: e.value
        			},
        			success: function(resp,opt) {
        				e.record.commit();
        			},
        			failure: function(resp,opt) {
        				// Do nothing
        			}
        		});
        	}*/
        }
    });

    Ext.get('flock_content').dom.innerHTML = "";
    tableGrid.render("flock_content");
};

var loadCentroidView = function(type) {
	resetSessionTimeout();
    Ext.get('flock_main').mask("Loading ...",'x-mask-loading');
    var conn = new Ext.data.Connection({timeout: 120000});
//    var url = '/flockWeb/loadFlockCentroid.do?taskId=' + taskId +
//    			'&fileInputId=' + fileInputId + "&type=" + type;
    var url = './bin/loadFlockCentroid.php?taskId=' + taskId +
    			'&fileInputId=' + fileInputId + "&type=" + type;
    conn.request( {
        url: url,
        success: function(resp,opt) {
            tableConfig = Ext.util.JSON.decode(resp.responseText);
            buildCentroidGrid();
            Ext.get('flock_main').unmask();
        },
        failure: function(resp,opt) {
            Ext.get('flock_main').dom.innerHTML = '<span style="color:red;">Failure to Load the Centroid Screen</span>';
            Ext.get('flock_main').unmask();
        }
    });
};

/*
 * Build the grid to display the Centroid table
*/
var buildCentroidGrid = function() {
    var store = new Ext.data.JsonStore({
        data: tableConfig,
        root: 'tableRows',
        fields: tableConfig.tableFields
    });

    var tableGrid = new Ext.grid.LockingGridPanel({
        title: '',
        store: store,
        frame:false,
        height: 400,
//        width: 800,
        viewConfig: {
            forceFit: false
        },
        columns: tableConfig.tableColumns
    });

    Ext.get('flock_content').dom.innerHTML = "";
    tableGrid.render("flock_content");
};

var loadMfiView = function(type) {
    resetSessionTimeout();
    Ext.get('flock_main').mask("Loading ...",'x-mask-loading');
    var conn = new Ext.data.Connection({timeout: 120000});
    //var url = '/flockWeb/loadFlockMfi.do?taskId=' + taskId +
    //  '&fileInputId=' + fileInputId + "&type=" + type;
    var url = './bin/loadFlockMfi.php?taskId=' + taskId +
                        '&fileInputId=' + fileInputId;
    conn.request( {
        url: url,
        success: function(resp,opt) {
            tableConfig = Ext.util.JSON.decode(resp.responseText);
            buildMfiGrid();
            Ext.get('flock_main').unmask();
        },
        failure: function(resp,opt) {
            Ext.get('flock_main').dom.innerHTML = '<span style="color:red;">Failure to Load the MFI Screen</span>';
            Ext.get('flock_main').unmask();
        }
    });
};

/*
 * Build the grid to display Mean Flouresence Intensity grid
*/
var buildMfiGrid = function() {
    var store = new Ext.data.JsonStore({
        data: tableConfig,
        root: 'tableRows',
        fields: tableConfig.tableFields
    });

    var tableGrid = new Ext.grid.LockingGridPanel({
        title: '',
        store: store,
        frame:false,
        height: 400,
//        width: 800,
        viewConfig: {
            forceFit: false
        },
        columns: tableConfig.tableColumns
    });

    Ext.get('flock_content').dom.innerHTML = "";
    tableGrid.render("flock_content");
};

var colorKey = function() {
    var html = "<table width=\"95%\">" +
                '<tr><td width="100">Population 1</td><td style="background-color:red;width:50;">&nbsp;</td></tr>' +
                '<tr><td width="100">Population 2</td><td style="background-color:yellow;width:50;">&nbsp;</td></tr>' +
                '<tr><td width="100">Population 3</td><td style="background-color:#008000;width:50;">&nbsp;</td></tr>' +
                '<tr><td width="100">Population 4</td><td style="background-color:blue;width:50;">&nbsp;</td></tr>' +
                '<tr><td width="100">Population 5</td><td style="background-color:orange;width:50;">&nbsp;</td></tr>' +               
                '<tr><td width="100">Population 6</td><td style="background-color:#8A2BE2;width:50;">&nbsp;</td></tr>' +
                '<tr><td width="100">Population 7</td><td style="background-color:#808000;width:50;">&nbsp;</td></tr>' +                                             
                '<tr><td width="100">Population 8</td><td style="background-color:cyan;width:50;">&nbsp;</td></tr>' +
                '<tr><td width="100">Population 9</td><td style="background-color:magenta;width:50;">&nbsp;</td></tr>' +               
                '<tr><td width="100">Population 10</td><td style="background-color:green;width:50;">&nbsp;</td></tr>' +
                '<tr><td width="100">Population 11</td><td style="background-color:#000080;width:50;">&nbsp;</td></tr>' +
                '<tr><td width="100">Population 12</td><td style="background-color:#F08080;width:50;">&nbsp;</td></tr>' +
                '<tr><td width="100">Population 13</td><td style="background-color:#800080;width:50;">&nbsp;</td></tr>' +
                '<tr><td width="100">Population 14</td><td style="background-color:#F0E68C;width:50;">&nbsp;</td></tr>' +
                '<tr><td width="100">Population 15</td><td style="background-color:#8FBC8F;width:50;">&nbsp;</td></tr>' +                                
                '<tr><td width="100">Population 16</td><td style="background-color:darkGray;width:50;">&nbsp;</td></tr>' +
                '<tr><td width="100">Population 17</td><td style="background-color:#008080;width:50;">&nbsp;</td></tr>' +                                
                '<tr><td width="100">Population 18</td><td style="background-color:#9932CC;width:50;">&nbsp;</td></tr>' +
                '<tr><td width="100">Population 19</td><td style="background-color:#FF7F50;width:50;">&nbsp;</td></tr>' +
                '<tr><td width="100">Population 20</td><td style="background-color:#FFD700;width:50;">&nbsp;</td></tr>' +                                                                                
                '<tr><td width="100">Population 21</td><td style="background-color:#008B8B;width:50;">&nbsp;</td></tr>' +
                '<tr><td width="100">Population 22</td><td style="background-color:#800000;width:50;">&nbsp;</td></tr>' +
                '<tr><td width="100">Population 23</td><td style="background-color:#5F9EA0;width:50;">&nbsp;</td></tr>' +
                '<tr><td width="100">Population 24</td><td style="background-color:pink;width:50;">&nbsp;</td></tr>' +                                                                               
                '<tr><td width="100">Population 25</td><td style="background-color:gray;width:50;">&nbsp;</td></tr>' +
                '</table>';

    var colorKeyWindow = new Ext.Window({
        title: 'Population Color Key',
        width: 150,
        height: 450,
        frame: true,
        html: html
    });

    colorKeyWindow.show();
};


var deletePopulation = function(idx1,idx2,popIdx) {
	resetSessionTimeout();
    Ext.get('flock_main').mask("Loading ...",'x-mask-loading');
    var conn = new Ext.data.Connection({timeout: 120000});
    //var url = '/flockWeb/deleteFlockPopulation.do?taskId=' + taskId + '&fileInputId=' + fileInputId
    var url = './bin/deleteFlockPopulation.php?taskId=' + taskId + '&fileInputId=' + fileInputId
    			+ "&popIdx=" + popIdx;
    conn.request( {
        url: url,
        success: function(resp,opt) {
            populationConfig = Ext.util.JSON.decode(resp.responseText);
            loadAdjustCentroid(idx1,idx2,populationConfig.popId);
            //loadOverview("color");
            //deletePopulationView();
            //Ext.get('flock_main').unmask();
        },
        failure: function(resp,opt) {
            Ext.get('flock_main').dom.innerHTML = '<span style="color:red;">Failure to delete the population</span>';
            Ext.get('flock_main').unmask();
        }
    });
};



var loadAdjustCentroid = function(idx1,idx2,popId) {
    resetSessionTimeout();
    Ext.get('flock_main').mask("Loading ...",'x-mask-loading');
    var conn = new Ext.data.Connection({timeout: 120000});
//    var url = '/flockWeb/loadCentroidAdjust.do?taskId=' + taskId
//    	+ '&fileInputId=' + fileInputId + "&idx1=" + idx1 + "&idx2=" + idx2 + "&popIdx=" + popId;
    var url = './bin/loadCentroidAdjust.php?taskId=' + taskId
    	+ '&fileInputId=' + fileInputId + "&idx1=" + idx1 + "&idx2=" + idx2 + "&popIdx=" + popId;
    conn.request( {
        url: url,
        success: function(resp,opt) {
            editConfig = Ext.util.JSON.decode(resp.responseText);
            var xValue = editConfig.data.xValue;
            var yValue = editConfig.data.yValue;
            var xCoord = editConfig.data.xCoord;
            var yCoord = editConfig.data.yCoord;
               
            var topHTML = '<center>Centroid Location - X: <span id="xValue">' + xValue + '</span> &nbsp;&nbsp;Y:<span id="yValue">'
            	+ yValue + '</span></center>';
            	
            var popName = "Population: " + popId + '<br>X Axis: ' + editConfig.data.m1Name + '&nbsp;&nbsp;&nbsp;Y Axis: '
              + editConfig.data.m2Name;
            	
            Ext.get('flock_content').dom.innerHTML = '';
            var boxHTML = "<center>Moving a Centroid</center>" +
            	"Step 1: Select 2-D image for editing.<br>" +
            	"Step 2: Place cursor over 'C' in image, click and move.<br>" +
            	"Step 3: Select 'Save Changes' to view change in population<br><br>" +
            	"<center>Deleting a Population</center>" +
            	"Step 1: Click on the 'Delete Population' button<br>" +
            	"Population is deleted (the events are assigned to other populations) and the population will be removed from the 2-D display, Summary Table, Centroid Table and MFI table<br>";
            
            var tableGrid = new Ext.Panel({
            	title: '',
            	layout: 'table',
            	border: true,
            	renderTo: 'flock_content',
            	layoutConfig: {
            		columns: 2
            	},
            	items: [{
            		cellCls: 'alignTop',
            		items: [{
            			html: popName
            		},{
            			html: '<div id="leftSideTop"></div>',
            			cellCls: 'alignTop'
            		},{
            			html: topHTML
            		},{
            			html: '<div id="leftSideBottom"></div>'
            		}, {
            			html: boxHTML
            		}]
            	}, {
            		html: '<div id="rightSide"></div>'
            	}]
            });
            	
              
            var r = Math.floor(Math.random()*1001);
            
            var imageURL = "background-image:url('" + editConfig.data.srcURL
                + encodeURIComponent(editConfig.data.m1Name) + "."
                + encodeURIComponent(editConfig.data.m2Name) + "." + editConfig.data.popIdx +
                ".color.highlighted.png&random=" + r + "')";

            var adjustToolBar = new Ext.Toolbar({
					renderTo: 'leftSideBottom',
					items: [
					{
						xtype: 'tbbutton',
						text: 'Save Changes',
						handler: function() {
							recompute();
						}
					},
					{
						xtype: 'tbseparator'
					},
					{
						xtype: 'tbbutton',
						text: 'Split Centroid',
						handler: function() {
							loadSplitCentroid(idx1,idx2,popId);
						}
					}
				]
			});
			
			if (editConfig.populations > 1) {
				adjustToolBar.add({
					xtype: 'tbseparator'
				});
				adjustToolBar.add(
					{
						xtype: 'tbbutton',
						text: 'Delete Population',
						handler: function() {
							deletePopulation(idx1,idx2,popId);
						}
					}
				);
			}

			//var popName = "Population: " + popId + '<br>' + editConfig.data.m1Name + ' by '
            //  + editConfig.data.m2Name;
            
            var imagePanel = new Ext.Panel( {
                id: 'imagePanel',
                title: '',
                border: false,
                height: 300,
                width: 300,
                renderTo: 'leftSideTop',
                bodyStyle: imageURL,
                divs: [ {x:editConfig.data.xCoord-6, y:editConfig.data.yCoord-6} ],
                tpl: new Ext.XTemplate(
                    '<tpl for="divs">',
                    '<div id="item-{#}" class="item draggable" style="top:{y}px;left:{x}px;">C</div>',
                    '</tpl>'
                ),
                
                afterRender:function() {
                    Ext.Panel.prototype.afterRender.apply(this, arguments);
                    this.tpl.overwrite(this.body, this);

                    // setup D&D
                    var items = this.body.select('div.draggable');

                    // loop through draggable items
                    items.each(function(el, ce, index) {
                        // create DDProxy
                        el.dd = new Ext.dd.DDProxy(el.dom.id, 'group');

                        // configure the proxy
                        Ext.apply(el.dd, {
                            win:this,
                            itemIndex:index,

                            // runs on drag start
                            // create nice proxy and constrain it to body
                            startDrag:function(x, y) {
                                var dragEl = Ext.get(this.getDragEl());
                                var el = Ext.get(this.getEl());

                                dragEl.applyStyles({border:'','z-index':this.win.lastZIndex + 1});
                                dragEl.update(el.dom.innerHTML);
                                dragEl.addClass(el.dom.className + ' dd-proxy');

                                this.constrainTo(this.win.body);
                            },

                            // runs on drag end
                            // save new position of item and fire itemdrag event to save state
                            afterDrag:function() {
                                var el = Ext.get(this.getEl());
                                var div = this.win.divs[this.itemIndex];
                                div.x = el.getLeft(true);
                                div.y = el.getTop(true);
                                this.win.fireEvent('itemdrag', this);
                                //xValue = parseInt((editConfig.data.max * div.x)/300) +5;
                                //yValue = parseInt(editConfig.data.max - ((editConfig.data.max * div.y)/300)+5);
                                //xValue = parseInt((editConfig.data.max * (div.x + 5))/300);
                                //yValue = parseInt(editConfig.data.max - (editConfig.data.max * (div.y + 5)/300));
								var x = parseInt(div.x) + 6;
								var y = 300 - parseInt(div.y) - 6;
						        var min = parseInt(editConfig.data.min);
								var max = parseInt(editConfig.data.max);
								var range = max - min + 1;
								//alert("X: " + div.x + " Y: " + div.y + " X: " + x + " Y: " + y + " Min: " + min + " Max: " + max + " Range: " + range);
								xValue = parseInt(((x * range)/300)+min);
								yValue = parseInt(((y * range)/300)+min);
                                Ext.get("xValue").dom.innerHTML = xValue;
                                Ext.get("yValue").dom.innerHTML = yValue;
                            } // eo function afterDrag

                        }) // eo apply
                    }, this); // eo each
                 } // eo function afterRender
 
          });
        
        var store = new Ext.data.JsonStore({
            data: editConfig,
            root: 'rows',
            fields: editConfig.fields
         });

         var markerByMarkerGrid = new Ext.grid.GridPanel({
             title: '',
             store: store,
             frame:false,
             border: false,
             height: 600,
             hideHeaders: true,
             enableHdMenu: false,
             renderTo: 'rightSide',
             enableColumnMove: false,
             width: 500,
             viewConfig: {
                forceFit: false
             },
             columns: editConfig.columns,
             sm: new Ext.grid.CellSelectionModel({singleSelect:true,
        	 listeners: {
        		cellselect: {
        			fn: function(sm,rowIndex,colIndex) {
        				var cell = sm.getSelectedCell();
        				var record = store.getAt(cell[0]);
        				var fieldName = markerByMarkerGrid.getColumnModel().getDataIndex(cell[1]);
        				var data = record.get(fieldName).split(";");
        				if (data[0] == '') {
        					return;
        				}
 
        				loadAdjustCentroid(data[1],data[3],data[4]);
        			}
        		}
        	}
       	})
         });
         
         Ext.get('flock_main').unmask();

        },
        failure: function(resp,opt) {
            Ext.get('flock_content').dom.innerHTML = '<span style="color:red">Failure to Load the Centroid Adjustment Screen</span>';
            Ext.get('flock_main').unmask();
        }
    });
};

var loadSplitCentroid = function(idx1,idx2,popId) {
	resetSessionTimeout();
    Ext.get('flock_main').mask("Loading ...",'x-mask-loading');
    var conn = new Ext.data.Connection({timeout: 120000});
//    var url = '/flockWeb/loadCentroidAdjust.do?taskId=' + taskId
//    	+ '&fileInputId=' + fileInputId + "&idx1=" + idx1 + "&idx2=" + idx2 + "&popIdx=" + popId;
    var url = './bin/loadCentroidAdjust.php?taskId=' + taskId
    	+ '&fileInputId=' + fileInputId + "&idx1=" + idx1 + "&idx2=" + idx2 + "&popIdx=" + popId;
    conn.request( {
        url: url,
        success: function(resp,opt) {
            editConfig = Ext.util.JSON.decode(resp.responseText);
            var xValue = editConfig.data.xValue;
            var yValue = editConfig.data.yValue;
            var xCoord = editConfig.data.xCoord;
            var yCoord = editConfig.data.yCoord;
 
            Ext.get('flock_content').dom.innerHTML = '';
            var topHTML = '<center>' + 
            	'Centroid Location - X: <span id="x1Value">' + xValue + '</span> &nbsp;&nbsp;Y:<span id="y1Value">'+ yValue + '</span><br>' +
            	'New Centroid Location - X: <span id="x2Value">' + xValue + '</span> &nbsp;&nbsp;Y:<span id="y2Value">'+ yValue + '</span><br>' +
            	'</center>';
            
            var boxHTML = "<center>Splitting a Centroid</center>" +
            	"Step 1: Select 2-D image for editing.<br>" +
            	"Step 2: Place cursor over 'N' in image, click and move.<br>" +
            	"Step 3: Select 'Save Changes' to view change in population<br>" +
            	"New image has been added to display populations<br><br>" + 
            	"<center>Deleting a Population</center>" +
            	"Step 1: Click on the 'Delete Population' button<br>" +
            	"Population is deleted (the events are assigned to other populations) and the population will be removed from the 2-D display, Summary Table, Centroid Table and MFI table<br>";
            	
            var popName = "Population: " + popId + '<br>X Axis: ' + editConfig.data.m1Name + '&nbsp;&nbsp;&nbsp;Y Axis: '
              + editConfig.data.m2Name;
              
             var tableGrid = new Ext.Panel({
            	title: '',
            	layout: 'table',
            	border: true,
            	renderTo: 'flock_content',
            	layoutConfig: {
            		columns: 2
            	},
            	items: [{
            		cellCls: 'alignTop',
            		items: [{
            			html: popName
            		},{
            			html: '<div id="leftSideTop"></div>',
            			cellCls: 'alignTop'
            		},{
            			html: topHTML
            		},{
            			html: '<div id="leftSideBottom"></div>'
            		},{
            		            		}, {
            			html: boxHTML
            		}]
            	}, {
            		html: '<div id="rightSide"></div>'
            	}]
            });
            
            //var topHTML = editConfig.data.m1Name + ' by ' + editConfig.data.m2Name + '&nbsp;&nbsp;Population: ' + popId
            //	+ '&nbsp;&nbsp;X1: <span id="x1Value">' + xValue + '</span>'
            // 	+ '&nbsp;&nbsp;Y1: <span id="y1Value">' + yValue + '</span>'
            //	+ '&nbsp;&nbsp;X2: <span id="x2Value">' + xValue + '</span>'
            //	+ '&nbsp;&nbsp;Y2: <span id="y2Value">' + yValue + '</span>'
            //	+ '<br><button type="button" onClick="recomputeSplit();">Save Changes</button>'
            //	+ '&nbsp;&nbsp;&nbsp;<button type="button" onClick="loadAdjustCentroid(' + idx1 + ', ' + idx2
            //  + ', ' + popId + ');">Adjust Centroid View</button>'
            //  + '&nbsp;&nbsp;&nbsp;<button type="button" onClick="deletePopulation(' + popId + ');">Delete Population</button></div>';;
            	
            
            var x1 = parseInt(xCoord) - 6;
            var y1 = parseInt(yCoord) - 6;
            var x2 = parseInt(xCoord) + 6;
            var y2 = parseInt(yCoord) - 6;
            
            
            var r = Math.floor(Math.random()*1001)
            var imageURL = "background-image:url('" + editConfig.data.srcURL
                + encodeURIComponent(editConfig.data.m1Name) + "."
                + encodeURIComponent(editConfig.data.m2Name) + "." + editConfig.data.popIdx +
                ".color.highlighted.png&random=" + r + "')";

            var adjustToolBar = new Ext.Toolbar({
					renderTo: 'leftSideBottom',
					items: [
					{
						xtype: 'tbbutton',
						text: 'Save Changes',
						handler: function() {
							recomputeSplit();
						}
					},
					{
						xtype: 'tbseparator'
					},
					{
						xtype: 'tbbutton',
						text: 'Move Centroid',
						handler: function() {
							loadAdjustCentroid(idx1,idx2,popId);
						}
					}
				]
			});
			
			if (editConfig.populations > 1) {
				adjustToolBar.add({
					xtype: 'tbseparator'
				});
				adjustToolBar.add(
					{
						xtype: 'tbbutton',
						text: 'Delete Population',
						handler: function() {
							deletePopulation(idx1,idx2,popId);
						}
					}
				);
			}

            
            var imagePanel = new Ext.Panel( {
                id: 'imagePanel',
                border: false,
                title: '',
                height: 300,
                width: 300,
                renderTo: 'leftSideTop',
                bodyStyle: imageURL,
                divs: [ {x:x1, y:y1, c: 'C'},{x:x2, y:y2, c: 'N'}],             
                tpl: new Ext.XTemplate(
                    '<tpl for="divs">',
                    '<div id="item-{#}" class="item draggable" style="top:{y}px;left:{x}px;">{c}</div>',
                    '</tpl>'
                ),
                
                afterRender:function() {
                    Ext.Panel.prototype.afterRender.apply(this, arguments);
                    this.tpl.overwrite(this.body, this);

                    // setup D&D
                    var items = this.body.select('div.draggable');

                    // loop through draggable items
                    items.each(function(el, ce, index) {
                        // create DDProxy
                        el.dd = new Ext.dd.DDProxy(el.dom.id, 'group');

                        // configure the proxy
                        Ext.apply(el.dd, {
                            win:this,
                            itemIndex:index,

                            // runs on drag start
                            // create nice proxy and constrain it to body
                            startDrag:function(x, y) {
                                var dragEl = Ext.get(this.getDragEl());
                                var el = Ext.get(this.getEl());

                                dragEl.applyStyles({border:'','z-index':this.win.lastZIndex + 1});
                                dragEl.update(el.dom.innerHTML);
                                dragEl.addClass(el.dom.className + ' dd-proxy');

                                this.constrainTo(this.win.body);
                            },

                            // runs on drag end
                            // save new position of item and fire itemdrag event to save state
                            afterDrag:function() {
                                var el = Ext.get(this.getEl());
                                var div = this.win.divs[this.itemIndex];
                                div.x = el.getLeft(true);
                                div.y = el.getTop(true);
                                this.win.fireEvent('itemdrag', this);
                                
                                //xValue = parseInt((editConfig.data.max * div.x)/300) +5;
                                //yValue = parseInt(editConfig.data.max - ((editConfig.data.max * div.y)/300)+5);
                                //xValue = parseInt((editConfig.data.max * (div.x + 5))/300);
                                //yValue = parseInt(editConfig.data.max - (editConfig.data.max * (div.y + 5)/300));
                                var x = parseInt(div.x) + 6;
								var y = 300 - parseInt(div.y) - 6;
								var min = parseInt(editConfig.data.min);
								var max = parseInt(editConfig.data.max);
								var range = max - min + 1;
								xValue = parseInt(((x * range)/300)+min);
								yValue = parseInt(((y * range)/300)+min);
								//alert("X: " + div.x + " Y: " + div.y + " X: " + x + " Y: " + y + " Min: " + min + " Max: " + max + " Range: " + range);
                                if (el.id == "item-1") {
                                    Ext.get("x1Value").dom.innerHTML = xValue;
                                    Ext.get("y1Value").dom.innerHTML = yValue;
                                } else {
                                    Ext.get("x2Value").dom.innerHTML = xValue;
                                    Ext.get("y2Value").dom.innerHTML = yValue;
                                }
                                
                            } // eo function afterDrag

                        }) // eo apply
                    }, this); // eo each
                 } // eo function afterRender
 
        });
        
        var store = new Ext.data.JsonStore({
            data: editConfig,
            root: 'rows',
            fields: editConfig.fields
         });

         var markerByMarkerGrid = new Ext.grid.GridPanel({
             title: '',
             store: store,
             frame:false,
             border: false,
             height: 600,
             hideHeaders: true,
             enableHdMenu: false,
             renderTo: 'rightSide',
             enableColumnMove: false,
             width: 500,
             viewConfig: {
                forceFit: false
             },
             columns: editConfig.columns,
                         sm: new Ext.grid.CellSelectionModel({singleSelect:true,
        	 listeners: {
        		cellselect: {
        			fn: function(sm,rowIndex,colIndex) {
        				var cell = sm.getSelectedCell();
        				var record = store.getAt(cell[0]);
        				var fieldName = markerByMarkerGrid.getColumnModel().getDataIndex(cell[1]);
        				var data = record.get(fieldName).split(";");
         				if (data[0] == '') {
        					return;
        				}
        				loadSplitCentroid(data[1],data[3],data[4]);
        			}
        		}
        	}
       	})

         });
         
         Ext.get('flock_main').unmask();
 
        },
        failure: function(resp,opt) {
            Ext.get('flock_content').dom.innerHTML = '<span style="color:red">Failure to Load the Centroid Split Adjustment Screen</span>';
            Ext.get('flock_main').unmask();
        }
    });
};
                    
var recompute = function() {
    resetSessionTimeout();
    Ext.get('flock_main').mask("Loading ...",'x-mask-loading');
    var conn = new Ext.data.Connection({timeout: 120000});
    var xValue = Ext.get('xValue').dom.innerHTML;
    var yValue = Ext.get('yValue').dom.innerHTML;
    var idx1 = editConfig.data.m1Idx
    var idx2 = editConfig.data.m2Idx
    var popIdx = editConfig.data.popIdx;
    	
//    var url = '/flockWeb/recompute.do?taskId=' + taskId + '&fileInputId=' + fileInputId +
    var url = './bin/recompute.php?taskId=' + taskId + '&fileInputId=' + fileInputId +
    	'&idx1=' + idx1 + '&idx2=' + idx2 + '&xValue=' + xValue + '&yValue='
        + yValue + '&popIdx=' + popIdx;
    conn.request( {
        url: url,
        success: function(resp,opt) {
            config = Ext.util.JSON.decode(resp.responseText);
            loadAdjustCentroid(idx1,idx2,popIdx);
            //loadMarkerByMarkerView(idx1,idx2);
        },
        failure: function(resp,opt) {
            Ext.get('flock_main').dom.innerHTML = '<span style="color:red;">Failure to adjust the centroid</span>';
            Ext.get('flock_main').unmask();
        }
    });
};

var recomputeSplit = function() {
	resetSessionTimeout();
    Ext.get('flock_main').mask("Loading ...",'x-mask-loading');
    var conn = new Ext.data.Connection({timeout: 120000});
    var x1Value = Ext.get('x1Value').dom.innerHTML;
    var y1Value = Ext.get('y1Value').dom.innerHTML;
    var x2Value = Ext.get('x2Value').dom.innerHTML;
    var y2Value = Ext.get('y2Value').dom.innerHTML;
    var idx1 = editConfig.data.m1Idx
    var idx2 = editConfig.data.m2Idx
    var popIdx = editConfig.data.popIdx;
    	
//    var url = '/flockWeb/recomputeSplit.do?taskId=' + taskId + '&fileInputId=' + fileInputId
    var url = './bin/recomputeSplit.php?taskId=' + taskId + '&fileInputId=' + fileInputId
    	+ '&idx1=' + idx1 + '&idx2=' + idx2 + '&x1Value=' + x1Value + '&y1Value=' + y1Value
    	+ '&x2Value=' + x2Value + '&y2Value=' + y2Value
    	+ '&popIdx=' + popIdx;
    	
    conn.request( {
        url: url,
        success: function(resp,opt) {
            config = Ext.util.JSON.decode(resp.responseText);
            loadAdjustCentroid(idx1,idx2,popIdx);
            //loadSplitCentroid(idx1,idx2,popIdx);
            //loadMarkerByMarkerView(idx1,idx2);
        },
        failure: function(resp,opt) {
            Ext.get('flock_main').dom.innerHTML = '<span style="color:red;">Failure to split the centroid</span>';
            Ext.get('flock_main').unmask();
        }
    });
};

var undoChanges = function(task) {
    resetSessionTimeout();
    Ext.get('flock_main').mask("Loading ...",'x-mask-loading');
    var conn = new Ext.data.Connection({timeout: 120000});
//    var url = '/flockWeb/undoChanges.do?taskId=' + taskId + '&fileInputId=' + fileInputId;
    var url = './bin/undoChanges.php?taskId=' + taskId + '&fileInputId=' + fileInputId;
    conn.request( {
        url: url,
        success: function(resp,opt) {
            config = Ext.util.JSON.decode(resp.responseText);
			loadOverview("color");
        },
        failure: function(resp,opt) {
            Ext.get('flock_main').dom.innerHTML = '<span style="color:red;">Failure to undo the changes</span>';
            Ext.get('flock_main').unmask();
        }
    });
};

/*
var downloadResults = function(type) {
	resetSessionTimeout();
    Ext.get('flock_main').mask("Loading ...",'x-mask-loading');
    var conn = new Ext.data.Connection({timeout: 120000});
    var url = '/flockWeb/fileDownload.do?taskId=' + taskId + '&fileInputId=' + fileInputId + "&type=" + type;
    
    conn.request( {
        url: url,
        success: function(resp,opt) {
            Ext.get('flock_main').unmask();
        },
        failure: function(resp,opt) {
            Ext.get('flock_content').dom.innerHTML = "Failure";
        }
    });
};
*/

var loadToolBar = function (){
	new Ext.Toolbar({
		renderTo: 'ras_toolbar',
        id: 'rasToolbar',
		items: [
			{
				xtype: 'tbbutton',
				text: 'Overview Color',
				handler: function() {
					loadOverview('color');
				}
			},
			{
				xtype: 'tbseparator'
			},
			{
				xtype: 'tbbutton',
				text: 'Overview BW',
				handler: function() {
					loadOverview('bw');
				}
			},
			{
				xtype: 'tbseparator'
			},
			{
				xtype: 'tbbutton',
				text: 'Summary Table',
				handler: function() {
					loadTableView();
				}
			},
			{
				xtype: 'tbseparator'
			},
			{
				xtype: 'tbbutton',
				text: 'Centroid Table',
				handler: function() {
					loadCentroidView();
				}
			},
			{
				xtype: 'tbseparator'
			},
			{
				xtype: 'tbbutton',
				text: 'MFI Table',
				handler: function() {
					loadMfiView();
				}
			},
						{
				xtype: 'tbseparator'
			},
//			{
//				xtype: 'tbbutton',
//				text: 'Download',
//				menu: [{
//					text: 'Results',
//					href: '/flockWeb/fileDownload.do?taskId=' + taskId + '&fileInputId=' + fileInputId + '&type=results',
//					hrefTarget: '_flock'
//				}, {
//					text: 'Summary Table',
//					href: '/flockWeb/fileDownload.do?taskId=' + taskId + '&fileInputId=' + fileInputId + '&type=summary',
//					hrefTarget: '_flock'
//				}, {
//					text: 'Centroid Table',
//					href: '/flockWeb/fileDownload.do?taskId=' + taskId + '&fileInputId=' + fileInputId + '&type=centroid',
//					hrefTarget: '_flock'
//				}, {
//					text: 'MFI Table',
//					href: '/flockWeb/fileDownload.do?taskId=' + taskId + '&fileInputId=' + fileInputId + '&type=mfi',
//					hrefTarget: '_flock'
//				}]	
//			},
//			{
//				xtype: 'tbseparator'
//			},
			{
				xtype: 'tbbutton',
				text: 'Undo Centroid Adjustments',
				handler: function() {
					undoChanges();
				}
			},
						{
				xtype: 'tbseparator'
			},
			{
				xtype: 'tbbutton',
				text: 'Color Key',
				handler: function() {
					colorKey();
				}
			}								
		]
	});
};
