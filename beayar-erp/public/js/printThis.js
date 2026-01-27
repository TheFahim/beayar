/*
 * printThis v2.0.0
 * @desc Printing plug-in for jQuery
 * @author Jason Day
 * @author Samuel Rouse
 *
 * Resources (based on):
 * - jPrintArea: http://plugins.jquery.com/project/jPrintArea
 * - jqPrint: https://github.com/permanenttourist/jquery.jqprint
 * - Ben Nadal: http://www.bennadel.com/blog/1591-Ask-Ben-Print-Part-Of-A-Web-Page-With-jQuery.htm
 *
 * Licensed under the MIT licence:
 *              http://www.opensource.org/licenses/mit-license.php
 *
 * (c) Jason Day 2015-2022
 *
 * Usage:
 *
 *  $("#mySelector").printThis({
 *      debug: false,                   // show the iframe for debugging
 *      importCSS: true,                // import parent page css
 *      importStyle: true,              // import style tags
 *      printContainer: true,           // grab outer container as well as the contents of the selector
 *      loadCSS: "path/to/my.css",      // path to additional css file - use an array [] for multiple
 *      pageTitle: "",                  // add title to print page
 *      removeInline: false,            // remove all inline styles from print elements
 *      removeInlineSelector: "body *", // custom selectors to filter inline styles. removeInline must be true
 *      printDelay: 1000,               // variable print delay
 *      header: null,                   // prefix to html
 *      footer: null,                   // postfix to html
 *      base: false,                    // preserve the BASE tag, or accept a string for the URL
 *      formValues: true,               // preserve input/form values
 *      canvas: true,                   // copy canvas elements
 *      doctypeString: '...',           // enter a different doctype for older markup
 *      removeScripts: false,           // remove script tags from print content
 *      copyTagClasses: true            // copy classes from the html & body tag
 *      copyTagStyles: true,            // copy styles from html & body tag (for CSS Variables)
 *      beforePrintEvent: null,         // callback function for printEvent in iframe
 *      beforePrint: null,              // function called before iframe is filled
 *      afterPrint: null                // function called before iframe is removed
 *  });
 *
 * Notes:
 *  - the loadCSS will load additional CSS (with or without @media print) into the iframe, adjusting layout
 */
