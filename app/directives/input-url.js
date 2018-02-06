//URL Input Validation
angular.module('app')
   .directive('input', function () {
                return {
                    require: '?ngModel',
                    priority: 0,
                    link: function (scope, element, attrs, ngModel) {
                        function allowSchemelessUrls() {
                            // Match Django's URL validator, which allows schemeless urls.
                            var URL_REGEXP = /^((?:http|ftp)s?:\/\/)(?:(?:[A-Z0-9](?:[A-Z0-9-]{0,61}[A-Z0-9])?\.)+(?:[A-Z]{2,6}\.?|[A-Z0-9-]{2,}\.?)|localhost|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})(?::\d+)?(?:\/?|[\/?]\S+)$/i;

                            // Silently prefixes schemeless URLs with 'http://' when 
                            // converting a view value to model value.    
                            ngModel.$parsers.unshift(function (value) {
                                if (!URL_REGEXP.test(value) && URL_REGEXP.test('http://' + value)) {
                                    return 'http://' + value;
                                } else {
                                    return value;
                                }
                            });

                            ngModel.$validators.url = function (value) {
                                return ngModel.$isEmpty(value) || URL_REGEXP.test(value);
                            };
                        }

                        if (ngModel && attrs.type === 'url') {
                            allowSchemelessUrls();
                        }
                    }
                }

            });