Math.decimalPlaces = function(num, p){return Math.round(num*(Math.pow(10,p)))/Math.pow(10,p)};
function var_dump () {
     var output = '', pad_char = ' ', pad_val = 4, lgth = 0, i = 0, d = this.window.document;
     var getFuncName = function (fn) {
         var name = (/\W*function\s+([\w\$]+)\s*\(/).exec(fn);
         if (!name) {
             return '(Anonymous)';
         }
         return name[1];
     };
     var repeat_char = function (len, pad_char) {
         var str = '';
         for (var i=0; i < len; i++) {             
             str += pad_char;         
         }         
         return str;     
     };     
     var getScalarVal = function (val) {         
         var ret = '';         
         if (val === null) {
              ret = 'NULL';
          } else if (typeof val === 'boolean') {
              ret = 'bool(' + val + ')';
          } else if (typeof val === 'string') {
              ret = 'string(' + val.length + ') "' + val + '"';
          } else if (typeof val === 'number') {
              if (parseFloat(val) == parseInt(val, 10)) {
                  ret = 'int(' + val + ')';
              } else {
                 ret = 'float(' + val + ')';
              }
          } else if (val === undefined) {
              ret = 'UNDEFINED'; // Not PHP behavior, but neither is undefined as value
          }  else if (typeof val === 'function') {
              ret = 'FUNCTION'; // Not PHP behavior, but neither is function as value
              ret = val.toString().split("\n");
              txt = '';
              for(var j in ret) {
                  txt += (j !=0 ? thick_pad : '') + ret[j] + "\n"; 
              }
              ret = txt;
         } else if (val instanceof Date) {
              val = val.toString();
              ret = 'string('+val.length+') "' + val + '"'
          }
          else if(val.nodeName) {
              ret = 'HTMLElement("' + val.nodeName.toLowerCase() + '")';
          }
          return ret;
      };
      var formatArray = function (obj, cur_depth, pad_val, pad_char) {
          var someProp = '';
          if (cur_depth > 0) {
             cur_depth++;
         }
         base_pad = repeat_char(pad_val * (cur_depth - 1), pad_char);
         thick_pad = repeat_char(pad_val * (cur_depth + 1), pad_char);
         var str = '';
         var val = '';
         if (typeof obj === 'object' && obj !== null) {
             if (obj.constructor && getFuncName(obj.constructor) === 'PHPJS_Resource') {
                 return obj.var_dump();
             }
             lgth = 0;
             for (someProp in obj) {
                 lgth++;
             }
             str += "array(" + lgth + ") {\n";
             for (var key in obj) {
                 if (typeof obj[key] === 'object' && obj[key] !== null && !(obj[key] instanceof Date) && !obj[key].nodeName) {
                     str += thick_pad + "["+key+"] =>\n" + thick_pad+formatArray(obj[key], cur_depth+1, pad_val, pad_char);
                 } else {
                     val = getScalarVal(obj[key]);
                     str += thick_pad + "["+key+"] =>\n" + thick_pad + val + "\n";
                 }
             }
             str += base_pad + "}\n";
         } else {
             str = getScalarVal(obj);
         }
         return str;
     };
     output = formatArray(arguments[0], 0, pad_val, pad_char);
     for ( i=1; i < arguments.length; i++ ) {
         output += '\n' + formatArray(arguments[i], 0, pad_val, pad_char);
     }
     return output;
}

function in_array(needle, haystack, strict) {

	var found = false, key, strict = !!strict;

	for (key in haystack) {
		if ((strict && haystack[key] === needle) || (!strict && haystack[key] == needle)) {
			found = true;
			break;
		}
	}

	return found;
}
