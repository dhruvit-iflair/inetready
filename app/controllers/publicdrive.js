app.controller('DriveCtrl', function ($scope, $http, $location, $window, site_config, $stateParams, $rootScope, deviceDetector, $sce, $modal, GloabalConfig) {
    
    var ec = new evercookie();
    GloabalConfig.site_preferences($stateParams.user_id);
    $scope.user = {};
    $scope.UID = '';
    $scope.$sce = $sce;
  
    if ($stateParams.type && $stateParams.user_id && $stateParams.item_id && $stateParams.item_name) {
        $scope.item_name = $stateParams.item_name;
        $scope.type = $stateParams.type;
        $http({
            method: 'POST',
            url: site_config.apiUrl + "user/getdrive",
            data: $stateParams,
            headers: {'Content-Type': 'application/json'}
        }).success(function (resp) {

            if (resp.status) {
                $scope.drive_files = resp.data;
                $scope.current_index = 0;
                var current_element = $scope.drive_files[0];
                $scope.current_item = current_element.file_id;
            }
        }).error(function () {
            $scope.errorMsg = 'Unable to submit form';
        });

        $scope.share = {};
        $http({
            method: 'POST',
            url: site_config.apiUrl + "user/getprofile",
            data: {
                user_id: $stateParams.user_id,
                visitor_id: (typeof ($rootScope.globals.currentUser) != 'undefined') ? $rootScope.globals.currentUser.user_id : 0
            },
            headers: {'Content-Type': 'application/json'}
        }).success(function (resp) {
            if (resp.status) {
                $scope.user = resp.data;
                $scope.followTxt = resp.data.follow_status ? 'Unfollow' : 'Follow';
                $rootScope.pagetitle = ((resp.data.organization) ? (resp.data.organization + " | ") : "") + resp.data.admin_name;

                $scope.share.url = site_config.siteURL + "/profile/" + $scope.user.id;
                $scope.share.tw_text = ((resp.data.organization) ? (resp.data.organization + " | ") : "") + resp.data.admin_name;

                $rootScope.chart_data = 0;
                setTimeout(function () {
                    $rootScope.$apply(function () {
                        $rootScope.chart_data = 1;
                    });
                    if (document.referrer == '') {
                        ec.get("UID", function (value) {
                            $scope.UID = value;
                            logVisitor(resp.data.id);
                        });
                    }
                }, 1000);


            }
        }).error(function () {
            $scope.errorMsg = 'Unable to submit form';
        });
    }
    $scope.changeSlide = function (index) {
        // $scope.modalLoader = true;
        $(".vidCls, .audCls").each(function () {
            $(this).get(0).pause();
        });
        $scope.current_index = index;

        var current_element = $scope.drive_files[index];

        $scope.current_item = current_element.file_id;
    };

    $scope.download_file = function ()
    {
        window.open(
                site_config.apiUrl + "user/downloadfile?fileid=" + $scope.current_item,
                '_blank' // <- This is what makes it open in a new window.
                );
    }


    /* Device type and os type get */
    var deviceData = deviceDetector;
    $scope.ip_address = myip;

    function logVisitor(adminId) {
        var source = 1;
        if (document.referrer == '') {
            source = 2;
        }
        $http({
            method: 'POST',
            url: site_config.apiUrl + "api/savevisitor",
            data: {
                ip_address: $scope.ip_address,
                user_id: adminId,
                browser: deviceData.browser,
                os: deviceData.os,
                device: (deviceData.device == 'unknown') ? 'desktop' : deviceData.device,
                visitor_id: (typeof ($rootScope.globals.currentUser) != 'undefined') ? $rootScope.globals.currentUser.user_id : 0,
                domain: $scope.user.instance_url,
                campaign_id: 0,
                key_id: 0,
                uid: $scope.UID,
                type: 3,
                source: source
            },
            headers: {'Content-Type': 'application/json'}
        }).success(function (resp) {
            if (resp.status == 1) {
                ec.set("UID", resp.uid);
            }
        }).error(function () {
            console.log('Unable to save visitors');
        });
    }

    $scope.seeLocation = function () {
        $scope.$locModalInstance = $modal.open({
            templateUrl: 'views/user-location.html',
            controller: 'LocController',
            scope: $scope
        });

    };

    $scope.shareEmail = function () {
        $scope.$shareModalInstance = $modal.open({
            templateUrl: 'views/email-share.html',
            controller: 'ShareController',
            scope: $scope
        });

    };
    $scope.check_login = function () {
        if (typeof ($rootScope.globals.currentUser) == 'undefined') {
            $http({
                method: 'GET',
                url: site_config.apiUrl + "user/getcookie?access_token=" + $scope.access_token,
                headers: {'Content-Type': 'application/json'}
            }).success(function (resp) {
                if (resp.status) {
                    $rootScope.globals = JSON.parse(resp.data.cookie);
                } else {
                    $window.location.href = site_config.siteURL + '/#/login?returnUrl=' + $location.absUrl();
                }
            });
        } else {
            $rootScope.returnUrl = $location.absUrl();
            $location.path('/login');
        }
    };
    $scope.login_register = function () {
        if (typeof ($rootScope.globals.currentUser) == 'undefined') {
            $http({
                method: 'GET',
                url: site_config.apiUrl + "user/getcookie?access_token=" + $scope.access_token,
                headers: {'Content-Type': 'application/json'}
            }).success(function (resp) {
                if (resp.status) {
                    $rootScope.globals = JSON.parse(resp.data.cookie);
                } else {

                    $scope.$loginRegisterInstance = $modal.open({
                        templateUrl: 'views/login-register.html',
                        //controller: 'LocController',
                        scope: $scope
                    });
                }
            });
        } else {
            $scope.$loginRegisterInstance = $modal.open({
                templateUrl: 'views/login-register.html',
                //controller: 'LocController',
                scope: $scope
            });
        }
    };
    $scope.login_register_close = function () {
        $scope.$loginRegisterInstance.close();
    };

    $scope.likeDislike = function (like) {
        // console.log($rootScope.globals);
        $scope.userlike = {};
        if (typeof ($rootScope.globals.currentUser) != 'undefined') {
            $rootScope.chart_data = 0;
            $scope.userlike.user_id = $rootScope.globals.currentUser.user_id;

            $scope.userlike.profile_id = $scope.user.id;
            $scope.userlike.like = like;
            console.log($scope.userlike);
            $http({
                method: 'POST',
                url: site_config.apiUrl + "message/userlikes",
                data: $scope.userlike,
                headers: {'Content-Type': 'application/json'}
            }).success(function (resp) {
                // console.log(resp.data);
                $scope.user.likes = resp.data.likes;
                $scope.user.dislikes = resp.data.dislikes;
                setTimeout(function () {
                    $rootScope.$apply(function () {
                        $rootScope.chart_data = 1;
                    });
                }, 100);
            }).error(function () {
                console.log('Unable to save visitors');
            });
        } else {
            $scope.login_register();
        }

    };

    $scope.addComment = function () {

        if (typeof ($rootScope.globals.currentUser) != 'undefined') {
            $scope.usercomment.user_id = $rootScope.globals.currentUser.user_id;

            $scope.usercomment.profile_id = $scope.user.id;
            console.log($scope.usercomment);
            $http({
                method: 'POST',
                url: site_config.apiUrl + "message/postcomment",
                data: $scope.usercomment,
                headers: {'Content-Type': 'application/json'}
            }).success(function (resp) {
                // console.log(resp.data);
                $scope.user.comments = resp.data.comments;
                $scope.usercomment.comment = "";
            }).error(function () {
                console.log('Unable to save visitors');
            });
        } else {
            $scope.login_register();
        }

    };

    $scope.influenceMe = function () {

        $scope.$followModalInstance = $modal.open({
            templateUrl: 'views/follow-user.html',
            //controller: 'LocController',
            size: 'sm',
            scope: $scope
        });

    };
    $scope.follow = function () {


        $scope.followuser = {};
        if (typeof ($rootScope.globals.currentUser) != 'undefined') {
            $rootScope.chart_data = 0;
            $scope.followuser.follower_id = $rootScope.globals.currentUser.user_id;
            $scope.followuser.following_id = $scope.user.id;
            $http({
                method: 'POST',
                url: site_config.apiUrl + "message/followuser",
                data: $scope.followuser,
                headers: {'Content-Type': 'application/json'}
            }).success(function (resp) {
                $scope.followTxt = resp.data.follow_status ? 'Unfollow' : 'Follow';
                $scope.user.followers = resp.data.followers;
                setTimeout(function () {
                    $rootScope.$apply(function () {
                        $rootScope.chart_data = 1;
                    });
                }, 100);
                //console.log($scope.user.likes);

            }).error(function () {
                console.log('Unable to save visitors');
            });
        } else {
            $scope.login_register();
        }

    };

    $scope.FollowPopupClose = function () {
        $scope.$followModalInstance.close();
    }

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


app.directive('chartDiv', function ($rootScope) {
    return {
        restrict: 'A',
        transclude: true,
        replace: true,
        //scope: true,  
        scope: {
            chartDiv: '='
        },
        link: function (scope, element, attrs) {
            Morris.Donut({
                element: 'donut-chart',
                data: [
                    {label: 'Likes', value: scope.$parent.user.likes},
                    {label: 'Dislikes', value: scope.$parent.user.dislikes},
                    {label: 'Followers', value: scope.$parent.user.followers},
                    {label: 'Views', value: scope.$parent.user.visits}
                ],
                colors: [scope.$parent.settings.color.themeprimary, scope.$parent.settings.color.themesecondary, scope.$parent.settings.color.themethirdcolor, scope.$parent.settings.color.themefourthcolor],
                formatter: function (y) {
                    return y;
                },
                resize: true
            });


        }
    };
});
app.controller('LocController', function ($scope, $http, $location, subdomain, site_config, $stateParams, $rootScope, deviceDetector, $sce, $modal) {


    var protocol = $location.protocol();
    var host = $location.host();
//    var absUrl = $location.absUrl();

    var instance_url = protocol + "://" + host;
    $scope.user = {};
    $http({
        method: 'POST',
        url: site_config.apiUrl + "user/getprofile",
        data: {
            user_id: $stateParams.user_id,
            visitor_id: (typeof ($rootScope.globals.currentUser) != 'undefined') ? $rootScope.globals.currentUser.user_id : 0
        },
        headers: {'Content-Type': 'application/json'}
    }).success(function (resp) {
        if (resp.status) {
            $scope.user = resp.data;
            $rootScope.pagetitle = ((resp.data.organization) ? (resp.data.organization + " | ") : "") + resp.data.admin_name;

            $scope.mapConfig = {
                idSelector: 'map-canvas',
                markerLocation: 'lib/jquery/img/marker.png'
            }
            $scope.myMap.init($scope.mapConfig);
        }
    }).error(function () {
        $scope.errorMsg = 'Unable to submit form';
    });


    $scope.myMap = function () {

        var options = {
            zoom: 1,
            center: new google.maps.LatLng(38.810821, -95.053711),
            //mapTypeId: google.maps.MapTypeId.ROADMAP,
            styles:
                    [{"featureType": "administrative", "elementType": "labels.text.fill", "stylers": [{"color": "#444444"}]}, {"featureType": "landscape", "elementType": "all", "stylers": [{"color": "#f2f2f2"}]}, {"featureType": "poi", "elementType": "all", "stylers": [{"visibility": "off"}]}, {"featureType": "road", "elementType": "all", "stylers": [{"saturation": -100}, {"lightness": 45}]}, {"featureType": "road.highway", "elementType": "all", "stylers": [{"visibility": "simplified"}]}, {"featureType": "road.arterial", "elementType": "labels.icon", "stylers": [{"visibility": "off"}]}, {"featureType": "transit", "elementType": "all", "stylers": [{"visibility": "off"}]}, {"featureType": "water", "elementType": "all", "stylers": [{"color": colors['primary-color']}, {"visibility": "on"}]}]
        }

        /*
         Load the map then markers
         @param object settings (configuration options for map)
         @return undefined
         */
        function init(settings, map, markerLocation) {
            map = new google.maps.Map(document.getElementById(settings.idSelector), options);
            markerLocation = settings.markerLocation;
            var markers = {};
            var markerList = [];
            loadMarkers($scope.user, markers, markerList, markerLocation, map);
            //console.log($scope.user);
        }

        /*
         =======
         MARKERS
         =======
         */
        var markers = {};
        var markerList = [];

        /*
         Load markers onto the Google Map from a provided array or demo personData (data.js)
         @param array personList [optional] (list of people to load)
         @return undefined
         */
        function loadMarkers(person, markers, markerList, markerLocation, map) {

            var j = 1; // for lorempixel
            var bounds = new google.maps.LatLngBounds();
            var lat = person.lat,
                    lng = person.lng,
                    markerId = person.id;

            var infoWindow = new google.maps.InfoWindow({
                maxWidth: 400
            });
//var bounds = new google.maps.LatLngBounds(); 
            var marker = new google.maps.Marker({
                position: new google.maps.LatLng(lat, lng),
                title: 'test',
                markerId: markerId,
                icon: markerLocation,
                map: map
            });

            markers[markerId] = marker;
            markerList.push(person.id);

            if (person.profile_image) {
                var pr_image = person.profile_image;
            } else {
                var pr_image = 'assets/img/avatars/default_user.png';
            }

            if (j > 10)
                j = 1; // for lorempixel, the thumbnail image
            var content = ['<div class="map-box"><img width="90" height="90" src="', pr_image, '">', '<div class="iw-text"><h4 class="margin-none">', person.admin_name, '</h4>Organization: ', person.organization, ' <br>Address: ', person.address, '<br>City: ', person.city, '<br>State: ', person.state, '</div></div>'].join('');

            j++; // lorempixel

            google.maps.event.addListener(marker, 'click', (function (marker, content) {
                return function () {
                    infoWindow.setContent(content);
                    infoWindow.open(map, marker);
                }
            })(marker, content));
            bounds.extend(marker.position);
            map.fitBounds(bounds);
            //now fit the map to the newly inclusive bounds
            //map.fitBounds(bounds);
        }


        return {
            init: init,
            loadMarkers: loadMarkers
        };
    }();

    $scope.sendMessage = function () {
        $scope.message.to_user_id = $scope.user.id;
        $http({
            method: 'POST',
            url: site_config.apiUrl + "message/sendmessage",
            data: $scope.message,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        }).success(function (resp) {
            if (resp.status) {
                $scope.successMsg = resp.data;

            } else {
                $scope.errorMsg = resp.data;
            }
        })
    }


    $scope.ULPopupClose = function () {
        $scope.$locModalInstance.close();
    }



});
app.controller('ShareController', function ($scope, $http, site_config, $stateParams, $rootScope, deviceDetector, $sce, $modal) {
    $scope.shareData = {
        sender_email: (typeof ($rootScope.globals.currentUser) != 'undefined') ? $rootScope.globals.currentUser.email : "",
        sender_name: (typeof ($rootScope.globals.currentUser) != 'undefined') ? $rootScope.globals.currentUser.username : "",
        to_user_id: $scope.user.id,
        subject: 'Recommeded',
        message: "Recommended...\n\n" + site_config.appName + " \nCheckout the SquibCard on the following URL.\n\n" + $scope.user.instance_url
    };
    $scope.shareNow = function () {

        $http({
            method: 'POST',
            url: site_config.apiUrl + "message/share",
            data: $scope.shareData,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        }).success(function (resp) {
            if (resp.status) {
                $scope.successMsg = resp.data;

            } else {
                $scope.errorMsg = resp.data;
            }
        })
    }


    $scope.SHPopupClose = function () {
        $scope.$shareModalInstance.close();
    }
});
