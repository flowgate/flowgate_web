
/*
 * displays messages
 */
 var msg = function(title, msg){
    Ext.Msg.show({
        title: title,
        msg: msg,
        minWidth: 200,
        modal: true,
        icon: Ext.Msg.INFO,
        buttons: Ext.Msg.OK
    });
};

var _utils = {
    clr_con: function() {
        Ext.get('ras_toolbar').dom.innerHTML = "";
        Ext.get('flock_content').dom.innerHTML = "";    
    }
};

var resetSessionTimeout = function() { };

//check if user session is still active
var sessionCheck = function() {
    $.ajax({
        type: 'POST',
        url: 'controller.php',
        data: 'j=u_s',
        datType: "json",
        success: function(res) {
            var _res = Ext.util.JSON.decode(res);
            if(!_res.s_a) {
                document.location.href='pages/logout.php';
            }
        }
    });
};

/*
 * Form used to run analysis
 */
 var loadAnalysisForm = function(data) {
    sessionCheck();
    var analysis_form = new Ext.FormPanel({
        title      : 'Project : "' + data.p_name + '"  - Run Analysis',
        fileUpload : false,
        width      : 500,
        labelWidth : 80,
        defaults: {
            anchor: '95%',
            allowBlank: false,
            msgTarget: 'side'
        },
        items: [
            {
                xtype: 'textfield',
                fieldLabel: 'File',
                value: data.f_name+" - "+data.f_org_name,
                disabled: true
            },
            {
                xtype: 'textfield',
                fieldLabel: 'Task Name',
                name: 'tname'
            },{
                xtype: 'textfield',
                fieldLabel: 'Number of Bins',
                value: '0',
                name: 'tbin'
            },{
                xtype: 'hidden',
                value: data._id,
                name: 'fid'
            },{
                xtype: 'textfield',
                fieldLabel: 'Density',
                value: '1',
                name: 'tden'
            },{
                xtype: 'hidden',
                value: "t_a",
                name: 'j'
            }
        ],
        buttons: [{
            text: 'Run Analysis',
            handler: function() {
                var url = 'controller.php';
                if (analysis_form.getForm().isValid()){
                    analysis_form.getForm().submit({
                        url: url,
                        timeout: 180000,
                        waitMsg: 'Running Analysis ...',
                        success: function(analysis_form,o){
                            msg('Success','FLOCK ANALYSIS COMPLETED');
                            Ext.get('flock_content').dom.innerHTML = "";
                        }
                    });
                }
            }
        },
        {
            text: 'Reset',
            handler: function() {
                analysis_form.getForm().reset();
            }
        }]
    });
	_utils.clr_con();
	analysis_form.render("flock_content");
},
/*
 * File upload form
 */
