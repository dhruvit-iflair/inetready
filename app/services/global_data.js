'use strict';

angular.module('GlobalData')

        .factory('GloabalConfig', ['$http', '$rootScope', 'site_config', '$location', '$sce',
            function ($http, $rootScope, site_config, $location, $sce) {
                return {
                    user_permissions: function () {
                        $http.get(site_config.apiUrl + "api/userpermissions?role=" + $rootScope.globals.currentUser.user_role + "&user_id=" + $rootScope.globals.currentUser.user_id)
                                .then(function (response)
                                {
                                    if (response.data.status) {
                                        $rootScope.user_permissions = response.data.data;

                                        if (($location.path() == '/app/masterfiles' || $location.path() == '/app/cloudinstances') && !$rootScope.user_permissions[2].status) {
                                            $location.path("/app/dashboard");
                                        }
                                        if ($location.path() == '/app/squibcards' && !$rootScope.user_permissions[4].status) {
                                            $location.path("/app/dashboard");
                                        }
                                        if ($location.path() == '/app/modulepermissions' && !$rootScope.user_permissions[9].status) {
                                            $location.path("/app/dashboard");
                                        }
                                        if ($location.path() == '/app/nameserverconfig' && !$rootScope.user_permissions[10].status) {
                                            $location.path("/app/dashboard");
                                        }

                                    }
                                });
                    },
                    site_preferences: function (user_id) {
                        $http.get(site_config.apiUrl + "api/sitepreferences?user_id=" + user_id)
                                .then(function (response)
                                {
                                    if (response.data.status) {
                                        $rootScope.site_preferences = response.data.data;
                                    }
                                });
                    },
                    get_access: function () {
                        $rootScope.iframe_data = 0;
                        $http({
                            method: 'GET',
                            url: site_config.apiUrl + "user/getaccesstoken",
                            headers: {'Content-Type': 'application/json'}
                        }).success(function (resp) {
                            if (resp.status) {
                                $rootScope.access_token=resp.data;
                                $rootScope.iframeURL = $sce.trustAsResourceUrl(site_config.siteURL + "/" + site_config.apiUrl + "/user/getmaincookie?access_token=" + $rootScope.access_token);
                                $rootScope.iframe_data = 1;

//                                setTimeout(function () {
//                                    $rootScope.$apply(function () {
//                                        $http({
//                                            method: 'GET',
//                                            url: site_config.apiUrl + "user/getcookie?access_token=" + resp.data,
//                                            headers: {'Content-Type': 'application/json'}
//                                        }).success(function (resp) {
//                                            if (resp.status) {
//                                                $rootScope.globals = JSON.parse(resp.data.cookie);
//                                            } else {
//                                                $rootScope.globals = {};
//                                            }
//
//                                        });
//                                    });
//                                }, 1000);
                            } else {
                                $rootScope.globals = JSON.parse(resp.data.cookie);
                            }

                        });
                    }
                };
            }]);