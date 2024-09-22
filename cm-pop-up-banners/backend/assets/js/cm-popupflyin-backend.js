var CM_popupflyin_backend = {};
plugin_url = window.cm_popupflyin_backend.plugin_url;

(function ($) {

    $('.cm-help-items-metacontrol').on('click', '.cm-template-control .cm-apply-template', function (e) {

        if (!confirm('Warning! This will change the current content of the editor. Are you sure?')) {
            return false;
        }

        var loadedTemplate = $(this).parents('.cm-template-control').find('select[name*="cm_load_template"]').val();
        var editor = $(this).parents('.customEditor').find('.wp-editor-area');
        var title = $(this).parents('.group-inside').find('.cm-help-item-title');
        var tinyMCEeditor = tinymce.get(editor.attr('id'));

        var data = {
            'action': 'cm_popupflyin_template_api',
            'template': loadedTemplate
        };

        $.post(window.cm_popupflyin_backend.ajaxurl, data, function (response) {

            if (typeof response !== 'undefined')
            {
                if (response.content.length)
                {
                    tinyMCEeditor.focus();
                    tinyMCEeditor.setContent(response.content);
                }
                if (response.title.length)
                {
                    title.val(response.title);
                }
            }

        }, 'json');

        return false;
    });
    $('#user_show_method-flying-bottom').on('change', function (e) {
        var resetField = $('#resetFloatingBottomBannerCookieContainer');
        var howManyTimesField = $('#resetFloatingBottomBannerHowManyTimes');
        if (this.value == 'once') {
            resetField.show();
            return;
        }
        if (this.value == 'fixed_times') {
            resetField.show();
            howManyTimesField.show();
            return;
        }
        resetField.hide();
        howManyTimesField.hide();
    }).change();
    $('#cm-campaign-widget-type').on('change', function (e) {
        var underlayField = $('#underlayTypeContainer');
        if (this.value == 'popup') {
            underlayField.show();
        } else {
            underlayField.hide();
        }
    }).change();
    $('#cm-campaign-widget-when-fire-the-popup').on('change', function (e) {
        var inactivityTypeField = $('#cmCampaignFireMethodInactiveTimeInput');
        var bottomPageFireDistanceInputField = $('#cmCampaignBottomPageFireDistanceInput');
        if (this.value == 'inactive') {
            inactivityTypeField.show();
        } else {
            inactivityTypeField.hide();
        }
        if (this.value == 'pageBottom') {
            bottomPageFireDistanceInputField.show();
        } else {
            bottomPageFireDistanceInputField.hide();
        }
    }).change();
    $('.cm-campaign-sound-effect-type').on('change', function (e) {
        var customPopupSoundContainer = $('#customPopupSoundContainer');
        if (this.value == 'custom' && $(this).is(':checked')) {
            customPopupSoundContainer.show();
        } else {
            customPopupSoundContainer.hide();
        }
    }).change();

    /*
     * accordeon tabs with campain options
     */
    jQuery(document).ready(function ($) {
        $("#cmpopfly-options-group-tabs .options-tab").removeClass('hidden');
        $("#cmpopfly-options-group-tabs").tabs().addClass("ui-tabs-vertical ui-helper-clearfix");
        $("#cmpopfly-options-group-tabs li").removeClass("ui-corner-top").addClass("ui-corner-left");
    });
    /*
     * filling selected banner select and validation
     */
    jQuery(".campaign-display-method").on('change', function () {
        if (jQuery(".campaign-display-method:checked").val() == 'selected') {
            jQuery('#campaign-selected-banner-panel').show();
        } else {
            jQuery('#campaign-selected-banner-panel').hide();
        }
    }).change();
    jQuery(document.body).on('wpa_copy', function () {
        jQuery('#campaign-selected-banner-back').val(jQuery('#cm-campaign-widget-selected-banner').val());
        fillSelectedBannerDropdown();
    });
    jQuery(document.body).on('change', '#cm-campaign-widget-selected-banner', function () {
        jQuery('#campaign-selected-banner-back').val(jQuery('#cm-campaign-widget-selected-banner').val());
    });
    jQuery(document.body).on('wpa_delete', function () {
        jQuery('#campaign-selected-banner-back').val(jQuery('#cm-campaign-widget-selected-banner').val());
        fillSelectedBannerDropdown();
    });

    function fillSelectedBannerDropdown() {
        var bannersArray = jQuery('.wpa_group-cm-help-item-group');
        var lastSelectedBanner = jQuery('#campaign-selected-banner-back').val();
        var selectedBanner = jQuery('#cm-campaign-widget-selected-banner');
        selectedBanner.find('option').remove();
        for (i = 0, ii = bannersArray.length - 1; i < ii; i++) {
            selectedBanner.append('<option value="' + i + '">Banner ' + (i + 1) + '</option>');
        }
        if (bannersArray.length - 1 < selectedBanner) {
            selectedBanner = '';
        }
        selectedBanner.val(lastSelectedBanner);
    }
    fillSelectedBannerDropdown();
    jQuery('p.meta-save button[name="save"]').on('click', function (e) {
        if (jQuery(".campaign-display-method:checked").val() === 'selected') {
            if (!jQuery('#cm-campaign-widget-selected-banner').val()) {
                e.preventDefault();
                alert('Selected banner field is empty!\nPlease select banner or chose "random" display method.');
            }
        }
        jQuery('#dates .date_range_row').each(function () {
            var datesInputs = jQuery(this).children('.date');
            if (datesInputs[0].value === '' && datesInputs[1].value === '') {
                e.preventDefault();
                alert('Activity dates range has been added.\nPlease delete activity dates ranges or fill at least one date.');
            }
        });
    });

    jQuery('#cm-campaign-widget-selected-banner').parents('form').on('submit', function (e) {
        jQuery('#cm-campaign-widget-selected-banner').val(jQuery("#cm-campaign-widget-selected-banner option:first").val());
    });
    /*
     * activity dates
     */
    $('#add_active_date_range').click(function (e) {
        var html;
        e.preventDefault();
        if ($('#dates .date_range_row').length >= 10)
            return;
        if ($('#dates .date_range_row').length === 0)
            $('#dates').empty();
        html = '<div class="date_range_row">';
        html += '<input type="text" name="_cm_advertisement_items_custom_fields[cm-campaign-widget-activity-dates][date_from][]" class="date" />&nbsp;';
        html += '<input class="h_spinner ac_spinner" name="_cm_advertisement_items_custom_fields[cm-campaign-widget-activity-dates][hours_from][]" value="0" />&nbsp;h&nbsp;';
        html += '<input class="m_spinner ac_spinner" name="_cm_advertisement_items_custom_fields[cm-campaign-widget-activity-dates][mins_from][]" value="0" />&nbsp;m';
        html += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="' + plugin_url + '/shared/assets/images/arrow_right.png' + '" style="vertical-align:bottom" />&nbsp;&nbsp;&nbsp;&nbsp;';
        html += '<input type="text" name="_cm_advertisement_items_custom_fields[cm-campaign-widget-activity-dates][date_till][]" class="date" />&nbsp;';
        html += '<input class="h_spinner ac_spinner" name="_cm_advertisement_items_custom_fields[cm-campaign-widget-activity-dates][hours_to][]" value="0" />&nbsp;h&nbsp;';
        html += '<input class="m_spinner ac_spinner" name="_cm_advertisement_items_custom_fields[cm-campaign-widget-activity-dates][mins_to][]" value="0" />&nbsp;m&nbsp;';
        html += '<a href="#" class="delete_link"><img src="' + plugin_url + '/shared/assets/images/close.png' + '" /></a>';
        html += '</div>';
        $('#dates').append(html);
        $('#dates .date_range_row').eq(-1).find('input[type="text"]').datepicker({"dateFormat": "yy-mm-dd"});
        $('#dates .date_range_row').eq(-1).find('.h_spinner').spinner({
            max: 24,
            min: 0
        });
        $('#dates .date_range_row').eq(-1).find('.m_spinner').spinner({
            max: 50,
            min: 0,
            step: 10
        });
        $('#dates .delete_link').eq(-1).bind('click', function (e) {
            e.preventDefault();
            $(this).parent().remove();
            if ($('#dates .date_range_row').length === 0)
                $('#dates').html('There are no date limitations set');
        });
    });
    $('#dates .date_range_row input[type="text"]').datepicker({"dateFormat": "yy-mm-dd"});
    $('.date_range_row .h_spinner').spinner({
        max: 24,
        min: 0
    });
    $('.date_range_row .m_spinner').spinner({
        max: 50,
        min: 0,
        step: 10
    });
    $('#dates .delete_link').click(function (e) {
        e.preventDefault();
        $(this).parent().remove();
        if ($('#dates .date_range_row').length === 0)
            $('#dates').html('There are no date limitations set');
    });
    
    $(document).on('click', '.adddesigner_trigger', function () {
        jQuery('#cmac_addesigner_container').dialog({
            height: 400,
            width: 600,
            minWidth: 600,
            minHeight: 400,
            position: {my: "center", at: "center", of: window},
            modal: false,
            closeText: "",
            classes: {
                "ui-dialog": "ad-designer"
            }
        });

        return false;
    });

})(jQuery);