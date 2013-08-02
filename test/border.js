Ext.require(['*']);

Ext.onReady(function() {
    var cw;

    Ext.state.Manager.setProvider(Ext.create('Ext.state.CookieProvider'));

    function closeRegion (e, target, header, tool) {
        var panel = header.ownerCt;
        newRegions.unshift(panel.initialConfig);
        viewport.remove(panel);
    }

    var newRegions = [{
            region: 'east',
            title: 'East 2',
            width: 100,
            collapsible: true,
            weight: -110
        }, {
            region: 'west',
            title: 'West 2',
            width: 100,
            collapsible: true,
            weight: -110
        }];

    var viewport = Ext.create('Ext.Viewport', {
        layout: {
            type: 'border',
            padding: 5
        },
        defaults: {
            split: true
        },
        items: [{
            region: 'west',
            collapsible: true,
            layout: 'absolute',
            title: 'Flock Results Files',
            autoScroll: true,
            split: true,
            width: '20%',
            minWidth: 100,
            minHeight: 140,
            //bodyPadding: 10,
            stateId: 'flockResults',
            stateful: true,
            html: '<div id="fileDiv"></div>',
            items: [{
                xtype: 'fileTreeCompo'
            }]
        },{
            region: 'center',
            html: 'center center',
            title: 'Details',
            minHeight: 100,
            items: []/*,
            bbar: [ 'Text followed by a spacer', ' ', {
                itemId: 'toggleCw',
                text: 'Constrained Window',
                enableToggle: true,
                toggleHandler: function() {
                    cw.setVisible(!cw.isVisible());
                }
            }, {
                text: 'Add Region',
                listeners: {
                    click: function () {
                        if (newRegions.length) {
                            var region = newRegions.pop();
                            region.tools = [ { type: 'close', handler: closeRegion }];
                            viewport.add(region);
                        } else {
                            Ext.Msg.show({
                                title: 'All added',
                                msg: 'Close one of the dynamic regions first',
                                //minWidth: Ext.Msg.minWidth,
                                buttons: Ext.Msg.OK,
                                icon: Ext.Msg.ERROR
                            });
                        }
                    }
                }
            }, {
                text: 'Change Titles',
                listeners: {
                    click: function () {
                        var panels = viewport.query('panel');
                        Ext.suspendLayouts();
                        Ext.Array.forEach(panels, function (panel) {
                            panel.setTitle(panel.title + '!');
                        });
                        Ext.resumeLayouts(true);
                    }
                }
            }]*/
        },{
            region: 'south',
            height: '70%',
            split: true,
            collapsible: true,
            title: 'Flock Results',
            minHeight: 150,
            html: 'image table goes here',
            weight: -100
        }]
    });
});

Ext.define('Ext.example.FileViewer', {
    extend: 'Ext.Component',
    xtype: 'fileTreeCompo',
    style: 'font-size: 30px;',
    afterRender: function () {
        $('#fileDiv').fileTree({
            root: '/export/gofcm/Tasks/',
            script: 'jqueryFileTree.php',
            expandSpeed: 1000,
            collapseSpeed: 1000,
            multiFolder: false
        }, function(file) {
            alert(file);
        });
    }
});
