angular
	.module('myApp')
	.config(['$routeProvider', 'config', function($routeProvider, config) {
	      $routeProvider
              .when('/home', {
                  templateUrl: '../resources/views/pages/home/home.html',
                  controller: 'HomeCtrl'
              })
			  .when('/property', {
	        		templateUrl: '../resources/views/pages/property/ListProperty/list-property.html',
	        		controller: 'ListPropertyCtrl'
	      		})
              .when('/add-property', {
                  	templateUrl: '../resources/views/pages/property/AddProperty/add-property.html',
                  	controller: 'AddPropertyCtrl'
              	})
              .when('/edit-property/:property_id', {
                  	templateUrl: '../resources/views/pages/property/EditProperty/edit-property.html',
                  	controller: 'EditPropertyCtrl'
              })
			  .when('/view-property/:property_id', {
			  	  	templateUrl: '../resources/views/pages/property/ViewProperty/view-property.html',
				  	controller: 'ViewPropertyCtrl'
			  });
	}]);