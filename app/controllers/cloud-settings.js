app.controller('CloudSettingsCtrl', function ($scope, $http, $location, $window, $templateCache, site_config) {


    $http.get(site_config.apiUrl + "api/cloudsettings")
            .then(function (resp)
            {
                if (resp.data.status) {
                    $scope.clouddetails = resp.data.data;
                } else {
                    $location.path("/login");
                }
            });


    $scope.updatecloudDetails = function () {

        var max_simul_file_upload = document.getElementById('max_simul_file_upload').value;
        var max_file_size = document.getElementById('max_file_size').value;
        var allowed_types = btoa(document.getElementById('allowed_types').value);
        var excluded_types = btoa(document.getElementById('excluded_types').value);
        var preview_extensions = document.getElementById('preview_extensions').value;
        var date_format = document.getElementById('date_format').value;
        var default_limit_size = document.getElementById('default_limit_size').value;
        var limit_unit = document.getElementById('limit_unit').value;

        var jsonString = '{"max_simul_file_upload":"' + max_simul_file_upload + '","max_file_size":"' + max_file_size + '","allowed_types":"' + allowed_types + '","excluded_types":"' + excluded_types + '","preview_extensions":"' + preview_extensions + '","date_format":"' + date_format + '","default_limit_size":"' + default_limit_size + '","limit_unit":"' + limit_unit + '"}';


        var obj = JSON.parse(jsonString);

        $http({
            method: 'POST',
            url: site_config.apiUrl + "api/updatecloud",
            data: {
                max_simul_file_upload: obj.max_simul_file_upload,
                max_file_size: obj.max_file_size,
                allowed_types: obj.allowed_types,
                excluded_types: obj.excluded_types,
                preview_extensions: obj.preview_extensions,
                date_format: obj.date_format,
                default_limit_size: obj.default_limit_size,
                limit_unit: obj.limit_unit
            },
            headers: {'Content-Type': 'application/json'}
        })

                .success(function (data, status, headers, config) {
                    if (data.status) {
                        $location.path("app/cloudsettings");
                        $scope.errorMsg = "Updated Successfully";
                    } else {
                        $location.path("/login");
                    }

                })
                .error(function (data, status, headers, config) {
                    //$scope.errorMsg = 'Unable to submit form';
                    $location.path("app/cloudsettings");
                    $scope.errorMsg = "Updated Successfully";

                })

    }




});