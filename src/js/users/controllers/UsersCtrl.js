app.controller('UsersCtrl',['$scope','users',function UsersControllerInitialization($scope,users){
	$scope.users = []

	users.dump(function UserControllerDumpSuccess(users){
		$scope.users = users
	},function UserControllerDumpError(message){
		showMessage(message)
	})

	$scope.userDelete = function UserControllerDeleteUser(user) {
		if(confirm("Are you sure you want to delete this user? All of their files will be removed")) {
			users.delete(user.id,function UserControllerDeleteUserSuccess(){
				$scope.users.splice($scope.users.indexOf(user),1)
				Materialize.toast("User deleted successfully",4000);
			}, function UserControllerDeleteUserError(message){
				Materialize.toast(message,4000);
			})
		}
	}

	$scope.userCreate = function UserControllerCreateUser(newUser) {
		users.create(newUser,function UserControllerCreateUserSuccess(){
			$scope.users.push({
				username: newUser.username,
				isAdmin: !!newUser.isAdmin
			})
			Materialize.toast("User created successfully!",4000);
			$scope.$apply()
		},function UserControllerCreateUserError(message){
			Materialize.toast(message,4000);
		})
	}
}])