loadUploadForm = function() {
    sessionCheck();
    var upload_form = new Ext.FormPanel({
        title      : 'File Upload',
        fileUpload : true,
        width      : 500,
        labelWidth : 50,
        defaults: {
            anchor: '95%',
            allowBlank: false,
            msgTarget: 'side'
        },
        items: [
            {
                xtype: 'combo',
                fieldLabel: 'Project',
                name: 'pname',
                allowBlank: false,
                editable: false,
                triggerAction: 'all',
                typeAhead: false,
                store: new Ext.data.JsonStore({
                    url:'controller.php?j=p_u',
                    root: 'projects',
                    idProperty:'p_name',
                    fields: ['p_id', 'p_name']
                }),
                readOnly: true,
                valueField: 'p_id',
                displayField: 'p_name',
                hiddenName: 'pid'
            }, {
                xtype: 'textfield',
                fieldLabel: 'Title',
                name: 'fname'
            }, {
                xtype: 'hidden',
                name: 'j',
                value: 'f_a'
            }, {
                xtype: 'fileuploadfield',
                fieldLabel: 'Upload File',
                emptyText: 'Select a new file',
                name: 'uploadFile',
                id: 'uploadFile'
            }
        ],
            buttons: [{
            text: 'Upload',
            handler: function() {
                if (upload_form.getForm().isValid()){
                    upload_form.getForm().submit({
                        url: 'controller.php',
                        waitMsg: 'Uploading TXT file ...',
                        success: function(analysis_form,o){
                            msg('Success','Processed file "'+o.result.file+'" on the server');
                            Ext.get('flock_content').dom.innerHTML = "";
                        }
                    });
                }
            }
        },
        {
            text: 'Reset',
            handler: function() {
                upload_form.getForm().reset();
            }
        }]
    });
	_utils.clr_con();
	upload_form.render("flock_content");
},
loadProjectForm = function() {
    sessionCheck();
    var project_form = new Ext.FormPanel({
        title      : 'Add Project',
        width      : 500,
        labelWidth : 100,
        defaults: {
            anchor: '95%',
            allowBlank: false,
            msgTarget: 'side'
        },
        id: 'projectForm',
        items: [
            { xtype: 'textfield',
            fieldLabel: 'Project Name',
            emptyText: 'Project name',
            name: 'pname' },
            { xtype: 'textfield',
            fieldLabel: 'Description',
            emptyText: 'Project Description',
            name: 'pdesc' },
            { xtype: 'hidden',
            name: 'j',
            value: 'p_a'}
        ],
        buttons: [{
            text: 'Add',
            handler: function() {
                if (project_form.getForm().isValid()){
                    project_form.getForm().submit({
                        url: './controller.php',
                        waitMsg: 'Adding new project...',
                        success: function(form,o){
                            msg('Success',o.result.msg);
                            Ext.get('flock_content').dom.innerHTML = "";
                        },
                        failure: function (form, o) {
                            msg('Failed', o.result.error.reason);
                        }
                    });
                }
            }
        },
        {
            text: 'Reset',
            handler: function() {
                project_form.getForm().reset();
            }
        }]
    });

    _utils.clr_con();
    project_form.render("flock_content");
};


/*
 * view files
 */
var loadFiles = function() {
    sessionCheck();
    Ext.get('flock_main').mask("Loading ...",'x-mask-loading');
    var conn = new Ext.data.Connection({timeout: 120000});
    //var url = './bin/loadFiles.php';
    conn.request( {
        url: 'controller.php?j=f_u',
        success: function(resp,opt) {
            filesConfig = Ext.util.JSON.decode(resp.responseText);
            buildFilesGrid();
            Ext.get('flock_main').unmask();
        },
        failure: function(resp,opt) {
            Ext.get('flock_main').dom.innerHTML = '<span style="color:red;">Failure to Load the Uploaded Files Screen</span>';
            Ext.get('flock_main').unmask();
        }
    });
},
buildFilesGrid = function() {
    var store = new Ext.data.JsonStore({
        data: filesConfig,
        root: 'rows',
        fields: filesConfig.fields,
        currp: filesConfig.currp
    });

    var filesGrid = new Ext.grid.LockingGridPanel({
        title: 'Project : "'+store.currp+'" (Click on a File to run Flock)',
        store: store,
        frame: false,
        height: 600,
        enableColumnMove: false,
        viewConfig: {
            forceFit: false
        },
        columns: filesConfig.columns,
        sm: new Ext.grid.RowSelectionModel({
            singleSelect: true,
            listeners: {
                rowselect: {
                    fn: function(sm,index,record) {
                        loadAnalysisForm(record.data);
                    }
                }
            }
        })
    });
    _utils.clr_con();
    filesGrid.render("flock_content");
};


