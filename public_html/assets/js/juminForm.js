function autoHypenJumin(str){
    str = str.replace(/[^0-9]/g, '');
    if (str.length>13){ //숫자의 수가 13자리를 초과하면
        str = str.substr(0,13);
    }
    var tmp = '';
    if( str.length < 6){
        return str;
    }else{              
        tmp += str.substr(0, 6);
        tmp += '-';
        tmp += str.substr(6, 7);
        return tmp;
    }
}