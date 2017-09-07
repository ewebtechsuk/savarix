'use strict';

angular
  .module('myApp.property.listProperty', ['ngRoute'])
  .controller('ListPropertyCtrl', ['$scope', '$http', 'config', 'Properties',
    function($scope, $http, config, Properties) {

      $scope.getProperties = function () {
          $scope.properties = Properties.query();
          console.log($scope.properties);
      }

      $scope.getProperties();
    
  }]);