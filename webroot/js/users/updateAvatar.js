define(["jquery"],function(e){return function(t,n){e.ajax({type:"POST",url:"gintonic_c_m_s/users/updateAvatar/",dataType:"json",data:{id:e("#user-id").val(),file_id:t},success:function(t,n){e("#userphoto").attr("src",t.file),t.success?e("#contact-alert").html('<div class="alert alert-dismissable alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>Success:</strong> '+t.message+"</div>"):e("#contact-alert").html('<div class="alert alert-dismissable alert-danger"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>Error:</strong> '+t.message+"</div>")},error:function(t,n){e("#contact-alert").html('<div class="alert alert-dismissable alert-danger"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>Error:</strong> unable to send message</div>')}})}});