'use strict';

angular.module('ngConfig', [])
	.constant('config', {
		  //api : 'http://localhost/east_backend/public/' ,
      //api : 'http://test.eastlondonestateagents.com/api/public/',
        api : 'http://13.58.142.173:8080/api/',
	    views: '/resources/views/pages/',
      url: '/east_frontend/#!/',
	});
// Declare app level module which depends on views, and components
var ressApp = angular.module('myApp', [
  'ui.tinymce',
  'ngRoute',
  'ngConfig',
  "ngResource" ,
  'myApp.home',
//  'myApp.sell',
//  'myApp.version',
//  'myApp.Property',
])
.config(['$locationProvider', '$routeProvider', function($locationProvider, $routeProvider) {
  $locationProvider.hashPrefix('!');
  
  $routeProvider.otherwise({redirectTo: '/home'});
}]);