var min = 1,
    max = 10,
    select = document.getElementById('rows_no');

for (var i = min; i<=max; i++){
    var opt = document.createElement('option');
    opt.value = i;
    opt.innerHTML = i;
    select.appendChild(opt);
}

function showDiv(divId, element)
{
    document.getElementById(divId).style.display = element.value == 'rur' ? 'block' : 'none';
}