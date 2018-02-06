'use strict';


app.controller('CampaignDashboardCtrl', function ($rootScope, $scope, $http, site_config, $stateParams) {
    /* GET Dashboard Data LIST */
    $http.get(site_config.apiUrl + "campaign/getdashboarddata?name=" + $stateParams.campaign_name).then(function (response)
    {
        if (response.data.status) {
            $rootScope.cdd = response.data.data;
        }
    });
    $scope.campaign_name = $stateParams.campaign_name;

    $scope.dashboard_filters = ['Daily', '1 Week', '2 Weeks', '30 Days', '3 Months', '6 Months', '1 Year'];
    $scope.d_filter = $scope.dashboard_filters[1];
    $scope.days = $scope.ceiling = 7;

    $scope.parent = 1;
    $scope.child = 1;
    $scope.visit_data = 0;
    $scope.realtime_data = 1;
    $scope.slider_bar = 1;

    //Slider config with callbacks
    $scope.slider_callbacks = {
        days: 7,
        options: {
            hideLimitLabels: true,
            showSelectionBar: true,
            onEnd: function () {
                $scope.changeTabs($scope.child)
            }
        }
    };

    $scope.changeFilter = function () {
        $scope.slider_bar = 0;
        if ($scope.d_filter == 'Daily') {
            $scope.slider_callbacks.days = $scope.ceiling = 1;
        } else if ($scope.d_filter == '1 Week') {
            $scope.slider_callbacks.days = $scope.ceiling = 7;
        } else if ($scope.d_filter == '2 Weeks') {
            $scope.slider_callbacks.days = $scope.ceiling = 14;
        } else if ($scope.d_filter == '30 Days') {
            $scope.slider_callbacks.days = $scope.ceiling = 30;
        } else if ($scope.d_filter == '3 Months') {
            $scope.slider_callbacks.days = $scope.ceiling = 90;
        } else if ($scope.d_filter == '6 Months') {
            $scope.slider_callbacks.days = $scope.ceiling = 180;
        } else if ($scope.d_filter == '1 Year') {
            $scope.slider_callbacks.days = $scope.ceiling = 365;
        }
        setTimeout(function () {
            $scope.slider_bar = 1;
            $scope.changeTabs($scope.child)
        }, 0);
    }

    $scope.changeTabs = function (child) {

        $scope.realtime_data = 0;
        $scope.child = child;
        if (child == 0) {
            setTimeout(function () {
                $scope.realtime_data = 1;
            }, 0);
            console.log($scope.realtime_data);
        }
        if (child == 1) {
            $scope.visit_data = 0;
            $scope.getvisitdata();
        }
        if (child == 2) {
            $rootScope.browser_data = 0;
            $scope.getBrowserData();
        }
    }

    /* GET VISITS STATISTICS */
    $scope.getvisitdata = function () {
        $http({
            url: site_config.apiUrl + "campaign/visitorchartdata",
            method: 'POST',
            data: {campaign: $stateParams.campaign_name, filter: $scope.d_filter, days: $scope.slider_callbacks.days},
            headers: {'Content-Type': 'application/json'}
        }).success(function (response) {
            $scope.visit_data = 1;
            $scope.visitChartData = [
                {
                    color: $rootScope.settings.color.themeprimary,
                    label: "Total Visits",
                    data: response.totalVisit,
                    bars: {
                        order: 1,
                        show: true,
                        borderWidth: 0,
                        barWidth: 0.4,
                        lineWidth: .5,
                        fillColor: {
                            colors: [
                                {
                                    opacity: 0.4
                                }, {
                                    opacity: 1
                                }
                            ]
                        }
                    }
                },
                {
                    color: $rootScope.settings.color.themesecondary,
                    label: "Unique Visits",
                    data: response.uniqueVisit,
                    lines: {
                        show: true,
                        fill: true,
                        lineWidth: .1,
                        fillColor: {
                            colors: [
                                {
                                    opacity: 0
                                }, {
                                    opacity: 0.4
                                }
                            ]
                        }
                    },
                    points: {
                        show: false
                    },
                    shadowSize: 0
                },
                {
                    color: $rootScope.settings.color.themethirdcolor,
                    label: "Total Users",
                    data: response.totalUser,
                    lines: {
                        show: true,
                        fill: false,
                        fillColor: {
                            colors: [
                                {
                                    opacity: 0.3
                                }, {
                                    opacity: 0
                                }
                            ]
                        }
                    },
                    points: {
                        show: true
                    }
                }
            ];
            $scope.visitChartOptions = {
                legend: {
                    show: false
                },
                xaxis: {
                    tickDecimals: 0,
                    color: '#f3f3f3',
                    mode: "categories",
                },
                yaxis: {
                    min: 0,
                    color: '#f3f3f3',
                    tickFormatter: function (val, axis) {
                        return "";
                    },
                },
                grid: {
                    hoverable: true,
                    clickable: false,
                    borderWidth: 0,
                    aboveData: false,
                    color: '#fbfbfb'

                },
                tooltip: true,
                tooltipOpts: {
                    defaultTheme: false,
                    content: "<b>%s</b> : <span>%y</span>",
                }
            };


        }).error(function () {
        });
    };

    /* GET REFERRAL STATISTICS */
    $scope.getBrowserData = function (type) {
        $http({
            url: site_config.apiUrl + "campaign/browserchartdata",
            method: 'POST',
            data: {campaign: $stateParams.campaign_name, filter: $scope.d_filter, days: $scope.slider_callbacks.days},
            headers: {'Content-Type': 'application/json'}
        }).success(function (response) {
            $scope.browserChartData = response.data.browser;
            $scope.deviceChartData = response.data.device;
            $scope.sourceChartData = response.data.source;
            setTimeout(function () {
                $rootScope.$apply(function () {
                    $rootScope.browser_data = 1;
                });
            }, 100);
        })
    }

    $scope.getvisitdata();

});

