jQuery(function ($) {

    /*  Functions  */

    function cm_compare_array(haystack, needle, multiple = true) {
        var needleLength = needle.length;
        var counter = 0;
        for (const needleKey in needle) {
            if (multiple === false) {
                if (needle[needleKey] == haystack) {
                    return true;
                }
            }
            else {
                for (const haystackKey in haystack) {
                    if (needle[needleKey] == haystack[haystackKey]) {
                        counter++;
                    }
                }
            }
        }
        return (needleLength == counter);
    }

    /*  Events  */

    if (Settings.optionsWithCondition.length) {
        var options = Settings.optionsWithCondition;
        for (var i = 0; i < options.length; i++) {

            let handledOption = options[i].handledOption;
            let conditionOption = options[i].conditionOption;
            var result = {};
            let $handledOption = $('.' + handledOption);

            for (const conditionOptionKey in conditionOption) {
                let currentConditionOption = conditionOption[conditionOptionKey];

                $('.' + currentConditionOption.name).find('select,input').each(function(i,input) {
                    $(input).change(function () {
                        if (Array.isArray(currentConditionOption.value)) {
                            result[currentConditionOption.name] = cm_compare_array($(this).val(), currentConditionOption.value, currentConditionOption.type.includes("multi"));
                        }
                        else {
                            result[currentConditionOption.name] = $(this).val() == currentConditionOption.value;
                        }
                        for (let resultKey in result) {
                            if (result[resultKey] === false) {
                                $handledOption.slideUp();
                                if (conditionOption.length === 1) {
                                    result = {};
                                }
                                return;
                            }
                        }
                        $handledOption.slideDown();
                    });
                });
            }
        }
    }

    var $body = $('body');

    $body.on('mouseenter', '.cm_field_help', function () {
        if ($(this).find('.cm_field_help--wrap').length > 0) {
            return;
        }
        var helpHtml = $(this).attr('data-title');
        var $helpItemWrapHeight = "style='min-height:" + $(this).parent().outerHeight() + "px'";
        var $helpItemWrap = $("<div class='cm_field_help--wrap'" + $helpItemWrapHeight + "><div class='cm_field_help--text'></div></div>");

        $(this).append($helpItemWrap);

        var $helpItemText = $(this).find('.cm_field_help--text');
        $helpItemText.html(helpHtml);
        $helpItemText.html($helpItemText.text());

        setTimeout(function () {
            $helpItemWrap.addClass('cm_field_help--active');
        }, 300);
    }).on('mouseleave', '.cm_field_help', function() {
        var $helpItem = $(this).find('.cm_field_help--wrap');
        setTimeout(function () {
            $helpItem.removeClass('cm_field_help--active');
        }, 600);
        setTimeout(function () {
            $helpItem.remove();
        }, 800);

    });


    $body.on('click', '.section-title', function () {
        $(this).siblings('table').slideToggle(300, "linear");
        $(this).find('.tab-arrow').toggleClass('tab-arrow--rotate');
    });


    $body.on('click', '.cminds_settings_toggle_tabs', function () {

        var $toggleBtn = $(this);
        var $sectionTitle = $(this).parent().find('.section-title');

        if ($(this).hasClass('cminds_settings_toggle-opened')) {
            $sectionTitle.each(function () {
                $(this).siblings('table').slideUp(300, "linear");
                $(this).find('.tab-arrow').addClass('tab-arrow--rotate');
                $toggleBtn.removeClass('cminds_settings_toggle-opened');
            });
        }
        else {
            $sectionTitle.each(function () {
                $(this).siblings('table').slideDown(300, "linear");
                $(this).find('.tab-arrow').removeClass('tab-arrow--rotate');
                $toggleBtn.addClass('cminds_settings_toggle-opened');
            });
        }
    });


    $body.on('click','.settings-tabs-link', function (e) {
        e.preventDefault();
        history.pushState(null,null, e.currentTarget.hash);
        var $currentTabItem = $(this).parent();
        var $currentTabTarget = $(this).attr('href').replace('#', '');

        $('.settings-tabs-item').removeClass('settings-tabs-active');
        $('.settings-tab').hide();

        $currentTabItem.addClass('settings-tabs-active');
        $('#' + $currentTabTarget).show();

    });


    $body.on('click','#cminds_settings_search_clear', function() {
        $('#cminds_settings_search').val('');
        $('#cminds_settings_search').trigger('keyup');
    });


    $body.on('click','.cm-settings-collapse-btn', function() {
        var time = 300;
        var btn = $(this);
        var content = btn.next();
        if (content.hasClass('cm-settings-collapse-open')) {
            btn.find('.dashicons').removeClass('dashicons-arrow-down').addClass('dashicons-arrow-right');
            content.slideUp(time, function() {
                content.removeClass('cm-settings-collapse-open');
                content.addClass('cm-settings-collapse-close');
            });
        } else {
            btn.find('.dashicons').removeClass('dashicons-arrow-right').addClass('dashicons-arrow-down');
            content.slideDown(time, function() {
                content.addClass('cm-settings-collapse-open');
                content.removeClass('cm-settings-collapse-close');
            });
        }
    });


    $body.on('click', '.cm-settings-collapse-toggle', function() {
        var container = $(this).parents('.cma-tab-content');
        if (container.find('.cm-settings-collapse-open').length == 0) {
            container.find('.cm-settings-collapse-btn').click();
        } else {
            container.find('.cm-settings-collapse-open').parents('.cma-settings-section').find('.cm-settings-collapse-btn').click();
        }
    });

    // library

    $('.field-multiselect > div > select').select2({width: '100%'});

    // Tooltips and tabs

    $('.cm_field_help_container').each(function () {
        var newElement,
            element = $(this);

        newElement = $('<div class="cm_field_help"></div>');
        newElement.attr('data-title', element.html());

        if (element.siblings('th').length) {
            element.siblings('th').append(newElement);
        } else {
            element.siblings('*').append(newElement);
        }
        element.remove();
    });

    var activeTab = $('.settings-tabs-active > .settings-tabs-link').attr('href').replace('#', '');
    $('.settings-tab').each(function () {
        if($(this).attr('id') != activeTab) {
            $(this).hide();
        }
    });

    if(window.location.hash.includes('tabs-')) {
        $('a[href="' + window.location.hash + '"]').trigger('click');
    }


    // Search


    var highlightTextInNode = function(node, text) {
        if (!text || text.length == 0) return;
        if (!node) return;
        if (node.parentNode.nodeName == 'SPAN' && node.parentNode.className == 'cma-hl' || node.parentNode.nodeName == 'TEXTAREA') {
            return;
        }
        else if (node.nodeType == document.ELEMENT_NODE) {
            if (node.nodeName == 'SPAN' && node.className == 'cma-hl') {
                // do nothing
            }
            else for (var i=0; i<node.childNodes.length; i++) {
                highlightTextInNode(node.childNodes[i], text);
            }
        }
        else if (node.nodeType == document.TEXT_NODE) {
//			console.log(node.textContent);
            var pos = node.textContent.toLowerCase().indexOf(text.toLowerCase());
            if (pos > -1) {
//				console.log(node.textContent);
                var html = node.textContent.substr(0, pos) + '<span class="cma-hl">'
                    + node.textContent.substr(pos, text.length) + '</span>' + node.textContent.substr(pos+text.length, node.textContent.length);
                $(node).replaceWith(html);
            }
        }
    };
    var clearHighlight = function(node) {
        $(node).find('.cma-hl').each(function() {
            var text = $(this).text();
            var outerHTML = $(this).parent().html();
//			console.log(outerHTML);
            if (outerHTML) {
                outerHTML = outerHTML.replace(/<span class="cma-hl">(.+)<\/span>/g, text);
                $(this).parent().html(outerHTML);
            }
//			$(this).replaceWith(this.textContent);
        });
    };

    var settingsSearchTimeout = null;
    $('#cminds_settings_search').keyup(function() {
        if (this.lastValue == this.value) return;
        this.lastValue = this.value;
        var input = $(this);
        clearTimeout(settingsSearchTimeout);
        settingsSearchTimeout = setTimeout(function() {
            clearHighlight(document.getElementById('cminds_settings_form'));
            highlightTextInNode(document.getElementById('cminds_settings_form'), input.val());
            runSettingsSearch(input);
        }, 500);
    });

    var runSettingsSearch = function(input) {

//		console.log(input.val());

        // Show or hide clear btn
        if (input.val().length == 0) {
            $('#cminds_settings_search_clear').hide();
        } else {
            $('#cminds_settings_search_clear').show();
        }
        // Search in rows
        $('#cminds_settings_form tr').each(function() {
            var row = $(this);
            if (input.val().length == 0 || this.textContent.toLowerCase().indexOf(input.val().toLowerCase()) > -1) {
//				console.log(row);
                row.show();
            } else {
                row.hide();
            }
        });
        // Hide sections
        $('#cminds_settings_form .block').each(function() {
            var section = $(this);
            if (input.val().length == 0 || this.textContent.toLowerCase().indexOf(input.val().toLowerCase()) > -1) {
                section.show();
                if (input.val().length > 0) {
                    //console.log('open')
                    section.find('.cm-settings-collapse-container').show().removeClass('cm-settings-collapse-close').addClass('cm-settings-collapse-open');
                    section.find('.cm-settings-collapse-btn .dashicons-arrow-right').removeClass('dashicons-arrow-right').addClass('dashicons-arrow-down');
                } else {
                    //console.log('close')
                    section.find('.cm-settings-collapse-container').hide().removeClass('cm-settings-collapse-open').addClass('cm-settings-collapse-close');
                    section.find('.cm-settings-collapse-btn.dashicons-arrow-down').removeClass('dashicons-arrow-down').addClass('dashicons-arrow-right');
                }
            } else {
                section.hide();
            }
        });
        // Hide tabs
        $('#cminds_settings_form .settings-tab').each(function() {
            var tab = $('.settings-tabs-wrapper').find('a[href="#'+ this.id +'"]');
            if (input.val().length == 0 || this.textContent.toLowerCase().indexOf(input.val().toLowerCase()) > -1) {
                tab.show();
            } else {
                tab.hide();
            }
        });


        if ($('.settings-tabs-wrapper .settings-tabs-active a:visible').length == 0) {
            $('.settings-tabs-wrapper .settings-tabs-item a:visible').first().click();
        }
    };

    var $split_input = $('input[name="cma_split_between_admin_and_author"]');
    if ($split_input.length > 0 ){
        var value = parseInt($split_input.val());
        $('.admin-value').text(value);
        $('.author-value').text(100-value);

        $split_input.on('input', function(e){
            value = parseInt($(e.target).val());
            $('.admin-value').text(value);
            $('.author-value').text(100-value);
        });
    }

});