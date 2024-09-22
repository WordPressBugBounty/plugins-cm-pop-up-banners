function safex( e, t )
{
    return typeof e === "undefined" ? t : e;
}

cmpopfly_AjaxRequestSent = false;

function cmpopfly_sendAjaxClickData( cmpopfly_ClicksCounter, AjaxRequestAsync )
{
    jQuery( document ).ready( function ( $ )
    {
        if ( flyin_custom_data.preview != true )
        {
            AjaxRequestAsync = typeof AjaxRequestAsync !== 'undefined' ? AjaxRequestAsync : true;
            $.ajax( {
                'url': flyin_custom_data.ajaxClickUrl,
                'type': 'post',
                'async': AjaxRequestAsync,
                'complete': cmpopfly_resetAjax,
                'data':
                    {
                        campaign_id: flyin_custom_data.campaign_id,
                        banner_id: flyin_custom_data.banner_id,
                        amount: cmpopfly_ClicksCounter,
                    }
            } );

            if ( flyin_custom_data.countingMethod !== 'one' )
            {
                // all clicks
                cmpopfly_ClicksCounter = 0;
            }
        }
    } );
}

var cmpopfly_resetAjax = function ()
{
    if ( flyin_custom_data.countingMethod != 'one' )
    {
        // all clicks
        cmpopfly_AjaxRequestSent = false;
        cmpopfly_ClicksCounter = 0;
    }
}