/*
 ****** FLOCK RESULT *******
*/
var loadFlockResults = function() {
    sessionCheck();
    Ext.get('flock_main').mask("Loading ...",'x-mask-loading');
    var conn = new Ext.data.Connection({timeout: 120000});
    conn.request( {
        url: 'controller.php?j=t_u',
        success: function(resp,opt) {
            flockResultsConfig = Ext.util.JSON.decode(resp.responseText);
            buildFlockResultsGrid();
            Ext.get('flock_main').unmask();
        },
        failure: function(resp,opt) {
            Ext.get('flock_main').dom.innerHTML = '<span style="color:red;">Failure to Load the FLOCK Results screen</span>';
            Ext.get('flock_main').unmask();
        }
    });
},
buildFlockResultsGrid = function() {
    var store = new Ext.data.JsonStore({
        data: flockResultsConfig,
        root: 'rows',
        fields: flockResultsConfig.fields
    });
    var flockResultsGrid = new Ext.grid.LockingGridPanel({
        title: 'Previously FLOCK Analysis Tasks - Click on a Task to View Results',
        store: store,
        frame:false,
        height: 600,
        enableColumnMove: false,
        viewConfig: {
            forceFit: false
        },
        columns: flockResultsConfig.columns,
        sm: new Ext.grid.RowSelectionModel({
            singleSelect: true,
            listeners: {
                rowselect: {
                    fn: function(sm,index,record) {
                        RAS.loadRAS(record.data);
                    }
                }
            }
        })
    });
    _utils.clr_con();
    flockResultsGrid.render("flock_content");
};
/*
 ****** END FLOCK RESULT *******
*/

