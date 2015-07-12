app.factory('files',['$http','fileUtils',function FileServiceInitialize($http,fileUtils){
	
	return {
		dump: function(callback){
			// Fetch files from server
			$http.get('dump.php')
				.success(function(data, status){
					if(data.success){
						for(idx in data.payload) {
							inf = fileUtils.getPathInfo(data.payload[idx].file,'UNKOWN')
							data.payload[idx].type = inf.extension.toUpperCase()
							data.payload[idx].image = inf.image
						}
						callback(data.payload)
					} else {
						console.error("Files were not received")
						console.error(data)
					}
				})
				.error(function(data, status){
					console.error("Dump returned error:")
					console.error(data)
				})
		},
		getFile: function(fileId,callback){
			$http.get('getFile.php?fileId='+fileId)
				.success(function(data, status){
					if(data.success){
						callback(data.fileInfo)
					} else {
						console.error("File information not receieved")
					}
				})
				.error(function(data, status){

				})
		}
	}
}])