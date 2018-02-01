app.controller('GlobalSettCtrl', function ($scope, $http, $location, $rootScope, $templateCache, site_config) {

    // $scope.permissions = {SQuibKey: true, SQuibPush: true, SQuibDrive: true, SQuibPage: true, SQuibMail: true, SQuibQR: true, SQuibSocial: true};

    $http.get(site_config.apiUrl + "api/modulepermissions")
            .then(function (response)
            {
                if (response.data.status) {
                    $scope.permissions = response.data.data;
                }
                else {
                    $location.path("/login");
                }
            }
            );


    $scope.setpermissions = function () {
        $http({
            method: 'POST',
            url: site_config.apiUrl + "api/setpermissions",
            data: $scope.permissions,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        })

                .success(function (response) {

                    if (response.data.status) {
                        $scope.errorMsg = response.data.data;
                        $http.get(site_config.apiUrl + "api/userpermissions?role=" + response.data.user_role + "&user_id=" + response.data.user_id)
                                .then(function (response)
                                {
                                    if (response.data.status) {
                                        $rootScope.user_permissions = response.data.data;
                                        //$scope.user_id = $scope.names[0];
                                    }
                                });
                    } else {
                        $scope.errorMsg = response.data.data;
                    }
                })
    };



});