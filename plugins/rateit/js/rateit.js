var RateIt = {
    init: function() {
        $('.rateit a').click(function(e){
            var stars = $(this).parent().parent();
            var id = stars.attr('id').split('-');
            if (id[0] == 'rateit') {
                RateIt.rating(id[2], $(this).text());
            }
            return false;
        });
    },
    rating: function(post_id, rating)
    {
        $('#rateit-loading-' + post_id).show();

        var query= {};
        query['post_id'] = post_id;
        query['rating'] = rating;
        $.post(rateit_habari_url + '/rateit/rating', query, function(result) {
            $('#rateit-loading-' + post_id).hide();
            if (result.error == 0) {
                $('#rateit-' + post_id).html(result.html);
            }
            alert(result.message);
        }, 'json');
    }
}

$(document).ready(function(){
    RateIt.init();
});
