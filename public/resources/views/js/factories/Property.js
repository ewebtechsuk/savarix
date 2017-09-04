angular.module( 'myApp.Property' )
	.factory( 'Properties' , [ '$resource' , 'config' , function ( $resource , config ) {
		return $resource( config.api + 'property/:id' , { id : '@id' } , {
			update : {
				method : 'PUT' // this method issues a PUT request
			},
			query : {
				method : 'GET',
				isArray: true
			},
			retrieve: {
				method: 'GET'
			}
		} );
	} ] );