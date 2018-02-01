app.controller('NameServerCtrl', function ($scope, $http, $location, $rootScope, $templateCache, site_config) {

    // $scope.permissions = {SQuibKey: true, SQuibPush: true, SQuibDrive: true, SQuibPage: true, SQuibMail: true, SQuibQR: true, SQuibSocial: true};

    $http.get(site_config.apiUrl + "api/getnameservers?user_id=" + $rootScope.globals.currentUser.user_id)
            .then(function (response)
            {
                if (response.data.status) {
                    $scope.name_servers = response.data.data;
                    $scope.name_servers.push({});
                }
            });


    $scope.save_name_server = function (name_server) {
        name_server.user_id = $rootScope.globals.currentUser.user_id;
        $http({
            method: 'POST',
            url: site_config.apiUrl + "api/savenameservers",
            data: name_server,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        })

                .success(function (response) {
                   $scope.successMsg = response.data;
                    if (response.status) {
                        $http.get(site_config.apiUrl + "api/getnameservers?user_id=" + $rootScope.globals.currentUser.user_id)
                                .then(function (response)
                                {
                                    if (response.data.status) {
                                        $scope.name_servers = response.data.data;
                                        $scope.name_servers.push({});
                                    }
                                });

                    } else {
                      
                        $scope.errorMsg = response.data;
                    }
                })
    };



});