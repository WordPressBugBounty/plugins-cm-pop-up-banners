
function safex( e, t ) {
    return typeof e === "undefined" ? t : e;
}

cmpopfly_AjaxRequestSent = false;

function cmpopfly_sendAjaxClickData( cmpopfly_ClicksCounter, AjaxRequestAsync )
{
    jQuery( document ).ready( function ( $ )
    {
        if ( popup_custom_data.preview != true )
        {
            AjaxRequestAsync = typeof AjaxRequestAsync !== 'undefined' ? AjaxRequestAsync : true;
            $.ajax( {
                'url': popup_custom_data.ajaxClickUrl,
                'type': 'post',
                'async': AjaxRequestAsync,
                'complete': cmpopfly_resetAjax,
                'data':
                    {
                        campaign_id: popup_custom_data.campaign_id,
                        banner_id: popup_custom_data.banner_id,
                        amount: cmpopfly_ClicksCounter,
                    }
            } );

            if ( popup_custom_data.countingMethod != 'one' )
            {
                // all clicks
                cmpopfly_ClicksCounter = 0;
            }
        }
    } );
}

var cmpopfly_resetAjax = function ()
{
    if ( popup_custom_data.countingMethod != 'one' )
    {
        // all clicks
        cmpopfly_AjaxRequestSent = false;
        cmpopfly_ClicksCounter = 0;
    }
}

function cmpopfly_setCookie( cname, cvalue, exdays, glob_path )
{
    var d, expires, path;

    if ( popup_custom_data.showMethod === 'only_once' || glob_path ) {
        path = ";path=/";
    } else {
        path = "";
    }

    if ( exdays > 0 ) {
        d = new Date();
        d.setTime( d.getTime() + ( exdays * 24 * 60 * 60 * 1000 ) );
        expires = "expires=" + d.toUTCString();
    } else {
        expires = "expires=Thu, 01 Jan 1970 00:00:01 GMT";
        path = ";path=/";
    }
    document.cookie = cname + "=" + cvalue + ";" + expires + path;
}

function cmpopfly_getCookie( cname )
{
    var name = cname + "=";
    var ca = document.cookie.split( ';' );
    for ( var i = 0; i < ca.length; i++ )
    {
        var c = ca[i];
        while ( c.charAt( 0 ) == ' ' )
            c = c.substring( 1 );
        if ( c.indexOf( name ) != -1 )
            return c.substring( name.length, c.length );
    }
    return "";
}

