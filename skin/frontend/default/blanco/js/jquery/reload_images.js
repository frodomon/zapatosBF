jQuery(document).ready(function(){

    jQuery(".zoom-thumbnail").click(function(e){
        e.preventDefault();
        $img = jQuery(this);
        $link = $img.closest('a');
        var href = $link.attr("href");
        jQuery(".zoom-image").attr('src',href);
    });
    
});
