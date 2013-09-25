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
  ajax: function(u,d,cb) {
    $.ajax({
      type: "POST",
      async: false,
      url: u,
      dataType: 'json',
      data: d,
      success: function (obj, ts) {
        if(cb)
          cb(obj);
      },
      error: function() {}
    });
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
  }
};