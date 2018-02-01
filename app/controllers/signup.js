app.controller('SignupCtrl', function ($scope, $http, $location, $timeout, GloabalConfig, AuthenticationService, site_config, $stateParams, $rootScope) {

    if ($stateParams.user_id) {
        var user_id = $stateParams.user_id;
    } else {
        var user_id = 0;
    }
    $scope.signup = function () {
        var protocol = $location.protocol();
        var host = $location.host();
        $scope.signupForm.domain_url = protocol + "://" + host;
        $scope.signupForm.ip_address = myip;
        $scope.signupForm.user_id = user_id;
        $http({
            method: 'POST',
            url: site_config.apiUrl + 'user/signup',
            data: $scope.signupForm,
            headers: {'Content-Type': 'application/json', 'Authorization': 'Basic 123456789'}
        })
                .success(function (resp) {
                    if (resp.status) {
                        $rootScope.successMsg = resp.data;
                    } else {
                        $rootScope.errorMsg = resp.data;
                    }
                    $timeout(function () {
                        $rootScope.errorMsg = false;
                        $rootScope.successMsg = false;
                    }, 5000);
                    //$location.path("login");
                })

    }

});