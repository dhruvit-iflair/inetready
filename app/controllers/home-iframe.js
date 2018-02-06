app.controller('HomeIframeCtrl', function ($scope, $http, $location, subdomain, site_config, $stateParams, $rootScope, deviceDetector, $sce, $modal) {

    var protocol = $location.protocol();
    var host = $location.host();
//    var absUrl = $location.absUrl();

    var instance_url = protocol + "://" + host;
    $scope.user = {};
    $http({
        method: 'POST',
        url: site_config.apiUrl + "user/getsubdomain",
        data: {
            //instance_url: instance_url
            instance_url: 'http://squibhub.squibdrive.net'
        },
        headers: {'Content-Type': 'application/json'}
    }).success(function (resp) {
        if (resp.status) {
            $rootScope.pagetitle = ((resp.data.organization) ? (resp.data.organization + " | ") : "") + resp.data.admin_name;
            //$scope.user_iframe = '<iframe src="'+site_config.siteURL+'/#/profile/' + resp.data.id + '" width="100%" height="' + ($(window).height() - 10) + '" frameborder="0"></iframe>'
            $scope.user_iframe = '<iframe src="http://arnav.squibdrive.net/#/" width="100%" height="' + ($(window).height() - 10) + '" frameborder="0"></iframe>'
        }
    }).error(function () {
        $scope.errorMsg = 'Unable to submit form';
    });
   

    /* Device type and os type get */
    deviceData = deviceDetector;
    $scope.ip_address = myip;

    function logVisitor(adminId) {
        $http({
            method: 'POST',
            url: site_config.apiUrl + "api/getlatlng",
            data: {ip_address: $scope.ip_address},
            headers: {'Content-Type': 'application/json'}
        }).success(function (response) {
            $scope.latitude = response.data.latitude;
            $scope.longitude = response.data.longitude;
            $scope.city = response.data.city;
            $scope.isp = response.data.isp;
            $scope.zipcode = response.data.zipcode;
            if ($scope.latitude != '' && $scope.longitude != '')
            {
                $http({
                    method: 'POST',
                    url: site_config.apiUrl + "api/savevisitor",
                    data: {
                        ip_address: $scope.ip_address,
                        latitude: $scope.latitude,
                        longitude: $scope.longitude,
                        user_id: adminId,
                        browser: deviceData.browser,
                        os: deviceData.os,
                        device: (deviceData.device == 'unknown') ? 'desktop' : deviceData.device,
                        visitor_id: (typeof ($rootScope.globals.currentUser) != 'undefined') ? $rootScope.globals.currentUser.user_id : 0,
                        domain: window.location.href,
                        city: $scope.city,
                        isp: $scope.isp,
                        zipcode: $scope.zipcode,
                    },
                    headers: {'Content-Type': 'application/json'}
                }).success(function (resp) {
                    console.log(resp.data);
                }).error(function () {
                    console.log('Unable to save visitors');
                });
            }
        }).error(function () {
            console.log('Unable to find Latitude nad longitude');
        });

    }

});