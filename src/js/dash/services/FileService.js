app.factory('files',['$http','fileUtils',function FileServiceInitialize($http,fileUtils){
	
	return {
		dump: function(success,error,always){
			error = error||function(){}
			always = always||function(){}

			// Fetch files from server
			$http.get('file.dump.php')
				.success(function(data, status){
					if(data.success){
						for(idx in data.payload) {
							inf = fileUtils.getPathInfo(data.payload[idx].file,'UNKOWN',data.payload[idx].has_thumb)
							data.payload[idx].type = inf.extension.toUpperCase()
							data.payload[idx].image = inf.image
						}
						success(data.payload)
						always()
					} else {
						error(data.error)
						always()
					}
				})
				.error(function(data, status){
					error(data.error)
					always()
				})
		},
		getFile: function(fileId,success,error){
			error = error||function(){}
			$http.get('file.get.php?fileId='+fileId)
				.success(function(data, status){
					if(data.success){
						success(data.fileInfo)
					} else {
						error(data.error)
					}
				})
				.error(function(data, status){
					error(data.error)
				})
		}
	}
}])