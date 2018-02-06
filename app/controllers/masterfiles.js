app.controller('MasterCtrl', function ($scope, $http, $location, $window, $modal, $rootScope, $sce, site_config, $timeout, $rootScope) {

    $scope.$sce = $sce;

    $scope.fileupload = false;
    $scope.master = 1;


    $scope.ListInstance = function () {
        $scope.top_menu_disable = true;
        $scope.current_item = 0;
        $http.get(site_config.apiUrl + "user/cloudinstancelist?user_id=" + $rootScope.globals.currentUser.user_id)
                .then(function (response) {
                    if (response.data.status) {
                        $scope.cloud_instance_names = response.data.data;
                    } else {
                        $location.path("/login");
                    }
                });
    }

    $scope.ListInstance();

    $scope.toggleModal = function (btnClicked) {
        $scope.$uploadModalInstance = $modal.open({
            templateUrl: 'views/fileupload.html',
            controller: 'AppController',
            size: 'lg',
            scope: $scope
        });
    };

    $scope.send_file = function () {
        $scope.$sendModalInstance = $modal.open({
            templateUrl: 'views/send-file.html',
            controller: 'SendFileController',
            //size: 'lg',
            scope: $scope
        });

    };
    $scope.shareFilesToUser = function () {

        if ($scope.active_items.length == 0) {
            alert('Please select atleast one file to share');
            return false;
        }

        $scope.$sendModalInstance = $modal.open({
            templateUrl: 'views/send-file.html',
            controller: 'SendFilesController',
            //size: 'lg',
            scope: $scope
        });

    };


    $scope.showModal1 = false;
    $scope.toggleModal1 = function (filepath) {
        $scope.newURL = site_config.siteURL + "/drive/" + $scope.current_type + "/" + $scope.current_instance + "/" + $scope.current_item + "/" + $scope.file_name;
        $scope.$copyLink = $modal.open({
            templateUrl: 'views/copylink.html',
            size: 'sm',
            scope: $scope
        });
    };

    $scope.CLPopupClose = function () {
        $scope.$copyLink.close();
    }

    $scope.toggleModal11 = function (btnClicked, filepath) {
        $scope.newURL = site_config.siteURL + "/drive/" + $scope.current_type + "/" + $scope.current_instance + "/" + $scope.current_item + "/" + $scope.file_name;
        $scope.buttonClicked = btnClicked;
        $scope.showModal1 = !$scope.showModal1;
    };

    $scope.showModal3 = false;
    $scope.toggleModal3 = function (index) {
        // initial image index
        $scope.modalLoader = true;
        $scope.current_index = index;
        $scope.showModal3 = !$scope.showModal3;
        $scope.showModal1 = false;

        $timeout(function () {
            $scope.modalLoader = false;
        }, 1000);
    };

// if a current image is the same as requested image

    $scope.getActive = function (type, item) {
        if ($scope.current_type == type && $scope.current_item == item) {
            return 'active';
        } else {
            return '';
        }
    };


    $scope.setCurrent = function (type, item, file_path, file_name, index) {
        $scope.top_menu_disable = false;
        $scope.current_type = type;
        $scope.current_item = item;
        $scope.current_index = index;
        $scope.file_path = file_path;
        $scope.file_name = file_name;
        console.log(item);
    };


    $scope.reverse = false;
    $scope.order = function () {
        $scope.reverse = !$scope.reverse;
    };

    $scope.changeSlide = function (index) {
        // $scope.modalLoader = true;
        $(".vidCls, .audCls").each(function () {
            $(this).get(0).pause();
        });

        if ($scope.reverse) {
            var reverse_index = $scope.master_files.length - index - 1;
            var current_element = $scope.master_files[Math.abs(reverse_index)];
        } else {
            var current_element = $scope.master_files[index];
        }
        $scope.top_menu_disable = false;
        $scope.current_type = 'file';
        $scope.current_item = current_element.file_id;
        $scope.current_index = index;
        $scope.file_path = current_element.file_path;
        $scope.file_name = current_element.orgnl_file_name;

        $scope.showModal1 = false;
        $timeout(function () {
            $scope.modalLoader = false;
        }, 1000);
    };

    $scope.download_file = function (fileid)
    {
        window.open(
                site_config.apiUrl + "user/downloadfile?fileid=" + fileid,
                '_blank' // <- This is what makes it open in a new window.
                );
    }
    $scope.create_zip = function () {

        if ($scope.current_item)
        {
//            $http.get(site_config.apiUrl + "user/downloadzip?folder_id=" + $scope.current_item)
//                    .success(function (response) {
//                        console.log(response);
//                $scope.download_file()
//                        window.open(site_config.apiUrl + "user/downloadnow?zip=" + response.data, '_blank');
//                    })
            window.open(
                    site_config.apiUrl + "user/downloadzip?folder_id=" + $scope.current_item,
                    '_blank' // <- This is what makes it open in a new window.
                    );
        }
    }


    $scope.img_remove = function (fileid) {
        var deleteInstance = $window.confirm('Are you absolutely sure you want to delete?');
        if (deleteInstance)
        {
            $http.get(site_config.apiUrl + "user/imagedelete?fileid=" + fileid)
                    .success(function (data) {
                        //$scope.master_files.splice(index, 1);
                        $scope.errorMsg = "Deleted Successfully";
                        if ($scope.parent_id == 0) {
                            $scope.MasterFiles();
                        } else {
                            $scope.GetFolderFiles();
                        }
                    })
        }
    }
    $scope.removeFolder = function (folder_id) {
        var deleteInstance = $window.confirm('Are you absolutely sure you want to delete?');
        if (deleteInstance)
        {
            $http.get(site_config.apiUrl + "user/removefolder?folder_id=" + folder_id)
                    .success(function (data) {
                        $scope.errorMsg = "Deleted Successfully";
                        if ($scope.parent_id == 0) {
                            $scope.MasterFiles();
                        } else {
                            $scope.GetFolderFiles();
                        }
                    })
        }
    }


    $scope.cld_inst_files = function (cinstence_id, instence_name) {

        //document.getElementById('title_head').innerHTML = "<ul id='title_head' class='breadcrumb ng-scope ng-isolate-scope' ncy-breadcrumb='' style='margin: 0 0'><li><i class='fa fa-home'></i><a href='#'>Home</a></li><li class='ng-scope active' ng-switch='$last || !!step.abstract' ng-class='{active: $last}' ng-repeat='step in steps'><span class='ng-binding ng-scope' ng-switch-when='true'>Cloud Instances</span></li><li>" + instence_name + "</li></ul>";

        $scope.fileupload = true;

        $scope.current_instance = cinstence_id;
        console.log(cinstence_id);
        $scope.parent_id = 0;

        $scope.MasterFiles();

    }
    $scope.MasterFiles = function () {
        $scope.top_menu_disable = true;
        $http({
            method: 'POST',
            url: site_config.apiUrl + "user/masterfiles",
            data: {
                user_id: $scope.current_instance,
                master: $scope.master
            },
            headers: {'Content-Type': 'application/json'}
        }).then(function (response) {
            if (response.data.status) {
                $scope.master_files = response.data.data.files;
                $scope.folders = response.data.data.folders;
            } else {
                $location.path("/login");
            }
        });
    }
    $scope.GetFolderFiles = function () {
        $scope.top_menu_disable = true;
        $http({
            method: 'POST',
            url: site_config.apiUrl + "user/getfiles",
            data: {
                folder_id: $scope.parent_id,
                user_id: $scope.current_instance,
                master: $scope.master
            },
            headers: {'Content-Type': 'application/json'}
        }).then(function (response) {
            if (response.data.status) {
                $scope.master_files = response.data.data.files;
                $scope.folders = response.data.data.folders;
            } else {
                $location.path("/login");
            }
        });
    }

    $scope.addFolder = function (filepath) {
        $scope.errorMsg = false;

        $scope.$addNewFolder = $modal.open({
            templateUrl: 'views/addfolder.html',
            controller: 'AddFolderController',
            size: 'sm',
            scope: $scope,
        });
    };

    $scope.get_files = function (folder_id) {
        $scope.parent_id = folder_id;
        $scope.master_files = {};
        $scope.folders = {};
        $scope.GetFolderFiles();
    }

    $scope.reload_all = function () {

        if ($scope.$uploadModalInstance !== undefined)
            $scope.$uploadModalInstance.close();


        if ($scope.parent_id == 0) {
            $scope.MasterFiles();
        } else {
            $scope.GetFolderFiles();
        }
        $(".vidCls, .audCls").each(function (index) {
            $(this).get(0).pause();
        });
    }

    $scope.listall = function () {
        $scope.current_instance = 0;
        $scope.fileupload = false;
        $scope.ListInstance();
    }


});
app.controller('AddFolderController', function ($rootScope, $scope, $http, site_config) {

    $scope.AFPopupClose = function () {
        $scope.$addNewFolder.close();
    }

    $scope.addNewFolder = function () {
        console.log($scope.folder_name);
        $http({
            method: 'POST',
            url: site_config.apiUrl + "user/addfolder",
            data: {
                user_id: $scope.current_instance,
                parent_id: $scope.parent_id,
                folder_name: $scope.folder_name,
                master: $scope.master
            },
            headers: {'Content-Type': 'application/json'}
        }).then(function (response) {
            if (response.data.status) {
                $scope.$addNewFolder.close();
                if ($scope.parent_id == 0) {
                    $scope.MasterFiles();
                } else {
                    $scope.GetFolderFiles();
                }
            } else {
                $scope.errorMsg = response.data.data;
            }
        });
    }

});
app.directive('modal', function () {
    return {
        template: '<div class="modal fade">' +
                '<div class="modal-dialog" style="width:100%;height:100vh;vertical-align=middle">' +
                '<div class="modal-content" style="height:100%;vertical-align=middle">' +
                '<div class="modal-header">' +
                '<button type="button" class="close" data-dismiss="modal" aria-hidden="true" ng-click="reload_all()">&times;</button>' +
                '<h4 class="modal-title" id="modal-title-id">&nbsp;</h4>' +
                '</div>' +
                '<div class="modal-body" ng-transclude></div>' +
                '</div>' +
                '</div>' +
                '</div>',
        restrict: 'E',
        transclude: true,
        replace: true,
        scope: true,
        link: function postLink(scope, element, attrs) {
            scope.$watch(attrs.visible, function (value) {
                if (value == true)
                    $(element).modal('show');
                else
                    $(element).modal('hide');
            });

            $(element).on('shown.bs.modal', function () {
                scope.$apply(function () {
                    scope.$parent[attrs.visible] = true;
                });
            });

            $(element).on('hidden.bs.modal', function () {
                scope.$apply(function () {
                    scope.$parent[attrs.visible] = false;
                });
            });
        }
    };
});


