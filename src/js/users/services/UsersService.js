app.factory('users',['$http',function UserServiceInitialization($http){
	return {
		dump: function UsersServiceDump(success,error){
			error = error||function(){}
			$http.get('user.dump.php')
				.success(function(data, status){
					if(data.success) {
						success(data.payload)
					} else {
						error(data.error)
					}
				}).error(function(data, status){
					error(data.error)
				})
		},
		delete: function UsersServiceDelete(userId,success,error){
			error = error||function(){}
			$http.get('user.delete.php?i='+userId)
				.success(function(data, status) {
					if(data.success) {
						success()
					} else {
						error(data.error)
					}
				}).error(function(data, status) {
					error(data.error)
				})
		},
		create: function UsersServiceCreate(newUser,success,error){
			error = error||function(){}

			// Check that all fields have been provided
			for(k in l=[
				['username','username'],
				['password','password'],
				['passwordConfirm','password confirmation']
			]) {
				if(!(newUser[l[k][0]]=$.trim(newUser[l[k][0]]))){
					return error("Please provide "+(l[k][1]));
				}
			}

			// Check that passwords match
			if(newUser.password != newUser.passwordConfirm) {
				return error("Passwords do not match");
			}

			// Hash password
			bc = new bCrypt()
			bc.hashpw(newUser.password,bc.gensalt(9),function(hash){

				// Have to use jQuery's ajax for POST since Angular's is broken on PHP
				arguments = {username: newUser.username, password: hash, isAdmin: !!newUser.isAdmin};
				function tryJSON(src){try{ return JSON.parse(src) } catch(e) {return src}}
				$.ajax({
					url: 'user.create.php',
					type: 'POST',
					data: arguments,
					success: function(res){
						data = tryJSON(res);
						if(!data.success){
							error(data.error);
						} else {
							success(data)
						}
					},
					error: function(xhr){
						data = tryJSON(xhr.responseText);
						error(data.error);
					}
				})
			})

		}
	}
}])