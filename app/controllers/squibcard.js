'use strict';
app.controller('SquibCardCtrl', [
    '$rootScope', '$scope', '$http', 'site_config', '$modal', function ($rootScope, $scope, $http, site_config, $modal) {

        
        $scope.toggleModal = function () {
            $scope.$uploadModalInstance = $modal.open({
                templateUrl: 'views/locations.html',
                controller: 'LocationCtrl',
                size: 'lg',
                scope: $scope
            });

        };


        $http.get(site_config.apiUrl + "squibcard/getsquibcards").then(function (response)
        {
            if (response.status) {
                $scope.squibcards = response.data.data;
            }
        });

        $scope.squibcardStatus = function () {
            $http({
                method: 'POST',
                url: site_config.apiUrl + "squibcard/changestatus",
                data: $scope.squibcards,
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            })

                    .success(function (response) {

                        if (response.data.status) {
                            $scope.errorMsg = response.data.data;
                            $http.get(site_config.apiUrl + "api/userpermissions?role=" + response.data.user_role + "&user_id=" + response.data.user_id)
                                    .then(function (response)
                                    {
                                        if (response.data.status) {
                                            $rootScope.user_permissions = response.data.data;
                                            //$scope.user_id = $scope.names[0];
                                        }
                                    });
                        } else {
                            $scope.errorMsg = response.data.data;
                        }
                    })
        }

    }
    
    
]);


app.directive("flipper", function () {
    return {
        restrict: "E",
        template: "<div class='flipper' ng-transclude ng-class='{ flipped: flipped }'></div>",
        transclude: true,
        scope: {
            flipped: "="
        }
    };
});

app.directive("front", function () {
    return {
        restrict: "E",
        template: "<div class='front tile' ng-transclude></div>",
        transclude: true
    };
});

app.directive("back", function () {
    return {
        restrict: "E",
        template: "<div class='back tile' ng-transclude></div>",
        transclude: true
    }
});  

  $scope.likeDislike = function (like) {
        $scope.userlike = {};
        if (typeof ($rootScope.globals.currentUser) != 'undefined') {
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
                console.log(resp.data);
                $scope.user.likes = resp.data.likes;
                $scope.user.dislikes = resp.data.dislikes;
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
                console.log(resp.data);
                $scope.user.comments = resp.data.comments;
                $scope.usercomment.comment = "";
            }).error(function () {
                console.log('Unable to save visitors');
            });
        } else {
            $scope.login_register();
        }

    };