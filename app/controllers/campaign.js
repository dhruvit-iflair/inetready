app.controller('CampaignCtrl', function ($scope, $stateParams, $http, site_config, deviceDetector, $rootScope, $location, $timeout, $window, browser, $sce, AuthenticationService) {
    $scope.loading_text = "Please wait while page loads...";
    $http.get(site_config.apiUrl + "api/campaignbrand?campaign=" + $stateParams.campaign_name)
            .then(function (response)
            {
                if (response.data.status) {
                    $scope.branding = response.data.data;
                }
            });
    var ec = new evercookie();
    $scope.ip_address = myip;
    $scope.UID = 0;
    $scope.type = 'direct';
    $scope.show = false;
    $scope.showOriginal = false;
    $scope.loading = true;
    var SquibKey = 0;
    if ($stateParams.squibkey && $stateParams.squibkey.indexOf("USB=") > -1) {
        var squibkey = $stateParams.squibkey.split("USB=");
        SquibKey = squibkey[1];
    }
    $scope.data = {};
    /////
    $rootScope.iframe_data = 0;
    $http({
        method: 'GET',
        url: site_config.apiUrl + "user/getaccesstoken",
        headers: {'Content-Type': 'application/json'}
    }).success(function (resp) {
        if (resp.status) {
            $scope.access_token = resp.data;
            $scope.iframeURL = $sce.trustAsResourceUrl(site_config.siteURL + "/" + site_config.apiUrl + "/user/getmaincookie?access_token=" + $scope.access_token);
            $rootScope.iframe_data = 1;
            setTimeout(function () {
                $rootScope.$apply(function () {
                    $http({
                        method: 'GET',
                        url: site_config.apiUrl + "user/getcookie?access_token=" + $scope.access_token,
                        headers: {'Content-Type': 'application/json'}
                    }).success(function (resp) {
                        if (resp.status) {
                            $rootScope.globals = JSON.parse(resp.data.cookie);
                        } else {
                            $rootScope.globals = {};
                        }

                    });
                });
            }, 1000);
        } else {
            $rootScope.globals = JSON.parse(resp.data.cookie);
        }

        ec.get("UID", function (value) {
            $scope.UID = value;
            $http({
                url: site_config.apiUrl + "api/check_campaign_activate",
                method: 'POST',
                data: {
                    name: $stateParams.campaign_name,
                    key: SquibKey,
                    uid: $scope.UID,
                    ip_address: $scope.ip_address,
                    visitor_id: (typeof ($rootScope.globals.currentUser) != 'undefined') ? $rootScope.globals.currentUser.user_id : 0,
                },
                headers: {'Content-type': 'application/json'},
            }).success(function (response) {
                $scope.loading_text = "Checking campaign activation status...";
                if (response != 0) {
                    $scope.user_id = response.data.user_id;
                    $scope.campaign_id = response.data.campaign_id;
                    $scope.on_the_fly = response.data.on_the_fly;
                    $scope.key_id = response.data.key_id;
                    $scope.link = response.data.link;
                    $scope.url = response.data.url;
                    $scope.connection_type = response.data.connection_type;
                    $scope.uid = response.data.uid;
                }

                if (response.status == 1) {
                    $scope.loading_text = "Key is Available and Campaign Loading...";
                    if (response.data.connection_type == 'optin') {
                        if (typeof ($rootScope.globals.currentUser) != 'undefined') {
                            $scope.UID = $rootScope.globals.currentUser.uid
                            logVisitor();
                        } else {
                            $scope.show = true;
                            $scope.loading = false;
                        }
                    } else {
                        logVisitor();
                    }
                } else if (response.status == 2) {
                    $scope.loading_text = "Key already assigned and campaign Loading...";
                    if (response.data.connection_type == 'optin') {
                        if (response.data.on_the_fly == '1') {
                            if (typeof ($rootScope.globals.currentUser) != 'undefined') {
                                $scope.UID = $rootScope.globals.currentUser.uid
                                logVisitor();
                            } else {
                                $scope.type = 'optin';
                                $scope.show = true;
                                $scope.loading = false;
                            }
                        } else {
                            if (value != response.data.uid) {
                                $scope.type = 'optin';
                                $scope.showOriginal = true;
                                $scope.loading = false;
                            } else {
                                // KEY VISIT ID SAME AS STORED IN THE COOKIE
                                logVisitor();
                            }
                        }


                    } else {
                        if (response.data.on_the_fly == '1') {
                            $scope.type = 'direct';
                            $scope.showOTF = true;
                            $scope.loading = false;
                        } else {
                            if ($scope.UID != response.data.uid) {
                                $scope.type = 'direct';
                                $scope.showOriginal = true;
                                $scope.loading = false;
                            } else {
                                logVisitor();
                            }
                        }
                    }
                } else {
                    $scope.loading = false;
                    $scope.errorMsg = response.message;
                    $rootScope.source = 1;
                    $timeout(function () {
                        $window.location = $scope.link + "?source=1";
                    }, 5000);
                }
            }).error(function () {
            });
        });

    });
    /////

    $scope.showOptin = function () {
        $scope.show = true;
        $scope.showLogin = false;
    }

    $scope.reply = function (value, type) {

        $scope.loading = true;
        if ($scope.on_the_fly == '1') {
            $scope.showOTF = false;
            if (type == 'optin') {
                if (value) {
                    $scope.UID = $scope.uid;
                    logVisitor();
                } else {
                    $scope.UID = 0;
                    $scope.show = true;
                    $scope.loading = false;
                }
            } else {

                if (value) {
                    $scope.UID = $scope.uid;
                    logVisitor();
                } else {
                    $scope.UID = 0;
                    logVisitor();
                }
            }
        } else {
            $scope.showOriginal = false;
            if (type == 'optin') {
                if (value) {
                    $scope.UID = $scope.uid;
                    logVisitor();
                } else {
                    $scope.UID = 0;
                    $scope.show = true;
                    $scope.loading = false;
                }
            } else {
                if (value) {
                    $scope.UID = $scope.uid;
                    $scope.visitor_id = 0;

                    logVisitor();

                } else {
                    $scope.UID = 0;
                    logVisitor();
                }
            }
        }

    }


    var deviceData = deviceDetector;

    $scope.signup = function () {
        var protocol = $location.protocol();
        var host = $location.host();
        $scope.data.domain_url = protocol + "://" + host;
        $scope.data.ip_address = $scope.ip_address;
        $scope.data.user_id = $scope.user_id;
        $scope.data.campaign_id = $scope.campaign_id;
        $http({
            method: 'POST',
            url: site_config.apiUrl + 'api/add_visitor',
            data: $scope.data,
            headers: {'Content-Type': 'application/json', 'Authorization': 'Basic 123456789'}
        }).success(function (resp) {
            if (resp.status) {
                $scope.show = false;
                $scope.successMsg = resp.data;
                ec.set("UID", resp.uid);
                ec.get("UID", function (value) {
                    $scope.UID = value;
                    logVisitor();
                });
            } else {
                $scope.errorMsg = resp.data + " Please <a href='javascript:void(0);' style='color:#fff;text-decoration:underline' ng-click='showLogin=true;show=false;'>Login</a> with your account or Enter another Email.";
            }
            $timeout(function () {
                $scope.errorMsg = false;
            }, 3000);

        })
    };
    $scope.login = function () {
        $rootScope.errorMsg = false;
        $http({
            method: 'POST',
            url: site_config.apiUrl + 'api/login',
            data: {
                prevUID: $scope.UID,
                emailid1: $scope.data.email_address,
                password1: $scope.data.password,
                ip_address: myip
            },
            headers: {'Content-Type': 'application/json', 'Authorization': 'Basic 123456789'}
        }).success(function (resp, status, headers, config) {
            if (resp.status) {
                AuthenticationService.SetCredentials($scope.data.email_address, $scope.data.password, resp.data);
                $scope.UID = resp.data.uid;
                logVisitor();
            } else {
                $scope.errorMsg = resp.data;
            }
            $timeout(function () {
                $scope.errorMsg = false;
            }, 3000);
        }).error(function (resp, status, headers, config) {
            $scope.errorMsg = 'Unable to submit form';
        })
    }
    function logVisitor() {
        console.log("UID=" + $scope.UID);
        $scope.loading_text = "Saving your visit so when you come next time will recognize you...";
        if (angular.isUndefined($scope.visitor_id)) {
            $scope.visitor_id = (angular.isDefined($rootScope.globals.currentUser)) ? $rootScope.globals.currentUser.user_id : 0;
        } else {
            if (angular.isDefined($rootScope.globals.currentUser))
                AuthenticationService.ClearCredentials();
        }

        $http({
            method: 'POST',
            url: site_config.apiUrl + "api/savevisitor",
            data: {
                ip_address: $scope.ip_address,
                user_id: $scope.user_id,
                browser: (deviceData.browser == 'unknown') ? browser() : deviceData.browser,
                os: deviceData.os,
                device: (deviceData.device == 'unknown') ? 'desktop' : deviceData.device,
                visitor_id: $scope.visitor_id,
                domain: window.location.href,
                campaign_id: $scope.campaign_id,
                key_id: $scope.key_id,
                uid: $scope.UID,
            },
            headers: {'Content-Type': 'application/json'}
        }).success(function (resp) {
            if (resp.status == 1) {
                ec.set("UID", resp.uid);
                console.log('Redirect Successfull.');
                sentGeoIPAlert(resp.id);
                $window.location.href = $scope.url;
            } else {
                $scope.loading = false;
                $scope.errorMsg = resp.data;
                $rootScope.source = 1;
                $timeout(function () {
                    $window.location = $scope.link + "?source=1";
                }, 5000);
            }
        }).error(function () {
            console.log('Unable to save visitors');
        });
    }

    function sentGeoIPAlert(visitor_id) {
        $scope.loading_text = "Sending campaign alert...";
        $http.get(site_config.apiUrl + "visitor/campaignalert?visitor_id=" + visitor_id)
                .then(function (response) {
                    if (response.data.status) {
                        console.log(response.data.status);
                    }
                });
    }

});
app.directive('dynamic', function ($compile) {
    return {
        restrict: 'A',
        replace: true,
        link: function (scope, ele, attrs) {
            scope.$watch(attrs.dynamic, function (html) {
                ele.html(html);
                $compile(ele.contents())(scope);
            });
        }
    };
});
app.controller('CampaignDetailCtrl', function ($scope, site_config, $rootScope, $stateParams, $http) {
    $http.get(site_config.apiUrl + "api/getcampaigndetail?id=" + $stateParams.campaign_id)
            .then(function (response) {
                if (response.data.status) {
                    console.log(response.data.status);
                }
            });
//    $('#example').DataTable({
//        serverSide: true,
//        ordering: false,
//        searching: false,
//        "processing": true,
//        "ajax": {
//            url: site_config.apiUrl + "api/getcampaigndetail?id=" + $stateParams.campaign_id,
//            headers: {
//                'Authorization': 'Basic ' + $rootScope.globals.currentUser.encryption
//            }
//        },
//        scrollY: 200,
//        scroller: {
//            loadingIndicator: true
//        }
//    });
    /* GET CAMPAIGN LIST */
//    $scope.campaignDetailsTable = {
//        serverSide: true,
//        "bProcessing": true,
//        ajax: {
//            url: site_config.apiUrl + "api/getcampaigndetail?id=" + $stateParams.campaign_id,
//            headers: {
//                'Authorization': 'Basic ' + $rootScope.globals.currentUser.encryption
//            }
//        },
//        aoColumns: [
//            {mData: 'key', "bSortable": true},
//            {mData: 'name', "bSortable": false, searchable: false},
//            {mData: 'start_date', "bSortable": false, searchable: false},
//            {mData: 'end_date', "bSortable": false, searchable: false},
//            {mData: 'uurl', "bSortable": true},
//        ],
//        "sDom": "Tflt<'row DTTTFooter'<'col-sm-6'i><'col-sm-6'p>>",
//        "iDisplayLength": 25,
//        "oTableTools": {
//            "aButtons": [
//                "copy", "csv", "xls", "pdf", "print"
//            ],
//            "sSwfPath": "assets/swf/copy_csv_xls_pdf.swf"
//        },
//        "language": {
//            "search": "",
//            "sLengthMenu": "_MENU_",
//            "oPaginate": {
//                "sPrevious": "Prev",
//                "sNext": "Next"
//            }
//        },
    //"aaSorting": [2, 4],
    // };
});
app.controller('CampaignListCtrl', function ($scope, $http, site_config, $compile, $window, $rootScope, $modal) {

    /* GET CAMPAIGN LIST */
    var oTable = $('#campaignListTable').dataTable({
        "bProcessing": true,
        ajax: {
            url: site_config.apiUrl + "api/getcampaignlist?id=" + $rootScope.globals.currentUser.user_id,
            headers: {
                'Authorization': 'Basic ' + $rootScope.globals.currentUser.encryption
            }
        },
        aoColumns: [
            {mData: 'name'},
            {mData: 'start_date'},
            {mData: 'end_date'},
            {mData: 'url'},
//            {
//                "bSortable": false,
//                "mData": 'key_generate_url'
//            },
            {mData: 'status'},
            {mData: 'keys'},
            {
                "bSortable": false,
                "mData": null,
                "sClass": "center",
                "fnCreatedCell": function (nTd, sData, oData, iRow, iCol)
                {
                    $compile(nTd)($scope);
                },
                mRender: function (data, type, row) {
                    // console.log(JSON.stringify(data));
                    // if((new Date(data.start_date)) > new Date())
                    // {
                        if (row.type != 0) {
                            var lnk = '<a href="/app/editCampaign/' + row.id + '"  class="btn btn-default btn-xs " title="Edit"><i class="fa fa-edit"></i> </a>';
                            if (row.campaign_keys_count == 0) {
                                lnk += '<a title="Export CSV" href="javascript:alert(\'Campaign Keys are waitng for approval.\')"  class="btn btn-default btn-xs btn-model" ><i class="fa fa-file-excel-o"></i> </a>';
                            } else {
                                lnk += '<a title="Export CSV" id="csv-' + row.id + '"  class="btn btn-default btn-xs btn-model" ><i class="fa fa-file-excel-o"></i> </a>';
                            }
                            lnk += ' <a title="Archive" id="arch-' + row.id + '" class="btn btn-default btn-xs black btn-model"><i class="fa fa-times"></i> </a>';
                            return lnk;
                        } else {
                            var lnk = '<a href="/app/editCampaign/' + row.id + '"  class="btn btn-default btn-xs " title="Edit"><i class="fa fa-edit"></i> </a>';
                            if (row.campaign_keys_count == 0) {
                                lnk += '<a title="Export CSV" href="javascript:alert(\'Campaign Keys are waitng for approval.\')"  class="btn btn-default btn-xs btn-model" ><i class="fa fa-file-excel-o"></i> </a>';
                            } else {
                                lnk += '<a title="Export CSV" id="csv-' + row.id + '"  class="btn btn-default btn-xs btn-model" ><i class="fa fa-file-excel-o"></i> </a>';
                            }
                            lnk += '<a title="Archive" id="arch-' + row.id + '" class="btn btn-default btn-xs black btn-model" ><i class="fa fa-times"></i> </a><a title="Delete" class="btn btn-default btn-xs black btn-model" id="del-' + row.id + '"><i class="fa fa-trash-o"></i> </a>';
                            return lnk;
                        }
                    // }
                    

                }
            },
        ],
        "sDom": "Tflt<'row DTTTFooter'<'col-sm-6'i><'col-sm-6'p>>",
        "iDisplayLength": 10,
        "oTableTools": {
            "aButtons": [
                "copy", "csv", "xls", "pdf", "print"
            ],
            "sSwfPath": "assets/swf/copy_csv_xls_pdf.swf"
        },
        "language": {
            "search": "",
            "sLengthMenu": "_MENU_",
            "oPaginate": {
                "sPrevious": "Prev",
                "sNext": "Next"
            }
        },
        "aaSorting": [],
    });
    $("#campaignListTable tbody").delegate(".btn-model", "click", function () {
        var count = 0;

        var id = this.id;
        var res = id.split("-");
        var campaign_id = res[1];

        var row = $(this).closest("tr").get(0);
        var iPos = oTable.fnGetPosition(row);
        var templateUrl, type;
        if (res[0] == 'csv') {
            window.open(
                    site_config.apiUrl + "api/getcampaigndetail?id=" + campaign_id,
                    '_blank' // <- This is what makes it open in a new window.
                    );
//            $http.get(site_config.apiUrl + "api/getcampaignkeys?id=" + campaign_id).success(function (resp) {
//                console.log(resp.data);
//                count = resp.data;
//
//                if (count == '0') {
//                    alert('Campaign Keys are waitng for approval.');
//
//                } else {
//                    window.location.href = (site_config.apiUrl + "api/getcampaigndetail?id=" + campaign_id
//                            // '_blank' // <- This is what makes it open in a new window.
//                            );
//                }
//            });


            type = 1;
        } else if (res[0] == 'arch') {
            templateUrl = 'archiveModel.html';
            type = 2;
        } else if(res[0]=='del'){
            templateUrl = 'deleteModel.html';
            type = 0;
        }
        if (type != 1) {
            var modalInstance = $modal.open({
                templateUrl: templateUrl,
                controller: 'ModalInstanceCtrl',
                size: 'sm',
                resolve: {
                    items: function () {
                    }
                }
            });
            modalInstance.result.then(function (selectedItem) {
                if (iPos !== null) {
                    $http.get(site_config.apiUrl + "api/deletecampaign?id=" + campaign_id + "&type=" + type).success(function (data) {
                        $scope.errorMsg = data.message;
                        oTable.fnDeleteRow(iPos);
                    });
                }
            }, function () {
                //$log.info('Modal dismissed at: ' + new Date());
            });
        }
    });
//    $scope.open = function (windowClass, templateUrl, size, id, index, type) {
//        var modalInstance = $modal.open({
//            windowClass: windowClass,
//            templateUrl: templateUrl,
//            controller: 'ModalInstanceCtrl',
//            size: size,
//            resolve: {
//                items: function () {
//                }
//            }
//        });
//
//        modalInstance.result.then(function (selectedItem) {
//            $http.get(site_config.apiUrl + "api/deletecampaign?id=" + id + "&type=" + type).success(function (data) {
//                $scope.errorMsg = data.message;
//                oTable.fnDeleteRow(index);
//            });
//        }, function () {
//            //$log.info('Modal dismissed at: ' + new Date());
//        });
//    };
//    $scope.deleteCampaign = function (id, index, type) {
//        var deleteCampaign = $window.confirm('Are you absolutely sure you want to delete?');
//        if (deleteCampaign) {
//            $http.get(site_config.apiUrl + "api/deletecampaign?id=" + id + "&type=" + type).success(function (data) {
//                $scope.errorMsg = data.message;
//                oTable.fnDeleteRow(index);
//            });
//        }
//    }

});
app.controller('ArchivedCampaignListCtrl', function ($scope, $http, site_config, $compile, $rootScope, $modal) {

    /* GET CAMPAIGN LIST */
    var oTable = $('#campaignListTable').dataTable({
        "bProcessing": true,
        ajax: {
            url: site_config.apiUrl + "api/getarchivedcampaignlist?id=" + $rootScope.globals.currentUser.user_id,
            headers: {
                'Authorization': 'Basic ' + $rootScope.globals.currentUser.encryption
            }
        },
        aoColumns: [
            {mData: 'name'},
            {mData: 'start_date'},
            {mData: 'end_date'},
            {mData: 'url'},
            {
                "bSortable": false,
                "mData": null,
                "sClass": "center",
                "fnCreatedCell": function (nTd, sData, oData, iRow, iCol)
                {
                    $compile(nTd)($scope);
                },
                mRender: function (data, type, row) {
                    return '<a href="/app/editCampaign/' + row.id + '"  class="btn btn-default btn-xs" ><i class="fa fa-edit"></i> </a> <a id="act-' + row.id + '" class="btn btn-default btn-xs black btn-model"><i class="fa fa-check"></i> </a><a id="del-' + row.id + '" class="btn btn-default btn-xs black btn-model" ><i class="fa fa-trash-o"></i> </a>'
                }
            },
        ],
        "sDom": "Tflt<'row DTTTFooter'<'col-sm-6'i><'col-sm-6'p>>",
        "iDisplayLength": 10,
        "oTableTools": {
            "aButtons": [
                "copy", "csv", "xls", "pdf", "print"
            ],
            "sSwfPath": "assets/swf/copy_csv_xls_pdf.swf"
        },
        "language": {
            "search": "",
            "sLengthMenu": "_MENU_",
            "oPaginate": {
                "sPrevious": "Prev",
                "sNext": "Next"
            }
        },
        "aaSorting": [],
    });
    $("#campaignListTable tbody").delegate(".btn-model", "click", function () {

        var id = this.id;
        var res = id.split("-");
        var campaign_id = res[1];
        var row = $(this).closest("tr").get(0);
        var iPos = oTable.fnGetPosition(row);
        var templateUrl, type;
        if (res[0] == 'act') {
            templateUrl = 'activateModel.html';
            type = 1;
        } else {
            templateUrl = 'deleteModel.html';
            type = 0;
        }
        var modalInstance = $modal.open({
            templateUrl: templateUrl,
            controller: 'ModalInstanceCtrl',
            size: 'sm',
            resolve: {
                items: function () {
                }
            }
        });
        modalInstance.result.then(function (selectedItem) {
            if (iPos !== null) {
                $http.get(site_config.apiUrl + "api/deletecampaign?id=" + campaign_id + "&type=" + type).success(function (data) {
                    $scope.errorMsg = data.message;
                    oTable.fnDeleteRow(iPos);
                });
            }
        }, function () {
            //$log.info('Modal dismissed at: ' + new Date());
        });
    });
//    $scope.open = function (windowClass, templateUrl, size, id, index, type) {
//        var modalInstance = $modal.open({
//            windowClass: windowClass,
//            templateUrl: templateUrl,
//            controller: 'ModalInstanceCtrl',
//            size: size,
//            resolve: {
//                items: function () {
//                }
//            }
//        });
//
//        modalInstance.result.then(function (selectedItem) {
//            $http.get(site_config.apiUrl + "api/deletecampaign?id=" + id + "&type=" + type).success(function (data) {
//                $scope.errorMsg = data.message;
//                oTable.fnDeleteRow(index);
//            });
//        }, function () {
//            //$log.info('Modal dismissed at: ' + new Date());
//        });
//    };

});
app.controller('CampaignKeysGenerateCtrl', function ($scope, $http, site_config, $stateParams, $location) {
    var search_par = $location.search()
    $scope.loading = true;
    $http.get(site_config.apiUrl + "api/campaignbrand?campaign=" + $stateParams.campaign_name)
            .then(function (response)
            {
                if (response.data.status) {
                    $scope.branding = response.data.data;
                }
            });
    $http({
        url: site_config.apiUrl + "campaign/getcampaignbyname",
        method: 'POST',
        data: {name: $stateParams.campaign_name, action: search_par.action},
        headers: {'Content-type': 'application/json'},
    }).success(function (response) {
        $scope.loading = false;
        if (response.status == 1) {
            $scope.loading = false;
            $scope.successMsg = response.message;
        } else {
            $scope.loading = false;
            $scope.errorMsg = response.message;
        }
    }).error(function () {
    });
});
app.filter('propsFilter', function () {
    return function (items, props) {
        var out = [];
        if (angular.isArray(items)) {
            items.forEach(function (item) {
                var itemMatches = false;
                var keys = Object.keys(props);
                for (var i = 0; i < keys.length; i++) {
                    var prop = keys[i];
                    var text = props[prop].toLowerCase();
                    if (item[prop].toString().toLowerCase().indexOf(text) !== -1) {
                        itemMatches = true;
                        break;
                    }
                }

                if (itemMatches) {
                    out.push(item);
                }
            });
        } else {
            // Let the output be the input untouched
            out = items;
        }

        return out;
    };
});
app.controller('AddCampaignCtrl', function ($scope, $http, site_config, $location, $rootScope, $timeout) {
    $scope.campaignList = [
        {id: 1, value: "Squibkey"},
        {id: 3, value: "Squibdrive"},
        {id: 2, value: "Squibcard"},
        {id: 4, value: "SquibPush"},
    ];
    $scope.disabled = undefined;

    $scope.folderType = [
        {label: "Master Drive", value: "master"},
        {label: "Account Drive", value: "account"}
    ];
    $scope.radioList = [
        {label: "Random", value: "random"},
        {label: "Sequential", value: "sequential"}
    ];
    $scope.urlType = [
        {label: "Vanity Url", value: "1"},
        {label: "Shorter Url", value: "2"}
    ];
    $scope.connectionType = [
        {label: "Direct", id: "direct"},
        {label: "Optin", id: "optin"}
    ];
    $scope.show = false;
    $scope.campaign = {};
    //$scope.campaign.campaign_type = $scope.campaignList[0];
    $scope.campaign.key_generate_type = $scope.radioList[0].value;
    $scope.campaign.folder_type = $scope.folderType[1].value;
    $scope.campaign.uurl_type = $scope.urlType[1].value;
    $scope.campaign.connection_type = $scope.connectionType[0].id;
    $scope.person = {};
    $scope.person.selected = {
        id: $rootScope.globals.currentUser.user_id,
        name: $rootScope.globals.currentUser.username,
        email: $rootScope.globals.currentUser.email,
        role: $rootScope.globals.currentUser.user_role
    };
    $http.get(site_config.apiUrl + "user/getchildusers?user_id=" + $rootScope.globals.currentUser.user_id).then(function (response)
    {
        if (response.status) {
            $scope.people = response.data.data;
        }
    });
    $scope.file = {};
    $scope.loadDrive = function () {
        $http.get(site_config.apiUrl + "user/getuserdrive?user_id=" + $scope.person.selected.id + "&folder_type=" + $scope.campaign.folder_type).then(function (response)
        {
            if (response.status) {
                $scope.drive = response.data.data;
            }
        });
    }
    $scope.loadDrive();
    $scope.toggleMin = function () {
        $scope.startMinDate = $scope.startMinDate ? null : new Date();
        $scope.endMinDate = $scope.endMinDate ? null : new Date();
    };
    $scope.toggleMin();
    $scope.$watch('campaign.start_date', function (v) {
        if (typeof (v) != 'undefined') {
            $scope.endMinDate = v;
        }
    });
    $scope.dateOptions = {
        formatYear: 'yy',
        startingDay: 1,
        showWeeks: false,
        showButtonBar: false
    };
    $scope.formats = ['dd-MMMM-yyyy', 'yyyy/MM/dd', 'dd.MM.yyyy', 'shortDate', 'MMMM dd yyyy'];
    $scope.format = $scope.formats[4];
    $scope.today = function () {
        $scope.campaign.start_date = new Date();
    };
    $scope.openStartDate = function ($event) {
        $event.preventDefault();
        $event.stopPropagation();
        $scope.startOpened = true;
    };
    $scope.openEndDate = function ($event) {
        $event.preventDefault();
        $event.stopPropagation();
        $scope.endOpened = true;
    };
    $scope.change = function (type, $event) {
        if (typeof (type) == 'string' && type == 'alert') {
            if ($event) {
                $scope.show = true;
            } else {
                $scope.show = false;
            }
        }

        if (typeof (type) == 'string' && type == 'key_generate_type') {
            if ($event == 'sequential') {
                $scope.startKey = true;
            } else {
                $scope.startKey = false;
            }
        }
        if (typeof (type) == 'string' && type == 'folder_type') {
            $scope.loadDrive();
        }
    };
    $('#simplewizardinwidget').wizard();
//    $scope.nextStep=function(){
//       //$scope.ERROR=true;
//    }
//    
    $('#simplewizardinwidget').on('change', function (e, data) {

        if (data.step === 1 && data.direction === 'next') {
            $scope.step1.error = true;
            if ($scope.step1.$invalid) {

                return e.preventDefault();
            }
        }
        if (data.step === 2 && data.direction === 'next') {
            $scope.step2.error = true;
            console.log($scope.step2);
            if ($scope.step2.$invalid) {

                return e.preventDefault();
            }

        }
    });
    $('#simplewizardinwidget').on('finished', function (e) {
        $scope.step3.error = true;
        if ($scope.step3.$invalid) {
            return e.preventDefault();
        } else {
            $scope.step3.saving = true;
            $("#simplewizardinwidget li").removeClass("active");
            $("#simplewizardinwidget li").removeClass("complete");

            var startDate = new Date($scope.campaign.start_date);
            $scope.campaign.start_date = (startDate.getFullYear() + '-' + (startDate.getMonth() + 1) + '-' + startDate.getDate());
            var endDate = new Date($scope.campaign.end_date);
            $scope.campaign.end_date = (endDate.getFullYear() + '-' + (endDate.getMonth() + 1) + '-' + endDate.getDate());
            $scope.campaign.user_id = $rootScope.globals.currentUser.user_id;
            if ($scope.campaign.campaign_type.id == 3) {
                var cloud_url = site_config.siteURL + "/drive/" + $scope.file.selected.type + "/" + $scope.person.selected.id + "/" + $scope.file.selected.id + "/" + $scope.file.selected.name;
                $scope.campaign.url = cloud_url;
                $scope.campaign.drive_user_id = $scope.person.selected.id;
                $scope.campaign.drive_id = $scope.file.selected.id;
                $scope.campaign.drive_type = $scope.file.selected.type;
            }

            $http({
                url: site_config.apiUrl + "api/savecampaign",
                method: 'POST',
                data: $scope.campaign,
                headers: {'Content-Type': 'application/json'}
            }).success(function (resp) {
                if (resp.status == 1)
                {
                    $rootScope.successMsg = resp.message;
                    $timeout(function () {
                        $rootScope.successMsg = false;
                    }, 3000);

                    $scope.step3.saving = false;
                    $location.path('app/campaignList');
                } else if (resp.status == 0)
                {
                    $scope.step3.saving = false;
                    $("#simplewizardinwidget li").addClass("active");
                    $("#simplewizardinwidget li").last().removeClass("active");
                    $("#simplewizardinwidget li").last().addClass("complete");
                    $scope.errorMsg = resp.message;
                }
            }).error(function () {
                console.log("Error");
                $scope.step3.saving = false;
            });

        }
        return e.preventDefault();
    });
});
app.controller('EditCampaignCtrl', function ($scope, $http, site_config, $stateParams, $location, $rootScope, $timeout) {
    $scope.campaignList = [
        //{id: 0, value: "Please Choose Campaign Type"},
        {id: '1', value: "Squibkey"},
        {id: '2', value: "Squibcard"},
        {id: '3', value: "Squibdrive"},
        {id: '4', value: "SquibPush"},
    ];
    $scope.radioList = [
        {label: "Random", value: "random"},
        {label: "Sequential", value: "sequential"}
    ];
    $scope.connectionType = [
        {label: "Direct", id: "direct"},
        {label: "Optin", id: "optin"}
    ];
    $scope.urlType = [
        {label: "Vanity Url", value: "1"},
        {label: "Shorter Url", value: "2"}
    ];
    $scope.folderType = [
        {label: "Master Drive", value: "master"},
        {label: "Account Drive", value: "account"}
    ];
//    $scope.toggleMin = function () {
//        $scope.startMinDate = $scope.startMinDate ? null : new Date();
//        $scope.endMinDate = $scope.endMinDate ? null : new Date();
//    };
    //$scope.toggleMin();
//    $scope.$watch('campaign.start_date', function (v) {
//        console.log(v)
//        if (typeof (v) != 'undefined') {
//            $scope.endMinDate = v;
//        }
//    });
    $scope.dateOptions = {
        formatYear: 'yy',
        startingDay: 1,
        showWeeks: false,
        showButtonBar: false
    };
    $scope.formats = ['dd-MMMM-yyyy', 'yyyy/MM/dd', 'dd.MM.yyyy', 'shortDate', 'MMMM dd yyyy'];
    $scope.format = $scope.formats[4];

    $scope.openStartDate = function ($event) {
        $event.preventDefault();
        $event.stopPropagation();
        $scope.startOpened = true;
    };
    $scope.openEndDate = function ($event) {
        $event.preventDefault();
        $event.stopPropagation();
        $scope.endOpened = true;
    };
    $scope.person = {};
    $scope.loadChilds = function (drive_user_id) {
        $http.get(site_config.apiUrl + "user/getchildusers?user_id=" + $rootScope.globals.currentUser.user_id).then(function (response)
        {
            if (response.status) {
                $scope.people = response.data.data;
                for (var i = 0; i < $scope.people.length; i++) {
                    if ($scope.people[i].id == drive_user_id) {
                        $scope.person.selected = $scope.people[i];
                    }
                }
            }
        });
    }



    $scope.file = {};
    $scope.loadDrive = function (drive_user_id, start) {
        $http.get(site_config.apiUrl + "user/getuserdrive?user_id=" + drive_user_id + "&folder_type=" + $scope.campaign.folder_type).then(function (response)
        {
            if (response.status) {
                $scope.drive = response.data.data;
                if (start == 0) {
                    // the code you're looking for
                    var needle = $scope.campaign.drive_id;
                    var needle1 = $scope.campaign.drive_type;
                    // iterate over each element in the array
                    for (var i = 0; i < $scope.drive.length; i++) {
                        // look for the entry with a matching `code` value
                        if ($scope.drive[i].id == needle && $scope.drive[i].type == needle1) {
                            // we found it
                            // obj[i].name is the matched result
                            $scope.file.selected = $scope.drive[i];
                        }
                    }
                } else {
                    $scope.file = {};
                }

            }
        });
    }


    $scope.change = function (type, $event) {
        if (typeof (type) == 'string' && type == 'alert') {
            if ($event) {
                $scope.editShow = true;
            } else {
                $scope.editShow = false;
            }
        }

        if (typeof (type) == 'string' && type == 'key_generate_type') {
            if ($event == 'sequential') {
                $scope.editStartKey = true;
            } else {
                $scope.editStartKey = false;
            }
        }
        if (typeof (type) == 'string' && type == 'folder_type') {

            $scope.loadDrive($scope.person.selected.id, 1);
        }
    };
    $http({
        url: site_config.apiUrl + "api/getcampaignbyid",
        method: 'POST',
        data: {id: $stateParams.campaign_id},
        headers: {'Content-type': 'application/json'},
    }).success(function (response) {
        if (response.status) {
            $scope.campaign = response.data;
            $scope.campaign.keys_exist = $scope.campaign.no_of_keys;
            $scope.endMinDate = $scope.campaign.end_date;
            if ($scope.campaign.campaign_type == 3) {
                $scope.loadDrive($scope.campaign.drive_user_id, 0);
                $scope.loadChilds($scope.campaign.drive_user_id);
            }
            if (response.data.key_generate_type == 'sequential') {
                $scope.editStartKey = true;
            }
            if (response.data.campaign_alert == 1) {
                response.data.campaign_alert = true;
                $scope.editShow = true;
            }
            if (response.data.on_the_fly == 1) {
                $scope.campaign.on_the_fly = true;
            }
        }
    }).error(function () {
    });
    $('#simplewizardinwidget').wizard();
    $('#simplewizardinwidget').on('change', function (e, data) {
        if (data.step === 1 && data.direction === 'next') {
            if ($scope.step1.$invalid) {
                return e.preventDefault();
            }
        }
        if (data.step === 2 && data.direction === 'next') {
            if ($scope.step2.$invalid) {
                return e.preventDefault();
            }
        }
    });
    $('#simplewizardinwidget').on('finished', function (e) {
        if ($scope.step3.$invalid) {
            return e.preventDefault();
        } else {
            $scope.step3.saving = true;
            $("#simplewizardinwidget li").removeClass("active");
            $("#simplewizardinwidget li").removeClass("complete");
            var startDate = new Date($scope.campaign.start_date);
            $scope.campaign.start_date = (startDate.getFullYear() + '-' + (startDate.getMonth() + 1) + '-' + startDate.getDate());
            var endDate = new Date($scope.campaign.end_date);
            $scope.campaign.end_date = (endDate.getFullYear() + '-' + (endDate.getMonth() + 1) + '-' + endDate.getDate());
            $scope.campaign.user_id = $rootScope.globals.currentUser.user_id;
            if ($scope.campaign.campaign_type == 3) {
                var cloud_url = site_config.siteURL + "/drive/" + $scope.file.selected.type + "/" + $scope.person.selected.id + "/" + $scope.file.selected.id + "/" + $scope.file.selected.name;
                $scope.campaign.url = cloud_url;
                $scope.campaign.drive_user_id = $scope.person.selected.id;
                $scope.campaign.drive_id = $scope.file.selected.id;
                $scope.campaign.drive_type = $scope.file.selected.type;
            }
            $http({
                url: site_config.apiUrl + "api/updatecampaign",
                method: 'POST',
                data: $scope.campaign,
                headers: {'Content-Type': 'application/json'}
            }).success(function (resp) {
                if (resp.status == 1)
                {
                    $rootScope.successMsg = resp.message;
                    $timeout(function () {
                        $rootScope.successMsg = false;
                    }, 3000);

                    $location.path('app/campaignList');
                } else if (resp.status == 0)
                {
                    $scope.step3.saving = false;
                    $("#simplewizardinwidget li").addClass("complete");
                    $("#simplewizardinwidget li").last().removeClass("complete");
                    $("#simplewizardinwidget li").last().addClass("active");
                    $scope.errorMsg = resp.message;
                }
            }).error(function () {
            });
        }
    });
});
app.controller('ClearCampaignCtrl', function ($scope, $http, site_config, $rootScope, $state) {
    $scope.clear = {};
    $scope.radioList = [
        {label: "All", value: "all"},
        {label: "Date Range", value: "date_range"},
        {label: "Keys Range", value: "keys_range"}
    ];
    $scope.clear.key_type = $scope.radioList[0].value;
    $scope.$watch('clear.start_date', function (v) {
        if (typeof (v) != 'undefined') {
            $scope.endMinDate = v;
        }
    });
    $scope.dateOptions = {
        formatYear: 'yy',
        startingDay: 1,
        showWeeks: false,
        showButtonBar: false
    };
    $scope.formats = ['dd-MMMM-yyyy', 'yyyy/MM/dd', 'dd.MM.yyyy', 'shortDate', 'MMMM dd yyyy'];
    $scope.format = $scope.formats[0];
    $scope.today = function () {
        $scope.clear.start_date = new Date();
    };
    $scope.openStartDate = function ($event) {
        $event.preventDefault();
        $event.stopPropagation();
        $scope.startOpened = true;
    };
    $scope.openEndDate = function ($event) {
        $event.preventDefault();
        $event.stopPropagation();
        $scope.endOpened = true;
    };
    $http.get(site_config.apiUrl + "api/getactivecampaignlist?id=" + $rootScope.globals.currentUser.user_id).then(function (response) {
        $scope.campaign = response.data.aaData;
        //$scope.clear.name = $scope.campaign[$scope.campaign.length];
    });
    $scope.change = function (value) {
        if (value == 'all') {
            $scope.key = false;
            $scope.clear.key_start_no = '';
            $scope.clear.key_end_no = '';
        } else {
            $scope.key = true;
        }
    }

    $scope.clearCampaign = function () {
        var startDate = new Date($scope.clear.start_date);
        $scope.clear.start_date = (startDate.getFullYear() + '-' + (startDate.getMonth() + 1) + '-' + startDate.getDate());
        var endDate = new Date($scope.clear.end_date);
        $scope.clear.end_date = (endDate.getFullYear() + '-' + (endDate.getMonth() + 1) + '-' + endDate.getDate());
        $scope.clear.user_id = $rootScope.globals.currentUser.user_id;
        
        $http({
            method: 'POST',
            url: site_config.apiUrl + 'api/clear_campaign',
            data: $scope.clear,
        }).success(function (resp) {
            if (resp.status == 1) {
                $scope.successMsg = resp.message;
                $state.go($state.current, {}, {reload: true});
            } else {
                $scope.errorMsg = resp.message;
            }
        })
    }

})
app.controller('ClearedCampaignDataCtrl', function ($scope, $http, site_config, $compile, $window, $rootScope, $modal) {
    var oTable = $('#clearedCampaignDataTable').dataTable({
        "bProcessing": true,
        ajax: {
            url: site_config.apiUrl + "api/getclearedcampaigndata",
        },
        aoColumns: [
            {mData: 'name'},
            {mData: 'type'},
            {mData: 'start_key'},
            {mData: 'end_key'},
            {mData: 'date'},
        ],
        "sDom": "Tflt<'row DTTTFooter'<'col-sm-6'i><'col-sm-6'p>>",
        "iDisplayLength": 10,
        "oTableTools": {
            "aButtons": [
                "copy", "csv", "xls", "pdf", "print"
            ],
            "sSwfPath": "assets/swf/copy_csv_xls_pdf.swf"
        },
        "language": {
            "search": "",
            "sLengthMenu": "_MENU_",
            "oPaginate": {
                "sPrevious": "Prev",
                "sNext": "Next"
            }
        },
        "aaSorting": [],
    });
});
// Please note that $modalInstance represents a modal window (instance) dependency.
// It is not the same as the $modal service used above.
app.controller('ModalInstanceCtrl', function ($scope, $modalInstance, items) {

    $scope.ok = function () {
        $modalInstance.close();
    };
    $scope.cancel = function () {
        $modalInstance.dismiss('cancel');
    };
});