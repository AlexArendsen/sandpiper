app.controller('DashCtrl',['$scope','files',function InitializeDashController($scope,files){

	files.dump(function(payload){
		$scope.results = payload
	}, function(error){
		$scope.error=error
	})
	
}])