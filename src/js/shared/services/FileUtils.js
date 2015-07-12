app.factory('fileUtils',[function(){

	var fileUtilFunctions = {
		getExtension: function(file,def){
			if(file) {
				if(mat=file.match(/\.(.+)$/)) {
					if(mat[1]){
						return mat[1]
					} else {return def;}
				} else {return def;}
			} else {return def;}
		},
		getPathInfo: function(file,def) {
			extension = fileUtilFunctions.getExtension(file,def).toLowerCase();
			image = 'static/img/filetypes/_blank.png'
			if(extension.search(/png|jpg|jpeg|gif|bmp/)!=-1) {
				image = 'uploads/'+file;
			} else if (['aac','aiff','ai','avi','bmp','c','cpp','css','dat','def','dmg','doc','dotx','dwg','dxf','eps','exe','flv','gif','h','hpp','html','ics','iso','java','jpg','js','key','less','mid','mp3','mp4','mpg','odf','ods','odt','otp','ots','ott','pdf','php','png','ppt','psd','py','qt','rar','rb','rtf','sass','scss','sql','tga','tgz','tiff','txt','wav','xls','xlsx','xml','yml','zip'].indexOf(extension)!=-1) {
				image = 'static/img/filetypes/'+extension+'.png';
			}
			return {
				extension: extension,
				image: image
			}
		}
	}

	return fileUtilFunctions;
}])
