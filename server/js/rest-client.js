var Promise = Promise || Q.promise;
var sgx = sgx || {};
sgx.lib = sgx.lib || {};

sgx.lib.rest = function(url) {
	var baseURL = url;
	
	function getRequest(paramList, verb, headerOptions) {
		var self = this;
		var url = baseURL;
		var joiner = '';
		
		url += "?";
		for(var p in paramList) {
			url += joiner + p + "=" + paramList[p];
			joiner = '&';
		}	
		
		return new Promise(function (fulfill, reject){
			try {			
				$.ajax({
					type: verb,
					url: url,
					headers: headerOptions || "",
					success: function(data, result, xhr) {
						if (result === 'success') {
							var r = data;
							try {
								r = JSON.parse(data);
							} catch (e) {
								// nop
							}
							fulfill(r);
						} else {
							reject(result);
						}
					},
					dataType: 'html'
				});
				
			} catch (ex) {
				reject(ex);
			}
		  
		});
	}
		
	return {
		getRequest: function(params, headerOptions) { return getRequest(params, 'GET', headerOptions); },
		postRequest: function(params, headerOptions) { return getRequest(params, 'POST', headerOptions); }
	};

}
