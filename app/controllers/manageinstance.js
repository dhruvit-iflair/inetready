app.filter('startFrom', function () {
    return function (input, start) {
        if (input) {
            start = +start; //parse to int
            return input.slice(start);
        }
        return [];
    }
});


app.controller('InstancelistCtrl', function ($scope, $http, $location, $window, $templateCache, $timeout, site_config) {

    $http.get(site_config.apiUrl + "api/getinstanceall").success(function (response) {
        if (response.status) {
            $scope.instancenames = response.data;
            $scope.currentPage = 1; //current page
            $scope.entryLimit = 5; //max no of items to display in a page
            $scope.filteredItems = $scope.instancenames.length; //Initially for no filter  
            $scope.totalItems = $scope.instancenames.length;
        } else {
            $location.path("/login");
        }

    });
    $scope.setPage = function (pageNo) {
        $scope.currentPage = pageNo;
    };
    $scope.filter = function () {
        $timeout(function () {
            $scope.filteredItems = $scope.filtered.length;
        }, 10);
    };
    $scope.sort_by = function (predicate) {
        $scope.predicate = predicate;
        $scope.reverse = !$scope.reverse;
    };

    $scope.open = function ($event) {
        //alert('enter');
        $event.preventDefault();
        $event.stopPropagation();

        $scope.opened = true;
    };
    $scope.addinstancefn = function () {

        window.location = "#/app/createinstance";

    }



    $scope.searchdetails = function () {


        var instence_name = $scope.searchinstance;

        var jsonString = '{"instence_name":"' + instence_name + '"}';

        var obj = JSON.parse(jsonString);

        $http({
            method: 'POST',
            url: site_config.apiUrl + "api/searchinstance",
            data: {
                instence_name: obj.instence_name
            },
            headers: {'Content-Type': 'application/json'}
        })
                .then(function (response)
                {
                    if (response.data.status) {
                        $scope.instancenames = response.data.data;
                    }
                    else {
                        $location.path("/login");
                    }
                }
                );

        /*.success(function(data, status, headers, config) {
         console.log(data);
         alert(data);                
         
         $scope.instancenames = response.data.records;                                               
         
         })
         .error(function(data, status, headers, config) {
         alert(data);
         $scope.errorMsg = 'Unable to submit form';
         })*/

    }


    $http.get(site_config.apiUrl + "api/instancelist")
            .then(function (response)
            {
                if (response.data.status) {
                    $scope.instancenames = response.data.data;
                }
                else {
                    $location.path("/login");
                }

            }
            );



    $scope.updateInstanceDetails = function ()
    {

        var cinstanceid_hid = $('#cinstanceid_hid').val();
        var loginusernameInput = $('#loginusernameInput').val();
        var loginpasswordInput = $('#loginpasswordInput').val();
        var databaseusernameInput = $('#databaseusernameInput').val();
        var databasepasswordInput = $('#databasepasswordInput').val();
        var hostnameInput = $('#hostnameInput').val();
        var prefixInput = $('#prefixInput').val();
        var instancenameInput = $('#instancenameInput').val();
        var instanceurlInput = $('#instanceurlInput').val();
        var instanceipInput = $('#instanceipInput').val();
        var clientnameInput = $('#clientnameInput').val();
        var clientemailInput = $('#clientemailInput').val();
        var clientstatusInput = $('#clientstatusInput').val();
        var syncstatusInput = $('#syncstatusInput').val();
        var expiryInput = $('#expiryInput').val();
        //var defaultdriveInput       = $('#defaultdriveInput').val;
        var defaultdriveInput = $scope.defaultdriveInput;
        //alert(instancenameInput);
        var keepGoing = true;
        var regex = /^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[^\w\s]).{8,}$/;

        if ((loginusernameInput == "") || (loginpasswordInput == "") || (databaseusernameInput == "") || (databasepasswordInput == "") || (instancenameInput == "") || (clientnameInput == "") || (clientemailInput == "") || (expiryInput == "") || (defaultdriveInput == "") || (instanceurlInput == "") || (instanceipInput == ""))
        {
            keepGoing = false;
            document.getElementById('mismatch_id').innerHTML = "Please enter the mandatory fields:";

        }
        else if (keepGoing == true)
        {
            document.getElementById('mismatch_id').innerHTML = '';
            if (loginpasswordInput != "")
            {
                if (!regex.test(loginpasswordInput))
                {
                    document.getElementById('mismatch_id').innerHTML = "Password must contain minimum of eight characters mixed with at least one capital letter, one symbol and one number";
                    keepGoing = false;
                }
                else
                {
                    var jsonString = '{"loginusernameInput":"' + loginusernameInput + '","loginpasswordInput":"' + loginpasswordInput + '","databaseusernameInput":"' + databaseusernameInput + '","databasepasswordInput":"' + databasepasswordInput + '","hostnameInput":"' + hostnameInput + '","prefixInput":"' + prefixInput + '","instancenameInput":"' + instancenameInput + '","instanceurlInput":"' + instanceurlInput + '","instanceipInput":"' + instanceipInput + '","clientnameInput":"' + clientnameInput + '","clientemailInput":"' + clientemailInput + '","clientstatusInput":"' + clientstatusInput + '","syncstatusInput":"' + syncstatusInput + '","expiryInput":"' + expiryInput + '","defaultdriveInput":"' + defaultdriveInput + '","cinstanceid_hid":"' + cinstanceid_hid + '"}';

                    //alert(jsonString);



                    var obj = JSON.parse(jsonString);

                    $http({
                        method: 'POST',
                        url: site_config.apiUrl + "api/updateinstance",
                        data: {
                            cinstanceid_hid: obj.cinstanceid_hid,
                            loginusernameInput: obj.loginusernameInput,
                            loginpasswordInput: obj.loginpasswordInput,
                            databaseusernameInput: obj.databaseusernameInput,
                            databasepasswordInput: obj.databasepasswordInput,
                            hostnameInput: obj.hostnameInput,
                            prefixInput: obj.prefixInput,
                            instancenameInput: obj.instancenameInput,
                            instanceurlInput: obj.instanceurlInput,
                            instanceipInput: obj.instanceipInput,
                            clientnameInput: obj.clientnameInput,
                            clientemailInput: obj.clientemailInput,
                            clientstatusInput: obj.clientstatusInput,
                            syncstatusInput: obj.syncstatusInput,
                            expiryInput: obj.expiryInput,
                            defaultdriveInput: obj.defaultdriveInput
                        },
                        headers: {'Content-Type': 'application/json'}
                    })

                            .success(function (data, status, headers, config)
                            {
                                console.log(data);

                                $location.path("app/manageinstances");
                                $scope.errorMsg = "Updated Successfully";
                                $http.get(site_config.apiUrl + "api/instancelist")
                                        .then(function (response)
                                        {
                                            if (response.data.status) {
                                                $scope.instancenames = response.data.data;
                                            }
                                            else {
                                                $location.path("/login");
                                            }


                                        });
                            })
                            .error(function (data, status, headers, config)
                            {
                                //$scope.errorMsg = 'Unable to submit form';
                                $location.path("app/manageinstances");
                                $scope.errorMsg = "Updated Successfully";
                                $http.get(site_config.apiUrl + "api/instancelist")
                                        .then(function (response)
                                        {
                                            if (response.data.status) {
                                                $scope.instancenames = response.data.data;
                                            }
                                            else {
                                                $location.path("/login");
                                            }

                                        });
                            })
                }//json else close            
            }//loginpasswordInput if close
        }//else if(keepGoing==true) close
    }///update main close

    $scope.updateglobalDetails = function () {

        var cinstanceid_global_hid = $('#cinstanceid_global_hid').val();
        //var campaignview          = $('#campaignview').val();
        var campaignview = $('input[name=campaignview]:checked').val();
        //var regverification       = $('#regverification').val();  
        var regverification = $('input[name=regverification]:checked').val();
        //var chartview                 = $('#chartview').val();
        var chartview = $('input[name=chartview]:checked').val();
        //var ROIWidget                 = $('#ROIWidget').val();
        var ROIWidget = $('input[name=ROIWidget]:checked').val();
        //var MapView               = $('#MapView').val();
        var MapView = $('input[name=MapView]:checked').val();
        var MapViewCountry = $('#MapViewCountry').val();
        var Template = $('#Template').val();
        var BannerAdLayout = $('#BannerAdLayout').val();
        //var EncryptURL                = $('#EncryptURL').val();
        var EncryptURL = $('input[name=EncryptURL]:checked').val();
        //var VerificationStatus        = $('#VerificationStatus').val();   
        var VerificationStatus = $('input[name=VerificationStatus]:checked').val();
        //var VerificationMethod        = $('#VerificationMethod').val();
        var VerificationMethod = $('input[name=VerificationMethod]:checked').val();
        //var NotifyAdminOfReg      = $('#NotifyAdminOfReg').val();
        var NotifyAdminOfReg = $('input[name=NotifyAdminOfReg]:checked').val();
        var URLNotFoundRedirectPage = $('#URLNotFoundRedirectPage').val();
        var SquibTrackerScript = $('#SquibTrackerScript').val();
        var GoogleMapApiDetails = $('#GoogleMapApiDetails').val();
        var SquibKeyPluginCustomerRegFormName = $('#SquibKeyPluginCustomerRegFormName').val();

        var jsonString = '{"cinstanceid_global_hid":"' + cinstanceid_global_hid + '","campaignview":"' + campaignview + '","regverification":"' + regverification + '","chartview":"' + chartview + '","ROIWidget":"' + ROIWidget + '","MapView":"' + MapView + '","MapViewCountry":"' + MapViewCountry + '","Template":"' + Template + '","BannerAdLayout":"' + BannerAdLayout + '","EncryptURL":"' + EncryptURL + '","VerificationStatus":"' + VerificationStatus + '","VerificationMethod":"' + VerificationMethod + '","NotifyAdminOfReg":"' + NotifyAdminOfReg + '","URLNotFoundRedirectPage":"' + URLNotFoundRedirectPage + '","SquibTrackerScript":"' + SquibTrackerScript + '","GoogleMapApiDetails":"' + GoogleMapApiDetails + '","SquibKeyPluginCustomerRegFormName":"' + SquibKeyPluginCustomerRegFormName + '"}';

//alert(jsonString);

        var obj = JSON.parse(jsonString);

        $http({
            method: 'POST',
            url: site_config.apiUrl + "api/updateglobal",
            data: {
                cinstanceid_global_hid: obj.cinstanceid_global_hid,
                campaignview: obj.campaignview,
                regverification: obj.regverification,
                chartview: obj.chartview,
                ROIWidget: obj.ROIWidget,
                MapView: obj.MapView,
                MapViewCountry: obj.MapViewCountry,
                Template: obj.Template,
                BannerAdLayout: obj.BannerAdLayout,
                EncryptURL: obj.EncryptURL,
                VerificationStatus: obj.VerificationStatus,
                VerificationMethod: obj.VerificationMethod,
                NotifyAdminOfReg: obj.NotifyAdminOfReg,
                URLNotFoundRedirectPage: obj.URLNotFoundRedirectPage,
                SquibTrackerScript: obj.SquibTrackerScript,
                GoogleMapApiDetails: obj.GoogleMapApiDetails,
                SquibKeyPluginCustomerRegFormName: obj.SquibKeyPluginCustomerRegFormName

            },
            headers: {'Content-Type': 'application/json'}
        })

                .success(function (data, status, headers, config) {
                    console.log(data);

                    $location.path("app/manageinstances");
                    $scope.errorMsg = "Updated Successfully";
                    $http.get(site_config.apiUrl + "api/instancelist")
                            .then(function (response)
                            {
                                if (response.data.status) {
                                    $scope.instancenames = response.data.data;
                                }
                                else {
                                    $location.path("/login");
                                }

                            }
                            );
                })
                .error(function (data, status, headers, config) {
                    //$scope.errorMsg = 'Unable to submit form';
                    $location.path("app/manageinstances");
                    $scope.errorMsg = "Updated Successfully";
                    $http.get(site_config.apiUrl + "api/instancelist")
                            .then(function (response)
                            {
                                if (response.data.status) {
                                    $scope.instancenames = response.data.data;
                                }
                                else {
                                    $location.path("/login");
                                }

                            }
                            );
                })

    }


    $scope.deleteinstanceDetails = function (cinstence_id, index) {


        var deleteInstance = $window.confirm('Are you absolutely sure you want to delete?');
        if (deleteInstance)
        {
            $http.get(site_config.apiUrl + "api/deleteinstance?cinstence_id=" + cinstence_id)
                    .success(function (data) {
                        if (data.status) {
                            $scope.instancenames.splice(index, 1);
                            //$location.path("app/manageusers");
                            $scope.errorMsg = "Deleted Successfully";
                        }
                        else {
                            $location.path("/login");
                        }


                    })
        }
    }

    $scope.editinstanceDetails = function (cinstence_id, index) {
        $scope.errorMsg = '';

        document.getElementById('updateinstance_id').style.visibility = "visible";

        document.getElementById('updateinstance_id').style.position = "relative";
        document.getElementById('globalinstance_id').style.visibility = "hidden";
        //$("#updateinstance_id").parent().css({position: 'none'});
        var jsonString = '{"cinstence_id":"' + cinstence_id + '"}';

        var obj = JSON.parse(jsonString);

        $http({
            method: 'POST',
            url: site_config.apiUrl + "api/getinstance",
            data: {
                cinstence_id: obj.cinstence_id
            },
            headers: {'Content-Type': 'application/json'}
        })

                .success(function (resp, status, headers, config) {

                    if (resp.status) {
                        var data_r = resp.data.trim();
                        var res = data_r.split('////');

                        $scope.databaseusernameInput = res[6];
                        $scope.databasepasswordInput = res[1];
                        $scope.hostnameInput = res[5];
                        $scope.prefixInput = res[7];
                        $scope.instancenameInput = res[3];
                        $scope.instanceurlInput = res[4];
                        $scope.instanceipInput = res[9];
                        $scope.clientnameInput = res[0];
                        $scope.clientemailInput = res[2];
                        $('#clientstatusInput').val(res[8]);
                        $('#syncstatusInput').val(res[11]);
                        $scope.expiryInput = res[12];
                        $scope.defaultdriveInput = res[10];
                        $('#cinstanceid_hid').val(cinstence_id);
                        $scope.loginusernameInput = res[13];
                        $scope.loginpasswordInput = res[14];
                    }
                    else {
                        $location.path("/login");
                    }



                })
                .error(function (data, status, headers, config) {
                    $scope.errorMsg = 'Unable to submit form';
                })

    }

    $scope.editglobalDetails = function (cinstence_id, index) {
        $scope.errorMsg = '';

        document.getElementById('updateinstance_id').style.visibility = "hidden";

        document.getElementById('updateinstance_id').style.position = "absolute";
        document.getElementById('globalinstance_id').style.visibility = "visible";
        document.getElementById('cinstanceid_global_hid').value = cinstence_id;
        //$("#updateinstance_id").parent().css({position: 'none'});
        var jsonString = '{"cinstence_id":"' + cinstence_id + '"}';

        var obj = JSON.parse(jsonString);

        $http({
            method: 'POST',
            url: site_config.apiUrl + "api/getglobal",
            data: {
                cinstence_id: obj.cinstence_id
            },
            headers: {'Content-Type': 'application/json'}
        })

                .success(function (resp, status, headers, config) {
                    if (resp.status) {
                        var data_r = resp.data.trim();
                        var res = data_r.split('////');
                        //alert(res);
                        //$('#campaignview').val(res[1]);
                        $('input[name="campaignview"][value="' + res[1] + '"]').attr('checked', true);
                        //$('#regverification').val(res[4]);
                        $('input[name="regverification"][value="' + res[4] + '"]').attr('checked', true);
                        //$('#chartview').val(res[2]);
                        $('input[name="chartview"][value="' + res[2] + '"]').attr('checked', true);
                        //$('#ROIWidget').val(res[3]);
                        $('input[name="ROIWidget"][value="' + res[3] + '"]').attr('checked', true);
                        //$('#MapView').val(res[0]);
                        $('input[name="MapView"][value="' + res[0] + '"]').attr('checked', true);
                        //$('#MapViewCountry').val(res[4]);
                        $('input[name="MapViewCountry"][value="' + res[4] + '"]').attr('checked', true);
                        $('#Template').val(res[6]);
                        $('#BannerAdLayout').val(res[7]);
                        //$('#EncryptURL').val(res[5]);
                        $('input[name="EncryptURL"][value="' + res[5] + '"]').attr('checked', true);
                        //$('#VerificationStatus').val(res[8]);
                        $('input[name="VerificationStatus"][value="' + res[8] + '"]').attr('checked', true);
                        //$('#VerificationMethod').val(res[9]);
                        $('input[name="VerificationMethod"][value="' + res[9] + '"]').attr('checked', true);
                        //$('#NotifyAdminOfReg').val(res[10]);
                        $('input[name="NotifyAdminOfReg"][value="' + res[10] + '"]').attr('checked', true);
                        $('#URLNotFoundRedirectPage').val(res[13]);
                        $('#SquibTrackerScript').val(res[12]);
                        $('#GoogleMapApiDetails').val(res[14]);
                        $('#SquibKeyPluginCustomerRegFormName').val(res[11]);
                    } else {

                    }



                    //document.getElementById('cinstanceid_hid').value= cinstence_id;
                    //$location.path("app/manageusers");
                })
                .error(function (data, status, headers, config) {
                    $scope.errorMsg = 'Unable to submit form';
                })

    }



});