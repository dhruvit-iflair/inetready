app.controller('ModulePerCtrl', function ($scope, $http, $rootScope, $window, $templateCache, site_config) {

    $scope.permissions = {SQuibKey: true, SQuibPush: true, SQuibDrive: true, SQuibPage: true, SQuibMail: true, SQuibQR: true, SQuibSocial: true};

    $http.get(site_config.apiUrl + "api/getsquibmodules")
            .then(function (response)
            {
                if (response.data.status) {
                    $scope.squib_modules = response.data.data;

                }
            });


    $scope.getusersbyrole = function () {
        console.log($rootScope.globals.currentUser.user_role);
        var api_url = site_config.apiUrl + "user/getusersbyrole?role=" + $scope.user_role;
        if ($rootScope.globals.currentUser.user_role == 'reseller') {
            api_url = site_config.apiUrl + "user/getusersbyrole?role=" + $scope.user_role + "&parent_id=" + $rootScope.globals.currentUser.user_id;
        } else if ($rootScope.globals.currentUser.user_role == 'client') {
            api_url = site_config.apiUrl + "user/getusersbyrole?role=" + $scope.user_role + "&parent_id=" + $rootScope.globals.currentUser.user_id;
        }
        $http.get(api_url)
                .then(function (response)
                {
                    if (response.data.status) {
                        $scope.users = response.data.data;
                        //$scope.user_id = $scope.names[0];
                    }
                });
    };

    $scope.getpermissions = function () {
        console.log($scope.user_id);
        $http.get(site_config.apiUrl + "api/userpermissions?role=" + $scope.user_role + "&user_id=" + $scope.user_id.id)
                .then(function (response)
                {
                    if (response.data.status) {
                        $scope.squib_modules = response.data.data;
                        //$scope.user_id = $scope.names[0];
                    }
                });
    };

    $scope.setPermissions = function () {
        if ($scope.user_id.id) {
            $http({
                method: 'POST',
                url: site_config.apiUrl + "api/setuserpermissions",
                data: {
                    user_id: $scope.user_id.id,
                    permissions: $scope.squib_modules
                },
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            })
                    .success(function (resp, status, headers, config) {

                        if (resp.status) {

                            $scope.errorMsg = resp.data;
                        } else {
                            $scope.errorMsg = resp.data;
                        }
                    })
        }

    }

    $scope.deletePermissions = function () {

        var deleteUser = $window.confirm('Are you absolutely sure you want to delete?');
        if (deleteUser)
        {
            $http.get(site_config.apiUrl + "api/deleteuserpermission?user_id=" + $scope.user_id.id)
                    .success(function (data) {
                        $scope.errorMsg = "Deleted Successfully";
                        $http.get(site_config.apiUrl + "api/getsquibmodules")
                                .then(function (response)
                                {
                                    if (response.data.status) {
                                        $scope.squib_modules = response.data.data;

                                    }
                                });
                    })
        }
    }



});