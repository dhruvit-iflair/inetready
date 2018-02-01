'use strict';
angular.module('GlobalData', []);
angular.module('Authentication', []);
angular.module('app', [
    'ngAnimate',
    'ngCookies',
    'ipCookie',
    'ngResource',
    'ngSanitize',
    'ngTouch',
    'ngStorage',
    'ui.router',
    'ncy-angular-breadcrumb',
    'ui.bootstrap',
    'ui.utils',
    'oc.lazyLoad',
    'angularFileUpload',
    'ngFileUpload',
    'Authentication',
    'GlobalData',
    'ng.deviceDetector',
    'rzModule'
]);


