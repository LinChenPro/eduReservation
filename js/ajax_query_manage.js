function AjaxQuery(name, responseFun, followedByFun, url, abortable=false){
	this.name = name;
	this.url = url;
	this.responseFun = responseFun;
	this.followedByFun = followedByFun;
	this.abortable = abortable;
	this.query = null;

	if(this.abortable){
		AjaxQuery.abortableQuerys.push(this);
	}else{
		AjaxQuery.noAbortableQuerys.push(this);
	}
}
AjaxQuery.noAbortableQuerys = new Array();
AjaxQuery.abortableQuerys = new Array();

AjaxQuery.checkMultyAjax = function(){
	for(var i = 0; i < AjaxQuery.noAbortableQuerys.length; i++){
		if(AjaxQuery.noAbortableQuerys[i].query != null){
			return false;
		}
	}
	return true;
}

AjaxQuery.abortRefreshAjax = function(){
	for(var i = 0; i < AjaxQuery.abortableQuerys.length; i++){
		var queryI = AjaxQuery.abortableQuerys[i]
		if( queryI.query != null){
			queryI.query.abort();
			queryI.query = null;
		}
	}
}

AjaxQuery.prototype.treatResponse = function(responseData){
	this.responseFun(responseData);
	this.query = null;
	if(this.followedByFun != null){
		this.followedByFun();	
	}
}

AjaxQuery.prototype.sendAjaxQuery = function(data){
	if(AjaxQuery.checkMultyAjax()){
		AjaxQuery.abortRefreshAjax();
		var currentQuery = this;
		this.query = $.post(
			this.url, 
			data, 
			function(responseData){
				currentQuery.treatResponse(responseData);
			}
			, "json"
		);
	}else{
		this.query = null;
	}

}

