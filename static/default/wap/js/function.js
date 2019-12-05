function PhoneType(){
	var agen=window.navigator.userAgent;
	if(agen.indexOf('Android')>-1){
		return 'Android';
	}else if(agen.indexOf('iPhone')>-1){ //苹果
		return 'iPhone';
	}else if(agen.indexOf('iPad')>-1){ //iPad
		return 'iPad';
	}else{
		return -1;
	}
}
