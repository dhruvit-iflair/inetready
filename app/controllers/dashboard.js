'use strict';

app
        // Dashboard Box controller 
        .controller('DashboardCtrl', [
            '$rootScope', '$scope', '$http', 'site_config', '$timeout', function ($rootScope, $scope, $http, site_config, $timeout) {
                $scope.boxWidth = $('.box-tabbs').width() - 20;
                $scope.campaign = {};
                var date = new Date();
                $scope.currentDate = date.getDate() + " " + date.getMonth();

                /* GET Dashboard Instance LIST */
                $http.get(site_config.apiUrl + "user/users?user_id=" + $rootScope.globals.currentUser.user_id).then(function (response)
                {
                    if (response.data.status) {
                        $rootScope.dashboard_users = response.data.data;
                    }
                });


                /* GET Dashboard Data LIST */
                $http.get(site_config.apiUrl + "user/getdashboarddata?user_id=" + $rootScope.globals.currentUser.user_id).then(function (response)
                {
                    if (response.data.status) {
                        $rootScope.dd = response.data.data;
                        setTimeout(function () {
                            $scope.widgetChart = 1;
                        }, 100);
                    }
                });

                /* GET Dashboard Data Visits */
                $http.get(site_config.apiUrl + "user/getdashboardvisits?user_id=" + $rootScope.globals.currentUser.user_id).then(function (response)
                {
                    
                    if (response.data.status) {
                        $scope.asas = response.data.data.visitsData;
                        $scope.currentDate = response.data.data.currentDate;
                        $scope.totalVisit = response.data.data.totalVisit;
                        $scope.lastWeekVisit = response.data.data.lastWeekVisit;
                        $scope.yesterdayVisit = response.data.data.yesterdayVisit;
                        $scope.todayVisit = response.data.data.todayVisit;
                        setTimeout(function () {
                              $scope.dayVisitChart =1;
                        }, 100);


                    }
                });

                //$scope.asas=[5,7,6,5,9,4,3,7,2];

                /* GET CAMPAIGN LIST */
                $http.get(site_config.apiUrl + "api/getallcampaignlist?id=" + $rootScope.globals.currentUser.user_id).then(function (response) {
                    $scope.campaign.data = response.data.aaData;
                });

                //---Visitor Sources Pie Chart---//
                $scope.visitorSourcePieData = [
                    {
                        data: [[1, 21]],
                        color: '#fb6e52'
                    },
                    {
                        data: [[1, 12]],
                        color: '#e75b8d'
                    },
                    {
                        data: [[1, 11]],
                        color: '#a0d468'
                    },
                    {
                        data: [[1, 10]],
                        color: '#ffce55'
                    },
                    {
                        data: [[1, 46]],
                        color: '#5db2ff'
                    }
                ];
                $scope.visitorSourcePieOptions = {
                    series: {
                        pie: {
                            innerRadius: 0.45,
                            show: true,
                            stroke: {
                                width: 4
                            }
                        }
                    }
                };

                $scope.dashboard_filters = ['Daily', '1 Week', '2 Weeks', '30 Days', '3 Months', '6 Months', '1 Year'];
                $scope.d_filter = $scope.dashboard_filters[2];
                $scope.days = $scope.ceiling = 30;

                $scope.parent = 0;
                $scope.child = 0;
                $scope.visit_data = 0;
                $scope.realtime_data = 1;
                $scope.slider_bar = 1;


                //Slider config with callbacks
                $scope.slider_callbacks = {
                    days: 14,
                    options: {
                        hideLimitLabels: true,
                        showSelectionBar: true,
                        onStart: function () {
                            $scope.otherData.start = $scope.slider_callbacks.value * 10;
                        },
                        onChange: function () {
                            $scope.otherData.change = $scope.slider_callbacks.value * 10;
                        },
                        onEnd: function () {
                            $scope.otherData.end = $scope.slider_callbacks.value * 10;
                            console.log($scope.slider_callbacks.days);
                            $scope.changeTabs($scope.parent, $scope.child)
                        }
                    }
                };
                $scope.otherData = {
                    start: 0,
                    change: 0,
                    end: 0
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
                        $scope.changeTabs($scope.parent, $scope.child)
                    }, 0);
                }



                $scope.changeTabs = function (parent, child) {

                    $scope.realtime_data = 0;
                    console.log($scope.ceiling);
                    $scope.parent = parent;
                    $scope.child = child;
                    if (child == 0) {
                        setTimeout(function () {
                            $scope.realtime_data = 1;
                        }, 0);

                    }
                    if (child == 1) {
                        $scope.visit_data = 0;
                        $scope.getvisitdata(parent);
                    }
                    if (child == 2) {
                        $scope.getSocialData(parent);
                    }
                    if (child == 3) {
                        $rootScope.browser_data = 0;
                        $scope.getBrowserData(parent);
                    }
                }

                /* GET VISITS STATISTICS */
                $scope.getvisitdata = function (type) {
                    $http({
                        url: site_config.apiUrl + "visitor/visitorchartdata",
                        method: 'POST',
                        data: {type: type, id: $rootScope.globals.currentUser.user_id, filter: $scope.d_filter, days: $scope.slider_callbacks.days},
                        headers: {'Content-Type': 'application/json'}
                    }).success(function (response) {
                        // console.log(response)
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
                                label: "Total Campaigns",
                                data: response.totalCampaign,
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
                        url: site_config.apiUrl + "visitor/browserchartdata",
                        method: 'POST',
                        data: {type: type, user_id: $rootScope.globals.currentUser.user_id, filter: $scope.d_filter, days: $scope.slider_callbacks.days},
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

                /* GET SOCIAL STATISTICS */
                $scope.getSocialData = function (type) {
                    $http({
                        url: site_config.apiUrl + "api/get_social_statistics",
                        method: 'POST',
                        data: {type: type, user_id: $rootScope.globals.currentUser.user_id, filter: $scope.d_filter, days: $scope.slider_callbacks.days},
                        headers: {'Content-Type': 'application/json'}
                    }).success(function (response) {
                        $scope.social = response.data;
                    }).error(function () {
                    });
                }
            }
        ]);

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