app.controller('uploadPhotoCtrl', ['$rootScope', '$scope', 'Upload', 'site_config', '$timeout', function ($rootScope, $scope, Upload, site_config, $timeout) {

        // upload on file select or drop

        $scope.upload = function (file) {
            if ($scope.uploadform.file.$error.minHeight || $scope.uploadform.file.$error.minWidth) {
                alert('Min image size 200X200 in pixels');
            }
            if($scope.uploadform.file.$error.maxSize){
                 alert('File too large :max 2M');
            }
            if ($scope.file) {

                Upload.upload({
                    url: site_config.apiUrl + "api/uploadphoto",
                    data: {file: file, 'user_id': $rootScope.globals.currentUser.user_id},
                    headers: {'Content-Type': 'multipart/form-data', 'Authorization': 'Basic ' + $rootScope.globals.currentUser.encryption},
                    withCredentials: true,
                }).then(function (resp) {
                    $scope.flash = true;
                    $timeout(function () {
                        console.log(resp.data.data.file);
                        if (resp.data.status) {
                            $scope.uploadSuccess = resp.data.data.answer;
                            $rootScope.globals.currentUser.profile_image = resp.data.data.file;
                        } else {
                            $scope.uploadError = resp.data.status.status_message;
                        }
                        $timeout(function () {
                            $scope.flash = false;
                        }, 5000);
                    });

                }, function (resp) {
                    console.log('Error status: ' + resp.status);
                }, function (evt) {
                    var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
                    console.log('progress: ' + progressPercentage + '% ');
                });
            }
        };
    }]);

