(function ($) {
    $.fn.filemanager = function (type, options) {
        type = type || "file";

        this.on("click", function (e) {
            var route_prefix =
                options && options.prefix ? options.prefix : "/filemanager";
            var target_input = $("#" + $(this).data("input"));
            var target_preview = $("#" + $(this).data("preview"));
            window.open(
                route_prefix + "?type=" + type,
                "FileManager",
                "width=900,height=600"
            );
            window.SetUrl = function (items) {
                var file_path = items
                    .map(function (item) {
                        return item.url.split("storage/")[1];
                    })
                    .join(",");
                //    Old Method
                // var file_path = items.map(function (item) {
                //     return item.url;
                //   }).join(',');

                // set the value of the desired input to image url
                target_input.val("").val(file_path).trigger("change");

                // clear previous preview
                target_preview.html("");

                // set or change the preview image src
                items.forEach(function (item) {
                    target_preview.append(
                        $("<img>")
                            .css("border", "2px solid blue")
                            .css("border-radius", "10px")
                            .css("box-shadow", "-4px 5px 9px 5px #00000066")
                            .css("height", "auto")
                            .css("width", "100%")
                            .attr("src", item.thumb_url)
                    );
                });

                // trigger change event
                target_preview.trigger("change");
            };
            return false;
        });
    };
})(jQuery);
