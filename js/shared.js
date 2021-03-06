var common = {
  error: function(t,id,d) {
    var type = t==='e'?'alert':'warning';
    $(id).prepend('<div class="'+type+'">'+
        '<button type="button" class="close" data-dismiss="'+type+'">&times;</button>'+
        '<strong>'+d+'</strong></div>');
  },
  modal: {
    error: function(t,d, cf) {
      $('#errorModal #title').html(t);
      $('#errorModal #desc').html(d);
      if(cf) {
        $('#errmClose').click(cf);
      }
      this.open('errorModal');
    },
    open: function(id) {
      $('#'+id).modal('show');
    },
    hide: function(id) {
      $('#'+id).modal('hide');
    }
  },
  alert:function(id,t,m) { //w-where(page, result), t-message, e-boolean error or alert
    var type = (t==='e'?'danger':t==='i'?'info':t==='s'?'success':'warning');
    $('#'+id).html(
      '<div class="alert alert-' + type + ' alert-dismissable">' +
        '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' +
        /*'<strong>Warning!</strong> ' +*/ m +
      '</div>');
  },
  p: {
    id: 'gofcm.pid',
    name: 'gofcm.pname',
    set: function(pid,pname) {
      common.ss_s(this.id, pid);
      common.ss_s(this.name, pname);
    },
    drop: function() {
      common.ss_d(this.id);
      common.ss_d(this.name);
    },
    toHeader: function(st) {
      var second = 'project: ' + common.ss_g(common.p.name) + (st ? ', dataset: ' + st : '');
      $('#pageHeader').append(' - <small>[' + second + ']</small>');
    }
  },
  ispset: function() {
    return (this.ss_g('gofcm.pid')!=null && this.ss_g('gofcm.pname')!=null);
  },
  ss_s: function(n,v) {
    sessionStorage.setItem(n, v);
  },
  ss_g: function(n) {
    return sessionStorage.getItem(n);
  },
  ss_d: function(n) {
    sessionStorage.removeItem(n);
  },
  paramv: function(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        param = regex.exec(location.search);
    return param == null ? "" : decodeURIComponent(param[1].replace(/\+/g, " "));
  }
};

function makeAjaxCall(t,u,d,cb) {
  $.ajax({
    type: t==='p'?"POST":"GET",
    async: true,
    url: u,
    dataType: 'json',
    data: d,
    success: function (obj, ts) {
      if(cb)
        cb(obj);
    },
    error: function() {}
  });
}

$.fn.imagesLoaded = function () {
  $imgs = this.find('img[src!=""]');
  // if there's no images, just return an already resolved promise
  if (!$imgs.length) {return $.Deferred.resolve().promise();}
  // for each image, add a deferred object to the array which resolves when the image is loaded
  var dfds = [];  
  $imgs.each(function(){
    var dfd = $.Deferred();
    dfds.push(dfd);
    var img = new Image();
    img.onload = function(){dfd.resolve();}
    img.src = this.src;
  });

  // return a master promise object which will resolve when all the deferred objects have resolved
  // IE - when all the images are loaded
  return $.when.apply($,dfds);
}