function cmpopfly_setCookie( cname, cvalue, exdays )
{
    var d, expires, path;

    if ( flyin_custom_data.showMethod === 'only_once' ) {
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

jQuery( document ).ready( function ()
{
    var ouibounceBannerBottomShown = false;
    var _localOuibounceDelayTimer = null;
    var ouibounceBannerShown = false;
    var inactivityDelay = flyin_custom_data.inactivityTime * 1000;
    function fireFlyingPopup()
    {
        jQuery( 'body' ).on( 'click', '#flyingBottomAd.linked', function () {
            jQuery( this ).find( '.cmpopfly-fullbody-link' )[0].click();
            jQuery('.flyingBottomAdClose').click();
        } );

        var cmpopfly_cookieName = 'ouibounceBannerBottomShown-' + flyin_custom_data.campaign_id;
        var cmpopfly_fixedNumberOfTimesCookieName = 'ouibounceBannerBottomShownNumberOfTimes-' + flyin_custom_data.campaign_id;
        var cmpopfly_cookie = cmpopfly_getCookie( cmpopfly_cookieName );
        var cmpopfly_fixedNumberOfTimesCookie = cmpopfly_getCookie( cmpopfly_fixedNumberOfTimesCookieName );
        var local_flyin_sound_path = '';
        if ( flyin_custom_data.soundMethod &&
            flyin_custom_data.soundMethod == 'custom' &&
            flyin_custom_data.customSoundPath &&
            flyin_custom_data.customSoundPath != '' ) {
            local_flyin_sound_path = flyin_custom_data.customSoundPath;
        }
        if ( flyin_custom_data.soundMethod &&
            flyin_custom_data.soundMethod == 'default' &&
            flyin_custom_data.standardSound ) {
            local_flyin_sound_path = flyin_custom_data.standardSound;
        }
        var local_flyin_sound = local_flyin_sound_path ? new Audio( local_flyin_sound_path ) : false;

        if ( screen.width >= parseInt( flyin_custom_data.minDeviceWidth )
            && ( flyin_custom_data.maxDeviceWidth == "0" || screen.width <= parseInt( flyin_custom_data.maxDeviceWidth ) ) ) {
            if ( flyin_custom_data.showMethod == 'always' )
            {
                cmpopfly_setCookie( cmpopfly_cookieName, '', -1 );
            }
            if ( flyin_custom_data.showMethod != 'fixed_times' )
            {
                cmpopfly_setCookie( cmpopfly_fixedNumberOfTimesCookieName, '0', 1, true );
                cmpopfly_setCookie( cmpopfly_fixedNumberOfTimesCookieName, '0', 1 );
                cmpopfly_fixedNumberOfTimesCookieName = '';
            }

            if ( cmpopfly_cookie === '' &&
                ( flyin_custom_data.showMethod != 'fixed_times' ||
                    cmpopfly_fixedNumberOfTimesCookie == '' ||
                    cmpopfly_fixedNumberOfTimesCookie < flyin_custom_data.showFixedNumberOfTimes ||
                    isNaN( cmpopfly_fixedNumberOfTimesCookie ) ) )
            {
                var _flyingBottomOui = flyingBottomAd(
                    {
                        htmlContent: '<div id=\"flyingBottomAd\" class=\"' + flyin_custom_data.additionalClass + '\">' + flyin_custom_data.bannerLink + '<span class=\"flyingBottomAdClose  popupflyin-close-button\"></span><div class=\"popupflyin-clicks-area\">' + flyin_custom_data.content + '</div></div>',
                        delay: flyin_custom_data.secondsToShow,
                        sound: local_flyin_sound
                    } );
                ouibounceBannerBottomShown = true;
                if ( flyin_custom_data.showMethod === 'once' || flyin_custom_data.showMethod === 'only_once' )
                {
                    cmpopfly_setCookie( cmpopfly_cookieName, 'true', flyin_custom_data.resetTime );
                }
                if ( flyin_custom_data.showMethod === 'fixed_times' )
                {
                    if ( typeof parseInt( cmpopfly_fixedNumberOfTimesCookie ) == "number" && !isNaN( parseInt( cmpopfly_fixedNumberOfTimesCookie ) ) ) {
                        var parsedNumber = parseInt( cmpopfly_fixedNumberOfTimesCookie );
                        cmpopfly_setCookie( cmpopfly_fixedNumberOfTimesCookieName, '', -1 );
                        cmpopfly_setCookie( cmpopfly_fixedNumberOfTimesCookieName, parsedNumber + 1, flyin_custom_data.resetTime );
                    } else {
                        cmpopfly_setCookie( cmpopfly_fixedNumberOfTimesCookieName, 1, flyin_custom_data.resetTime );
                    }

                }
                if ( flyin_custom_data.enableStatistics ) {
                    jQuery( document ).on( 'click', '.popupflyin-clicks-area', function ()
                    {
                        if ( typeof cmpopfly_ClicksCounter === 'undefined' )
                        {
                            cmpopfly_ClicksCounter = 0;
                        }

                        if ( !cmpopfly_AjaxRequestSent )
                        {
                            if ( flyin_custom_data.countingMethod === 'one' )
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
            }
        }
    }

    if ( flyin_custom_data.fireMethod === 'pageload' ) {
        fireFlyingPopup();
    }

    if ( flyin_custom_data.fireMethod === 'click' ) {
        jQuery( '.cm-pop-up-banners-trigger' ).on( 'click', function () {
            fireFlyingPopup();
        } )
    }

    if ( flyin_custom_data.fireMethod === 'hover' ) {
        jQuery( '.cm-pop-up-banners-trigger' ).on( 'mouseenter', function () {
            fireFlyingPopup();
        } )
    }
    if ( flyin_custom_data.fireMethod === 'leave' ) {
        document.documentElement.addEventListener( 'mouseleave', handlePopupMouseLeave );
    }
    if ( flyin_custom_data.fireMethod === 'inactive' )
    {
        document.documentElement.addEventListener( 'mousemove', handleDelayMouseAndKeyMove );
        document.documentElement.addEventListener( 'keydown', handleDelayMouseAndKeyMove );
    }
    if ( flyin_custom_data.fireMethod === 'pageBottom' ) {
        jQuery( 'body' ).append( '<div style="clear: both"></div><div id="cm-pop-up-banners-scrollspy-marker" class="cm-pop-up-banners-scrollspy-marker"></div>' );
        jQuery( '#cm-pop-up-banners-scrollspy-marker' ).on( 'scrollSpy:enter', function () {
            if ( !ouibounceBannerBottomShown ) {
                fireFlyingPopup();
            }
        } );
        jQuery( '.cm-pop-up-banners-scrollspy-marker' ).scrollSpy();
    }
    function handlePopupMouseLeave( e ) {
        if ( ouibounceBannerBottomShown === true ) {
            document.documentElement.removeEventListener( 'mouseleave', handlePopupMouseLeave );
            return false;
        }
        if ( e.clientY < 20 ) {
            fireFlyingPopup();
        }
    }
    function handleDelayMouseAndKeyMove( e ) {
        if ( ouibounceBannerBottomShown ) {
            document.documentElement.removeEventListener( 'mousemove', handleDelayMouseAndKeyMove );
            document.documentElement.removeEventListener( 'keydown', handleDelayMouseAndKeyMove );
            return false;
        }
        if ( _localOuibounceDelayTimer ) {
            clearTimeout( _localOuibounceDelayTimer );
        }
        _localOuibounceDelayTimer = setTimeout( fireFlyingPopup, inactivityDelay );
    }

	if ( parseInt( WidgetConf.closeTime ) !== 0 && WidgetConf.closeTime !== null ) {
        setTimeout( function () {
            jQuery( '.popupflyin-close-button' ).trigger('click');
        }, parseInt( WidgetConf.closeTime + '000' ) );
    }

} );