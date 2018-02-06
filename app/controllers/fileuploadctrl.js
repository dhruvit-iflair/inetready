app.controller('AppController', ['$rootScope', '$scope', 'FileUploader', 'site_config', '$http', '$location', function ($rootScope, $scope, FileUploader, site_config, $http, $location) {
        $scope.UploadPopupClose = function () {
            $scope.$uploadModalInstance.close();

            $(".vidCls, .audCls").each(function (index) {
                $(this).get(0).pause();
            });

            $http({
                method: 'POST',
                url: site_config.apiUrl + "api/cloudinstancefiles",
                data: {
                    cinstence_id: $scope.current_instance
                },
                headers: {'Content-Type': 'application/json'}
            }).then(function (response) {
                if (response.data.status) {
                    $scope.cloud_instance_files = response.data.data;
                } else {
                    $location.path("/login");
                }
            });
        }
        //var cinstence_id = $('#instance_hid').val();
        var master = ($location.path() == '/app/masterfiles') ? 1 : 0;
        console.log($scope.parent_id);
        //var user_id = ($location.path() == '/app/masterfiles') ? $rootScope.globals.currentUser.user_id : cinstence_id;

        var uploader = $scope.uploader = new FileUploader({
            url: site_config.apiUrl + "user/filesupload",
            type: 'post',
            formData: [{master: master, user_id: $scope.current_instance, folder_id: $scope.parent_id}],
            headers: {
                'Authorization': 'Basic ' + $rootScope.globals.currentUser.encryption
            },
        });

        // FILTERS

        uploader.filters.push({
            name: 'customFilter',
            fn: function (item /*{File|FileLikeObject}*/, options) {
                return this.queue.length < 10;
            }
        });

        // CALLBACKS

//        uploader.onWhenAddingFileFailed = function (item /*{File|FileLikeObject}*/, filter, options) {
//            console.info('onWhenAddingFileFailed', item, filter, options);
//        };
//        uploader.onAfterAddingFile = function (fileItem) {
//            console.info('onAfterAddingFile', fileItem);
//        };
//        uploader.onAfterAddingAll = function (addedFileItems) {
//            console.info('onAfterAddingAll', addedFileItems);
//        };
//        uploader.onBeforeUploadItem = function (item) {
//            console.info('onBeforeUploadItem', item);
//        };
//        uploader.onProgressItem = function (fileItem, progress) {
//            console.info('onProgressItem', fileItem, progress);
//        };
//        uploader.onProgressAll = function (progress) {
//            console.info('onProgressAll', progress);
//        };
//        uploader.onSuccessItem = function (fileItem, response, status, headers) {
//            console.info('onSuccessItem', fileItem, response, status, headers);
//        };
//        uploader.onErrorItem = function (fileItem, response, status, headers) {
//            console.info('onErrorItem', fileItem, response, status, headers);
//        };
//        uploader.onCancelItem = function (fileItem, response, status, headers) {
//            console.info('onCancelItem', fileItem, response, status, headers);
//        };
//        uploader.onCompleteItem = function (fileItem, response, status, headers) {
//            console.info('onCompleteItem', fileItem, response, status, headers);
//        };
//        uploader.onCompleteAll = function () {
//
//            console.info('onCompleteAll');
//        };
//
//        console.info('uploader', uploader);
    }]);

