var app =
        angular.module('app')
        .config(
                [
                    '$controllerProvider', '$compileProvider', '$filterProvider', '$provide',
                    function ($controllerProvider, $compileProvider, $filterProvider, $provide) {
                        app.controller = $controllerProvider.register;
                        app.directive = $compileProvider.directive;
                        app.filter = $filterProvider.register;
                        app.factory = $provide.factory;
                        app.service = $provide.service;
                        app.constant = $provide.constant;
                        app.value = $provide.value;
                    }
                ]);


app.config(function ($breadcrumbProvider) {
    $breadcrumbProvider.setOptions({
        template: '<ul class="breadcrumb"><li><i class="fa fa-home"></i><a href="#">Home</a></li><li ng-repeat="step in steps" ng-class="{active: $last}" ng-switch="$last || !!step.abstract"><a ng-switch-when="false" href="{{step.ncyBreadcrumbLink}}">{{step.ncyBreadcrumbLabel}}</a><span ng-switch-when="true">{{step.ncyBreadcrumbLabel}}</span></li></ul>'
    });
    
});
app.constant('site_config', {
    appName: 'SquibHub',
    siteURL: 'http://app.squibdrive.com',
    instanceURL: 'squibdrive.com',
    apiUrl: 'yii/web/index.php/'
});
app.factory('subdomain', ['$location', function ($location) {
    var host = $location.host();
    if (host.indexOf('.') < 0) 
        return null;
    else
        return host.split('.')[0];
}]);
app.service('browser', ['$window', function($window) {

     return function() {

         var userAgent = $window.navigator.userAgent;

        var browsers = {chrome: /chrome/i, safari: /safari/i, firefox: /firefox/i, ie: /internet explorer/i};

        for(var key in browsers) {
            if (browsers[key].test(userAgent)) {
                return key;
            }
       };

       return 'unknown';
    }

}]);