app.directive('ngRightClick', function ($parse) {
    return function (scope, element, attrs) {
        var fn = $parse(attrs.ngRightClick);
        element.bind('contextmenu', function (event) {
            scope.$apply(function () {
                event.preventDefault();
                fn(scope, {$event: event});
            });
        });
    };
});


app.directive('html5FallbackVideo', function () {
    return {
        restrict: 'A', //this means the direct must be declared as an attribute on an element
        replace: false, //don't replace the surrounding element with the template code
        link: function (scope, element, attrs) { // manipulate the DOM in here
            if (!Modernizr.video) { //if html5 video is not supported start flowplayer
                //alert('flash video');
                flowplayer("flash-player", "flowplayer-3.2.16.swf", {
                    clip: {
                        url: scope.mp4Url,
                        autoPlay: false,
                        autoBuffering: true,
                        scaling: "fit"
                    },
                    canvas: {
                        backgroundColor: "#000000",
                        backgroundGradient: "none"
                    }
                });
            }
        },
        scope: {
            webmUrl: '@', //binds property value to the element's attribute
            mp4Url: '@',
            videoWidth: '@',
            videoHeight: '@',
            splashImage: '@'
        },
        templateUrl: 'views/html5-fallback-video.html' //contains the video code
    }
});

app.directive('html5FallbackVideo1', function () {
    return {
        restrict: 'A', //this means the direct must be declared as an attribute on an element
        replace: false, //don't replace the surrounding element with the template code
        link: function (scope, element, attrs) { // manipulate the DOM in here
            if (!Modernizr.video) { //if html5 video is not supported start flowplayer
                //alert('flash video');
                flowplayer("flash-player", "flowplayer-3.2.16.swf", {
                    clip: {
                        url: scope.mp4Url,
                        autoPlay: false,
                        autoBuffering: true,
                        scaling: "fit"
                    },
                    canvas: {
                        backgroundColor: "#000000",
                        backgroundGradient: "none"
                    }
                });
            }
        },
        scope: {
            webmUrl: '@', //binds property value to the element's attribute
            mp4Url: '@',
            videoWidth: '@',
            videoHeight: '@',
            splashImage: '@'
        },
        templateUrl: 'views/html5-fallback-video1.html' //contains the video code
    }
});
app.directive('uiSelectRequired', function () {
    return {
        require: 'ngModel',
        link: function (scope, elm, attrs, ctrl) {
            ctrl.$validators.uiSelectRequired = function (modelValue, viewValue) {

                var determineVal;
                if (angular.isArray(modelValue)) {
                    determineVal = modelValue;
                } else if (angular.isArray(viewValue)) {
                    determineVal = viewValue;
                } else {
                    return false;
                }

                return determineVal.length > 0;
            };
        }
    }
});


