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

	$scope.userSubmit = function UserControllerSubmitUser(userFormInput) {

		if(isNewUser = !userFormInput.id) {

			// Insert user
			users.create(userFormInput,function UserControllerCreateUserSuccess(data){
				$scope.users.push({
					id: data.userData.id,
					username: data.userData.username,
					isAdmin: !!data.userData.isAdmin
				})
				$('#modal-user-form').closeModal();
				Materialize.toast("User created successfully!",4000);
				$scope.$apply()
			},function UserControllerCreateUserError(message){
				Materialize.toast(message,4000);
			})
		} else {

			// Update user
			users.update(userFormInput, function UserControllerUpdateUserSuccess(data){
				if((idx=$scope._userIndex(data.userData.id))!=-1){
					$scope.users[idx] = data.userData;
				} else {console.error("Failed to find user with ID "+data.userData.id);}
				$('#modal-user-form').closeModal()
				Materialize.toast("User updated successfully!",4000)
				$scope.$apply()
			}), function UserControllerUpdateUserError(message) {
				Materialize.toast(message,4000)
			}

		}

	}

	$scope.clearUser = function UserControllerClearUser() {
		$scope.editor = {}
	}

	$scope.userSelect = function UserControllerSelectUser(user) {
		$scope.editor = {
			username: user.username,
			isAdmin: !!user.isAdmin,
			id: user.id
		}
	}

	$scope._userIndex = function(userId) {
		for(idx in $scope.users) {
			if($scope.users[idx].id==userId){
				return idx
			}
		}
		return -1;
	}
}])