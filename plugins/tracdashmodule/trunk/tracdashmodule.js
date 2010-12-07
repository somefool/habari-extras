YUI().use('scrollview', function(Y) {
   if( !Y.Lang.isNull(Y.one('.modulecore ul.trac')) )
    {
        var scrollView = new Y.ScrollView({
            srcNode: ".modulecore ul.trac",
            "height": 200
        });
    
        scrollView.render();
    }
});