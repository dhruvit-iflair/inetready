app.controller('HomeCtrl', function ($scope, $http, $location, $window, site_config, $stateParams, $rootScope, deviceDetector, $sce, $modal) {
    
    if ($stateParams.user_id) {
       

        var user_id = $stateParams.user_id;
        // Get existing user details
        var visitor_id = (typeof ($rootScope.globals.currentUser) != 'undefined') ? $rootScope.globals.currentUser.user_id : 0;

        $scope.user = {};

        $scope.share = {};



        $http({
            method: 'POST',
            url: site_config.apiUrl + "user/getprofile",
            data: {
                user_id: user_id,
                visitor_id: visitor_id
            },
            headers: {'Content-Type': 'application/json'}
        }).success(function (resp) {
            if (resp.status) {
                $scope.user = resp.data;
                $window.location.href = $scope.user.instance_url;



            }
        }).error(function () {
            $scope.errorMsg = 'Unable to submit form';
        });

    }
});
