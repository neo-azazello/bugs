 function addTaskCheclistComment(id) {

     $.get('../addchecklistcomment/?id=' + id,

         function (data) {
             $("#addComment").remove();
             $('#modalAdd').fadeIn("slow", function () {
                 $(this).append(data);
                 $("#addComment").modal();

             });

         });

     return false;

 }

 function editTaskCheclistComment(id) {

     $.get('../editchecklistcomment/?id=' + id,

         function (data) {
             $("#editComment").remove();
             $('#modalEdit').fadeIn("slow", function () {
                 $(this).append(data);
                 $("#editComment").modal();
                 $("#editComment").focus(function () {
                     simplemde.codemirror.refresh();
                 });

             });

         });

     return false;

 }

 function editTaskCheclist(id) {

     $.get('../editchecklist/?id=' + id,

         function (data) {
             $("#editChecklist").remove();
             $('#modalEdit').fadeIn("slow", function () {
                 $(this).append(data);
                 $("#editChecklist").modal();
                 $("#editChecklist").focus(function () {
                     simplemde.codemirror.refresh();
                 });

             });

         });

     return false;

 }