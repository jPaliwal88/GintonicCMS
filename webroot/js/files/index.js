define(["jquery"],function(e){return function(t,n){e("#upload-alert").html('<div class="alert alert-success"><a class="close" data-dismiss="alert">×</a><span>Upload successful</span></div>'),e.get("gintonic_c_m_s/files/getRow/"+t,function(t){e("#all-files tr:last").after(t)})}});