;
(function($) {

    function appendContent($el, content) {
        if (!content) return;

        // Simple test for a jQuery element
        $el.append(content.jquery ? content.clone() : content);
    }

    function appendBody($body, $element, opt) {
        // Clone for safety and convenience
        // Calls clone(withDataAndEvents = true) to copy form values.
        var $content = $element.clone(opt.formValues);

        if (opt.formValues) {
            // Copy original select and textarea values to their cloned counterpart
            // Makes up for inability to clone select and textarea values with clone(true)
            copyValues($element, $content, 'select, textarea');
        }

        if (opt.removeScripts) {
            $content.find('script').remove();
        }

        if (opt.printContainer) {
            // grab $.selector as container
            $content.appendTo($body);
        } else {
            // otherwise just print interior elements of container
            $content.each(function() {
                $(this).children().appendTo($body)
            });
        }
    }

    // Copies values from origin to clone for passed in elementSelector
    function copyValues(origin, clone, elementSelector) {
        var $originalElements = origin.find(elementSelector);

        clone.find(elementSelector).each(function(index, item) {
            $(item).val($originalElements.eq(index).val());
        });
    }

    var opt;
    $.fn.printThis = function(options) {
        opt = $.extend({}, $.fn.printThis.defaults, options);
        var $element = this instanceof jQuery ? this : $(this);

        var strFrameName = "printThis-" + (new Date()).getTime();

        if (window.location.hostname !== document.domain && navigator.userAgent.match(/msie/i)) {
            // Ugly IE hacks due to IE not inheriting document.domain from parent
            // checks if document.domain is set by comparing the host name against document.domain
            var iframeSrc = "javascript:document.write(\"<head><script>document.domain=\\\"" + document.domain + "\\\";</script></head><body></body>\")";
            var printI = document.createElement('iframe');
            printI.name = "printIframe";
            printI.id = strFrameName;
            printI.className = "MSIE";
            document.body.appendChild(printI);
            printI.src = iframeSrc;

        } else {
            // other browsers inherit document.domain, and MSIE checks if document.domain matches hostname
            var $frame = $("<iframe id='" + strFrameName + "' name='printIframe' />");
            $frame.appendTo("body");
        }

        var $iframe = $("#" + strFrameName);

        // show frame if in debug mode
        if (!opt.debug) $iframe.css({
            position: "absolute",
            width: "0px",
            height: "0px",
            left: "-600px",
            top: "-600px"
        });

        // wait for iframe to load all content
        setTimeout(function() {

            // Add doctype to fix the style difference between printing and render
            function setDocType($iframe, doctype) {
                var win, doc;
                win = $iframe.get(0);
                win = win.contentWindow || win.contentDocument || win;
                doc = win.document || win.contentDocument || win;
                doc.open();
                doc.write(doctype);
                doc.close();
            }

            if (opt.doctypeString) {
                setDocType($iframe, opt.doctypeString);
            }

            var $doc = $iframe.contents(),
                $head = $doc.find("head"),
                $body = $doc.find("body"),
                $base = $('base'),
                baseURL;

            // add base tag to ensure elements use the parent domain
            if (opt.base === true && $base.length > 0) {
                // take the base tag from the original page
                baseURL = $base.attr('href');
                $head.append('<base href="' + baseURL + '">');
            } else if (typeof opt.base === 'string') {
                // accept a string as the URL for the base tag
                $head.append('<base href="' + opt.base + '">');
            }

            // import page stylesheets
            if (opt.importCSS) $("link[rel=stylesheet]").each(function() {
                var href = $(this).attr("href");
                if (href) {
                    var media = $(this).attr("media") || "all";
                    $head.append("<link type='text/css' rel='stylesheet' href='" + href + "' media='" + media + "'>");
                }
            });

            // import style tags
            if (opt.importStyle) $("style").each(function() {
                $head.append(this.outerHTML);
            });

            // add title of the page
            if (opt.pageTitle) $head.append("<title>" + opt.pageTitle + "</title>");

            // import additional stylesheet(s)
            if (opt.loadCSS) {
                if ($.isArray(opt.loadCSS)) {
                    jQuery.each(opt.loadCSS, function(i, value) {
                        $head.append("<link type='text/css' rel='stylesheet' href='" + this + "'>");
                    });
                } else {
                    $head.append("<link type='text/css' rel='stylesheet' href='" + opt.loadCSS + "'>");
                }
            }

            var pageHtml = $('html')[0];

            // copy 'root' tag classes
            if (opt.copyTagClasses) {
                $doc.find('html').addClass(pageHtml.className);
                $body.addClass($('body')[0].className);
            }

            // copy 'root' tag styles
            if (opt.copyTagStyles) {
                $doc.find('html').attr('style', pageHtml.style.cssText);
                $body.attr('style', $('body')[0].style.cssText);
            }

            // print header
            appendContent($body, opt.header);

            if (opt.canvas) {
                // add canvas data-ids for easy access after cloning.
                var canvasId = 0;
                // .addBack('canvas') adds the top-level element if it is a canvas.
                $element.find('canvas').addBack('canvas').each(function() {
                    $(this).attr('data-printthis', canvasId++);
                });
            }

            appendBody($body, $element, opt);

            if (opt.canvas) {
                // Re-draw new canvases by referencing the originals
                $body.find('canvas').each(function() {
                    var cid = $(this).data('printthis'),
                        $src = $('[data-printthis="' + cid + '"]');

                    this.getContext('2d').drawImage($src[0], 0, 0);

                    // Remove the markup from the original
                    $src.removeAttr('data-printthis');
                });
            }

            // remove inline styles
            if (opt.removeInline) {
                // $.isFunction checks if the variable is a function and if it is,
                // it calls it with the elements.
                if ($.isFunction(opt.removeInline)) {
                    opt.removeInline($body);
                } else {
                    // Remove inline styles from the body and all its children.
                    var selector = opt.removeInlineSelector || "body *";
                    $body.find(selector).removeAttr("style");
                }
            }

            // print "footer"
            appendContent($body, opt.footer);

            // attach event handler function to beforePrint event
            function attachOnBeforePrintEvent($iframe, beforePrintHandler) {
                var win = $iframe.get(0);
                win = win.contentWindow || win.contentDocument || win;

                if (typeof beforePrintHandler === "function") {
                    if ('matchMedia' in win) {
                        win.matchMedia('print').addListener(function(mql) {
                            if (mql.matches) beforePrintHandler();
                        });
                    } else {
                        win.onbeforeprint = beforePrintHandler;
                    }
                }
            }

            if (opt.beforePrintEvent) {
                attachOnBeforePrintEvent($iframe, opt.beforePrintEvent);
            }

            setTimeout(function() {
                if ($iframe.hasClass("MSIE")) {
                    // check if the iframe was created with the ugly hack
                    // and call the print function of the iframe's window
                    window.frames["printIframe"].focus();
                    $head.append("<script>window.print();</script>");
                } else {
                    // proper method
                    if (document.queryCommandSupported("print")) {
                        $iframe[0].contentWindow.document.execCommand("print", false, null);
                    } else {
                        $iframe[0].contentWindow.focus();
                        $iframe[0].contentWindow.print();
                    }
                }

                // remove iframe after print
                if (!opt.debug) {
                    setTimeout(function() {
                        $iframe.remove();

                    }, 1000);
                }

                if (opt.afterPrint) {
                    opt.afterPrint();
                }

            }, opt.printDelay);

        }, 333);

    };

    $.fn.printThis.defaults = {
        debug: false, // show the iframe for debugging
        importCSS: true, // import parent page css
        importStyle: true, // import style tags
        printContainer: true, // print outer container/$.selector
        loadCSS: "", // path to additional css file - use an array [] for multiple
        pageTitle: "", // add title to print page
        removeInline: false, // remove all inline styles
        removeInlineSelector: "*", // custom selectors to filter inline styles. removeInline must be true
        printDelay: 1000, // variable print delay
        header: null, // prefix to html
        footer: null, // postfix to html
        base: false, // preserve the BASE tag, or accept a string for the URL
        formValues: true, // preserve input/form values
        canvas: true, // copy canvas elements
        doctypeString: '<!DOCTYPE html>', // enter a different doctype for older markup
        removeScripts: false, // remove script tags from print content
        copyTagClasses: true, // copy classes from the html & body tag
        copyTagStyles: true, // copy styles from html & body tag (for CSS Variables)
        beforePrintEvent: null, // callback function for printEvent in iframe
        beforePrint: null, // function called before iframe is filled
        afterPrint: null // function called before iframe is removed
    };

})(jQuery);
