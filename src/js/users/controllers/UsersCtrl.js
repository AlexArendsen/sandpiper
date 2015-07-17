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
		users.create(newUser,function UserControllerCreateUserSuccess(data){
			$scope.users.push({
				id: data.userData.id,
				username: data.userData.username,
				isAdmin: !!data.userData.isAdmin
			})
			$('#modal-create-user').closeModal();
			Materialize.toast("User created successfully!",4000);
			$scope.$apply()
		},function UserControllerCreateUserError(message){
			Materialize.toast(message,4000);
		})
	}

	$scope.newUserClear = function UserControllerClearNewUser() {
		$scope.newUser = {}
	}

	$scope.userEdit = function UserControllerEditUser(user) {
		users.edit(newUser)
	}

	$scope.userSelect = function UserControllerSelectUser(user) {
		$scope.editedUser = user
	}
}])