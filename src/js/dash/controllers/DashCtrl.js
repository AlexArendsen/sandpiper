app.controller('DashCtrl',['$scope','files',function InitializeDashController($scope,files){

	$scope.ready = false;

	files.dump(function(payload){
		$scope.results = payload
	}, function(error){
		$scope.error=error
	}, function(){
		$scope.ready = true
	})
	
}])