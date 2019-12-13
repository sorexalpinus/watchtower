function iframeform($iframe) {
    var object = this;
    object.time = new Date().getTime();
    object.iframe = $iframe;
    var url = $iframe.data("src");
    var iframeName = $iframe.attr("name");
    console.log(url);
    console.log(iframeName);
    object.form = $('<form action="' + url + '" target="' + iframeName + '" method="post" style="display:none;" id="form' + object.time + '" name="form' + object.time + '"></form>');
    object.addParameter = function (parameter, value) {
        $("<input type='hidden' />")
            .attr("name", parameter)
            .attr("value", value)
            .appendTo(object.form);
    };
    object.send = function () {
        $("body").append(object.form);
        object.form.submit();
    }
}

function toggleExpand() {
    $("button.expand-error").on("click", function () {
        var $button = $(this);
        var $iframe = $(this).closest(".wt-minibox-wrapper").find(".wt-mainbox iframe");
        $iframe.attr("src", $iframe.data("src"));
        var ifform = new iframeform($iframe);
        ifform.addParameter('handler', $iframe.data('handler'));
        ifform.addParameter('event', $iframe.data('event'));
        ifform.addParameter('type', $iframe.data('type'));
        ifform.send();
        if ($button.attr("action") === "expand") {
            var thisId = $button.closest(".wt-minibox-wrapper").attr("id");
            $button.attr("action", "collapse");
            $button.html("COLLAPSE");
            $button.closest(".wt-minibox-wrapper").find(".wt-mainbox").removeClass("collapsed");
            $("div#" + thisId + " .frame.active").trigger("click");
        } else {
            $button.attr("action", "expand");
            $button.html("EXPAND");
            $button.closest(".wt-minibox-wrapper").find(".wt-mainbox").removeClass("expanded").addClass("collapsed");
        }
    });
}

$("document").ready(function () {
    toggleExpand();
});