/*
 ****** RAS *******
*/
var RAS = {
    t_id: null, 
    f_id: null,
    pops: null,
    totalPops: 0,
    pop_arr: [],
    loadRAS: function(data) { //currentTaskId,currentFileInputId) {
        this.t_id = data.t_id;
        this.f_id = data.f_id;
        Ext.get('flock_content').dom.innerHTML = "";
        this.loadOverview("color");
    },
    loadPopulationMenu: function(tot, pops) {
        var items = [];
        for(var i=1;i<=tot;i++) {
            var mitem = {};
            mitem['text'] = 'Population'+i+' ('+pops[i]+'%)';
            mitem['iconCls'] = 'popul'+i;
            mitem['handler'] = "function() { loadFiles(); }";
            items.push(mitem);
        }
        this.loadToolBar(items);
    },
    loadToolBar: function (pops){
        new Ext.Toolbar({
            renderTo: 'ras_toolbar',
            id: 'rasToolbar',
            items: [
                {
                    xtype: 'tbbutton',
                    text: 'Populations',
                    menu: pops
                },
                { xtype: 'tbseparator' },
                { xtype: 'tbbutton', text: 'Overview Color', handler: function() { RAS.loadOverview('color'); } },
                { xtype: 'tbseparator' },
                { xtype: 'tbbutton', text: 'Overview BW', handler: function() { RAS.loadOverview('bw'); } },
                { xtype: 'tbseparator' },
                { xtype: 'tbbutton', text: 'Summary Table', handler: function() { RAS.loadTableView(); } },
                { xtype: 'tbseparator' },
                { xtype: 'tbbutton', text: 'Centroid Table', handler: function() { RAS.loadCentroidView(); } },
                { xtype: 'tbseparator' },
                { xtype: 'tbbutton', text: 'MFI Table', handler: function() { RAS.loadMfiView(); } },
                { xtype: 'tbseparator' },
                { xtype: 'tbbutton', text: 'Undo Centroid Adjustments', handler: function() { RAS.undoChanges(); } },
                { xtype: 'tbseparator' },
                { xtype: 'tbbutton', text: 'Color Key', handler: function() { RAS.colorKey(); } }
            ]
        });
    },
    loadOverview: function(type) {
        sessionCheck();
        Ext.get('flock_main').mask("Loading ...",'x-mask-loading');
        var conn = new Ext.data.Connection({timeout: 120000});
        conn.request( {
            url: './bin/loadOverview.php?taskId=' + this.t_id +'&fileInputId=' + this.f_id + "&type=" + type,
            success: function(resp,opt) {
                overviewConfig = Ext.util.JSON.decode(resp.responseText);
                RAS.buildOverviewGrid();
                Ext.get('flock_main').unmask();
            },
            failure: function(resp,opt) {
                Ext.get('flock_main').dom.innerHTML = '<span style="color:red;">Failure to Load the Overview Screen</span>';
                Ext.get('flock_main').unmask();
            }
        });
    },
    buildOverviewGrid: function() { //Build the grid to display the marker by marker images
        var store = new Ext.data.JsonStore({
            data: overviewConfig,
            root: 'rows',
            fields: overviewConfig.fields
        });

        _utils.clr_con();
        this.loadPopulationMenu(overviewConfig.totalPops, overviewConfig.pops);

        var overviewGrid = new Ext.grid.LockingGridPanel({
            title: 'Click on any thumbnail image to view and adjust populations',
            store: store,
            frame:false,
            height: 600,
            enableColumnMove: false,
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
                            if (data[1] == undefined || data[1] == data[3]) {
                                return;
                            }
                            RAS.loadAdjustCentroid(data[1],data[3],overviewConfig.popId);
                        }
                    }
                }
            })
        });
        overviewGrid.render("flock_content");
    },
    loadTableView: function(type) {
        Ext.get('flock_main').mask("Loading ...",'x-mask-loading');
        var conn = new Ext.data.Connection({timeout: 120000});
        var url = './bin/loadFlockTable.php?taskId=' + this.t_id + '&fileInputId=' + this.f_id + "&type=" + type;
        conn.request( {
            url: url,
            success: function(resp,opt) {
                tableConfig = Ext.util.JSON.decode(resp.responseText);
                RAS.buildTableGrid();
                Ext.get('flock_main').unmask();
            },
            failure: function(resp,opt) {
                Ext.get('flock_main').dom.innerHTML = '<span style="color:red;">Failure to Load the Summary Screen</span>';
                Ext.get('flock_main').unmask();
            }
        });
    },
    buildTableGrid: function() { //Build the grid to display the Summary information
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
    },
    loadCentroidView: function(type) {
        resetSessionTimeout();
        Ext.get('flock_main').mask("Loading ...",'x-mask-loading');
        var conn = new Ext.data.Connection({timeout: 120000});
        var url = './bin/loadFlockCentroid.php?taskId=' + this.t_id + '&fileInputId=' + this.f_id + "&type=" + type;
        conn.request( {
            url: url,
            success: function(resp,opt) {
                tableConfig = Ext.util.JSON.decode(resp.responseText);
                RAS.buildCentroidGrid();
                Ext.get('flock_main').unmask();
            },
            failure: function(resp,opt) {
                Ext.get('flock_main').dom.innerHTML = '<span style="color:red;">Failure to Load the Centroid Screen</span>';
                Ext.get('flock_main').unmask();
            }
        });
    },
    buildCentroidGrid: function() { //Build the grid to display the Centroid table
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
            viewConfig: {
                forceFit: false
            },
            columns: tableConfig.tableColumns
        });

        Ext.get('flock_content').dom.innerHTML = "";
        tableGrid.render("flock_content");
    },
    loadMfiView: function(type) {
        resetSessionTimeout();
        Ext.get('flock_main').mask("Loading ...",'x-mask-loading');
        var conn = new Ext.data.Connection({timeout: 120000});
        var url = './bin/loadFlockMfi.php?taskId=' + this.t_id + '&fileInputId=' + this.f_id;
        conn.request( {
            url: url,
            success: function(resp,opt) {
                tableConfig = Ext.util.JSON.decode(resp.responseText);
                RAS.buildMfiGrid();
                Ext.get('flock_main').unmask();
            },
            failure: function(resp,opt) {
                Ext.get('flock_main').dom.innerHTML = '<span style="color:red;">Failure to Load the MFI Screen</span>';
                Ext.get('flock_main').unmask();
            }
        });
    },
    buildMfiGrid: function() { //Build the grid to display Mean Flouresence Intensity grid
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
    },
    colorKey: function() {
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
    },
    deletePopulation: function(idx1,idx2,popIdx) {
        sessionCheck();
        Ext.get('flock_main').mask("Loading ...",'x-mask-loading');
        var conn = new Ext.data.Connection({timeout: 120000});
        var url = './bin/deleteFlockPopulation.php?taskId=' + this.t_id + '&fileInputId=' + this.f_id + "&popIdx=" + popIdx;
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
    },
    loadAdjustCentroid: function(idx1,idx2,popId) {
        sessionCheck();
        Ext.get('flock_main').mask("Loading ...",'x-mask-loading');
        var conn = new Ext.data.Connection({timeout: 120000});
        var url = './bin/loadCentroidAdjust.php?taskId=' + this.t_id + '&fileInputId=' + this.f_id + "&idx1=" + idx1 + "&idx2=" + idx2 + "&popIdx=" + popId;
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
                                    var x = parseInt(div.x) + 6;
                                    var y = 300 - parseInt(div.y) - 6;
                                    var min = parseInt(editConfig.data.min);
                                    var max = parseInt(editConfig.data.max);
                                    var range = max - min + 1;
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
                 sm: new Ext.grid.CellSelectionModel({
                    singleSelect:true,
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
         
                                RAS.loadAdjustCentroid(data[1],data[3],data[4]);
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
    },
    loadSplitCentroid: function(idx1,idx2,popId) {
        resetSessionTimeout();
        Ext.get('flock_main').mask("Loading ...",'x-mask-loading');
        var conn = new Ext.data.Connection({timeout: 120000});
        var url = './bin/loadCentroidAdjust.php?taskId=' + this.t_id + '&fileInputId=' + this.f_id + 
            "&idx1=" + idx1 + "&idx2=" + idx2 + "&popIdx=" + popId;
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
                var x1 = parseInt(xCoord) - 6, y1 = parseInt(yCoord) - 6, x2 = parseInt(xCoord) + 6, y2 = parseInt(yCoord) - 6;
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
    },
    recompute: function() {
        resetSessionTimeout();
        Ext.get('flock_main').mask("Loading ...",'x-mask-loading');
        var conn = new Ext.data.Connection({timeout: 120000});
        var xValue = Ext.get('xValue').dom.innerHTML;
        var yValue = Ext.get('yValue').dom.innerHTML;
        var idx1 = editConfig.data.m1Idx
        var idx2 = editConfig.data.m2Idx
        var popIdx = editConfig.data.popIdx;
            
        var url = './bin/recompute.php?taskId=' + this.t_id + '&fileInputId=' + this.f_id + 
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
    },
    recomputeSplit: function() {
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
            
        var url = './bin/recomputeSplit.php?taskId=' + this.t_id + '&fileInputId=' + this.f_id
            + '&idx1=' + idx1 + '&idx2=' + idx2 + '&x1Value=' + x1Value + '&y1Value=' + y1Value
            + '&x2Value=' + x2Value + '&y2Value=' + y2Value
            + '&popIdx=' + popIdx;
            
        conn.request( {
            url: url,
            success: function(resp,opt) {
                config = Ext.util.JSON.decode(resp.responseText);
                loadAdjustCentroid(idx1,idx2,popIdx);
            },
            failure: function(resp,opt) {
                Ext.get('flock_main').dom.innerHTML = '<span style="color:red;">Failure to split the centroid</span>';
                Ext.get('flock_main').unmask();
            }
        });
    },
    undoChanges: function(task) {
        resetSessionTimeout();
        Ext.get('flock_main').mask("Loading ...",'x-mask-loading');
        var conn = new Ext.data.Connection({timeout: 120000});
        var url = './bin/undoChanges.php?taskId=' + this.t_id + '&fileInputId=' + this.f_id;
        conn.request( {
            url: url,
            success: function(resp,opt) {
                config = Ext.util.JSON.decode(resp.responseText);
                RAS.loadOverview("color");
            },
            failure: function(resp,opt) {
                Ext.get('flock_main').dom.innerHTML = '<span style="color:red;">Failure to undo the changes</span>';
                Ext.get('flock_main').unmask();
            }
        });
    }
};
/*
 ****** END RAS *******
*/

var showProjectSelect = function() {
    sessionCheck();
    var projectSelectForm = new Ext.form.FormPanel({
        baseCls: 'x-plain',
        labelWidth: 55,
        width: 200,
        url:'save-form.php',
        defaultType: 'textfield',
        items: [{
            xtype: 'combo',
            fieldLabel: 'Project',
            name: 'pname',
            id: 'pname',
            allowBlank: false,
            editable: false,
            triggerAction: 'all',
            typeAhead: false,
            store: new Ext.data.JsonStore({
                url:'controller.php?j=p_u',
                root: 'projects',
                idProperty:'p_name',
                fields: ['p_id', 'p_name']
            }),
            readOnly: true,
            valueField: 'p_id',
            displayField: 'p_name',
            hiddenName: 'pid'
        }]
    });

    var projectWindow = new Ext.Window({
        title: 'Select or Change Project',
        id: 'projectSelWin',
        width: 300,
        height:150,
        minWidth: 200,
        minHeight: 150,
        layout: 'fit',
        plain:true,
        modal: true,
        bodyStyle:'padding:5px;',
        buttonAlign:'center',
        items: projectSelectForm,
        buttons: [
            {
                text: 'Select',
                handler: function() {
                    var combobox = Ext.getCmp('pname');
                    Ext.getCmp('pname').getValue();
                    //switch current project information in session
                    $.ajax({
                        type: 'POST',
                        url: 'controller.php',
                        data: 'j=p_s&pid='+combobox.getValue()+'&pname='+combobox.getRawValue(),
                        success: function(res) {
                            Ext.MessageBox.alert('Switch Project', 'Switched project to '+combobox.getRawValue()+' successfully.', function() {
                                Ext.getCmp('projectSelWin').close();
                                document.location.reload(true);
                            });
                        }
                    });
                }
            },{
                text: 'Add New Project',
                handler: function(){
                    loadProjectForm();
                    Ext.getCmp('projectSelWin').close();
                }
            }
        ]
    }).show();
};


/*
 * main toolbar initializer
 */
var loadMainToolBar = function() {
    var mainToolbar = new Ext.Toolbar({
        renderTo: 'main_toolbar',
        id: 'mainToolbar',
        items: [
            /*{ xtype: 'tbtext', text: 'Project: "<?php echo $_SESSION[\'currp\']; ?>"'},
            { xtype: 'tbseparator' }, */
            {
                xtype: 'tbbutton',
                text: 'File Management',
                menu: { items: [
                    { text: 'Add Project', handler: function() { loadProjectForm(); } },
                    { text: 'Upload TXT', handler: function() { loadUploadForm(); } },
                    { text: 'View Files', handler: function() { loadFiles(); } }
                ]}
            },
            { xtype: 'tbseparator' },
            { xtype: 'tbbutton', text: 'FLOCK Results', handler: function() { loadFlockResults(); } },
            { xtype: 'tbseparator' },
            { xtype: 'tbbutton', text: 'Switch Project', handler: function() { showProjectSelect(); } },
            { xtype: 'tbseparator' },
            { xtype: 'tbbutton', text: 'Log Out', handler: function() { location.href='pages/logout.php'; } },
            { xtype: 'tbseparator' }
        ]
    });
    $.ajax({
        type: 'GET',
        url: 'controller.php?j=p_g',
        success: function(res) {
            var res_json = JSON.parse(res);
            if(!res_json.success) {
                showProjectSelect();
            }
        }
    });
};
