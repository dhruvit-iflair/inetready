'use strict';

app
    // Location Box controller 
    .controller('LocationCtrl', ['$timeout', '$rootScope', '$scope', '$http', 'site_config', '$compile', function ($timeout, $rootScope, $scope, $http, site_config, $compile) {
        $scope.cluster = false
        $scope.personData = [];
        $scope.test=false;
        $scope.people = [];
        $scope.options = {
            age: [
                {value: "0", name: 'All'},
                {value: "10", name: '10+'},
                {value: "20", name: "20+"},
                {value: "30", name: "30+"},
                {value: "40", name: "40+"},
                {value: "50", name: "50+"}
            ],
            gender: [
                {value: "0", name: 'Both'},
                {value: "male", name: 'Male'},
                {value: "female", name: "Female"}
            ],
            date: [
                {value: "today", name: 'Today'},
                {value: "0", name: '1 Week'},
                {value: "2w", name: "2 Weeks"},
                {value: "1m", name: "1 Month"},
                {value: "3m", name: "3 Months"},
                {value: "6m", name: "6 Months"},
                {value: "12m", name: "12 Months"},
            ],
        }

        /* GET DEVICE TYPE FROM DATABASE */
        $http({
            method: 'GET',
            url: site_config.apiUrl + "api/getdevicetype",
            async: false,
            headers: {'Content-Type': 'application/json'}
        }).success(function (resp) {
            $scope.options.device = resp.data;
            $scope.filter.device = $scope.options.device[0];
        }).error(function () {
            console.log("Devices Couldn't found");
        });

        // default all filters off
        $scope.filter = {
            age: $scope.options.age[0],
            gender: $scope.options.gender[0],
            date: $scope.options.date[1],
        }

        $scope.myMap = function () {
            var markers = {};
            var markersClusterList = {};
            var markerList = [];
            var markerLocation;
            var map;
            var markerCluster;
            var options = {
                zoom: 1,
                center: new google.maps.LatLng(38.810821, -95.053711),
                //mapTypeId: google.maps.MapTypeId.ROADMAP,
                styles: [
                    {"featureType": "administrative", "elementType": "labels.text.fill", "stylers": [{"color": "#444444"}]}, {"featureType": "landscape", "elementType": "all", "stylers": [{"color": "#f2f2f2"}]}, {"featureType": "poi", "elementType": "all", "stylers": [{"visibility": "off"}]}, {"featureType": "road", "elementType": "all", "stylers": [{"saturation": -100}, {"lightness": 45}]}, {"featureType": "road.highway", "elementType": "all", "stylers": [{"visibility": "simplified"}]}, {"featureType": "road.arterial", "elementType": "labels.icon", "stylers": [{"visibility": "off"}]}, {"featureType": "transit", "elementType": "all", "stylers": [{"visibility": "off"}]}, {"featureType": "water", "elementType": "all", "stylers": [{"color": colors['primary-color']}, {"visibility": "on"}]}
                ]
            }

            /*
                Load the map then markers
                @param object settings (configuration options for map)
                @return undefined
                */
            function init(settings) {
                map = new google.maps.Map(document.getElementById(settings.idSelector), options);
                markerLocation = settings.markerLocation;
                $scope.test=true;
            }

            /*
                Load markers onto the Google Map from a provided array or demo personData (data.js)
                @param array personList [optional] (list of people to load)
                @return undefined
                */
            function loadMarkers(result) {
                // optional argument of person
                $scope.people = (typeof result !== 'undefined') ? result : $scope.personData;
                var j = 1; // for lorempixel
                var bounds = new google.maps.LatLngBounds();
                if($scope.cluster == false) {
                    for (i = 0; i < $scope.people.length; i++) {
                        var person = $scope.people[i];
                        var lat = person.lat,
                                lng = person.lng,
                                markerId = person.id,
                                title = person.admin_name;
                        var infoWindow = new google.maps.InfoWindow({
                            maxWidth: 300
                        });

                        if($scope.cluster == false) {
                            var marker = new google.maps.Marker({
                                position: new google.maps.LatLng(lat, lng),
                                title: title,
                                markerId: markerId,
                                icon: markerLocation,
                                map: map,
                                draggable: true
                            });
                            markers[markerId] = marker;
                            markerList.push(person.id);
                        } 
                        if (j > 10)
                            j = 1; // for lorempixel, the thumbnail image
                        var content = ['<div class="map-box"><img width="50" height="50" src="', person.profile_image, '">', '<div class="iw-text"><h5 class="margin-none">', person.admin_name, '</br>', person.organization, '</br>', person.city, ', ', person.region, ' ', person.zipcode, '<br>Visit: ', person.visit, '<br>Campaign: ', person.campaign, '<br>SquibKey: ', person.squibkey, ' <br>Last Visit: ', person.last_visited, '<br><img width="15" height="15" src="', person.device_icon, '"> &nbsp;<img width="15" height="15" src="', person.browser, '"><br></h6>Domain Url: ', person.domain, '</h6></h5></div></div>'].join('');
                        j++; // lorempixel

                        google.maps.event.addListener(marker, 'click', (function (marker, content) {
                            return function () {
                                alert('Helloo')
                                infoWindow.setContent(content);
                                infoWindow.open(map, marker);
                            }
                        })(marker, content));
                        
                    }
                }
                else {
                    var map1 = new google.maps.Map(document.getElementById('map-canvas'), {
                        zoom: 2,
                        center: {lat: 38.810821, lng: -95.053711},
                        styles: [
                            {"featureType": "administrative", "elementType": "labels.text.fill", "stylers": [{"color": "#444444"}]}, {"featureType": "landscape", "elementType": "all", "stylers": [{"color": "#f2f2f2"}]}, {"featureType": "poi", "elementType": "all", "stylers": [{"visibility": "off"}]}, {"featureType": "road", "elementType": "all", "stylers": [{"saturation": -100}, {"lightness": 45}]}, {"featureType": "road.highway", "elementType": "all", "stylers": [{"visibility": "simplified"}]}, {"featureType": "road.arterial", "elementType": "labels.icon", "stylers": [{"visibility": "off"}]}, {"featureType": "transit", "elementType": "all", "stylers": [{"visibility": "off"}]}, {"featureType": "water", "elementType": "all", "stylers": [{"color": colors['primary-color']}, {"visibility": "on"}]}
                        ]
                    });
                    angular.forEach($scope.people, function(item) {
                        var marker = new google.maps.Marker({
                            position: new google.maps.LatLng(parseFloat(item.lat), parseFloat(item.lng)),
                            id: item.id
                        });
                        marker.addListener('click', function() {
                            alert('Cluster markers')
                        })
                        bounds.extend(marker.getPosition());
                        markerList.push(marker);
                    })
                    if(markerCluster) markerCluster.clearMarkers();
                    markerCluster = new MarkerClusterer(map1, markerList, {
                        maxZoom: 8,
                        imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'  
                    })

                    // map.setCenter(bounds.getCenter());
                    // map.fitBounds(bounds);
                    // $scope.myMap = map;

                    // var labels = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    // $scope.people.map(function(location, i) {
                    //     var infoWindow = new google.maps.InfoWindow;
                    //     var markerId = location.id
                    //     var latLng = new google.maps.LatLng(parseFloat(location.lat), parseFloat(location.lng));
                    //     var marker1 = new google.maps.Marker({
                    //         position: latLng,
                    //         title: location.admin_name,
                    //         markerId : markerId,
                    //         icon: 'lib/jquery/img/marker.png',
                    //         draggable: true
                    //     })

                    //     markersClusterList[markerId] = marker1;
                    //     markerList.push(marker1);
                    // })
                    
                    // var mcOptions = {
                    //     // maxZoom: 8,
                    //     imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'
                    // }
                    // markerCluster = new MarkerClusterer(map, markerList,mcOptions);
                    
                    // google.maps.event.addListener(markerCluster, 'clusterclick', function(cluster) {
                        // markerCluster.clearMarkers();
                        // map1.setCenter(bounds.getCenter());
                        // map1.fitBounds(bounds);
                        // map.setCenter(cluster.getCenter());
                        // map.setZoom(map.getZoom());
                        // cluster.markerClusterer_.maxZoom_ = 2
                    // });
                }
            }

            /*
                Remove marker from map and our list of current markers
                @param int id (id of the marker element)
                @return undefined
                */
            function removePersonMarker(id)
            {
                if (markers[id]) {
                    markers[id].setMap(null);
                    var loc = markerList.indexOf(id);
                    if (loc > -1)
                        markerList.splice(loc, 1);
                    delete markers[id];
                }
            }

            /*
                Helper function
                @param array a (array of arrays)
                @return array (common elements from all arrays)
                */
            function reduceArray(a)
            {
                var r = a.shift().reduce(function (res, v) {
                    if (res.indexOf(v) === -1 && a.every(function (a) {
                        return a.indexOf(v) !== -1;
                    }))
                        res.push(v);
                    return res;
                }, []);
                return r;
            }

            /*
                Decides which filter function to call and stacks all filters together
                @param string filterType (the property that will be filtered upon)
                @param string value (selected filter value)
                @return undefined
                */
            function filterCtrl(filterType, value)
            {
                var results = [];
                for (var k in $scope.filter)
                {
                    if (!$scope.filter.hasOwnProperty(k) && !($scope.filter[k].value !== 0))
                    {
                        loadMarkers();
                        return false;
                    } else if ($scope.filter[k].value != 0)
                    {
                        // call filterMap function and append to r array
                        results.push($scope.filterMap[k]($scope.filter[k].value));
                    } else
                    {
                        results.push($scope.personData);
                    }
                }

                if (results.length === 1)
                {
                    results = results[0];
                } else {
                    results = reduceArray(results);
                }
                loadMarkers(results);
            }

            /*
                The keys in this need to be mapped 1-to-1 with the keys in the filter variable.
                */
            $scope.filterMap = {
                age: function (value) {
                    return filterIntsLessThan('age', value);
                },
                gender: function (value) {
                    return filterByString('gender', value);
                },
                device: function (value) {
                    return filterByString('device', value);
                },
                date: function (value) {
                    return filterByDate('date', value);
                },
            }

            /*
                Filters marker data based upon a date match
                @param string dataProperty (the key that will be filtered upon)
                @param string value (selected filter value)
                @return array (people that made it through the filter)
                */
            function filterByDate(dataProperty, value)
            {
                var people = [];
                var filterDate = returnDate(value);
                var d2 = new Date(filterDate);
                for (var i = 0; i < $scope.personData.length; i++) {
                    var person = $scope.personData[i];
                    var d1 = new Date(person[dataProperty]);
                    if (d1.getTime() >= d2.getTime()) {
                        people.push(person);
                    } else {
                        removePersonMarker(person.id);
                    }
                }
                return people;
            }

            /* CALCULATE DATE ACCORDING TO THE FILTER */
            function returnDate(value) {
                var curDate = new Date();
                switch (value) {
                    case "today":
                        return curDate.getFullYear() + '-' + ('0' + (curDate.getMonth() + 1)).slice(-2) + '-' + ('0' + curDate.getDate()).slice(-2);
                        break;
                    case "1w":
                        curDate.setDate(curDate.getDate() - 7);
                        return curDate.getFullYear() + '-' + ('0' + (curDate.getMonth() + 1)).slice(-2) + '-' + ('0' + curDate.getDate()).slice(-2);
                        break;
                    case "2w":
                        curDate.setDate(curDate.getDate() - 14);
                        return curDate.getFullYear() + '-' + ('0' + (curDate.getMonth() + 1)).slice(-2) + '-' + ('0' + curDate.getDate()).slice(-2);
                        break;
                    case "1m":
                        curDate.setMonth(curDate.getMonth() - 0);
                        return curDate.getFullYear() + '-' + ('0' + curDate.getMonth()).slice(-2) + '-0' + 1;
                        break;
                    case "3m":
                        curDate.setMonth(curDate.getMonth() - 2);
                        return curDate.getFullYear() + '-' + ('0' + curDate.getMonth()).slice(-2) + '-0' + 1;
                        break;
                    case "6m":
                        curDate.setMonth(curDate.getMonth() - 5);
                        return curDate.getFullYear() + '-' + ('0' + curDate.getMonth()).slice(-2) + '-0' + 1;
                        break;
                    case "12m":
                        curDate.setMonth(curDate.getMonth() - 11);
                        return curDate.getFullYear() + '-' + ('0' + curDate.getMonth()).slice(-2) + '-0' + 1;
                        break;

                }
            }

            /*
                Filters marker data based upon a string match
                @param string dataProperty (the key that will be filtered upon)
                @param string value (selected filter value)
                @return array (people that made it through the filter)
                */
            function filterByString(dataProperty, value)
            {
                var people = [];
                for (var i = 0; i < $scope.personData.length; i++) {
                    var person = $scope.personData[i];
                    if (person[dataProperty] == value) {
                        people.push(person);
                    } else {
                        removePersonMarker(person.id);
                    }
                }
                return people;
            }

            /*
                Filters out integers that are under the provided value
                @param string dataProperty (the key that will be filtered upon)
                @param int value (selected filter value)
                @return array (people that made it through the filter)
                */
            function filterIntsLessThan(dataProperty, value)
            {
                var people = [];
                for (var i = 0; i < $scope.personData.length; i++) {
                    var person = $scope.personData[i];
                    if (person[dataProperty] > value) {
                        people.push(person)
                    } else {
                        removePersonMarker(person.id);
                    }
                }
                return people;
            }

            // Takes all the filters off
            function resetFilter()
            {
                $scope.filter = {
                    age: $scope.options.age[0],
                    gender: $scope.options.gender[0],
                    device: $scope.options.device[0],
                    date: $scope.options.date[1],
                }
            }

            /*
                * RESET ALL MARKER ON THE MAP
                * @returns {undefined}
                */
            function resetMarker()
            {
                if($scope.cluster == false) {
                    $.each(markers, function (index, value) {
                        markers[index].setMap(null)
                    });
                    $scope.cluster = true
                    markerList = [];
                    loadMarkers();
                } else {
                    markerCluster.clearMarkers();
                    markerList = [];
                    loadMarkers();
                }
            }
            return {
                init: init,
                loadMarkers: loadMarkers,
                filterCtrl: filterCtrl,
                resetFilter: resetFilter,
                resetMarker: resetMarker
            };
        }();

        $scope.filterByAge = function ()
        {
            $scope.myMap.filterCtrl('age', $scope.filter.age.value);
        }

        $scope.filterByGender = function ()
        {
            $scope.myMap.filterCtrl('gender', $scope.filter.gender.value);
        }

        $scope.filterBydevice = function ()
        {
            $scope.myMap.filterCtrl('device', $scope.filter.device.value);
        }

        $scope.filterByDate = function ()
        {
            $scope.myMap.filterCtrl('date', $scope.filter.date.value);
        }

        $scope.clearFilter = function ()
        {
            $scope.myMap.resetFilter();
            $scope.myMap.loadMarkers();
            $scope.myMap.filterCtrl('date', '1w');
        }

        $scope.result = {};

        var oTable = $('#visitorTable').dataTable({
            "bProcessing": true,
            "scrollX": true,
            //"sScrollY": "750px",
            ajax: {
                url: site_config.apiUrl + "api/getvisitorfortable?user_id=" + $rootScope.globals.currentUser.user_id,
                headers: {
                    'Authorization': 'Basic ' + $rootScope.globals.currentUser.encryption
                }
            },
            "serverSide": true,
            "aoColumns": [
                {
                    "bSortable": false,
                    "mData": null,
                    "fnCreatedCell": function (nTd, sData, oData, iRow, iCol)
                    {
                        $compile(nTd)($scope);
                    },
                    mRender: function (data, type, row) {
                        return '<i class="fa fa-plus-square-o row-details" id="' + row.id + '" ng-click="userDetail(' + row.id + ')"></i>'
                    },
                },
                {mData: 'admin_name', "bSortable": false},
                {mData: 'visit', "bSortable": false},
                {
                    "bSortable": false,
                    "mData": null,
                    "sClass": "center",
                    "fnCreatedCell": function (nTd, sData, oData, iRow, iCol)
                    {
                        $compile(nTd)($scope);
                    },
                    mRender: function (data, type, row) {
                        return '<img width="20" src="' + row.device_icon + '"/>'
                    }
                },
                {
                    "bSortable": false,
                    "mData": null,
                    "sClass": "center",
                    "fnCreatedCell": function (nTd, sData, oData, iRow, iCol)
                    {
                        $compile(nTd)($scope);
                    },
                    mRender: function (data, type, row) {
                        return '<img width="20" src="' + row.browser + '"/>'
                    }
                },
                {
                    "bSortable": false,
                    "mData": null,
                    "sClass": "center",
                    "fnCreatedCell": function (nTd, sData, oData, iRow, iCol)
                    {
                        $compile(nTd)($scope);
                    },
                    mRender: function (data, type, row) {
                        return '<img width="20" src="' + row.source + '"/>'
                    }
                },
                {mData: 'domain'},
                {mData: 'city'},
                {mData: 'region'},
                {mData: 'zipcode'},
                {mData: 'uid'},
                {mData: 'squibkey', "bSortable": false},
                {mData: 'ip_address'},
            ],
            "sDom": "Tflt<'row DTTTFooter'<'col-sm-6'i><'col-sm-6'p>>",
            "iDisplayLength": 10,
            "oTableTools": {
                "aButtons": [],
                "sSwfPath": []
            },
            "language": {
                "search": "",
                "sLengthMenu": "_MENU_",
                "oPaginate": {
                    "sPrevious": "Prev",
                    "sNext": "Next"
                }
            },
            //"aaSorting": [],
            "initComplete": function (settings, json) {
                $scope.mapConfig = {
                    idSelector: 'map-canvas',
                    markerLocation: 'lib/jquery/img/marker.png'
                }

                $scope.myMap.init($scope.mapConfig);
                $scope.personData = json.data;
                $scope.myMap.filterCtrl('date', '1w');
                $('div.dataTables_scrollBody').css('min-height', 70 * json.data.length);
                $('div.dataTables_scrollBody').css('height', '100%');
            }
        });

        $('#visitorTable').on('click', ' tbody td .row-details', function () {
            var nTr = $(this).parents('tr');
            if (oTable.fnIsOpen(nTr)) {
                /* This row is already open - close it */
                $(this).addClass("fa-plus-square-o").removeClass("fa-minus-square-o");
                oTable.fnClose(nTr);
            } else {
                /* Open this row */
                $(this).addClass("fa-minus-square-o").removeClass("fa-plus-square-o");
                oTable.fnOpen(nTr, fnFormatDetails(oTable, nTr), 'history');
            }
        });

        function fnFormatDetails(oTable, nTr)
        {   
            // $scope.cluster = true
            var aData = oTable.fnGetData(nTr);
            $scope.personData = aData.details;
            $scope.myMap.resetMarker();
            // $scope.myMap.filterCtrl('date', '1w');
            var sOut = '<table class="table table-bordered">';

            // TABLE HEAD SECTION
            sOut += '<thead>';
            sOut += '<tr>';
            sOut += '<th>Profile Image</th>';
            sOut += '<th>Visits</th>';
            sOut += '<th>Device</th>';
            sOut += '<th>Browser</th>';
            sOut += '<th>Source</th>';
            sOut += '<th>Domain</th>';
            sOut += '<th>City</th>';
            sOut += '<th>State</th>';
            sOut += '<th>Zip</th>';
            sOut += '<th>User ID</th>';
            sOut += '<th>SQUIBKey</th>';
            sOut += '<th>IP Address</th>';
            sOut += '</tr>';
            sOut += '</thead>';

            // TABLE BODY SECTION

            sOut += '<tbody>';
            for (var i = 0; i < aData.details.length; i++) {
                if (aData.details[i].profile_image) {
                    var pr_image = aData.details[i].profile_image;
                } else {
                    var pr_image = 'assets/img/avatars/default_user.png';
                }
                sOut += '<tr role="row" class="odd">';
                sOut += '<td><img src="' + pr_image + '" style="width:50px"/></td>';
                sOut += '<td>' + aData.details[i].visit + '</td>';
                sOut += '<td><img src="' + aData.details[i].device_icon + '" style="width:20px"/></td>';
                sOut += '<td><img src="' + aData.details[i].browser + '" style="width:20px"/></td>';
                sOut += '<td><img src="' + aData.details[i].source + '" style="width:20px"/></td>';
                sOut += '<td>' + aData.details[i].domain + '</td>';
                sOut += '<td>' + aData.details[i].city + '</td>';
                sOut += '<td>' + aData.details[i].region + '</td>';
                sOut += '<td>' + aData.details[i].zipcode + '</td>';
                if (aData.details[i].visitor_id == 0)
                {
                    sOut += '<td>' + aData.details[i].uid + '</td>';
                }
                else
                {
                    sOut += '<td>' + aData.details[i].uid + '</td>';
                }
                sOut += '<td>' + aData.details[i].squibkey + '</td>';
                sOut += '<td>' + aData.details[i].ip_address + '</td>';
                sOut += '</tr>';
            }
            sOut += '</tbody>';
            sOut += '</table>';
            return sOut;
        }
    }

]);