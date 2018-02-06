'use strict';
app
        // Profile Controller
        .controller('ProfileCtrl', [
            '$rootScope', '$scope', '$http', 'site_config', '$location','Upload', function ($rootScope, $scope, $http, site_config, $location,Upload) {
                // console.log($rootScope.user_permissions)
                var search_par = $location.search()
                if (search_par.tab != '' && search_par.tab != undefined) {
                    $scope.isActive = true;
                } else {
                    $scope.isActive = false;
                }

                var user_id = $rootScope.globals.currentUser.user_id;
                // console.log(user_id)

                // Checking for followers on squibcard page
                // $scope.followuser.follower_id = $rootScope.globals.currentUser.user_id;
                // $scope.followuser.following_id = $scope.user.id;
                // $http({
                //     method: 'POST',
                //     url: site_config.apiUrl + "message/followuser",
                //     data: $scope.followuser,
                //     headers: {'Content-Type': 'application/json'}
                // }).success(function (resp) {
                //     $scope.followTxt = resp.data.follow_status ? 'Unfollow' : 'Follow';
                //     $scope.user.followers = resp.data.followers;
                //     setTimeout(function () {
                //         $rootScope.$apply(function () {
                //             $rootScope.chart_data = 1;
                //         });
                //     }, 100);
                //     //console.log($scope.user.likes);
    
                // }).error(function () {
                //     console.log('Unable to save visitors');
                // });




                // Get existing user details
                $http({
                    method: 'POST',
                    url: site_config.apiUrl + "user/getuser",
                    data: {
                        user_id: user_id
                    },
                    headers: {'Content-Type': 'application/json'}
                }).success(function (resp) {
                    // console.log("Get user" + JSON.stringify(resp))
                    if (resp.status) {
                        console.log(resp.data)
                        $scope.user = resp.data;
                        if($scope.user.role != "admin") {
                            $http.get(site_config.apiUrl + "api/usermodulepermission?user_id=" + $scope.user.parent_id + "&module_id=11").then(function (response)
                            {
                                if (response.data.data !='1') {
                                    $scope.user.url_type = 0;
                                    $scope.user.instance_url = $scope.user.default_url;
                                }
                            });
                        }
                        
                        $scope.user.passwd = "";
                        $scope.user.user_social_network.push({});
//                        $scope.myLatlng = new google.maps.LatLng($scope.user.lat, $scope.user.lng);
//                        $scope.mapOptions = {
//                            zoom: 15,
//                            scrollwheel: false,
//                            center: $scope.myLatlng,
//                            mapTypeId: google.maps.MapTypeId.ROADMAP
//                        }
//                        $scope.map;
                    }
                }).error(function () {
                    $scope.errorMsg = 'Unable to submit form';
                })

                $scope.getMap = function () {
                    $scope.mapLoad = 0;
                    setTimeout(function () {
                        $rootScope.$apply(function () {
                            $scope.mapLoad = 1;
                        });
                    }, 10);
//                    function initialize() {
//                        if ($scope.map == undefined) {
//                            $scope.map = new google.maps.Map(document.getElementById('contact-map'), $scope.mapOptions);
//                        }
//                        $scope.marker = new google.maps.Marker({
//                            position: $scope.myLatlng,
//                            map: $scope.map,
//                            title: 'Map'
//                        });
//                    }
//                    google.maps.event.addDomListener(window, 'click', initialize);
//                    function initialize() {
//                        var myLatlng = new google.maps.LatLng($scope.user.lat, $scope.user.lng);
//                        var mapOptions = {
//                            zoom: 15,
//                            scrollwheel: false,
//                            center: myLatlng,
//                            mapTypeId: google.maps.MapTypeId.ROADMAP
//                        }
//                        var map = new google.maps.Map(document.getElementById('contact-map'), mapOptions);
//                        var marker = new google.maps.Marker({
//                            position: myLatlng,
//                            map: map,
//                            title: 'Map'
//                        });
//                    }
                    //google.maps.event.addDomListener(window, 'click', initialize);
                }

                $scope.add_network = function () {
                    $scope.user.user_social_network.push({});
                };
                $scope.del_network = function (index) {
                    $scope.user.user_social_network.splice(index, 1);
                };
                $scope.maxDate = new Date();
                $scope.today = function () {
                    $scope.user.dob = new Date();
                };
                //$scope.today();

                $scope.clear = function () {
                    $scope.user.dob = null;
                };

                $scope.toggleMin = function () {
                    $scope.minDate = $scope.minDate ? null : new Date();
                };
                $scope.toggleMin();

                $scope.open = function ($event) {
                    $event.preventDefault();
                    $event.stopPropagation();

                    $scope.opened = true;
                };

                $scope.dateOptions = {
                    formatYear: 'yy',
                    startingDay: 1
                };
                $scope.formats = ['dd-MMMM-yyyy', 'yyyy/MM/dd', 'dd.MM.yyyy', 'shortDate', 'MMMM dd yyyy'];
                $scope.format = $scope.formats[4]


                $http.get(site_config.apiUrl + "squibcard/thumbnail").then(function (response)
                {
                    if (response.status) {
                        $scope.thumbnails = response.data.data;
                    }
                });

                $http.get(site_config.apiUrl + "api/socialnetworks").then(function (response)
                {
                    if (response.status) {
                        $scope.social_networks = response.data.data;
                    }
                });

                $http.get(site_config.apiUrl + "api/getcountries").then(function (response)
                {
                    if (response.status) {
                        $scope.countries = response.data.data;
                    }
                });

                $scope.get_vanity_url = function () {
                    if ($rootScope.globals.currentUser.user_role == "admin") {
                        if ($scope.user.role == 'client') {
                            var toSearch = $scope.user.reseller_id;
                            for (var i = 0; i < $scope.reseller_list.length; i++) {
                                if ($scope.reseller_list[i]['id'] == toSearch) {
                                    return $scope.user.reseller_vanity_url = $scope.reseller_list[i]['vanity_domain'];
                                    console.log($scope.user.reseller_vanity_url);
                                }
                            }
                        } else if ($scope.user.role == 'user') {
                            var toSearch = $scope.user.client_id;
                            for (var i = 0; i < $scope.client_list.length; i++) {
                                if ($scope.client_list[i]['id'] == toSearch) {
                                    return $scope.user.client_vanity_url = $scope.client_list[i]['vanity_domain'];
                                    console.log($scope.user.client_vanity_url);
                                }
                            }
                        }

                    } else if ($rootScope.globals.currentUser.user_role == "reseller") {
                        if ($scope.user.role == 'client') {
                            return $scope.user.reseller_vanity_url = $rootScope.globals.currentUser.instance_url;
                        } else {
                            var toSearch = $scope.user.client_id;
                            for (var i = 0; i < $scope.client_list.length; i++) {
                                if ($scope.client_list[i]['id'] == toSearch) {
                                    return $scope.user.client_vanity_url = $scope.client_list[i]['vanity_domain'];
                                    console.log($scope.user.client_vanity_url);
                                }
                            }
                        }
                    } else {
                        return $scope.user.reseller_vanity_url = $rootScope.globals.currentUser.instance_url;
                    }
                }


                $scope.instanceUrl = function () {
			console.log(site_config.instanceURL);
                    if ($scope.user.url_type == 0) {
                        if ($scope.user.instance_name && ($scope.user.role == 'admin' || $scope.user.role == 'reseller'))
                            $scope.user.instance_url = "http://" + $scope.user.instance_name + "." + site_config.instanceURL;
                        else if ($scope.user.instance_name && $scope.user.role == 'client')
                            $scope.user.instance_url = ($scope.get_vanity_url()).replace("http://", "http://" + $scope.user.instance_name + ".");
                        else if ($scope.user.instance_name && $scope.user.role == 'user')
                            $scope.user.instance_url = ($scope.get_vanity_url()).replace("http://", "http://" + $scope.user.instance_name + ".");
                        else
                            $scope.user.instance_url = "";
                    } else if (!$rootScope.user_permissions[8].status) {
                        if ($scope.user.instance_name && ($scope.user.role == 'admin' || $scope.user.role == 'reseller'))
                            $scope.user.default_url = "http://" + $scope.user.instance_name + "." + site_config.instanceURL;
                        else if ($scope.user.instance_name && $scope.user.role == 'client')
                            $scope.user.default_url = ($scope.get_vanity_url()).replace("http://", "http://" + $scope.user.instance_name + ".");
                        else if ($scope.user.instance_name && $scope.user.role == 'user')
                            $scope.user.default_url = ($scope.get_vanity_url()).replace("http://", "http://" + $scope.user.instance_name + ".");
                        else
                            $scope.user.default_url = "";
                    }

                };

                // Update existing user details
                $scope.updateUser = function () {
                    
                    $http({
                        method: 'POST',
                        url: site_config.apiUrl + "user/updateuser",
                        data: $scope.user,
                        headers: {'Content-Type': 'application/json'}
                    }).success(function (resp) {
                        if (resp.status) {
                            $scope.successMsg = resp.data;
                            $scope.errorMsg=false;
                            if ($scope.user.cover) {
                                Upload.upload({
                                    url: site_config.apiUrl + "user/uploadcoverphoto",
                                    data: {file: $scope.user.cover, 'user_id': $scope.user.id},
                                    headers: {'Content-Type': 'multipart/form-data', 'Authorization': 'Basic ' + $rootScope.globals.currentUser.encryption},
                                    withCredentials: true,
                                }).then(function (resp) {
//                                    if (resp.data.status) {
//                                        $scope.successMsg = resp.data.data;
//                                    } else {
//                                        $scope.errorMsg = resp.data.data;
//                                    }
                                   

                                }, function (resp) {
                                    console.log('Error status: ' + resp.status);
                                }, function (evt) {
                                    var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
                                    console.log('progress: ' + progressPercentage + '% ');
                                });
                            }
                        } else {
                            $scope.errorMsg = resp.data;
                        }
                    }).error(function () {
                        $scope.errorMsg = 'Unable to submit form';
                    })
                }

                $http({
                    method: 'POST',
                    url: site_config.apiUrl + "api/getvisitorcount",
                    data: {user_id: $rootScope.globals.currentUser.user_id},
                    dataType: 'json',
                    headers: {'Content-Type': 'application/json'}
                }).success(function (resp) {
                    if(resp.data.length == 0) {
                        $scope.dates = [["1st February 2018",1],["31st January 2018",28],["30th January 2018",4],["29th January 2018",3],["26th January 2018",1],["25th January 2018",5]]
                    } else {
                        $scope.dates = resp.data
                    }
                    $.plot($("#placeholder"),
                            [
                                {
                                    color: $rootScope.settings.color.themeprimary,
                                    label: "Visits",
                                    data: $scope.dates.reverse()
                                },
                            ],
                            {
                                series: {
                                    lines: {
                                        show: true,
                                        fill: true,
                                        fillColor: {colors: [{opacity: 0.2}, {opacity: 0}]}
                                    },
                                    points: {
                                        show: true
                                    }
                                },
                                axisLabels: {
                                    show: true
                                },
                                legend: {
                                    show: false
                                },
                                xaxis: {
                                    tickLength: 0,
                                    color: '#ccc',
                                    mode: "categories",
                                },
                                yaxis: {
                                    min: 0,
                                    tickLength: 0,
                                    color: '#ccc',
                                },
                                grid: {
                                    hoverable: true,
                                    clickable: false,
                                    borderWidth: 0,
                                    aboveData: false,
                                    color: '#fbfbfb'

                                },
                                tooltip: true,
                                tooltipOpts: {
                                    defaultTheme: false,
                                    content: " <b>%s </b>: <span>%y</span>",
                                }
                            }
                    );

                }).error(function () {
                    console.log('Unable to fetch visitors');
                });

            }
        ]);


app.directive('mapDiv', function ($rootScope) {
    return {
        restrict: 'A',
        transclude: true,
        replace: true,
        //scope: true,  
        scope: {
            chartDiv: '='
        },
        link: function (scope, element, attrs) {
            var myLatlng = new google.maps.LatLng(scope.$parent.user.lat, scope.$parent.user.lng);
            var mapOptions = {
                zoom: 15,
                scrollwheel: false,
                center: myLatlng,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            }
            var map = new google.maps.Map(document.getElementById('contact-map'), mapOptions);
            var marker = new google.maps.Marker({
                position: myLatlng,
                map: map,
                title: 'Map'
            });
        }
    };
});