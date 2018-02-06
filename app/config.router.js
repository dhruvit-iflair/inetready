'use strict';
angular.module('app')
        .run(
                [
                    '$rootScope', '$state', '$stateParams', '$cookieStore', '$http', '$location', 'GloabalConfig', '$sce', 'ipCookie', '$window',
                    function ($rootScope, $state, $stateParams, $cookieStore, $http, $location, GloabalConfig, $sce, ipCookie, $window) {
                        $rootScope.headerActive = 1;
                        // keep user logged in after page refresh
                        $rootScope.globals = $cookieStore.get('globals') || {};
                        //$rootScope.globals = ipCookie('globals') || {};
                        
                        if ($rootScope.globals.currentUser) {
                            $http.defaults.headers.common['Authorization'] = 'Basic ' + $rootScope.globals.currentUser.encryption; // jshint ignore:line
                        }


                        $rootScope.$on('$stateChangeStart', function (event, toState, toParams, fromState, fromParams) {

                            var path = $location.path().substr(1).split("/", 2);
                            if (path[0] !== '' && path[0] !== 'profile' && path[0] !== 'login' && path[0] !== 'signup' && path[0] !== 'forgotPassword' && path[0] !== 'resetPassword' && path[0] !== 'confirmation' && path[0] !== 'campaign' && path[0] !== 'drive' && path[0] !== 'squibkeys' && !$rootScope.globals.currentUser) {
                                $location.path('/login');
                            } else if ($rootScope.globals.currentUser) {

                                GloabalConfig.user_permissions();
                                GloabalConfig.site_preferences($rootScope.globals.currentUser.user_id);
                                if ((path[1] == 'manageusers' || path[1] == 'adduser' || path[1] == 'edituser' || path[1] == 'globalsettings' || path[1] == 'modulepermissions' || path[1] == 'nameserverconfig') && $rootScope.globals.currentUser.user_role == "user") {
                                    $location.path("/app/profile");
                                }
                                if (path[1] == 'globalsettings' && $rootScope.globals.currentUser.user_role != "admin") {
                                    $location.path("/app/profile");
                                }
                            }
                        });
                       // GloabalConfig.get_access();
                        $rootScope.$state = $state;
                        $rootScope.$stateParams = $stateParams;
                        $rootScope.convertHtml = function (data) {
                            return $sce.trustAsHtml(data);
                        };

                       
                    }
                ]
                )
        .config(
                [
                    '$stateProvider', '$urlRouterProvider', '$locationProvider',
                    function ($stateProvider, $urlRouterProvider, $locationProvider) {
                        $locationProvider.html5Mode(true);
                        //////////////////edited by arun
                        $urlRouterProvider.otherwise('/');
                        ////////////////////////////////               

                        
                        $stateProvider
                                .state('home', {
                                    url: '/',
                                    templateUrl: 'views/home.html',
//                                    ncyBreadcrumb: {
//                                        label: 'Reseller SQuibCard'
//                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load({
                                                    serie: true,
                                                    files: [
                                                        'lib/jquery/vendor/tether.min.js',
                                                        'lib/jquery/colors.js',
                                                        'lib/jquery/charts/morris/raphael-2.0.2.min.js',
                                                        'lib/jquery/charts/morris/morris.js',
                                                        'app/controllers/home.js',
                                                        'assets/css/style-demo.css',
                                                    ]
                                                });
                                            }
                                        ]
                                    }
                                })
                                .state('generateKeys', {
                                    url: '/squibkeys/:campaign_name',
                                    templateUrl: 'views/generate_keys.html',
                                    ncyBreadcrumb: {
                                        label: 'Generate Key',
                                        description: 'Generate Key'
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load({
                                                    serie: true,
                                                    files: [
                                                        'app/controllers/campaign.js',
                                                    ]
                                                });
                                            }
                                        ]
                                    }
                                })
                                .state('app', {
                                    abstract: true,
                                    url: '/app',
                                    templateUrl: 'views/layout.html'
                                })
                                .state('app.dashboard', {
                                    url: '/dashboard',
                                    templateUrl: 'views/dashboard.html',
                                    ncyBreadcrumb: {
                                        label: 'Dashboard',
                                        description: ''
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load(['vr.directives.slider']).then(
                                                        function () {
                                                            return $ocLazyLoad.load(
                                                                    {
                                                                        serie: true,
                                                                        files: [
                                                                            'lib/jquery/charts/sparkline/jquery.sparkline.js',
                                                                            'lib/jquery/charts/easypiechart/jquery.easypiechart.js',
                                                                            'lib/jquery/charts/flot/jquery.flot.js',
                                                                            'lib/jquery/charts/flot/jquery.flot.resize.js',
                                                                            'lib/jquery/charts/flot/jquery.flot.pie.js',
                                                                            'lib/jquery/charts/flot/jquery.flot.tooltip.js',
                                                                            'lib/jquery/charts/flot/jquery.flot.orderBars.js',
                                                                            'lib/jquery/charts/flot/jquery.flot.categories.js',
                                                                            'lib/jquery/charts/morris/raphael-2.0.2.min.js',
                                                                            'lib/jquery/charts/morris/morris.js',
                                                                            'app/controllers/dashboard.js',
                                                                            'app/directives/realtimechart.js',
                                                                            'assets/css/style-demo.css',
                                                                            'assets/css/rzslider.min.css',
                                                                        ]
                                                                    });
                                                        }
                                                );
                                            }

                                        ]
                                    }
                                })
                                .state('persian', {
                                    abstract: true,
                                    url: '/persian',
                                    templateUrl: 'views/layout-persian.html'
                                })
                                .state('persian.dashboard', {
                                    url: '/dashboard',
                                    templateUrl: 'views/dashboard-persian.html',
                                    ncyBreadcrumb: {
                                        label: 'Ø¯Ø§Ø´Ø¨ÙˆØ±Ø¯'
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load({
                                                    serie: true,
                                                    files: [
                                                        'lib/jquery/charts/sparkline/jquery.sparkline.js',
                                                        'lib/jquery/charts/easypiechart/jquery.easypiechart.js',
                                                        'lib/jquery/charts/flot/jquery.flot.js',
                                                        'lib/jquery/charts/flot/jquery.flot.resize.js',
                                                        'lib/jquery/charts/flot/jquery.flot.pie.js',
                                                        'lib/jquery/charts/flot/jquery.flot.tooltip.js',
                                                        'lib/jquery/charts/flot/jquery.flot.orderBars.js',
                                                        'app/controllers/dashboard.js',
                                                        'app/directives/realtimechart.js'
                                                    ]
                                                });
                                            }
                                        ]
                                    }
                                })
                                .state('arabic', {
                                    abstract: true,
                                    url: '/arabic',
                                    templateUrl: 'views/layout-arabic.html'
                                })
                                .state('arabic.dashboard', {
                                    url: '/dashboard',
                                    templateUrl: 'views/dashboard-arabic.html',
                                    ncyBreadcrumb: {
                                        label: 'Ù„ÙˆØ­Ø© Ø§Ù„Ù‚ÙŠØ§Ø¯Ø©'
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load({
                                                    serie: true,
                                                    files: [
                                                        'lib/jquery/charts/sparkline/jquery.sparkline.js',
                                                        'lib/jquery/charts/easypiechart/jquery.easypiechart.js',
                                                        'lib/jquery/charts/flot/jquery.flot.js',
                                                        'lib/jquery/charts/flot/jquery.flot.resize.js',
                                                        'lib/jquery/charts/flot/jquery.flot.pie.js',
                                                        'lib/jquery/charts/flot/jquery.flot.tooltip.js',
                                                        'lib/jquery/charts/flot/jquery.flot.orderBars.js',
                                                        'app/controllers/dashboard.js',
                                                        'app/directives/realtimechart.js'
                                                    ]
                                                });
                                            }
                                        ]
                                    }
                                })
                                .state('app.usermanagementdetails', {
                                    url: '/manageusers',
                                    templateUrl: 'views/users-list.html',
                                    ncyBreadcrumb: {
                                        label: 'User Management',
                                        description: 'User Management Details'
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load({
                                                    serie: true,
                                                    files: [
                                                        'app/controllers/manageusers.js',
                                                        'app/controllers/nggrid.js',
                                                        'lib/jquery/datatable/dataTables.bootstrap.css',
                                                        'lib/jquery/datatable/jquery.dataTables.min.js',
                                                        'lib/jquery/datatable/ZeroClipboard.js',
                                                        'lib/jquery/datatable/dataTables.tableTools.min.js',
                                                        'lib/jquery/datatable/dataTables.bootstrap.min.js',
                                                        'app/controllers/datatable.js'
                                                    ]
                                                });
                                            }
                                        ]
                                    }
                                })
                                .state('app.adduserdetails', {
                                    url: '/adduser',
                                    templateUrl: 'views/add-user.html',
                                    ncyBreadcrumb: {
                                        label: 'User Management',
                                        description: 'Add User Details'
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load(['ui.mask']).then(
                                                        function () {
                                                            return $ocLazyLoad.load(
                                                                    {
                                                                        serie: true,
                                                                        files: [
                                                                            'lib/jquery/inputmask/mask.min.js',
                                                                            'lib/jquery/textarea/jquery.autosize.js',
                                                                            'app/controllers/manageusers.js'
                                                                        ]
                                                                    });
                                                        }
                                                );
                                            }
                                        ]
                                    }
                                })
                                .state('app.edituserdetails', {
                                    url: '/edituser/:user_id',
                                    templateUrl: 'views/edit-user.html',
                                    ncyBreadcrumb: {
                                        label: 'User Management',
                                        description: 'Edit User Details'
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load(['ui.mask']).then(
                                                        function () {
                                                            return $ocLazyLoad.load(
                                                                    {
                                                                        serie: true,
                                                                        files: [
                                                                            'lib/jquery/inputmask/mask.min.js',
                                                                            'lib/jquery/textarea/jquery.autosize.js',
                                                                            'app/controllers/manageusers.js'
                                                                        ]
                                                                    });
                                                        }
                                                );
                                            }

                                        ]
                                    }
                                })
                                .state('app.instancemanagement', {
                                    url: '/manageinstances',
                                    templateUrl: 'views/instance-list.html',
                                    ncyBreadcrumb: {
                                        label: 'Manage Instances',
                                        description: 'Instance Details'
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load({
                                                    serie: true,
                                                    files: [
                                                        'app/controllers/datepicker.js',
                                                        'app/controllers/manageinstance.js'
                                                    ]
                                                });
                                            }
                                        ]
                                    }
                                })
                                .state('app.cloudinstances', {
                                    url: '/cloudinstances',
                                    templateUrl: 'views/cloud-instances.html',
                                    ncyBreadcrumb: {
                                        label: 'SquibDrive',
                                        description: 'Cloud Instances'
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load({
                                                    serie: true,
                                                    files: [
                                                        'app/controllers/cloudinstances.js',
                                                        'app/controllers/fileuploadctrl.js'
                                                    ]
                                                });
                                            }
                                        ]
                                    }
                                })

                                .state('app.masterfiles', {
                                    url: '/masterfiles',
                                    templateUrl: 'views/master-files.html',
                                    ncyBreadcrumb: {
                                        label: 'SquibDrive',
                                        description: 'Master Instances'
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load(['ui.select']).then(
                                                        function () {
                                                            return $ocLazyLoad.load(
                                                                    {
                                                                        serie: true,
                                                                        files: [
                                                                            'app/controllers/fileuploadctrl.js',
                                                                            'app/controllers/masterfiles.js',
                                                                            'app/controllers/select2.js',
                                                                        ]
                                                                    });
                                                        }
                                                );
                                            }
                                        ]
                                    },
                                })
                                .state('app.datatables', {
                                    url: '/datatables',
                                    templateUrl: 'views/tables-data.html',
                                    ncyBreadcrumb: {
                                        label: 'Datatables',
                                        description: 'jquery plugin for data management'
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load(['ngGrid']).then(
                                                        function () {
                                                            return $ocLazyLoad.load(
                                                                    {
                                                                        serie: true,
                                                                        files: [
                                                                            'app/controllers/nggrid.js',
                                                                            'lib/jquery/datatable/dataTables.bootstrap.css',
                                                                            'lib/jquery/datatable/jquery.dataTables.min.js',
                                                                            'lib/jquery/datatable/ZeroClipboard.js',
                                                                            'lib/jquery/datatable/dataTables.tableTools.min.js',
                                                                            'lib/jquery/datatable/dataTables.bootstrap.min.js',
                                                                            'app/controllers/datatable.js'
                                                                        ]
                                                                    });
                                                        }
                                                );
                                            }
                                        ]
                                    }

                                })
                                .state('app.profile', {
                                    url: '/profile',
                                    templateUrl: 'views/profile.html',
                                    ncyBreadcrumb: {
                                        label: 'User Profile'
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load(['ui.mask']).then(
                                                        function () {
                                                            return $ocLazyLoad.load(
                                                                    {
                                                                        serie: true,
                                                                        files: [
                                                                            'lib/jquery/inputmask/mask.min.js',
                                                                            'lib/jquery/charts/flot/jquery.flot.js',
                                                                            'lib/jquery/charts/flot/jquery.flot.tooltip.js',
                                                                            'lib/jquery/charts/flot/jquery.flot.categories.js',
                                                                            'lib/jquery/textarea/jquery.autosize.js',
                                                                            'app/controllers/profile.js'
                                                                        ]
                                                                    });
                                                        }
                                                );
                                            }

                                        ]
                                    }
                                })
                                .state('app.modulepermissions', {
                                    url: '/modulepermissions',
                                    templateUrl: 'views/module-permissions.html',
                                    ncyBreadcrumb: {
                                        label: 'Admin Settings',
                                        description: 'User Permissions'
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load({
                                                    serie: true,
                                                    files: [
                                                        'app/controllers/modulepermissions.js'
                                                    ]
                                                });
                                            }
                                        ]
                                    }
                                })
                                .state('app.privatebranding', {
                                    url: '/privatebranding',
                                    templateUrl: 'views/private-branding.html',
                                    ncyBreadcrumb: {
                                        label: 'Admin Settings',
                                        description: 'Private Branding'
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load({
                                                    serie: true,
                                                    files: [
                                                        'app/controllers/privatebranding.js'
                                                    ]
                                                });
                                            }
                                        ]
                                    }
                                })
                                .state('app.squibcards', {
                                    url: '/squibcards',
                                    templateUrl: 'views/squibcards.html',
                                    ncyBreadcrumb: {
                                        label: 'SQuibCard',
                                        description: 'SQuibCard Template Library'
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load({
                                                    serie: true,
                                                    files: [
                                                        'lib/jquery/vendor/tether.min.js',
                                                        'lib/jquery/colors.js',
                                                        'lib/jquery/charts/morris/raphael-2.0.2.min.js',
                                                        'lib/jquery/charts/morris/morris.js',
                                                        'app/controllers/squibcard.js',
                                                        'assets/css/style-demo.css',
                                                    ]
                                                });
                                            }
                                        ]
                                    }
                                })
                                .state('app.morris', {
                                    url: '/morris',
                                    templateUrl: 'views/morris.html',
                                    ncyBreadcrumb: {
                                        label: 'Morris Charts',
                                        description: 'simple & flat charts'
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load({
                                                    serie: true,
                                                    files: [
                                                        'lib/jquery/charts/morris/raphael-2.0.2.min.js',
                                                        'lib/jquery/charts/morris/morris.js',
                                                        'app/controllers/morris.js'
                                                    ]
                                                });
                                            }
                                        ]
                                    }
                                })
                                .state('app.globalsettings', {
                                    url: '/globalsettings',
                                    templateUrl: 'views/global-settings.html',
                                    ncyBreadcrumb: {
                                        label: 'Admin Settings',
                                        description: 'Global Permissions'
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load({
                                                    serie: true,
                                                    files: [
                                                        'app/controllers/gloabalsettings.js'
                                                    ]
                                                });
                                            }
                                        ]
                                    }
                                })
                                .state('app.nameserverconfig', {
                                    url: '/nameserverconfig',
                                    templateUrl: 'views/ns-config.html',
                                    ncyBreadcrumb: {
                                        label: 'Admin Settings',
                                        description: 'Name Server Configuration'
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load({
                                                    serie: true,
                                                    files: [
                                                        'app/controllers/nsconfig.js'
                                                    ]
                                                });
                                            }
                                        ]
                                    }
                                })
                                .state('app.messagenotification', {
                                    url: '/messagenotification',
                                    templateUrl: 'views/message-notification.html',
                                    ncyBreadcrumb: {
                                        label: 'Tables',
                                        description: 'simple and responsive tables'
                                    }
                                })
                                .state('app.folderlist', {
                                    url: '/cloud-storage',
                                    templateUrl: 'views/folder-list.html',
                                    ncyBreadcrumb: {
                                        label: 'Cloud Storage',
                                        description: 'Root'
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load({
                                                    serie: true,
                                                    files: [
                                                        'lib/jquery/charts/flot/jquery.flot.js',
                                                        'lib/jquery/charts/flot/jquery.flot.resize.js',
                                                        'app/controllers/profile.js'
                                                    ]
                                                });
                                            }
                                        ]
                                    }
                                })
                                .state('app.cloudsettings', {
                                    url: '/cloudsettings',
                                    templateUrl: 'views/cloud-settings.html',
                                    ncyBreadcrumb: {
                                        label: 'Cloud Settings',
                                        description: 'Cloud Manage'
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load({
                                                    serie: true,
                                                    files: [
                                                        'app/controllers/cloud-settings.js'
                                                    ]
                                                });
                                            }
                                        ]
                                    }
                                })
                                .state('usersquibcard', {
                                    url: '/profile/:user_id',
                                    templateUrl: 'views/home.html',
                                    ncyBreadcrumb: {
                                        label: 'User SquibCard'
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load({
                                                    serie: true,
                                                    files: [
                                                        //'lib/jquery/vendor/tether.min.js',
                                                        //'lib/jquery/colors.js',
                                                        //'lib/jquery/charts/morris/raphael-2.0.2.min.js',
                                                        //'lib/jquery/charts/morris/morris.js',
                                                        'app/controllers/userprofile.js',
                                                                //'assets/css/style-demo.css',
                                                    ]
                                                });
                                            }
                                        ]
                                    }
                                })
                                .state('publicdrive', {
                                    url: '/drive/:type/:user_id/:item_id/:item_name',
                                    templateUrl: 'views/drive-view.html',
                                    ncyBreadcrumb: {
                                        label: 'User SquibDrive'
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load({
                                                    serie: true,
                                                    files: [
                                                        'lib/jquery/vendor/tether.min.js',
                                                        'lib/jquery/colors.js',
                                                        'lib/jquery/charts/morris/raphael-2.0.2.min.js',
                                                        'lib/jquery/charts/morris/morris.js',
                                                        'app/controllers/publicdrive.js',
                                                        'assets/css/style-demo.css',
                                                    ]
                                                });
                                            }
                                        ]
                                    }
                                })

                                .state('login', {
                                    url: '/login',
                                    templateUrl: 'views/login.html',
                                    ncyBreadcrumb: {
                                        label: 'Login'
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load({
                                                    serie: true,
                                                    files: [
                                                        'app/controllers/adminlogin.js'
                                                    ]
                                                });
                                            }
                                        ]
                                    }
                                })
                                .state('signup', {
                                    url: '/signup/:user_id',
                                    templateUrl: 'views/signup.html',
                                    ncyBreadcrumb: {
                                        label: 'Sign Up'
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load({
                                                    serie: true,
                                                    files: [
                                                        'app/controllers/signup.js',
                                                        'assets/css/style-demo.css',
                                                    ]
                                                });
                                            }
                                        ]
                                    }
                                })
                                .state('forgotPassword', {
                                    url: '/forgotPassword',
                                    templateUrl: 'views/forgotpassword.html',
                                    ncyBreadcrumb: {
                                        label: 'Forgot Password'
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load({
                                                    serie: true,
                                                    files: [
                                                        'app/controllers/adminlogin.js'
                                                    ]
                                                });
                                            }
                                        ]
                                    }
                                })
                                .state('resetPassword', {
                                    url: '/resetPassword/:par1/:par2/:par3/:par4',
                                    templateUrl: 'views/resetpassword.html',
                                    ncyBreadcrumb: {
                                        label: 'Reset Password'
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load({
                                                    serie: true,
                                                    files: [
                                                        'app/controllers/adminlogin.js'
                                                    ]
                                                });
                                            }
                                        ]
                                    }
                                })
                                .state('confirmation', {
                                    url: '/login/:par1/:par2/:par3',
                                    controller: 'AdminloginCtrl',
                                    //templateUrl: 'views/resetpassword.html',
//                                    ncyBreadcrumb: {
//                                        label: 'Reset Password'
//                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load({
                                                    serie: true,
                                                    files: [
                                                        'app/controllers/adminlogin.js'
                                                    ]
                                                });
                                            }
                                        ]
                                    }
                                })
                                .state('app.mvc', {
                                    url: '/mvc',
                                    templateUrl: 'views/mvc.html',
                                    ncyBreadcrumb: {
                                        label: 'BeyondAdmin Asp.Net MVC Version'
                                    }
                                })
                                .state('app.locations', {
                                    url: '/locations',
                                    templateUrl: 'views/locations.html',
                                    ncyBreadcrumb: {
                                        label: 'Locations',
                                        description: ''
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load({
                                                    serie: true,
                                                    files: [
                                                        'lib/jquery/vendor/tether.min.js',
                                                        'lib/jquery/colors.js',
                                                        'app/controllers/locations.js',
                                                        'assets/css/style-demo.css',
                                                        'app/controllers/nggrid.js',
                                                        'lib/jquery/datatable/dataTables.bootstrap.css',
                                                        'lib/jquery/datatable/jquery.dataTables.min.js',
                                                        'lib/jquery/datatable/ZeroClipboard.js',
                                                        'lib/jquery/datatable/dataTables.tableTools.min.js',
                                                        'lib/jquery/datatable/dataTables.bootstrap.min.js',
                                                        'app/controllers/datatable.js'
                                                    ]
                                                });
                                            }
                                        ]
                                    }
                                })
                                .state('app.addCampaign', {
                                    url: '/addCampaign',
                                    templateUrl: 'views/add-campaign.html',
                                    ncyBreadcrumb: {
                                        label: 'Campaign',
                                        description: 'Add'
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load(['ui.select']).then(
                                                        function () {
                                                            return $ocLazyLoad.load(
                                                                    {
                                                                        serie: true,
                                                                        files: [
                                                                            'app/controllers/select2.js',
                                                                            'app/controllers/campaign.js',
                                                                            'lib/jquery/inputmask/jasny-bootstrap.min.js',
                                                                            'lib/jquery/fuelux/wizard/wizard-custom.min.js',
                                                                        ]
                                                                    });
                                                        }
                                                );
                                            }

                                        ]
                                    }
                                })
                                .state('app.editCampaign', {
                                    url: '/editCampaign/:campaign_id',
                                    templateUrl: 'views/edit-campaign.html',
                                    ncyBreadcrumb: {
                                        label: 'Campaign',
                                        description: 'Edit'
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load(['ui.select']).then(
                                                        function () {
                                                            return $ocLazyLoad.load(
                                                                    {
                                                                        serie: true,
                                                                        files: [
                                                                            'app/controllers/select2.js',
                                                                            'app/controllers/campaign.js',
                                                                            'lib/jquery/inputmask/jasny-bootstrap.min.js',
                                                                            'lib/jquery/fuelux/wizard/wizard-custom.min.js',
                                                                        ]
                                                                    });
                                                        }
                                                );
                                            }

                                        ]
                                    }
                                })
                                .state('app.campaignList', {
                                    url: '/campaignList',
                                    templateUrl: 'views/campaign-list.html',
                                    ncyBreadcrumb: {
                                        label: 'Campaign',
                                        description: 'List'
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load({
                                                    serie: true,
                                                    files: [
                                                        'app/controllers/campaign.js',
                                                        'lib/jquery/datatable/dataTables.bootstrap.css',
                                                        'lib/jquery/datatable/jquery.dataTables.min.js',
                                                        'lib/jquery/datatable/dataTables.tableTools.min.js',
                                                        'lib/jquery/datatable/dataTables.bootstrap.min.js',
                                                    ]
                                                });
                                            }
                                        ]
                                    }
                                })
                                .state('app.archivedCampaignList', {
                                    url: '/archivedCampaignList',
                                    templateUrl: 'views/archived-campaign-list.html',
                                    ncyBreadcrumb: {
                                        label: 'Campaign',
                                        description: 'Archive List'
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load({
                                                    serie: true,
                                                    files: [
                                                        'app/controllers/campaign.js',
                                                        'lib/jquery/datatable/dataTables.bootstrap.css',
                                                        'lib/jquery/datatable/jquery.dataTables.min.js',
                                                        'lib/jquery/datatable/dataTables.tableTools.min.js',
                                                        'lib/jquery/datatable/dataTables.bootstrap.min.js',
                                                    ]
                                                });
                                            }
                                        ]
                                    }
                                })
                                .state('app.campaignDetail', {
                                    url: '/campaignDetail/:campaign_id',
                                    templateUrl: 'views/campaign-details.html',
                                    ncyBreadcrumb: {
                                        label: 'Campaign',
                                        description: 'Detail'
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load({
                                                    serie: true,
                                                    files: [
                                                        'app/controllers/campaign.js',
                                                        'lib/jquery/datatable/dataTables.bootstrap.css',
                                                        'lib/jquery/datatable/jquery.dataTables.min.js',
                                                        'lib/jquery/datatable/dataTables.tableTools.min.js',
                                                        'lib/jquery/datatable/dataTables.bootstrap.min.js',
                                                    ]
                                                });
                                            }
                                        ]
                                    }
                                })
                                .state('app.campaignDashboard', {
                                    url: '/campaign/dashboard/:campaign_name',
                                    templateUrl: 'views/campaign-dashboard.html',
                                    ncyBreadcrumb: {
                                        label: 'Campaign',
                                        description: 'Dashboard'
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load({
                                                    serie: true,
                                                    files: [
                                                        'lib/jquery/charts/sparkline/jquery.sparkline.js',
                                                        'lib/jquery/charts/easypiechart/jquery.easypiechart.js',
                                                        'lib/jquery/charts/flot/jquery.flot.js',
                                                        'lib/jquery/charts/flot/jquery.flot.resize.js',
                                                        'lib/jquery/charts/flot/jquery.flot.pie.js',
                                                        'lib/jquery/charts/flot/jquery.flot.tooltip.js',
                                                        'lib/jquery/charts/flot/jquery.flot.orderBars.js',
                                                        'lib/jquery/charts/flot/jquery.flot.categories.js',
                                                        'lib/jquery/charts/morris/raphael-2.0.2.min.js',
                                                        'lib/jquery/charts/morris/morris.js',
                                                        'app/controllers/campaign_dashboard.js',
                                                        //'app/directives/realtimechart.js',
                                                        'assets/css/style-demo.css',
                                                        'assets/css/rzslider.min.css',
                                                        'lib/jquery/vendor/tether.min.js',
                                                        'lib/jquery/colors.js',
                                                        'app/controllers/campaign_locations.js',
                                                        'lib/jquery/datatable/dataTables.bootstrap.css',
                                                        'lib/jquery/datatable/jquery.dataTables.min.js',
                                                        'lib/jquery/datatable/dataTables.tableTools.min.js',
                                                        'lib/jquery/datatable/dataTables.bootstrap.min.js',
                                                    ]
                                                });
                                            }
                                        ]
                                    }
                                })
                                .state('campaign', {
                                    url: '/campaign/:campaign_name/:squibkey',
                                    templateUrl: 'views/campaign.html',
                                    ncyBreadcrumb: {
                                        label: 'Campaign',
                                        description: 'Dashboard'
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load({
                                                    serie: true,
                                                    files: [
                                                        'app/controllers/campaign.js',
                                                        'assets/css/style-demo.css',
                                                    ]
                                                });
                                            }
                                        ]
                                    }
                                })
                                .state('otfcampaign', {
                                    url: '/campaign/:campaign_name',
                                    templateUrl: 'views/campaign.html',
                                    ncyBreadcrumb: {
                                        label: 'Campaign',
                                        description: 'Dashboard'
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load({
                                                    serie: true,
                                                    files: [
                                                        'app/controllers/campaign.js',
                                                        'assets/css/style-demo.css',
                                                    ]
                                                });
                                            }
                                        ]
                                    }
                                })
                                .state('app.clearCampaign', {
                                    url: '/clearCampaignData/',
                                    templateUrl: 'views/clear-campaign.html',
                                    ncyBreadcrumb: {
                                        label: 'Campaign',
                                        description: 'Clear'
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load({
                                                    serie: true,
                                                    files: [
                                                        'app/controllers/campaign.js',
                                                        'assets/css/style-demo.css',
                                                    ]
                                                });
                                            }
                                        ]
                                    }
                                })
                                .state('app.clearedCampaignData', {
                                    url: '/clearedCampaignData/',
                                    templateUrl: 'views/cleared-campaign-data.html',
                                    ncyBreadcrumb: {
                                        label: 'Campaign',
                                        description: 'Cleared Data'
                                    },
                                    resolve: {
                                        deps: [
                                            '$ocLazyLoad',
                                            function ($ocLazyLoad) {
                                                return $ocLazyLoad.load({
                                                    serie: true,
                                                    files: [
                                                        'app/controllers/campaign.js',
                                                        'lib/jquery/datatable/dataTables.bootstrap.css',
                                                        'lib/jquery/datatable/jquery.dataTables.min.js',
                                                        'lib/jquery/datatable/dataTables.tableTools.min.js',
                                                        'lib/jquery/datatable/dataTables.bootstrap.min.js',
                                                        'assets/css/style-demo.css',
                                                    ]
                                                });
                                            }
                                        ]
                                    }
                                })
                                ;
                    }
                ]
                );