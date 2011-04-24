var regex_DecimalNum=/^-?[1-9]*[0-9]*(\.)?[0-9]*$/;
var regex_duodecimalNum=/^-?[0-9TE]*(\.)?[0-9TE]*$/;

var saved_decimal="";
var saved_duodecimal="";

function gradient_to_both(intv){
	var res="";

}

function int_to_duo(intv) {
	var res="";
	var R, Q=Math.floor(Math.abs(intv));
	while (true) {
		R = Q % 12;
		res = "0123456789TE".charAt(R)+res;
		Q = (Q-R) / 12;
		if (Q == 0) break;
	}
	return ((intv<0) ? "-"+res : res);
}

function intfrac_to_duo(frac) {
	var len = frac.length;
	frac = parseFloat("0."+frac);
	var res="";
	while (len > 0) {
		var v = 0;
		var n = frac * 12;
		if (len > 1) {
			v = Math.floor(n);
		} else {
			v = Math.round(n);
		}
		frac = n - v;
		res += int_to_duo(v);
		len--;
	}
	return res;
}

function to_duo(dec) {
	if (dec == "" || dec == "-") return dec;
	var res="";
	var parts = dec.split('.');
	intpart = parts[0];
	fracpart = parts[1];

	result = int_to_duo(intpart);

	if (parts.length > 1 && fracpart.length > 0) {
		result += '.'+intfrac_to_duo(fracpart);
	}

	return result;
}

function intduo_to_dec(intduo) {
	var neg = false;
	if (intduo.charAt(0) == '-') {
		intduo = intduo.substring(1);
		neg = true;
	}
	var res=0, n=0;
	for (i=0; i<intduo.length; i++) {
		d = intduo.charAt(i);
		if (d == 'E') {
			n = 11;
		} else if (d == 'T') {
			n = 10;
		} else {
			n = parseInt(d);
		}
		res += n*Math.pow(12,(intduo.length-i-1));
	}
	if (neg) {
		res = '-'+res;
	}
	return res;
}

function from_duo(duo) {
	if (duo == "" || duo == "-") return duo;
	var parts = duo.split('.');
	intpart = parts[0];
	fracpart = parts[1];

	var div_times = 0;
	var prec = 0;
	if (parts.length > 1) {
		prec = div_times = fracpart.length;
		intpart = intpart+fracpart;
	}

	result = intduo_to_dec(intpart);

	while (div_times > 0) {
		result = result/12;
		div_times--;
	}

	result *= Math.pow(10,prec);
	result = Math.round(result);
	result /= Math.pow(10,prec);

	return result;
}

function decimal_changed() {
	var decval = document.duocalc.decimal.value;
	var duoval = '';
	if (regex_DecimalNum.test(decval)) {
		duoval = to_duo(decval);
		document.duocalc.duodecimal.value=duoval;
		saved_duodecimal = duoval;
		saved_decimal = decval;
	} else {
//		alert('decimal field must contain decimal digits only\n'
//			  +'(0, 1, 2, 3, 4, 5, 6, 7, 8, and 9, with . and'
//				 +' - allowed in their proper places)');
		document.duocalc.decimal.value=saved_decimal;
	}
}

function duodecimal_changed() {
	var duoval = document.duocalc.duodecimal.value;
	var decval = '';
	if (regex_duodecimalNum.test(duoval)) {
		decval = from_duo(duoval);
		document.duocalc.decimal.value=decval;
		saved_decimal = decval;
		saved_duodecimal = duoval;
	} else {
//		alert('duodecimal field must contain duodecimal digits only\n'
//			  +'(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, T, E, with , and'
//				 +' - allowed in their proper places)');
		document.duocalc.duodecimal.value=saved_duodecimal;
	}
}

function do_calc() {
}

