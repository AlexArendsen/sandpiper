app.controller('EditorCtrl',['$scope','files',function($scope,files){
	$scope.editor = {
		title: "Loading...",
		submitlabel: "...",
		continuelabel: "..."
	}

	$scope.file = {}

	if(id=$('#file-id').val()) {
		$scope.file.isNew = false;
		$scope.file.action = "update"
		$('#file-action').val("update")

		files.getFile(id,function(fileInfo) {
			$scope.file = fileInfo;

			$scope.editor.title = "Update "+$scope.file.title
			$scope.editor.submitlabel = "Update"
			$scope.editor.continuelabel = "Update and Add Another"
		},function(message){
			Materialize.toast(message);
		})

	} else {
		$scope.file.isNew = true;
		$scope.file.action = "insert"
		$('#file-action').val("insert")

		$scope.editor.title = "Add New File"
		$scope.editor.submitlabel = "Create"
		$scope.editor.continuelabel = "Create and Add Another"
	}

	$scope.onFileChange = function(){
		if(!$scope.file.title){
			$scope.file.title = $scope.file.path
		}
	}
}])