app.controller('SendFileController', function ($scope, site_config, $http, $location, $timeout, $rootScope) {
    $scope.SFPopupClose = function () {
        $scope.$sendModalInstance.close();
    }

    $scope.disabled = undefined;
    $scope.searchEnabled = undefined;

    $http.get(site_config.apiUrl + "user/getchilduser?user_id=" + $rootScope.globals.currentUser.user_id + "&role=" + $rootScope.globals.currentUser.user_role).then(function (response)
    {
        if (response.status) {
            $scope.reseller_list = response.data.data;
        }
    });

    $scope.shareFile = {};

    $scope.shareFile.src_id = $scope.current_instance;
    $scope.sendfile = function () {
        $scope.shareFile.type = $scope.current_type;
        $scope.shareFile.item_id = $scope.current_item;
        $http({
            method: 'POST',
            url: site_config.apiUrl + "user/sendfiles",
            data: $scope.shareFile,
            headers: {'Content-Type': 'application/json'}
        }).then(function (response) {

            if (response.data.status) {
                $scope.successMsg = response.data.data;

            } else {
                $scope.errorMsg = response.data.data;
            }
        });
    }


});


app.controller('SendFilesController', function ($scope, site_config, $http, $location, $timeout, $rootScope) {
    $scope.SFPopupClose = function () {
        $scope.$sendModalInstance.close();
    }

    $scope.disabled = undefined;
    $scope.searchEnabled = undefined;

    $http.get(site_config.apiUrl + "user/getchilduser?user_id=" + $rootScope.globals.currentUser.user_id + "&role=" + $rootScope.globals.currentUser.user_role).then(function (response)
    {
        if (response.status) {

            $scope.reseller_list = response.data.data;
        }

    });

    $scope.shareFile = {};
    console.log("dsdsd" + $scope.current_instance);
    $scope.shareFile.src_id = $scope.current_instance;
    $scope.sendfile = function () {
        $scope.shareFile.files_id = $scope.active_items;
        $http({
            method: 'POST',
            url: site_config.apiUrl + "user/sendfiles",
            data: $scope.shareFile,
            headers: {'Content-Type': 'application/json'}
        }).then(function (response) {

            if (response.data.status) {
                $scope.successMsg = response.data.data;

            } else {
                $scope.errorMsg = response.data.data;
            }
        });
    }


});