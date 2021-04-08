(function($){
    $(document).ready(function(){
        $('.apply-form').on('click', '.show-form', function(event){
            event.preventDefault();
   
            // show form
            $( '.report-a-bug-message' ).slideDown( 'slow' );

        })

    });

})(jQuery);

(function($){
  $("#enquiry_email_form").on("submit", function (event){
              event.preventDefault();

              var form= $(this);
              var ajaxurl = form.data("url");
              var title=$("#post_title").html();
              var formdata= new FormData(this);
              var detail_info = {
                title,
                  name: form.find("#name").val(),
                  email: form.find("#email").val(),
                  phone: form.find("#phone").val(),
                  edu: form.find("#edu").val()
              }

              if(detail_info.name === "" || detail_info.email === "" || detail_info.phone === "" || detail_info.edu === "" ) {
                  alert("Fields cannot be blank");
                  return;
              }

              $.ajax({
                  url: settings.ajaxurl,
                  type: 'POST',
                  data: {
                      post_details : detail_info,
                      action: 'save_post_details_form'
                  },
                  error: function(error) {
                      alert("Insert Failed" + error);
                  },
                  success: function(response) {
                    var items = [];
                        $.each( response.data, function( key, val ) {
                         items.push( ("<li >" + key   + ' : ' + val + "</li>") );
                     });

                     $( "<ul/>", {
                         html: items.join( "" )
                     })

                     $('#report-a-bug-message').html(response);
                     $('#message').html("<h2>Job Applied</h2>")
                     .append("<h4>Job Applicant Details</h4>")
                     .append(items)
                     .append("<p>Will contact soon.</p>")
                   }
              });
          });
})( jQuery );

(function($){
    $(document).ready(function(){

         $(document).on('click', '.delete_app', function(event){

            var id = $(this).data('id');
            console.log(id);
            var post = $(this).parents('.post:first');
            console.log(post);

            $.ajax({
                type: 'post',
                url: settings.ajaxurl,
                data: {
                    action: 'delete_job',
                    id: id
                },
            success: function( response ) {
                if( response == 'success' ) {
                    console.log(response);
                    post.fadeOut( function(){
                        post.remove();
                    });
                }
            }
        })
        return false;
    })
})

})(jQuery);