jQuery( document ).ready( function ( $ )
{
    //$( "#ouibounce-modal .modal .modal-body img" ).error( function () {
    //    $( this ).hide();
    //} );

    var local_ouibounce;
    var cmpopfly_cookieName = 'ouibounceBannerShown-' + popup_custom_data.campaign_id;
    var cmpopfly_cookie = cmpopfly_getCookie( cmpopfly_cookieName );
    var cmpopfly_fixedNumberOfTimesCookieName = 'ouibounceBannerBottomShownNumberOfTimes-' + popup_custom_data.campaign_id;
//    var cmpopfly_fixedNumberOfTimesCookie = cmpopfly_getCookie( cmpopfly_fixedNumberOfTimesCookieName );
    var local_ouibounce_sound_path = '';
    var inactivityDelay = popup_custom_data.inactivityTime * 1000;
    var _localOuibounceDelayTimer = null;
    var ouibounceBannerShown = false;
    if ( popup_custom_data.soundMethod &&
        popup_custom_data.soundMethod == 'custom' &&
        popup_custom_data.customSoundPath &&
        popup_custom_data.customSoundPath != '' ) {
        local_ouibounce_sound_path = popup_custom_data.customSoundPath;
    }
    if ( popup_custom_data.soundMethod &&
        popup_custom_data.soundMethod == 'default' &&
        popup_custom_data.standardSound ) {
        local_ouibounce_sound_path = popup_custom_data.standardSound;
    }
    var local_ouibounce_sound = local_ouibounce_sound_path ? new Audio( local_ouibounce_sound_path ) : false;

    function cminds_popup_callback() {

        $( '#ouibounce-modal' ).css( 'display', 'flex' );
        var first_image = $( '#ouibounce-modal .modal .modal-body.auto-size img:eq(0)' );
        resize_modal = function () {
            var width = $( '#ouibounce-modal .modal .modal-body' ).width();
            var height = $( '#ouibounce-modal .modal .modal-body' ).height();

            $( '#ouibounce-modal .modal' ).css( 'width', width );
            $( '#ouibounce-modal .modal' ).css( 'height', height );
        };

        if ( first_image.length ) {
            first_image.on( 'load', resize_modal );
        } else {
//            resize_modal();
        }

        $( '.modal.linked' ).on( 'click', function () {
            $( this ).parents( '#ouibounce-modal' ).find( '.cmpopfly-fullbody-link' )[0].click();
        } );

        $( '#ouibounce-modal' ).trigger( 'resize' );
    }

    function fireOuibounce( cookie, cookieName, fixedNumberOfTimesCookieName )
    {
        var fixedNumberOfTimesCookie, parsedfixedNumberOfTimesCookie;

        if ( fixedNumberOfTimesCookieName.length ) {
            fixedNumberOfTimesCookie = cmpopfly_getCookie( fixedNumberOfTimesCookieName );
            parsedfixedNumberOfTimesCookie = parseInt( fixedNumberOfTimesCookie );
        } else {
            fixedNumberOfTimesCookie = '';
            parsedfixedNumberOfTimesCookie = 0;
        }
        if ( cookie === '' &&
            ( popup_custom_data.showMethod != 'fixed_times' ||
                fixedNumberOfTimesCookie == '' ||
                parsedfixedNumberOfTimesCookie < parseInt( popup_custom_data.showFixedNumberOfTimes ) ||
                isNaN( parsedfixedNumberOfTimesCookie ) ) )
        {
            if ( $( "#ouibounce-modal" ).length == 0 ) {
                $( "body" ).append( safex( popup_custom_data.content, '' ) );
            }
            local_ouibounce = ouibounce( document.getElementById( 'ouibounce-modal' ), { callback: cminds_popup_callback } );
            setTimeout( function ()
            {
                local_ouibounce.fire();
                ouibounceBannerShown = true;
                if ( local_ouibounce_sound ) {
                    local_ouibounce_sound.play();
                }

                if ( popup_custom_data.showMethod === 'once' || popup_custom_data.showMethod === 'only_once' )
                {
                    cmpopfly_setCookie( cookieName, 'true', popup_custom_data.resetTime );
                }
                if ( popup_custom_data.showMethod === 'fixed_times' )
                {
                    if ( typeof parsedfixedNumberOfTimesCookie == "number" && !isNaN( parsedfixedNumberOfTimesCookie ) ) {

                        cmpopfly_setCookie( fixedNumberOfTimesCookieName, '', -1 );
                        cmpopfly_setCookie( fixedNumberOfTimesCookieName, parsedfixedNumberOfTimesCookie + 1, popup_custom_data.resetTime );
                    } else {
                        cmpopfly_setCookie( fixedNumberOfTimesCookieName, 1, popup_custom_data.resetTime );
                    }

                }
                if ( popup_custom_data.closeOnUnderlayClick ) {
                    $( 'body' ).on( 'click.popup_close_underlay', function ()
                    {
                        local_ouibounce.close();
                        document.documentElement.removeEventListener( 'mouseleave', handlePopupMouseLeave );
                        document.documentElement.removeEventListener( 'mousemove', handleDelayMouseAndKeyMove );
                        document.documentElement.removeEventListener( 'keydown', handleDelayMouseAndKeyMove );
                        $( 'body' ).off( '.popup_close_underlay' );
                    }
                    );
                }
            }, popup_custom_data.secondsToShow );

            $( '#ouibounce-modal #close_button' ).on( 'click', function ()
            {
                local_ouibounce.close();
                document.documentElement.removeEventListener( 'mouseleave', handlePopupMouseLeave );
                document.documentElement.removeEventListener( 'mousemove', handleDelayMouseAndKeyMove );
                document.documentElement.removeEventListener( 'keydown', handleDelayMouseAndKeyMove );
            } );

            if ( popup_custom_data.enableStatistics ) {
                $( '#ouibounce-modal .popupflyin-clicks-area' ).on( 'click', function () {
                    if ( typeof cmpopfly_ClicksCounter === 'undefined' ) {
                        cmpopfly_ClicksCounter = 0;
                    }

                    if ( !cmpopfly_AjaxRequestSent )
                    {
                        if ( popup_custom_data.countingMethod === 'one' )
                        {
                            // one click
                            cmpopfly_ClicksCounter = 1;
                            cmpopfly_sendAjaxClickData( cmpopfly_ClicksCounter, true );
                            cmpopfly_AjaxRequestSent = true;
                        } else
                        {
                            // all clicks
                            cmpopfly_ClicksCounter = 1;
                            cmpopfly_sendAjaxClickData( cmpopfly_ClicksCounter, true );
                            cmpopfly_AjaxRequestSent = false;
                        }
                    }
                } );
            }

            $( '#ouibounce-modal .modal' ).on( 'click', function ( e )
            {
                /*
                 * Fixes the conflict with CM Registration, but caused the problems with the forms inside modal
                 */
                e.stopPropagation();
            } );
        }
    }

    if ( screen.width >= parseInt( popup_custom_data.minDeviceWidth )
        && ( popup_custom_data.maxDeviceWidth == "0" || screen.width <= parseInt( popup_custom_data.maxDeviceWidth ) ) ) {
        if ( popup_custom_data.showMethod === 'always' ) {
            cmpopfly_setCookie( cmpopfly_cookieName, '', -1 );
            cmpopfly_cookie = '';
        }
        if ( popup_custom_data.showMethod != 'fixed_times' ) {
            cmpopfly_setCookie( cmpopfly_fixedNumberOfTimesCookieName, '0', 1, true );
            cmpopfly_setCookie( cmpopfly_fixedNumberOfTimesCookieName, '0', 1 );
            cmpopfly_fixedNumberOfTimesCookieName = '';
        }
        if ( popup_custom_data.fireMethod === 'pageload' )
        {
            fireOuibounce( cmpopfly_cookie, cmpopfly_cookieName, cmpopfly_fixedNumberOfTimesCookieName );
        }

        if ( popup_custom_data.fireMethod === 'click' )
        {
            $( '.cm-pop-up-banners-trigger' ).on( 'click', function () {
                fireOuibounce( cmpopfly_cookie, cmpopfly_cookieName, cmpopfly_fixedNumberOfTimesCookieName );
            } );
        }

        if ( popup_custom_data.fireMethod === 'hover' )
        {
            $( '.cm-pop-up-banners-trigger' ).on( 'mouseenter', function () {
                fireOuibounce( cmpopfly_cookie, cmpopfly_cookieName, cmpopfly_fixedNumberOfTimesCookieName );
            } );
        }
        if ( popup_custom_data.fireMethod === 'leave' )
        {
            document.documentElement.addEventListener( 'mouseleave', handlePopupMouseLeave );
        }
        if ( popup_custom_data.fireMethod === 'inactive' )
        {
            document.documentElement.addEventListener( 'mousemove', handleDelayMouseAndKeyMove );
            document.documentElement.addEventListener( 'keydown', handleDelayMouseAndKeyMove );
        }
        if ( popup_custom_data.fireMethod === 'pageBottom' )
        {
            $( 'body' ).append( '<div style="clear: both"></div><div id="cm-pop-up-banners-scrollspy-marker" class="cm-pop-up-banners-scrollspy-marker"></div>' );
            $( '#cm-pop-up-banners-scrollspy-marker' ).on( 'scrollSpy:enter', function () {
                if ( !ouibounceBannerShown ) {
                    fireOuibounce( cmpopfly_cookie, cmpopfly_cookieName, cmpopfly_fixedNumberOfTimesCookieName );
                }
            } );
            $( '.cm-pop-up-banners-scrollspy-marker' ).scrollSpy();
        }
    }
    function handlePopupMouseLeave( e ) {
        if ( e.clientY < 20 ) {
            fireOuibounce( cmpopfly_cookie, cmpopfly_cookieName, cmpopfly_fixedNumberOfTimesCookieName );
        }
    }
    function handleDelayMouseAndKeyMove( e ) {
        if ( ouibounceBannerShown ) {
            document.documentElement.removeEventListener( 'mousemove', handleDelayMouseAndKeyMove );
            document.documentElement.removeEventListener( 'keydown', handleDelayMouseAndKeyMove );
            return false;
        }
        if ( _localOuibounceDelayTimer ) {
            clearTimeout( _localOuibounceDelayTimer );
        }
        _localOuibounceDelayTimer = setTimeout( fireStandardOuibounce, inactivityDelay );
    }
    function fireStandardOuibounce() {
        fireOuibounce( cmpopfly_cookie, cmpopfly_cookieName, cmpopfly_fixedNumberOfTimesCookieName );
        ouibounceBannerShown = true;
    }

    if ( parseInt( WidgetConf.closeTime ) !== 0 && WidgetConf.closeTime !== null ) {
        setTimeout( function () {
            $( '#ouibounce-modal' ).fadeOut();
        }, parseInt( WidgetConf.closeTime + '000' ) );
    }
} );