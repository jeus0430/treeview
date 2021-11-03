<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Treebox</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">
        <link href="<?= asset('gijgo/gijgo.min.css') ?>" rel="stylesheet" type="text/css" />
        <link rel="stylesheet" type="text/css" href="<?= asset('treebox/tree-boxes.css') ?>">
        <link rel="stylesheet" href="<?= asset('bootstrap.min.css') ?>" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <link rel="stylesheet" type="text/css" href="<?= asset('fontawesome/css/font-awesome.css') ?>">
        <link rel="stylesheet" type="text/css" href="<?= asset('apexchart/apexchart.css') ?>">
        <style>
            fieldset {
                border: 1px solid #333;
                border-radius: 5px;
                padding: 10px;
            }
            .form-group {
                margin-bottom: 0;
            }
        </style>
    </head>
    <body style="overflow: hidden;" oncontextmenu="return false;">
        <div class="container-fluid">
            <form method="get" class="form-horizontal" style="margin: 20px 20px 0;">
                <div style="display: flex;align-items: center;">
                    <fieldset>
                        <legend>Display: </legend>
                        <div style='display: flex'>
                            <input class="form-control" id="mone_av" name="mone_av" value="{{request()->mone_av}}">
                            <button style="align-self: center;margin-left: 20px;" type="submit" class="btn btn-primary">Search</button>
                        </div>
                        <div class="form-group form-check" style="margin-left: 1em;">
                            <input type="checkbox" class="form-check-input" name="sivug" id="sivug" @if (request()->sivug) checked @endif>
                            <label class="form-check-label" for="sivug">Sivug</label>
                        </div>
                    </fieldset>
                    <fieldset style="flex-grow: 2;margin-left: 20px;">
                        <legend>Graph:(For Graph press right click on Mone) </legend>
                        <div style="display: flex;justify-content: space-around;">
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="day_step" id="exampleRadios1" value="daily" checked>
                                    <label class="form-check-label" for="exampleRadios1">
                                        Daily
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="day_step" id="exampleRadios2" value="hourly">
                                    <label class="form-check-label" for="exampleRadios2">
                                        Hourly
                                    </label>
                                </div>
                            </div>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="chart_type" id="exampleRadios3" value="line" checked>
                                    <label class="form-check-label" for="exampleRadios3">
                                        Line
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="chart_type" id="exampleRadios4" value="bar">
                                    <label class="form-check-label" for="exampleRadios4">
                                        Bar
                                    </label>
                                </div>
                            </div>
                            <div>
                                <div class="form-group form-check">
                                    <input type="checkbox" class="form-check-input" id="together">
                                    <label class="form-check-label" for="together">3 Together</label>
                                </div>
                            </div>

                            <div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-md-5 text-right" style="padding-right: 0;" for="exampleInputEmail1">End Date: </label>
                                            <div class="col-md-7">
                                                <input id="endDate" name="endDate" class="form-control" value="<?= $end_date ?>" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-md-5 text-right" style="padding-right: 0;" for="exampleInputEmail1">Start Date: </label>
                                            <div class="col-md-7">
                                                <input id="startDate" name="startDate" class="form-control" value="<?= $start_date ?>" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </form>
            @isset($mones)
            <ct-visualization id="tree-container"></ct-visualization>
            @else
                <h2><center>No data display</center></h2>
            @endisset
        </div>
        <div class="modal fade" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" id="chart-modal">
            <div class="modal-dialog  modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Modal title</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="chart_1"></div>
                        <div id="chart_2"></div>
                        <div id="chart_3"></div>
                        <div id="chart_4"></div>
                    </div>
                </div>
            </div>
        </div>
        <script src="<?= asset('jquery-3.3.1.min.js') ?>" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
        <script src="<?= asset('d3.v3.min.js') ?>"></script>
        <script src="<?= asset('treebox/tree-boxes.js') ?>"></script>
        <script src="<?= asset('apexchart/apexcharts.js') ?>"></script>
        <script src="<?= asset('bootstrap.min.js') ?>" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
        <script src="<?= asset('jquery.blockUI.js') ?>"></script>
        <script src="<?= asset('gijgo/gijgo.min.js') ?>" type="text/javascript"></script>
            <script>
                // Shared Colors Definition
                const primary = '#6993FF';
                const success = '#1BC5BD';
                const danger = '#F64E60';
                const info = '#C6EE60';
                var chart_1, chart_2, chart_3, chart_4 ;
                @isset($mones)
                    var data = JSON.parse(atob('<?= $mones ?>'));
            console.log(data);
                    treeBoxes('',data);
                @endisset
                function generateDayWiseTimeSeries(arr, name) {
                    var series = [];
                    arr.forEach(function(element) {
                        myDate = element['date'].split("-");
                        var newDate = new Date( myDate[0], myDate[1] - 1, myDate[2]);
                        series.push([newDate.getTime(), element[name]]);
                    });
                    return series;
                }
                function generateQtys(arr) {
                    var series = [];
                    arr.forEach(function(element) {
                        tmp = element['reading_date'].split(" ")
                        myDate = tmp[0].split("-")
                        myMins = tmp[1].split(":")
                        var newDate = new Date( myDate[0], myDate[1] - 1, myDate[2], myMins[0], myMins[1], myMins[2])
                        console.log('newDate', newDate)
                        series.push([newDate.getTime(), element['qty']]);
                    });
                    console.log(series)
                    return series;
                }
                function openChart(ev, mone, address)
                {
                    if (ev.which == 3) {
                        $.ajax({
                            url : 'getChartJSON',
                            data: {
                                mone: mone,
                                day_step: $('input[name="day_step"]:checked').val(),
                                start_date:$("#startDate").val(),
                                end_date:$("#endDate").val()
                            },
                            beforeSend: function(result) {
                                $.blockUI({
                                    css: {
                                        border: "0",
                                        padding: "0",
                                        backgroundColor: "none"
                                    },
                                    overlayCSS: {
                                        backgroundColor: "#555",
                                        opacity:.05,
                                        cursor: "wait"
                                    }
                                });
                            },
                            dataType: 'json',
                            success: function(result) {
                                if (chart_1) chart_1.destroy();
                                if (chart_2) chart_2.destroy();
                                if (chart_3) chart_3.destroy();
                                if (chart_4) chart_4.destroy();
                                $("#chart_1").text('');
                                if (result.org.length) {
                                    if ($('input[name="day_step"]:checked').val() == 'daily') {
                                        if ($("#together:checked").val()) {
                                            var options = {
                                                series: [
                                                    {
                                                        name: "qty",
                                                        data: result.map(a => a.qty)
                                                    },
                                                    {
                                                        name: "real_qty",
                                                        data: result.map(a => a.real_qty)
                                                    },
                                                    {
                                                        name: "delta",
                                                        data: result.map(a => a.delta)
                                                    }
                                                ],
                                                chart: {
                                                    height: 350,
                                                    type: $('input[name="chart_type"]:checked').val(),
                                                    zoom: {
                                                        enabled: true
                                                    }
                                                },
                                                dataLabels: {
                                                    enabled: false
                                                },
                                                stroke: {
                                                    curve: 'straight',
                                                    width: 1
                                                },
                                                grid: {
                                                    row: {
                                                        colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
                                                        opacity: 0.5
                                                    },
                                                },
                                                xaxis: {
                                                    categories: result.map(a => a.date),
                                                },
                                                colors: [primary, success, danger]
                                            };
                                            chart_1 = new ApexCharts(document.querySelector("#chart_1"), options);
                                            chart_1.render();
                                        } else {
                                            var options_1 = {
                                                series: [{
                                                    name: 'qty',
                                                    data: generateDayWiseTimeSeries(result.org, 'qty')
                                                }],
                                                chart: {
                                                    id: 'fb',
                                                    group: 'social',
                                                    type: $('input[name="chart_type"]:checked').val(),
                                                    height: 200
                                                },
                                                title: {
                                                    text:'qty'
                                                },
                                                stroke: {
                                                    width: 1
                                                },
                                                colors: [primary],
                                                yaxis: {
                                                    labels: {
                                                    minWidth: 40
                                                    }
                                                },
                                                xaxis: {
                                                    type: 'datetime',
                                                    labels: {
                                                        datetimeUTC: false,
                                                        format: 'yyyy-MM-dd',
                                                        datetimeFormatter: {
                                                            year: 'yyyy',
                                                            month: 'MMM \'yy',
                                                            day: 'dd MMM',
                                                            hour: 'HH:mm'
                                                        }
                                                    }
                                                },
                                            };
                                            var options_2 = {
                                                series: [{
                                                    name: 'real_qty',
                                                    data: generateDayWiseTimeSeries(result.org, 'real_qty')
                                                }],
                                                chart: {
                                                    id: 'fb',
                                                    toolbar: {
                                                        show: false
                                                    },
                                                    group: 'social',
                                                    type: $('input[name="chart_type"]:checked').val(),
                                                    height: 200
                                                },
                                                title: {
                                                    text: 'real_qty'
                                                },
                                                stroke: {
                                                    width: 1
                                                },
                                                colors: [success],
                                                yaxis: {
                                                    labels: {
                                                    minWidth: 40
                                                    }
                                                },
                                                xaxis: {
                                                    type: 'datetime',
                                                    labels: {
                                                        datetimeUTC: false,
                                                        format: 'yyyy-MM-dd',
                                                        datetimeFormatter: {
                                                            year: 'yyyy',
                                                            month: 'MMM \'yy',
                                                            day: 'dd MMM',
                                                            hour: 'HH:mm'
                                                        }
                                                    }
                                                },
                                            };
                                            var options_3 = {
                                                series: [{
                                                    name: 'delta',
                                                    data: generateDayWiseTimeSeries(result.org, 'delta')
                                                }],
                                                stroke: {
                                                    width: 1
                                                },
                                                title: {
                                                    text: 'delta'
                                                },
                                                chart: {
                                                    id: 'fb',
                                                    toolbar: {
                                                        show: false
                                                    },
                                                    group: 'social',
                                                    type: $('input[name="chart_type"]:checked').val(),
                                                    height: 200
                                                },
                                                colors: [danger],
                                                yaxis: {
                                                    labels: {
                                                    minWidth: 40
                                                    }
                                                },
                                                xaxis: {
                                                    type: 'datetime',
                                                    labels: {
                                                        datetimeUTC: false,
                                                        format: 'yyyy-MM-dd',
                                                        datetimeFormatter: {
                                                            year: 'yyyy',
                                                            month: 'MMM \'yy',
                                                            day: 'dd MMM',
                                                            hour: 'HH:mm'
                                                        }
                                                    }
                                                },
                                            };
                                            var options_4 = {
                                                series: [{
                                                    name: 'qty from kirot',
                                                    data: generateQtys(result.new)
                                                }],
                                                stroke: {
                                                    width: 1
                                                },
                                                title: {
                                                    text: 'qty from kirot'
                                                },
                                                chart: {
                                                    id: 'fb',
                                                    toolbar: {
                                                        show: false
                                                    },
                                                    group: 'social',
                                                    type: $('input[name="chart_type"]:checked').val(),
                                                    height: 200
                                                },
                                                colors: [info],
                                                yaxis: {
                                                    labels: {
                                                    minWidth: 40
                                                    }
                                                },
                                                xaxis: {
                                                    type: 'datetime',
                                                    labels: {
                                                        datetimeUTC: false,
                                                        format: 'yyyy-MM-dd H',
                                                        datetimeFormatter: {
                                                            year: 'yyyy',
                                                            month: 'MMM \'yy',
                                                            day: 'dd MMM',
                                                            hour: 'HH:mm'
                                                        }
                                                    }
                                                },
                                            };
                                            chart_1 = new ApexCharts(document.querySelector("#chart_1"), options_1);
                                            chart_1.render();
                                            chart_2 = new ApexCharts(document.querySelector("#chart_2"), options_2);
                                            chart_2.render();
                                            chart_3 = new ApexCharts(document.querySelector("#chart_3"), options_3);
                                            chart_3.render();
                                            chart_4 = new ApexCharts(document.querySelector("#chart_4"), options_4);
                                            chart_4.render();
                                        }
                                    } else {
                                        var options = {
                                            series: [
                                                {
                                                    name: "qty",
                                                    data: result.map(a => a.qty)
                                                }
                                            ],
                                            chart: {
                                                animations: {
                                                    enabled: true,
                                                    easing: 'easeinout',
                                                    speed: 800,
                                                    animateGradually: {
                                                        enabled: true,
                                                        delay: 150
                                                    },
                                                    dynamicAnimation: {
                                                        enabled: true,
                                                        speed: 350
                                                    }
                                                },
                                                height: 500,
                                                type: $('input[name="chart_type"]:checked').val(),
                                                zoom: {
                                                    enabled: true
                                                }
                                            },
                                            dataLabels: {
                                                enabled: false
                                            },
                                            stroke: {
                                                curve: 'straight',
                                                width: 1
                                            },
                                            grid: {
                                                row: {
                                                    colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
                                                    opacity: 0.5
                                                },
                                            },
                                            xaxis: {
                                                categories: result.map(a => a.date),
                                                tickPlacement: "on"
                                            },
                                            colors: [primary]
                                        };
                                        chart_1 = new ApexCharts(document.querySelector("#chart_1"), options);
                                        chart_1.render();
                                    }
                                } else {
                                    $("#chart_1").text('Data Not Exisiting with chosen criteria');
                                }
                                $("#chart-modal .modal-title").text(mone + '(' + address + ')' + '-chart');
                                $("#chart-modal").modal('show');
                            },
                            complete: function () {
                                $.unblockUI();
                            }
                        });
                    }
                }
                $(document).ready(function() {
                    $('#startDate').datepicker({
                        uiLibrary: 'bootstrap4',
                        format: 'yyyy-mm-dd',
                        iconsLibrary: 'fontawesome',
                        maxDate: function () {
                            return $('#endDate').val();
                        }
                    });
                    $('#endDate').datepicker({
                        uiLibrary: 'bootstrap4',
                        iconsLibrary: 'fontawesome',
                        format: 'yyyy-mm-dd',
                        minDate: function () {
                            return $('#startDate').val();
                        },
                        maxDate: new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate())
                    });
                })
            </script>
    </body>
</html>
