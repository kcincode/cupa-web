var chart;

$(document).ready(function() {
    $('#browser-data').click(function(e){
        loadBrowserGraph();
    });

});

function loadBrowserGraph() {
    $.ajax({
        url: BASE_URL + '/admin/loadbrowserdata',
        success: function(resp) {
            chart = new Highcharts.Chart({
                chart: {
                    renderTo: 'chart',
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false
                },
                title: {
                    text: 'Site Browser Access'
                },
                tooltip: {
                    formatter: function() {
                        return '<b>'+ this.point.name +'</b>: '+ this.percentage +' %';
                    }
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            color: '#000000',
                            connectorColor: '#000000',
                            formatter: function() {
                                return '<b>'+ this.point.name +'</b>: '+ this.percentage +' %';
                            }
                        }
                    }
                },
                series: [{
                    type: 'pie',
                    name: 'Browser share',
                    data: resp
                }]
            });
        },
        dataType: 'json'
    });
}