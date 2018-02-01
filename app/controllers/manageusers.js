app.controller('UserslistCtrl', function ($scope, $http, $location, $window, $stateParams, site_config, $rootScope, $compile) {


    function fnFormatDetails(oTable, nTr) {

        var aData = oTable.fnGetData(nTr);
        if (aData.profile_image) {
            var pr_image = aData.profile_image;
        } else {
            var pr_image = 'assets/img/avatars/default_user.png';
        }
        var sOut = '<table>';
        sOut += '<tr><td rowspan="5" style="padding:0 10px 0 0;"><img src="' + pr_image + '" width="128"/></td><td>Name:</td><td>' + aData.Name + '</td></tr>';
        sOut += '<tr><td width="150">Email:</td><td>' + aData.Emailid + '</td></tr>';
        sOut += '<tr><td>Role:</td><td>' + aData.Role + '</td></tr>';
        sOut += '<tr><td>Instance Name:</td><td>' + aData.instance_name + '</td></tr>';
        sOut += '<tr><td>Instance URL:</td><td><a href="' + aData.instance_url + '">' + aData.instance_url + '</a></td></tr>';
        sOut += '</table>';
        return sOut;
    }

    var oTable = $('#expandabledatatable').dataTable({
        "bProcessing": true,
        ajax: {
            url: site_config.apiUrl + "user/userall?user_id=" + $rootScope.globals.currentUser.user_id,
            headers: {
                'Authorization': 'Basic ' + $rootScope.globals.currentUser.encryption
            }
        },
        "aoColumns": [
            {
                "bSortable": false,
                "mData": null,
                "fnCreatedCell": function (nTd, sData, oData, iRow, iCol)
                {
                    $compile(nTd)($scope);
                },
                mRender: function (data, type, row) {
                    return '<i class="fa fa-plus-square-o row-details" id="' + row.Adminid + '" ng-click="userDetail(' + row.Adminid + ')"></i>'
                },
            },
            {mData: 'Name'},
//            {mData: 'Emailid'},
            {mData: 'Role'},
            {mData: 'Status'},
            {
                "bSortable": false,
                "mData": null,
                "sClass": "center",
                "fnCreatedCell": function (nTd, sData, oData, iRow, iCol)
                {
                    $compile(nTd)($scope);
                },
                mRender: function (data, type, row) {
                    if (row.Adminid != 1)
                        return '<a href="/app/edituser/' + row.Adminid + '"  class="btn btn-default btn-xs purple" ><i class="fa fa-edit"></i> </a> <a  class="btn btn-default btn-xs black delbtn" id="' + row.Adminid + '"><i class="fa fa-trash-o"></i> </a>'
                    else
                        return '<a href="/app/edituser/' + row.Adminid + '"  class="btn btn-default btn-xs purple" ><i class="fa fa-edit"></i> </a>';
                    //return '<a class="table-edit" data-id="' + row.Name + '">EDIT</a>'
                }
            }
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
        "aaSorting": []
    });


//    $scope.userDetail = function (user_id) {
//        console.log("User" + user_id);
//        if ($("#" + user_id).hasClass("fa-minus-square-o")) {
//            $("#" + user_id).addClass("fa-plus-square-o").removeClass("fa-minus-square-o");
//            $("#" + user_id).closest('tr').next().remove();
//        } else {
//            $("#" + user_id).addClass("fa-minus-square-o").removeClass("fa-plus-square-o");
//
//            $http({
//                method: 'POST',
//                url: site_config.apiUrl + "api/getuser",
//                data: {
//                    user_id: user_id
//                },
//                headers: {'Content-Type': 'application/json'}
//            }).success(function (resp) {
//                if (resp.status) {
//
//                    var sOut = '<table>';
//                    sOut += '<tr><td rowspan="5" style="padding:0 10px 0 0;"><img src="assets/img/avatars/default_user.png" width="128"/></td><td>Name:</td><td>' + resp.data.admin_name + '</td></tr>';
//                    sOut += '<tr><td width="150">Email:</td><td>' + resp.data.email_id + '</td></tr>';
//                    sOut += '<tr><td>Role:</td><td>' + resp.data.role + '</td></tr>';
//                    sOut += '<tr><td>Instance Name:</td><td>' + resp.data.instance_name + '</td></tr>';
//                    sOut += '<tr><td>Instance URL:</td><td><a href="' + resp.data.instance_url + '">' + resp.data.instance_url + '</a></td></tr>';
//                    sOut += '</table>';
//                    $("#" + user_id).closest('tr').after('<tr class="details"><td colspan="6" >' + sOut + '</td></tr>');
//
//                }
//            }).error(function () {
//                $scope.errorMsg = 'Unable to submit form';
//            })
//        }
//
//
//    };

    $('#expandabledatatable').on('click', ' tbody td .row-details', function () {
        //var oTable=$('#expandabledatatable table').dataTable();
        var nTr = $(this).parents('tr')[0];

        if (oTable.fnIsOpen(nTr)) {
            //alert(1)
            /* This row is already open - close it */
            $(this).addClass("fa-plus-square-o").removeClass("fa-minus-square-o");
            oTable.fnClose(nTr);
        } else {
            //alert(2)
            /* Open this row */
            $(this).addClass("fa-minus-square-o").removeClass("fa-plus-square-o");
            ;
            oTable.fnOpen(nTr, fnFormatDetails(oTable, nTr), 'details');
        }
    });

    $("#expandabledatatable tbody").delegate(".delbtn", "click", function () {
        var deleteUser = $window.confirm('Are you absolutely sure you want to delete?');
        if (deleteUser)
        {
            var id = this.id; //getting the ID of the pressed button
            var row = $(this).closest("tr").get(0);
            var iPos = oTable.fnGetPosition(row);
            if (iPos !== null) {
                $http.get(site_config.apiUrl + "user/deleteuser?id=" + id).success(function (data) {
                    $scope.errorMsg = "Deleted Successfully";
                    oTable.fnDeleteRow(iPos);//delete row
                })

            }
        }
    });


//    $scope.deleteDetails = function (id, index) {
//
//        var deleteUser = $window.confirm('Are you absolutely sure you want to delete?');
//        if (deleteUser)
//        {
//            oTable.fnDeleteRow(index);
////            $http.get(site_config.apiUrl + "user/deleteuser?id=" + id).success(function (data) {
////                $scope.errorMsg = "Deleted Successfully";
////                  //var aPos = oTable.fnGetPosition( this );
////                  alert(index);
////                oTable.fnDeleteRow(index);
////            })
//
//        }
//    }



});
app.controller('EditUserCtrl', function ($scope, $http, $location, $window, $stateParams, site_config, $rootScope, $compile) {

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


    $http.get(site_config.apiUrl + "api/getcountries").then(function (response)
    {
        if (response.status) {
            $scope.countries = response.data.data;
        }
    });

    $http.get(site_config.apiUrl + "api/socialnetworks").then(function (response)
    {
        if (response.status) {
            $scope.social_networks = response.data.data;
        }
    });
//    $scope.reseller_list = {};
    $scope.client_list = {};

    if ($rootScope.globals.currentUser.user_role == "reseller") {
        $http.get(site_config.apiUrl + "user/getusersbyrole?role=client&parent_id=" + $rootScope.globals.currentUser.user_id).then(function (response)
        {
            if (response.status) {
                $scope.client_list = response.data.data;
            }
        });
    }

    $scope.getclients = function () {
        if ($scope.user.reseller_id) {
            $http.get(site_config.apiUrl + "user/getusersbyrole?role=client&parent_id=" + $scope.user.reseller_id).then(function (response)
            {
                if (response.status) {
                    $scope.client_list = response.data.data;
                }
            });
        }
    }
    $http.get(site_config.apiUrl + "user/getusersbyrole?role=reseller").then(function (response)
    {
        if (response.status) {
            $scope.reseller_list = response.data.data;
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
        if ($scope.user.url_type == 0) {
            if ($scope.user.instance_name && ($scope.user.role == 'admin' || $scope.user.role == 'reseller'))
                $scope.user.instance_url = "http://" + $scope.user.instance_name + "." + site_config.instanceURL;
            else if ($scope.user.instance_name && $scope.user.role == 'client')
                $scope.user.instance_url = ($scope.get_vanity_url()).replace("http://", "http://" + $scope.user.instance_name + ".");
            else if ($scope.user.instance_name && $scope.user.role == 'user')
                $scope.user.instance_url = ($scope.get_vanity_url()).replace("http://", "http://" + $scope.user.instance_name + ".");
            else
                $scope.user.instance_url = "";
        }

    };

    if ($stateParams.user_id) {
        var user_id = $stateParams.user_id;
        // Get existing user details
        $http({
            method: 'POST',
            url: site_config.apiUrl + "user/getuser",
            data: {
                user_id: user_id
            },
            headers: {'Content-Type': 'application/json'}
        }).success(function (resp) {
            if (resp.status) {
                $scope.user = resp.data;
                if (!$rootScope.user_permissions[10].status) {
                    $scope.user.url_type = 0;
                    $scope.user.instance_url = $scope.user.default_url;
                }
                $scope.user.passwd = "";
                $scope.user.user_social_network.push({});
                if ($scope.user.reseller_id && $scope.user.role == 'user') {
                    $http.get(site_config.apiUrl + "user/getusersbyrole?role=client&parent_id=" + $scope.user.reseller_id).then(function (response)
                    {
                        if (response.status) {
                            $scope.client_list = response.data.data;
                        }
                    });
                }



            }
        }).error(function () {
            $scope.errorMsg = 'Unable to submit form';
        })

        // Update existing user details
        $scope.updateUser = function () {
            $http({
                method: 'POST',
                url: site_config.apiUrl + "user/updateuser",
                data: $scope.user,
                headers: {'Content-Type': 'application/json'}
            }).success(function (resp) {
                if (resp.status) {
                    $location.path("app/manageusers");
                    $scope.errorMsg = resp.data;
                } else {
                    $scope.errorMsg = resp.data;
                }
            }).error(function () {
                $scope.errorMsg = 'Unable to submit form';
            })
        }
    }
});
app.controller('AddUserCtrl', function ($scope, $http, $location, $window, $stateParams, site_config, $rootScope, $compile) {
    $scope.user = {};
    $scope.user.country_code = 'US';

    if (!$rootScope.user_permissions[10].status) {
        $scope.user.url_type = 0;
    }

    $scope.user.user_social_network = [{}];
    $scope.user.about_me = "Welcome to my SquibCard where you can connect with all my social networks and contact information";

    //$scope.user.ip_address = '1234565';

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
    $http.get(site_config.apiUrl + "api/getcountries").then(function (response)
    {
        if (response.status) {
            $scope.countries = response.data.data;
        }
    });

    $http.get(site_config.apiUrl + "api/socialnetworks").then(function (response)
    {
        if (response.status) {
            $scope.social_networks = response.data.data;
        }
    });
//    $scope.reseller_list = {};
    $scope.client_list = {};

    if ($rootScope.globals.currentUser.user_role == "reseller") {
        $http.get(site_config.apiUrl + "user/getusersbyrole?role=client&parent_id=" + $rootScope.globals.currentUser.user_id).then(function (response)
        {
            if (response.status) {
                $scope.client_list = response.data.data;
            }
        });
    }

    $scope.getclients = function () {
        if ($scope.user.reseller_id) {
            $http.get(site_config.apiUrl + "user/getusersbyrole?role=client&parent_id=" + $scope.user.reseller_id).then(function (response)
            {
                if (response.status) {
                    $scope.client_list = response.data.data;
                }
            });
        }
    }

    $http.get(site_config.apiUrl + "user/getusersbyrole?role=reseller").then(function (response)
    {
        if (response.status) {

            $scope.reseller_list = response.data.data;
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
        if ($scope.user.url_type == 0) {
            if ($scope.user.instance_name && ($scope.user.role == 'admin' || $scope.user.role == 'reseller'))
                $scope.user.instance_url = "http://" + $scope.user.instance_name + "." + site_config.instanceURL;
            else if ($scope.user.instance_name && $scope.user.role == 'client')
                $scope.user.instance_url = ($scope.get_vanity_url()).replace("http://", "http://" + $scope.user.instance_name + ".");
            else if ($scope.user.instance_name)
                $scope.user.instance_url = ($scope.get_vanity_url()).replace("http://", "http://" + $scope.user.instance_name + ".");
            else
                $scope.user.instance_url = "";
        }

    };


    $scope.addUser = function () {

        $scope.user.parent_id = $rootScope.globals.currentUser.user_id;
        if ($rootScope.globals.currentUser.user_role == "client") {
            $scope.user.role = 'user';
        }
        $scope.user.domain_url = $rootScope.globals.currentUser.instance_url;
        $http({
            method: 'POST',
            url: site_config.apiUrl + "user/createuser",
            data: $scope.user,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        }).success(function (resp) {
            if (resp.data.trim() === 'correct') {
                $scope.errorMsg = "User created Successfully";
                $location.path("/app/manageusers");
            } else {
                $scope.errorMsg = "User already exist.";
            }
        })
    }


});
