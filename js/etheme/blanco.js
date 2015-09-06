var blancoTheme = (function () {



    theme = {

        initZoom: function (zoom, thumbnails) {
            var $eazyzoom = $j(zoom).easyZoom();
            var $thumbs = $j(thumbnails);
            theme.zoomApi = $eazyzoom.data("easyZoom");
            $thumbs.on("click", "a", function (e) {
                var $this = $j(this);
                e.preventDefault();
                theme.zoomApi.swap($this.attr("data-easyzoom-source"), $this.attr("href"));
            })

        }
    }

    return theme;
})();