app.controller('BrowserChartCtrl', function ($scope, $http, $location, subdomain, site_config, $stateParams, $rootScope, deviceDetector, $sce, $modal) {
    $scope.getBrowserData = function () {

        $http.get(site_config.apiUrl + "visitor/browserchartdata?user_id=" + $rootScope.globals.currentUser.user_id + "&type=" + $scope.parent)
                .success(function (response) {
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
                    return y ;
                }
            });


        }
    };
});