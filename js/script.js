$(function() {

    var smartFeedsUpdateClass = 'js-smart-feeds-update-select',
        previousSlot = '';

    $( '.' + smartFeedsUpdateClass )
        .on('focus', function () {
            previousSlot = this.value;
        })
        .change( function() {
            var el = $(this);

            // Update slot number with feed id
            $.ajax({
                url: './action.php',
                type: 'POST',
                dataType: "html",
                data: 'feed-id=' + el.data( 'feed-id' ) + '&previous-slot=' + previousSlot + '&new-slot=' + el.val(),

                success: function( data ) {
                    location.reload(false);
                }
            });
        });

});
