$(function() {
    var attachMainBehaviors = function() {
        $("select[name=type]").on("change", function() {
            var selected = $(this).find("option:selected");
            window.location.href = selected.val();
        });

        $("select[name=filetype]").on("change", function() {
            var selected = $(this).find("option:selected"),
                val = selected.val(),
                dpi = $("input[name=dpi]"),
                dpiUnavailable = $("#dpiUnavailable");

            if (val === "PNG" || val === "JPEG") {
                dpi.prop("disabled", false);
                dpiUnavailable.hide();
            } else {
                dpi.prop("disabled", true);
                dpiUnavailable.show();
            }
        }).change();

        var text = $("input[name=text]");

        $("#validCharacters").on("click", "[data-output]", function() {
            var $this = $(this),
                escaped = $this.data("escaped"),
                value = $this.data("output");
            if (escaped) {
                value = unescape(value);
            }

            text
                .val(text.val() + value)
                .focus();
        });
    }, attachUIBehaviors = function() {
        $("table").each(function() {
            var $this = $(this);
            $this.find("tr:even").addClass("even");
            $this.find("tr:odd").addClass("odd");
        });
    }, attachSpecificBehaviors = function() {
        $("#specificOptions li").each(function() {
            var $this = $(this),
                code = $("<tr><td class='title'></td><td class='value'></td></tr>");
                code.find(".title").append($this.find(".title"));
                code.find(".value").append($this.find(".value"));
            
            $("div.configurations tr:last").before(code);
        });
    }, attachInfoBehaviors = function() {
        var showTooltip = function(object) {
            object
                .on("mouseover", function() {
                    var timer = $(this).data("timer");
                    if (timer) {
                        clearTimeout(timer);
                    }
                })
                .on("mouseout", function() {
                    var that = $(this);
                    that.data("timer", setTimeout(function() {
                        that.removeClass("visible");
                    }, 1000));
                });

            return function() {
                    var $this = $(this),
                        offset = $this.offset(),
                        timer = object.data("timer");

                    if (timer) {
                        clearTimeout(timer);
                    }

                    // Show it once so we can get the outerWidth properly
                    object
                        .css({
                            left: -99999,
                            top: -99999
                        })
                        .addClass("visible")
                        .css({
                            left: offset.left + $this.width() - object.outerWidth(),
                            top: offset.top + $this.height()
                        });
                    return false;
                };
            },
            hideTooltip = function(object) {
                return function() {
                    object.data("timer", setTimeout(function() {
                        object.removeClass("visible");
                    }, 1000));
                };
            },
            bubbleize = function(object) {
                return object
                    .addClass("bubble")
                    .attr("role", "tooltip")
                    .appendTo(document.body);
            },
            explanation = bubbleize($("#explanation")),
            validCharacters = bubbleize($("#validCharacters"));

        $(".info.explanation")
            .on("mouseover focusin", showTooltip(explanation))
            .on("mouseout focusout", hideTooltip(explanation));

        $(".info.characters")
            .on("mouseover focusin", showTooltip(validCharacters))
            .on("mouseout focusout", hideTooltip(validCharacters));
    };

    attachSpecificBehaviors();
    attachMainBehaviors();
    attachUIBehaviors();
    attachInfoBehaviors();
});