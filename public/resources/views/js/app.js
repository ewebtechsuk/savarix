'use strict';

angular.module('ngConfig', [])
	.constant('config', {
        //api : 'http://localhost:8000/api/',
        api : 'http://13.58.142.173:8080/api/',
	    views: '/resources/views/pages/',
        url: '/east_frontend/#!/',
	});

// Declare app level module which depends on views, and components
var savirixApp = angular.module('myApp', [
  'ui.tinymce',
  'ngRoute',
  'ngConfig',
  "ngResource" ,
  'myApp.home',
  'myApp.property'
])
.config(['$locationProvider', '$routeProvider', function($locationProvider, $routeProvider) {
  $locationProvider.hashPrefix('!');
  
  $routeProvider.otherwise({redirectTo: '/home'});
}]);