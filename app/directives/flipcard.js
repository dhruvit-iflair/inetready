//Maximize Widget
        angular.module('app')
        .directive("flipper", function () {
            return {
                restrict: "E",
                template: "<div class='flipper' ng-transclude ng-class='{ flipped: flipped }'></div>",
                transclude: true,
                scope: {
                    flipped: "="
                }
            };
        });

//Collapse Widget
angular.module('app')
        .directive("front", function () {
            return {
                restrict: "E",
                template: "<div class='front tile' ng-transclude></div>",
                transclude: true
            };
        });


//Config Widget
angular.module('app')
        .directive("back", function () {
            return {
                restrict: "E",
                template: "<div class='back tile' ng-transclude></div>",
                transclude: true
            }
        });

angular.module('app').directive('onFinishRenderComment', function ($timeout) {
    return {
        //restrict: 'A',
        link: function (scope, element, attr) {
            if (scope.$last === true) {
                var additionalHeight = 0;
                if ($(window).width() < 531)
                    additionalHeight = 45;
                var position = 'right';
                $('.chatbar-contacts .contacts-list').slimscroll({
                    position: position,
                    size: '4px',
                    color: scope.settings.color.themeprimary,
                    //height: $(window).height() - (86 + additionalHeight),
                    height:300,
                });
            }
        }
    }
});
angular.module('app').directive('onFinishRenderCampaign', function ($timeout) {
    return {
        //restrict: 'A',
        link: function (scope, element, attr) {
            if (scope.$last === true) {
                var additionalHeight = 0;
                if ($(window).width() < 531)
                    additionalHeight = 45;
                var position = 'right';
                $('#campaignlist').slimscroll({
                    position: position,
                    size: '4px',
                    color: scope.settings.color.themeprimary,
                    //height: $(window).height() - (86 + additionalHeight),
                    height: 401,
                });
            }
        }
    }
})
angular.module('app').directive('onFinishRenderInstance', function ($timeout) {
    return {
        //restrict: 'A',
        link: function (scope, element, attr) {
            //if (scope.$last === true) {
                var additionalHeight = 0;
                if ($(window).width() < 531)
                    additionalHeight = 45;
                var position = 'right';
                $('#instanceList').slimscroll({
                    position: position,
                    size: '4px',
                    color: scope.settings.color.themeprimary,
                    //height: $(window).height() - (86 + additionalHeight),
                    height: 340,
                });
            //}
        }
    }
})