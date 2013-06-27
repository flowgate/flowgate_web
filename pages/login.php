<?php
    session_start();
    session_unset();
    srand();
?>
<!DOCTYPE html>

<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" type="text/css" href="../ext-2.2/resources/css/ext-all.css">
    <title id="page-title">Flock Main</title>
</head>

<body>
    <div id="loginDiv" style="height:300px;width: 500px;margin:30px 30px;">
        <div id="login-panel"></div>
    </div>
    <script src="../ext-2.2/adapter/jquery/jquery.js"></script>
    <script src="../ext-2.2/adapter/jquery/ext-jquery-adapter.js"></script>
    <script src="../ext-2.2/adapter/ext/ext-base.js"></script>
    <script src="../ext-2.2/ext-all.js"></script>
    <script>
        Ext.onReady(function(){
            Ext.QuickTips.init();
            new Ext.Panel({
                id : "ext-panel",
                renderTo : "login-panel",
                bodyStyle : "background-color:aliceblue;",
                height : 250,
                width : 500,
                items : [
                    new Ext.FormPanel({
                        id : "login-form",
                        bodyStyle : "background-color:transparent",
                        style : {
                            'padding-top': 80
                        },
                        border : false,
                        height : 180,
                        width : 370,
                        monitorValid : true,
                        items : [
                            new Ext.form.TextField({
                                id : 'username',
                                name : 'username',
                                fieldLabel : 'Username',
                                allowBlank : false,
                                blankText : 'Username is required.',
                                msgTarget : 'side',
                                width : 200
                            }),
                            new Ext.form.TextField({
                                id : 'password',
                                name : 'password',
                                inputType : 'password',
                                fieldLabel : 'Password',
                                blankText : 'Password is required.',
                                allowBlank : false,
                                msgTarget : 'side',
                                width : 200
                            })
                        ],
                        buttons:[
                            {
                                text : 'Login',
                                handler : function(){
                                    authenticate(Ext.getCmp('username').getValue(),Ext.getCmp('password').getValue())
                                }
                            },
                            {
                                text : 'Reset',
                                handler : function(){
                                    Ext.getCmp('login-form').getForm().reset();
                                }
                            }
                        ]
                    })
                ]
            });
        });

        function authenticate(u,p){
            var dataPanel = Ext.getCmp('ext-panel');
            Ext.Ajax.on('beforerequest',function(conn,o,result){
                dataPanel.getEl().mask('Authenticating...','x-mask-loading');
            })

            Ext.Ajax.on('requestcomplete',function(conn,o,result){
                dataPanel.getEl().unmask(true);
            })

            Ext.Ajax.on('requestexception',function(conn,o,result){
                dataPanel.getEl().unmask(true);
                Ext.MessageBox.show({
                    title   : 'Message',
                    msg     : '<div style="margin-top:5px;text-align:center;color:red;">Server Error</div>',
                    width   : 300,
                    buttons : Ext.MessageBox.OK,
                    animEl  : 'ext-panel',
                    iconCls : 'login-button-icon'
                });
            });

            Ext.Ajax.request({
                url : "../controller.php",
                method : "POST",
                params : {pass:p,uname:u, j:'u_l'},
                callback : function(options,success,result){
                    var response = Ext.util.JSON.decode(result.responseText);
                    if(!response.success){
                        Ext.MessageBox.show({
                            title   : 'Message',
                            msg     : '<div style="margin-top:5px;text-align:center">' + response.error.reason + '</div>',
                            width   : 300,
                            buttons : Ext.MessageBox.OK,
                            animEl  : 'ext-panel',
                            iconCls : 'login-button-icon'
                        });
                    }else{
                        Ext.MessageBox.show({
                            title   : 'Message',
                            msg     : '<div style="margin-top:5px;text-align:center">Login Successful</div>',
                            width   : 300,
                            buttons : Ext.MessageBox.OK,
                            animEl  : 'ext-panel',
                            iconCls : 'login-button-icon'
                        });
                        window.location = '../index.php';
                    }
                }
            })
        }
    </script>
</body>
</html>
