angular.module('app').directive("flotChartRealtime", ['$http', 'site_config', '$rootScope', '$location',
    function ($http, site_config, $rootScope, $location) {
        return {
            restrict: "AE",
            link: function (scope, ele) {
                var realTimedata,
                        realTimedata2,
                        totalPoints,
                        getSeriesObj,
                        getRandomData,
                        getRandomData2,
                        getRandomDataDemo,
                        getRandomDataDemo2,
                        updateInterval,
                        plot,
                        update;
                return realTimedata = [],
                        realTimedata2 = [],
                        totalPoints = 1,
                        getSeriesObj = function () {
                            if ($location.path() == '/app/dashboard') {
                                if (scope.parent == 0 && scope.child == 0) {
                                    return [
                                        {
                                            label: ' Squibkey',
                                            data: getRandomData(),
                                            lines: {
                                                show: true,
                                                lineWidth: 1,
                                                fill: true,
                                                fillColor: {
                                                    colors: [
                                                        {
                                                            opacity: .5
                                                        }, {
                                                            opacity: 1
                                                        }
                                                    ]
                                                },
                                                steps: false
                                            },
                                            shadowSize: 0
                                        },
                                        {
                                            label: ' Squibcard',
                                            data: getRandomData2(),
                                            lines: {
                                                lineWidth: 0,
                                                fill: true,
                                                fillColor: {
                                                    colors: [
                                                        {
                                                            opacity: .5
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
                                } else if (scope.parent == 1 && scope.child == 0) {
                                    return [
                                        {
                                            label: ' Squibkey',
                                            data: getRandomData(),
                                            lines: {
                                                show: true,
                                                lineWidth: 1,
                                                fill: true,
                                                fillColor: {
                                                    colors: [
                                                        {
                                                            opacity: .5
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

                                } else if (scope.parent == 2 && scope.child == 0) {
                                    return [
                                        {
                                            label: ' Squibcard',
                                            data: getRandomData2(),
                                            lines: {
                                                lineWidth: 1,
                                                fill: true,
                                                fillColor: {
                                                    colors: [
                                                        {
                                                            opacity: .5
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
                                } else {
                                    return [
                                        {
                                            //label: ' Squibkey',
                                            data: getRandomDataDemo(),
                                            lines: {
                                                show: true,
                                                lineWidth: 1,
                                                fill: true,
                                                fillColor: {
                                                    colors: [
                                                        {
                                                            opacity: .5
                                                        }, {
                                                            opacity: 1
                                                        }
                                                    ]
                                                },
                                                steps: false
                                            },
                                            shadowSize: 0
                                        },
                                        {
                                            //label: ' Squibcard',
                                            data: getRandomDataDemo2(),
                                            lines: {
                                                lineWidth: 0,
                                                fill: true,
                                                fillColor: {
                                                    colors: [
                                                        {
                                                            opacity: .5
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
                            }
                        },
                        getRandomData = function () {
                            //alert(realTimedata.length);
                            if (realTimedata.length > 0)
                                realTimedata = realTimedata.slice(1);

                            if (realTimedata.length == 0) {
                                $http.get(site_config.apiUrl + "api/getsquibkeydata?load=init&id=" + $rootScope.globals.currentUser.user_id).then(function (response) {
                                    realTimedata = (response.data.data);
                                });
                            } else {
                                $http.get(site_config.apiUrl + "api/getsquibkeydata?id=" + $rootScope.globals.currentUser.user_id).then(function (response) {
                                    realTimedata.push(response.data.data);
                                });
                            }

//                            for (var i = 0; i < 1; ++i) {
//                                $http.get(site_config.apiUrl + "api/getsquibkeydata?id=" + $rootScope.globals.currentUser.user_id).then(function (response) {
//                                    realTimedata.push(response.data.data);
//                                });
//                            }
                            var res = [];
                            for (var i = 0; i < realTimedata.length; ++i) {
                                res.push([i, realTimedata[i]]);
                            }

                            return res;
                        },
                        getRandomData2 = function () {
                            if (realTimedata2.length > 0)
                                realTimedata2 = realTimedata2.slice(1);
                            //alert(realTimedata2.length);
                            if (realTimedata2.length == 0) {
                                $http.get(site_config.apiUrl + "api/getsquibcarddata?load=init&id=" + $rootScope.globals.currentUser.user_id).then(function (response) {
                                    realTimedata2 = (response.data.data);
                                });
                            } else {
                                $http.get(site_config.apiUrl + "api/getsquibcarddata?id=" + $rootScope.globals.currentUser.user_id).then(function (response) {
                                    realTimedata2.push(response.data.data);
                                });
                            }
//                            for (var i = 0; i < 2; ++i) {
//                                $http.get(site_config.apiUrl + "api/getsquibcarddata?id=" + $rootScope.globals.currentUser.user_id).then(function (response) {
//                                    realTimedata2.push(response.data.data);
//                                });
//                            }
                            var res = [];
                            for (var i = 0; i < realTimedata2.length; ++i) {
                                res.push([i, realTimedata2[i]]);
                            }

                            return res;
                        },
                        getRandomDataDemo = function () {
                            if (realTimedata.length > 0)
                                realTimedata = realTimedata.slice(1);

                            // Do a random walk

                            while (realTimedata.length < totalPoints) {

                                var prev = realTimedata.length > 0 ? realTimedata[realTimedata.length - 1] : 50,
                                        y = prev + Math.random() * 10 - 5;

                                if (y < 0) {
                                    y = 0;
                                } else if (y > 100) {
                                    y = 100;
                                }
                                realTimedata.push(y);
                            }

                            // Zip the generated y values with the x values

                            var res = [];
                            for (var i = 0; i < realTimedata.length; ++i) {
                                res.push([i, realTimedata[i]]);
                            }

                            return res;
                        },
                        getRandomDataDemo2 = function () {
                            if (realTimedata2.length > 0)
                                realTimedata2 = realTimedata2.slice(1);

                            // Do a random walk

                            while (realTimedata2.length < totalPoints) {

                                var prev = realTimedata2.length > 0 ? realTimedata[realTimedata2.length] : 50,
                                        y = prev - 25;

                                if (y < 0) {
                                    y = 0;
                                } else if (y > 100) {
                                    y = 100;
                                }
                                realTimedata2.push(y);
                            }


                            var res = [];
                            for (var i = 0; i < realTimedata2.length; ++i) {
                                res.push([i, realTimedata2[i]]);
                            }

                            return res;
                        },
                        // Set up the control widget
                        updateInterval = 5000,
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
//                            colors: [scope.settings.color.themeprimary, scope.settings.color.themesecondary, scope.settings.color.themethirdcolor]
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

angular.module('app').directive("databoxFlotChartRealtime", [
    function () {
        return {
            restrict: "AE",
            link: function (scope, ele) {
                var data = [],
                        totalPoints = 300,
                        updateInterval = 100,
                        plot,
                        update,
                        getRandomData;
                return getRandomData = function () {

                    if (data.length > 0)
                        data = data.slice(1);

                    // Do a random walk

                    while (data.length < totalPoints) {

                        var prev = data.length > 0 ? data[data.length - 1] : 50,
                                y = prev + Math.random() * 10 - 5;

                        if (y < 0) {
                            y = 0;
                        } else if (y > 100) {
                            y = 100;
                        }

                        data.push(y);
                    }

                    // Zip the generated y values with the x values

                    var res = [];
                    for (var i = 0; i < data.length; ++i) {
                        res.push([i, data[i]]);
                    }

                    return res;
                },
                        // Set up the control widget
                        plot = $.plot(ele[0], [getRandomData()], {
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
                                tickDecimals: 0,
//                            tickFormatter: function(val, axis) {
//                                return "";
//                            }
                            },
                            colors: ['#fff'],
                            series: {
                                lines: {
                                    lineWidth: 2,
                                    fill: false,
                                    fillColor: {
                                        colors: [
                                            {
                                                opacity: 0.5
                                            }, {
                                                opacity: 0
                                            }
                                        ]
                                    },
                                    steps: false
                                },
                                shadowSize: 0
                            },
                            grid: {
                                show: false,
                                hoverable: true,
                                clickable: false,
                                borderWidth: 0,
                                aboveData: false
                            }
                        }), update = function () {

                    plot.setData([getRandomData()]);
                    plot.draw();
                    setTimeout(update, updateInterval);
                },
                        update();
            }
        };
    }
]);