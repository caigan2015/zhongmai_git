//限定备注字数 
function textCounter(field, countfield, maxlimit) {  
   // 函数，3个参数，表单名字，表单域元素名，限制字符；  
   if (field.value.length > maxlimit)  
   //如果元素区字符数大于最大字符数，按照最大字符数截断；  
   field.value = field.value.substring(0, maxlimit);  
   else  
   //在记数区文本框内显示剩余的字符数；  
   countfield.value = maxlimit - field.value.length;  
}  