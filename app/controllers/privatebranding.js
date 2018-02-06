app.controller('PrivateBrandCtrl', ['$rootScope', '$scope', 'Upload', 'site_config', '$http', '$window', '$templateCache', '$timeout', function ($rootScope, $scope, Upload, site_config, $http, $window, $templateCache, $timeout) {
        //$scope.site_name = globals.currentUser.site_name;


        $scope.site_name = $rootScope.site_preferences.site_name;
        $scope.addWebsite = function () {
            //console.log($scope);
            if ($scope.site_name) {
                Upload.upload({
                    url: site_config.apiUrl + "api/private_branding",
                    data: {favicon: $scope.favicon, sitelogo: $scope.logo, sitename: $scope.site_name, 'user_id': $rootScope.globals.currentUser.user_id},
                    headers: {'Content-Type': 'multipart/form-data', 'Authorization': 'Basic ' + $rootScope.globals.currentUser.encryption},
                    withCredentials: true,
                }).then(function (resp) {
                    if (resp.data.status) {
                        $scope.successMsg = resp.data.data;
                    } else {
                        $scope.errorMsg = resp.data.data;
                    }
                    $timeout(function () {
                        $scope.errorMsg = false;
                        $scope.successMsg = false;
                    }, 5000);


                }, function (resp) {
                    console.log('Error status: ' + resp.status);
                }, function (evt) {
                    var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
                    console.log('progress: ' + progressPercentage + '% ');
                });
            }
        };

        $scope.removeBrand = function () {
            $http.get(site_config.apiUrl + "api/removebrand?user_id=" + $rootScope.globals.currentUser.user_id)
                    .then(function (response)
                    {
                        if (response.data.status) {
                            $rootScope.site_preferences = {};
                        }
                    });
        }


    }]);