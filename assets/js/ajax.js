var simplemde;

 function send(id, id2) {
   
       $.ajax({
          url: '/status',
          type: 'POST',
          data: {'status': id, 'task': id2},
          success: function () {
              console.log('it worked!');
          },
          error: function () {
              console.log('it failed!');
          }
      });
   
 }
 
      function files(id) {
   
       $.ajax({
          url: '/files',
          type: 'POST',
          data: {'file': id},
          success: function () {
              $( "#file_" + id ).remove();
          },
          error: function () {
              console.log('it failed!');
          }
      });
   
 }

var parseTpl = function(tpl, vars) {
   this.rplc = function(needle, subject, where) {
       where = where.replace(needle, subject);

       return (where.indexOf(needle) != -1 ? arguments.callee(arguments[0], arguments[1], arguments[2]) : where.replace(needle, subject));
   }
   for (var index in vars) {
       if (typeof vars[index] == "undefined" || vars[index] == null) {
           tpl = this.rplc('%' + index + '%', '', tpl);
       } else {
           tpl = this.rplc('%' + index + '%', vars[index], tpl);
       }
   }
   return tpl;
}

$(document).ready(function(){
    
    var templateCommentItem = '<li class="list-group-item py-5" id="comment_%commentid%">\
    <div class="media">\
      <div class="media-object avatar avatar-md mr-4" style="background-image: url(%photo%)"></div>\
      <div class="media-body">\
        <div class="media-heading">\
          <small class="float-right text-muted">%dt% <b onclick="comment(%commentid%)" class="fe fe-x-circle ikon"></b></small>\
          <h5>%name%</h5>\
        </div>\
        <div>%text%</div>\
      </div>\
    </div>\
</li>';
    
    $('#userForm').submit(function(){
    
   var currentCount = $('#comment-count').text();
    
    // Call ajax for pass data to other place
        $.ajax({
        type: 'POST',
        url: '/addcomment',
        data: $(this).serialize() // getting filed value in serialize form
        })
            
            .done(function(data){ 
                
                simplemde.value('');
                data = JSON.parse(data);
        
        var template = parseTpl(templateCommentItem, {
               'commentid': data.commentid,
               'photo': data.photo,
               'dt': data.date,
               'name': data.name,
               'text': data.text
           });
            
            //templateCommentItem = replace(templateCommentItem);
            
            // show the response
            $('#comments-list').fadeIn( "slow", function(){ 
                    $(this).append(template);
                    $('#comment-count').text(++currentCount);
                    $(".no-comments").remove();
                });
            
            })
            
            .fail(function() { // if fail then getting message
            
            // just in case posting your form failed
            alert( "Posting failed." );
            
            });
    
    // to prevent refreshing the whole page page
    return false;
    
    });
});

 function comment(id) {
     
     var currentCount = $('#comment-count').text();
   
       $.ajax({
          url: '/deletecomment',
          type: 'POST',
          data: {'commentid': id},
          success: function () {
              $( "#comment_" + id ).fadeOut(400, function(){ 
                    $(this).remove();
                    $('#comment-count').text(--currentCount);
                    
                });
          },
          error: function () {
              console.log('it failed!');
          }
      });
   
 }

$(document).ready(function(){ 
    
    $('#editprofile').click(function(){
        $('input').prop('disabled', false);
        $('select').prop('disabled', false);
        $('.editprofile').css( "display", "none" );
        $('#updateprofile').prop('disabled', false);
        $('.editcancel').css( "display", "inline-block" );
        $('.browse-photo').css( "display", "inherit" );
    });
});

$(document).ready(function(){ 
    
    $('#editcancel').click(function(){
        $('input').prop('disabled', true);
        $('select').prop('disabled', true);
        $('.editcancel').css( "display", "none" );
        $('.browse-photo').css( "display", "none" );
        $('#updateprofile').prop('disabled', true);
        $('.editprofile').css( "display", "inline-block" );
        
    });
});

$(function() {
    $("#profile_file").change(function() {
        
        var file = this.files[0];
        
        var imagefile = file.type;
        var imagesize = file.size;
        
        var match= ["image/jpeg","image/png","image/jpg"];

        var allowed = 17000;

        
        if(!((imagefile==match[0]) || (imagefile==match[1]) || (imagefile==match[2])))
        
        {
            alert('Image must be jpeg, png or jpg!');
            
            return false;
        
        }
        
        if(imagesize >= allowed) {
    
            alert('Image size should be 17kb or less!');
            
            return false;
            
        } 
            var reader = new FileReader();
            reader.onload = imageIsLoadedA;
            reader.readAsDataURL(this.files[0]);
        
    });
});

function imageIsLoadedA(e) {
    $('#profile_image_preview').css("display", "block");
    $('#previewing_profile').attr('src', e.target.result);
    $('#photo').attr('value', e.target.result);
    $('#previewing_profile').attr('width', '250px');

}


 function notification(id) {
   
       $.ajax({
          url: '/updatenotify',
          type: 'POST',
          data: {'noty': id},
          success: function () {

          },
          error: function () {
              console.log('it failed!');
          }
      });
   
 }
 
 
 
 $(function() {
    $("#upload_image").change(function() {
        
        var file = this.files[0];
        
        var imagefile = file.type;
        var imagesize = file.size;
        
        var match= ["image/jpeg","image/png","image/jpg"];

        var allowed = 17000;

        
        if(!((imagefile==match[0]) || (imagefile==match[1]) || (imagefile==match[2])))
        
        {
            alert('Image must be jpeg, png or jpg!');
            
            return false;
        
        }
        
        if(imagesize >= allowed) {
    
            alert('Image size should be 17kb or less!');
            
            return false;
            
        } 
            var reader = new FileReader();
            reader.onload = newimage;
            reader.readAsDataURL(this.files[0]);
        
    });
});

function newimage(e) {
    $('#profile_image_preview').css("display", "block");
    $('#new_photo_preview').attr('src', e.target.result);
    $('#new_photo').attr('value', e.target.result);
    $('#new_photo_preview').attr('width', '250px');

}