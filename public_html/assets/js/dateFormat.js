function dateFormat(date) {
	let dateFormat2 = date.getFullYear() +
		'-' + ( (date.getMonth()+1) < 9 ? "0" + (date.getMonth()+1) : (date.getMonth()+1) )+
		'-' + ( (date.getDate()) < 9 ? "0" + (date.getDate()) : (date.getDate()) );
	return dateFormat2;
}

function dateCalc(date,scale,n) {
    if(scale=='y'){
        return new Date(date.setYear(date.getFullYear() + n));    
    }else if(scale=='m'){
	    return new Date(date.setMonth(date.getMonth() + n));
    }else if(scale=='d'){
	    return new Date(date.setDate(date.getDate() + n));
    }
}