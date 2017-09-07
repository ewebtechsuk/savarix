

angular
    .module('myApp.property.viewProperty')
    .controller('ViewPropertyCtrl', function($scope, $http, $route, config, Properties) {
            var id = $route.current.params.property_id.split(":")[1];
            $scope.getProperty = function() {
                Properties.retrieve({id:id})
                    .$promise.then(function(data)
                {
                    $scope.property = data;
                });
            };

            $scope.getProperty();
    });