app.directive('browserChart', function ($rootScope, $http, site_config) {
    return {
        restrict: 'A',
        transclude: true,
        replace: true,
        scope: true,
        link: function (scope, element, attrs) {
            Morris.Donut({
                element: 'browser-donut-chart',
                data: scope.browserChartData,
                colors: [$rootScope.settings.color.themeprimary, $rootScope.settings.color.themesecondary, $rootScope.settings.color.themethirdcolor, $rootScope.settings.color.themefourthcolor],
                formatter: function (y) {
                    return y;
                }
            });
        }
    };
});

app.directive('deviceChart', function ($rootScope) {
    return {
        restrict: 'A',
        transclude: true,
        replace: true,
        scope: true,
        link: function (scope, element, attrs) {
            Morris.Donut({
                element: 'device-donut-chart',
                data: scope.deviceChartData,
                colors: [$rootScope.settings.color.themeprimary, $rootScope.settings.color.themesecondary, $rootScope.settings.color.themethirdcolor, $rootScope.settings.color.themefourthcolor],
                formatter: function (y) {
                    return y;
                }
            });


        }
    };
});

app.directive('sourceChart', function ($rootScope) {
    return {
        restrict: 'A',
        transclude: true,
        replace: true,
        scope: true,
        link: function (scope, element, attrs) {
            Morris.Donut({
                element: 'source-donut-chart',
                data: [
                    {label: 'Direct', value: scope.sourceChartData},
//                    {label: 'USB', value: 10, },
//                    {label: 'Webkey', value: 10, },
//                    {label: 'App', value: 10, },
//                    {label: 'Link', value: 30},
//                    {label: 'Email', value: 25},
//                    {label: 'Search Engine', value: 5},
                ],
                colors: [$rootScope.settings.color.themeprimary, $rootScope.settings.color.themesecondary, $rootScope.settings.color.themethirdcolor, $rootScope.settings.color.themefourthcolor],
                formatter: function (y) {
                    return y;
                }
            });


        }
    };
});

angular.module('app').directive("campaignChartRealtime", ['$http', 'site_config', '$rootScope', '$location',
    function ($http, site_config, $rootScope, $location) {
        return {
            restrict: "AE",
            link: function (scope, ele) {
                var realTimedata,
                        totalPoints,
                        getSeriesObj,
                        getRandomData,
                        updateInterval,
                        plot,
                        update;
                return realTimedata = [],
                        totalPoints = 300,
                        getSeriesObj = function () {
                             var path = $location.path().substr(1).split("/", 3);
                             console.log(scope.child);
                             console.log(path[2]);
                            if (path[1] == 'campaign' && path[2] == 'dashboard') {
                                return [
                                    {
                                        data: getRandomData(),
                                        lines: {
                                            show: true,
                                            lineWidth: 1,
                                            fill: true,
                                            fillColor: {
                                                colors: [
                                                    {
                                                        opacity: 0
                                                    }, {
                                                        opacity: 1
                                                    }
                                                ]
                                            },
                                            steps: false
                                        },
                                        shadowSize: 0
                                    }
                                ];
                            }
                        },
                        getRandomData = function () {
                            if (realTimedata.length > 0)
                                realTimedata = realTimedata.slice(1);

                            // Do a random walk

                            if (realTimedata.length == 0) {
                                $http.get(site_config.apiUrl + "campaign/getrealtimedata?load=init&name=" + scope.campaign_name).then(function (response) {
                                    realTimedata = (response.data.data);
                                });
                            } else {
                                $http.get(site_config.apiUrl + "campaign/getrealtimedata?name=" + scope.campaign_name).then(function (response) {
                                    realTimedata.push(response.data.data);
                                });
                            }

//                            while (realTimedata.length < totalPoints) {
//
//                                var prev = realTimedata.length > 0 ? realTimedata[realTimedata.length - 1] : 50,
//                                        y = prev + Math.random() * 10 - 5;
//
//                                if (y < 0) {
//                                    y = 0;
//                                } else if (y > 100) {
//                                    y = 100;
//                                }
//                                realTimedata.push(y);
//                            }

                            // Zip the generated y values with the x values

                            var res = [];
                            for (var i = 0; i < realTimedata.length; ++i) {
                                res.push([i, realTimedata[i]]);
                            }

                            return res;
                        },
                        // Set up the control widget
                        updateInterval = 1000,
                        plot = $.plot(ele[0], getSeriesObj(), {
                            yaxis: {
                                color: '#f3f3f3',
                                min: 0,
                                max: 100,
                                tickFormatter: function (val, axis) {
                                    return "";
                                }
                            },
                            xaxis: {
                                color: '#f3f3f3',
                                min: 0,
                                max: 100,
                                tickFormatter: function (val, axis) {
                                    return "";
                                }
                            },
                            grid: {
                                hoverable: true,
                                clickable: false,
                                borderWidth: 0,
                                aboveData: false
                            },
                            colors: [scope.settings.color.themeprimary, scope.settings.color.themesecondary],
                        }),
                        update = function () {

                            plot.setData(getSeriesObj());

                            plot.draw();
                            setTimeout(update, updateInterval);
                        },
                        update();

            }
        };
    }
]);