function flyingBottomAd( e ) {
    function safex( e, t ) {
        return typeof e === "undefined" ? t : e
    }
    function getFlyingTimeT( e ) {
        var t = e * 24 * 60 * 60 * 1e3;
        var n = new Date;
        n.setTime( n.getTime() + t );
        return "; expires=" + n.toGMTString() + "; path=/";
    }
    function showFlyingBottom() {
        if ( jQuery( "#flyingBottomAd", "body" ).length === 0 ) {
            jQuery( "body" ).append( o );
            jQuery( ".flyingBottomAdClose" ).on( "click", function () {
                //jQuery( "#flyingBottomAd" ).hide();
                jQuery( "#flyingBottomAd" ).remove();
                if ( typeof ( CMpopupClosed ) === "function" ) {
                    CMpopupClosed();
                }
            } );
            if ( typeof ( CMregisterPopupFlyinWatchers ) === "function" ) {
                CMregisterPopupFlyinWatchers();
            }
        } else {
            jQuery( "#flyingBottomAd", "body" ).show();
        }
    }
    var e = e || { },
        t = safex( e.sensitivity, 20 ),
        n = safex( e.timer, 0 ),
        r = getFlyingTimeT( e.cookieExpire ) || "",
        i = getFlyingTimeT( e.longExpire ) || "",
        s = safex( e.auto, "false" ),
        o = safex( e.htmlContent, "" ),
        f = e.delay || 0,
        sound = e.sound || false;
    setTimeout( function () {
        showFlyingBottom();
        if(sound){
            sound.play();
        }
    }, f );
}