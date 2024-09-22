jQuery(document).ready(function ($) {
   //initialize datepickers
   $('.datepicker').datepicker({
       "dateFormat" : "yy-mm-dd"
   });
   
   $('#send-statistics-data-period').on('click', function(){
       doAjaxCall({
           resultContainer: '#CM-popupflyin-statistics-period-result-container',
           resultTableName: '#period-statistics-data-table',
           data: {
             type: 'period',
             date_from: $('#period-date-from').val(),
             date_to: $('#period-date-to').val(),
           },
       });
   });
   $('#send-statistics-data-daily').on('click', function(){
       doAjaxCall({
           resultContainer: '#CM-popupflyin-statistics-daily-result-container',
           resultTableName: '#daily-statistics-data-table',
           data: {
             type: 'daily',
             date_from: $('#daily-date-from').val(),
             date_to: $('#daily-date-to').val(),
           },
       });
   });
   $('#send-statistics-data-access-log').on('click', function(){
       doAjaxCall({
           resultContainer: '#CM-popupflyin-statistics-access-log-result-container',
           resultTableName: '#access-log-statistics-data-table',
           data: {
             type: 'access_log',
             date_from: $('#access-log-date-from').val(),
             date_to: $('#access-log-date-to').val(),
             campaign_id: $('#access-log-campaign-id').val(),
             group_by: $('#access-log-display-type').val()
           },
       });
   });
   $('#clear-data-period').on('click', function(){
       $('#period-date-from').val('');
       $('#period-date-to').val('');
   });
   $('#clear-data-daily').on('click', function(){
       $('#daily-date-from').val('');
       $('#daily-date-to').val('');
   });
   $('#clear-data-access-log').on('click', function(){
       $('#access-log-date-from').val('');
       $('#access-log-date-to').val('');
   });
   function doAjaxCall(options){
       var resultContainer = $(options.resultContainer);
       preloaderHtml = '<div id="loader-wrapper"><div id="loader"></div><div id="loaderMessage">Loading</div></div>';
       resultContainer.html(preloaderHtml);
       $.ajax({
            'url': statistics_data.ajaxUrl,
            'type': 'post',
            'data': options.data,
            'complete' : function(response, x, y){
                data = $.parseJSON(response.responseText);
                resultContainer.html(data.content);
                $(options.resultTableName).DataTable();
                $('.banner_tooltip').tooltip({
                    content: function () {
                        var element = jQuery(this);
                        return element.attr('title');
                    },
                    position: {
                        my: "left top",
                        at: "right top"
                    }
                });
            }
        });
   }

});
function draw_graph (data) {
 var options = {
     series: {
         bars: {
            show: true,
            barWidth: 60*60*1000*2,
            order: 1
         },
         stack: false
     },
     yaxes: {
         min: 0
     },
     xaxis: {
         mode: 'time',
         timeformat: "%d/%m/%y",
         minTickSize: [1, "month"],
         tickSize: [1, "day"],
         autoscaleMargin: .10
     }
 };

 jQuery.plot('#server_load_graph', data, options);
}