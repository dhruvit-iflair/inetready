app.controller('AdminloginCtrl', function ($scope, $http, $location, $timeout, GloabalConfig, AuthenticationService, site_config, $stateParams, $rootScope, $window) {
    var ec = new evercookie();
    $scope.UID = '';
    ec.get("UID", function (value) {
        $scope.UID = value;
    });

    console.log($location.search());
    console.log($location.hash());

    //check params to to confirm user account
    if ($stateParams.par1 && $stateParams.par2 && $stateParams.par3 && $stateParams.par4) {
        $http({
            method: 'POST',
            url: site_config.apiUrl + 'api/resetpassword',
            data: $stateParams,
            headers: {'Content-Type': 'application/json', 'Authorization': 'Basic 123456789'}
        })
                .success(function (resp, status, headers, config) {
                    if (resp.status) {
                        $rootScope.errorMsg = resp.data;
                    } else {
                        $rootScope.errorMsg = resp.data;
                        $location.path("login");
                    }
                    $timeout(function () {
                        $rootScope.errorMsg = false;
                    }, 3000);

                })
                .error(function (resp, status, headers, config) {
                    $scope.errorMsg = 'Unable to submit form';
                })

        $stateParams = {};
    }
    if ($stateParams.par1 && $stateParams.par2 && $stateParams.par3) {
        $http({
            method: 'POST',
            url: site_config.apiUrl + 'user/confirmation',
            data: $stateParams,
            headers: {'Content-Type': 'application/json', 'Authorization': 'Basic 123456789'}
        })
                .success(function (resp, status, headers, config) {
                    if (resp.status) {
                        $rootScope.returnUrl = resp.data.domain_url;
                        $rootScope.successMsg = resp.data.message;
                    } else {
                        $rootScope.errorMsg = resp.data;
                    }
                    $location.path("login");
                    $timeout(function () {
                        $rootScope.errorMsg = false;
                        $rootScope.successMsg = false;
                    }, 3000);

                })
                .error(function (resp, status, headers, config) {
                    $scope.errorMsg = 'Unable to submit form';
                })

        $stateParams = {};
    }

    AuthenticationService.ClearCredentials();

    $scope.postForm = function (emailid1, password1) {
        $scope.dataLoadingR = true;
        var jsonString = '{"emailid1":"' + emailid1 + '","password1":"' + password1 + '"}';

        var obj = JSON.parse(jsonString);

        $http({
            method: 'POST',
            url: site_config.apiUrl + 'api/login',
            data: {
                prevUID: $scope.UID,
                emailid1: obj.emailid1,
                password1: obj.password1,
                ip_address: myip
            },
            headers: {'Content-Type': 'application/json', 'Authorization': 'Basic 123456789'}
        })

                .success(function (resp, status, headers, config) {
                    // console.log(resp);
                    if (resp.status) {
                        AuthenticationService.SetCredentials(obj.emailid1, obj.password1, resp.data);
                        //GloabalConfig.user_permissions();
                        var search_par = $location.search()

                        if ($rootScope.returnUrl != '' && $rootScope.returnUrl != undefined) {
                            var returnUrl = $rootScope.returnUrl;
                            $window.location.href = returnUrl;
                        } else if (search_par.returnUrl != '' && search_par.returnUrl != undefined) {
                            var returnUrl = search_par.returnUrl;
                            $window.location.href = returnUrl;
                        }
                        else
                            $location.path("app/profile");
                    } else {
                        $scope.errorMsg = resp.data;
                    }
                    $scope.dataLoadingR = false;
                })
                .error(function (resp, status, headers, config) {
                    $scope.errorMsg = 'Unable to submit form';
                    $scope.dataLoadingR = false;
                })
    }

    $scope.forgotPassword = function () {

        $http({
            method: 'GET',
            url: site_config.apiUrl + 'api/forgotpassword?email=' + encodeURIComponent($scope.email),
            headers: {'Content-Type': 'application/json', 'Authorization': 'Basic 123456789'}
        })
                .success(function (resp) {
                    if (resp.status) {
                        $scope.errorMsg = resp.data;
                    } else {
                        $scope.errorMsg = resp.data;
                    }
                })
                .error(function (resp, status, headers, config) {
                    $scope.errorMsg = 'Unable to submit form';
                })
    }

    $scope.confirmPassword = function () {

        $http({
            method: 'POST',
            url: site_config.apiUrl + 'api/confirmpassword',
            data: {
                "par1": $rootScope.$stateParams.par1,
                "par2": $rootScope.$stateParams.par2,
                "par3": $rootScope.$stateParams.par3,
                "par4": $rootScope.$stateParams.par4,
                "password": $scope.newPassword
            },
            headers: {'Content-Type': 'application/json', 'Authorization': 'Basic 123456789'}
        })
                .success(function (resp) {
                    if (resp.status) {
                        $rootScope.errorMsg = resp.data;
                    } else {
                        $rootScope.errorMsg = resp.data;
                    }
                    $timeout(function () {
                        $rootScope.errorMsg = false;
                    }, 3000);
                    $location.path("login");
                })
                .error(function (resp, status, headers, config) {
                    $scope.errorMsg = 'Unable to submit form';
                })
    }


});