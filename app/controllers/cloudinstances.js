app.controller('CloudCtrl', function ($scope, $http, $location, $window, $modal, $rootScope, $sce, site_config, $timeout, $rootScope) {
    $rootScope.headerActive = 0;
    $scope.$sce = $sce;
    $scope.hide_master = true;
    $scope.fileupload = false;
    $scope.master = 0;

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
                    setTimeout(function () {
                        $rootScope.$apply(function () {
                            $rootScope.headerActive = 1;
                        });
                    }, 100);

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

    $scope.showModal1 = false;
    $scope.toggleModal1 = function () {
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
        console.log(file_name);
    };

    $scope.changeSlide = function (index) {
        // $scope.modalLoader = true;
        $(".vidCls, .audCls").each(function () {
            $(this).get(0).pause();
        });
        if ($scope.reverse) {
            var reverse_index = $scope.cloud_instance_files.length - index - 1;
            var current_element = $scope.cloud_instance_files[Math.abs(reverse_index)];
        } else {
            var current_element = $scope.cloud_instance_files[index];
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



    $scope.download_file = function (fileid)
    {
        window.open(
                site_config.apiUrl + "user/downloadfile?fileid=" + fileid,
                '_blank' // <- This is what makes it open in a new window.
                );
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
    $scope.img_remove = function (fileid) {
        var deleteInstance = $window.confirm('Are you absolutely sure you want to delete?');
        if (deleteInstance)
        {
            $http.get(site_config.apiUrl + "user/imagedelete?fileid=" + fileid)
                    .success(function (data) {
                        //$scope.cloud_instance_files.splice(index, 1);
                        $scope.errorMsg = "Deleted Successfully";
                        if ($scope.parent_id == 0) {
                            $scope.CloudFiles();
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
                            $scope.CloudFiles();
                        } else {
                            $scope.GetFolderFiles();
                        }
                    })
        }
    }



    $scope.reverse = false;
    $scope.order = function () {
        $scope.reverse = !$scope.reverse;
    };

    $scope.cld_inst_files = function (cinstence_id, instence_name) {
        $rootScope.headerActive = 0;
        $scope.hide_master = true;
//        document.getElementById('title_head').innerHTML = "<ul id='title_head' class='breadcrumb ng-scope ng-isolate-scope' ncy-breadcrumb='' style='margin: 0 0'><li><i class='fa fa-home'></i><a href='#'>Home</a></li><li class='ng-scope active' ng-switch='$last || !!step.abstract' ng-class='{active: $last}' ng-repeat='step in steps'><span class='ng-binding ng-scope' ng-switch-when='true'>Cloud Instances</span></li><li>" + instence_name + "</li></ul>";
//        $breadcrumbProvider.setOptions({
//            template: '<ul class="breadcrumb"><li><i class="fa fa-home"></i><a href="#">Home</a></li><li ng-repeat="step in steps" ng-class="{active: $last}" ng-switch="$last || !!step.abstract"><a ng-switch-when="false" href="{{step.ncyBreadcrumbLink}}">{{step.ncyBreadcrumbLabel}}</a><span ng-switch-when="true">{{step.ncyBreadcrumbLabel}}</span></li><li>' + instence_name + '</li></ul>'
//        });
        setTimeout(function () {
            $rootScope.$apply(function () {
                $rootScope.instence_name = instence_name;
                $rootScope.headerActive = 1;
            });
        }, 100);
        $scope.fileupload = true;
        $scope.masterfiles = true;

        $scope.current_instance = cinstence_id;
        $scope.parent_id = 0;

        $scope.CloudFiles();

    }

    $scope.CloudFiles = function () {
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
                $scope.cloud_instance_files = response.data.data.files;
                $scope.folders = response.data.data.folders;
            } else {
                $location.path("/login");
            }
        });
    }
    $scope.GetFolderFiles = function () {
        $scope.hide_master = false;
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
                $scope.cloud_instance_files = response.data.data.files;
                $scope.folders = response.data.data.folders;
            } else {
                $location.path("/login");
            }
        });
    }

    $scope.master_files = function (instance_name) {
        $scope.masterfiles = false;

        //document.getElementById('title_head').innerHTML = "<ul id='title_head' class='breadcrumb ng-scope ng-isolate-scope' ncy-breadcrumb='' style='margin: 0 0'><li><i class='fa fa-home'></i><a href='#'>Home</a></li><li class='ng-scope active' ng-switch='$last || !!step.abstract' ng-class='{active: $last}' ng-repeat='step in steps'><span class='ng-binding ng-scope' ng-switch-when='true'>Cloud Instances</span></li><li>" + instance_name + "</li></ul>";
        $scope.folders = {};
        $http({
            method: 'POST',
            url: site_config.apiUrl + "user/sharedfiles",
            data: {
                cinstence_id: $scope.current_instance
            },
            headers: {'Content-Type': 'application/json'}
        }).then(function (response) {
            if (response.data.status) {
                $scope.cloud_instance_files = response.data.data;
                $scope.hide_master = false;
            } else {
                $location.path("/login");
            }
        });
    }
    $scope.get_files = function (folder_id) {
        $scope.parent_id = folder_id;
        $scope.folders = {};
        $scope.GetFolderFiles();
        console.log("leftclick");
    }

    $scope.reload_all = function () {

        if ($scope.$uploadModalInstance !== undefined)
            $scope.$uploadModalInstance.close();

        $(".vidCls, .audCls").each(function (index) {
            $(this).get(0).pause();
        });

        var cinstence_id = $scope.current_instance;

        if ($scope.parent_id == 0) {
            $scope.CloudFiles();
        } else {
            $scope.GetFolderFiles();
        }
    }

    $scope.listall = function () {
        $scope.hide_master = true;
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
                    $scope.CloudFiles();
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

//
//
app.controller('FilePreviewCtrl', function ($scope, site_config, $http, $location, $timeout) {
//        $scope.UploadPopupClose = function () {
//            $scope.$uploadModalInstance.close();
//        }
    // initial image index
    $scope._Index = 0;
    $scope.showModal3 = !$scope.showModal3;
    $scope.showModal1 = false;
    $scope.modalLoader = true;
    //alert(fileid)

    // Set of Photos
    var jsonString = '{"fileid":1}';

    var obj = JSON.parse(jsonString);
    $http({
        method: 'POST',
        url: site_config.apiUrl + 'api/cloudfilelist',
        data: {
            fileid: obj.fileid
        },
        headers: {'Content-Type': 'application/json'}
    })

            .then(function (response)
            {
                if (response.data.status) {
                    $scope.photos = response.data.data;
                    console.log($scope.photos);
                } else {
                    $location.path("/login");
                }

                $timeout(function () {
                    $scope.modalLoader = false;
                }, 1000);
            }
            );

// if a current image is the same as requested image
    $scope.setActive = function (item) {
        $scope.active_item = item;
    };

});

angular.module('app')
        .directive('headerTitle', [
            '$rootScope', '$timeout', '$http', 'site_config',
            function ($rootScope, $timeout) {
                return {
                    link: function (scope, element) {
                        console.log(scope);

                        var title = 'SquibDrive';
                        var description = 'Cloud Instances';


                        $timeout(function () {
                            if (description == '')
                                element.text(title);
                            else if (typeof ($rootScope.instence_name) != 'undefined') {
                                element.html(title + ' <small> <i class="fa fa-angle-right"> </i> ' + description + ' <i class="fa fa-angle-right"> </i> </small>' + ' <small class="black">  ' + $rootScope.instence_name + ' </small>');
                            }
                            else
                                element.html(title + ' <small> <i class="fa fa-angle-right"> </i> ' + description + ' </small>');
                        }, 100);

                    }
                };
            }
        ]);

