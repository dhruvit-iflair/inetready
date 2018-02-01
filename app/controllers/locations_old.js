'use strict';

app
        // Location Box controller 
        .controller('LocationCtrl', [
            '$rootScope', '$scope','$http','site_config', function ($rootScope, $scope, $http, site_config) {
                
             
	$scope.personData = [
		{
			"id": 0,
			"name": "Lynnette Gibson",
			"age": 75,
			"followers": 55,
			"occupation": "amet commodo",
			"from": "Michigan",
			"college": "FSU",
			"lat": 36.848384,
			"lng": -88.486336
		},
		{
			"id": 1,
			"name": "Carla Reese",
			"age": 33,
			"followers": 41,
			"occupation": "pariatur aute",
			"from": "National",
			"college": "MTU",
			"lat": 34.757467,
			"lng": -117.289205
		},
		{
			"id": 2,
			"name": "Mccarthy Blevins",
			"age": 58,
			"followers": 47,
			"occupation": "qui laborum",
			"from": "National",
			"college": "MSU",
			"lat": 40.096377,
			"lng": -118.61762
		},
		{
			"id": 3,
			"name": "Geneva Holcomb",
			"age": 62,
			"followers": 66,
			"occupation": "adipisicing nostrud",
			"from": "National",
			"college": "CMU",
			"lat": 40.113991,
			"lng": -82.080224
		},
		{
			"id": 4,
			"name": "Parker Campbell",
			"age": 41,
			"followers": 32,
			"occupation": "et sit",
			"from": "Michigan",
			"college": "UM",
			"lat": 35.552591,
			"lng": -86.801539
		},
		{
			"id": 5,
			"name": "Osborne Briggs",
			"age": 41,
			"followers": 60,
			"occupation": "ad ipsum",
			"from": "National",
			"college": "FSU",
			"lat": 32.497364,
			"lng": -115.930087
		}
		
	]

	$scope.myMap = function() {

		$scope.options = {
			zoom: 4,
			center: new google.maps.LatLng(38.810821,-95.053711),
			//mapTypeId: google.maps.MapTypeId.ROADMAP,
			styles:
					[{"featureType":"administrative","elementType":"labels.text.fill","stylers":[{"color":"#444444"}]},{"featureType":"landscape","elementType":"all","stylers":[{"color":"#f2f2f2"}]},{"featureType":"poi","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"all","stylers":[{"saturation":-100},{"lightness":45}]},{"featureType":"road.highway","elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"road.arterial","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"transit","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"all","stylers":[{"color": colors['primary-color']},{"visibility":"on"}]}]
		}

		/*
		 Load the map then markers
		 @param object settings (configuration options for map)
		 @return undefined
		 */
		function init(settings) {
			$scope.map = new google.maps.Map(document.getElementById( settings.idSelector ), $scope.options);
			$scope.markerLocation = settings.markerLocation;
			loadMarkers();
		}

		/*
		 =======
		 MARKERS
		 =======
		 */
		$scope.markers = {};
		$scope.markerList = [];

		/*
		 Load markers onto the Google Map from a provided array or demo personData (data.js)
		 @param array personList [optional] (list of people to load)
		 @return undefined
		 */
		function loadMarkers(personList) {
			// optional argument of person
			var people = $scope.personData;

			var j = 1; // for lorempixel

			for( i=0; i < people.length; i++ ) {
				var person = people[i];

				// if its already on the map, dont put it there again
				if( $scope.markerList.indexOf(person.id) !== -1 ) continue;

				$scope.lat = person.lat,
						$scope.lng = person.lng,
						$scope.markerId = person.id;

				var infoWindow = new google.maps.InfoWindow({
					maxWidth: 400
				});

				var marker = new google.maps.Marker({
					position: new google.maps.LatLng( $scope.lat, $scope.lng ),
					title: person.name,
					markerId: $scope.markerId,
					icon: $scope.markerLocation,
					map: $scope.map
				});

				$scope.markers[$scope.markerId] = marker;
				$scope.markerList.push(person.id);

				if( j > 10 ) j = 1; // for lorempixel, the thumbnail image
				$scope.content = ['<div class="map-box"><img src="http://lorempixel.com/90/90/people/',
					$scope.j, '" width="90" height="90">', '<div class="iw-text"><h4 class="margin-none">', person.name,
					'</h4>Age: ', person.age, '<br/>Followers: ', person.followers,
					'<br/>College: ', person.college, '</div></div>'].join('');
				j++; // lorempixel

				google.maps.event.addListener($scope.marker, 'click', (function (marker, content) {
					return function() {
                                            console.log('$scope.content = ' + $scope.content);
						infoWindow.setContent($scope.content);
						infoWindow.open($scope.map, $scope.marker);
					}
				})($scope.marker, $scope.content));
			}
		}

		/*
		 Remove marker from map and our list of current markers
		 @param int id (id of the marker element)
		 @return undefined
		 */
		function removePersonMarker(id) {
			if( markers[id] ) {
				markers[id].setMap(null);
				loc = markerList.indexOf(id);
				if (loc > -1) markerList.splice(loc, 1);
				delete markers[id];
			}
		}

		/*
		 ======
		 FILTER
		 ======
		 */

		// default all filters off
		$scope.filter = {
			followers: 0,
			college: 0,
			from: 0
		}
		$scope.filterMap;

		/*
		 Helper function
		 @param array a (array of arrays)
		 @return array (common elements from all arrays)
		 */
		function reduceArray(a) {
			r = a.shift().reduce(function(res, v) {
				if (res.indexOf(v) === -1 && a.every(function(a) {
					return a.indexOf(v) !== -1;
				})) res.push(v);
				return res;
			}, []);
			return r;
		}

		/*
		 Helper function
		 @param string n
		 @return bool
		 */
		function isInt(n) {
			return n % 1 === 0;
		}
		
		/*
		 Decides which filter function to call and stacks all filters together
		 @param string filterType (the property that will be filtered upon)
		 @param string value (selected filter value)
		 @return undefined
		 */
		function filterCtrl(filterType, value) {
			// result array
			$scope.results = [];
			if( isInt(value) ) {
				filter[filterType] = parseInt(value);
			} else {
				filter[filterType] = value;
			}

			for( k in filter ) {
				if( !filter.hasOwnProperty(k) && !( filter[k] !== 0 ) ) {
					// all the filters are off
					loadMarkers();
					return false;
				} else if ( filter[k] !== 0 ) {
					// call filterMap function and append to r array
					results.push( filterMap[k]( filter[k] ) );
				} else {
					// fail silently
				}
			}

			if( filter[filterType] === 0 ) results.push( personData );

			/*
			 if there is 1 array (1 filter applied) set it,
			 else find markers that are common to every results array (pass every filter)
			 */
			if( results.length === 1 ) {
				results = results[0];
			} else {
				results = reduceArray( results );
			}
			loadMarkers( results );
		}

		/*
		 The keys in this need to be mapped 1-to-1 with the keys in the filter variable.
		 */
		$scope.filterMap = {
			followers: function( value ) {
				return filterIntsLessThan('followers', value);
			},

			college: function( value ) {
				return filterByString('college', value);
			},

			from: function( value ) {
				return filterByString('from', value);
			}
		}

		/*
		 Filters marker data based upon a string match
		 @param string dataProperty (the key that will be filtered upon)
		 @param string value (selected filter value)
		 @return array (people that made it through the filter)
		 */
		function filterByString( dataProperty, value ) {
			$scope.people = [];

			for( $scope.i=0; i < personData.length; i++ ) {
				$scope.person = personData[i];
				if( person[dataProperty] == value ) {
					people.push( person );
				} else {
					removePersonMarker( person.id );
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
		function filterIntsLessThan( dataProperty, value ) {
			$scope.people = [];

			for( $scope.i=0; i < $scope.personData.length; i++ ) {
				$scope.person = $scope.personData[i];
				if( $scope.person[dataProperty] > value ) {
					$scope.people.push( $scope.person )
				} else {
					removePersonMarker( $scope.person.id );
				}
			}
			return people;
		}

		// Takes all the filters off
		function resetFilter() {
			filter = {
				followers: 0,
				college: 0,
				from: 0
			}
		}

		return {
			init: init,
			loadMarkers: loadMarkers,
			filterCtrl: filterCtrl,
			resetFilter: resetFilter
		};
	}();



	
		$scope.mapConfig = {
			idSelector: 'map-canvas',
			markerLocation: 'lib/jquery/img/marker.png'
		}
		$scope.myMap.init($scope.mapConfig);

		
	

                
            }    
            
        ]);