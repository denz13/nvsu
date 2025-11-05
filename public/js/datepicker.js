
$( document ).ready(function() {

});

function __DATEPICKER_CREATE_RANGE(target = "datepicker", format = "YYYY/MM/DD", setDate = false, startDate = "", endDate = "") {
  var tsd = new Date();
  var ted = new Date();
  try{
    if(setDate) {
      if(startDate.trim() != "") {
        tsd = new Date(startDate);
      }
    }
  }catch(err){}
  try{
    if(setDate) {
      if(endDate.trim() != "") {
        ted = new Date(endDate);
      }
    }
  }catch(err){}
  var dp;
  if(setDate) {
    dp = new Litepicker({
        element: document.getElementById(target),
        autoApply: false,
        singleMode: false,
        numberOfColumns: 2,
        numberOfMonths: 2,
        showWeekNumbers: true,
        format: format,
        startDate: tsd,
        endDate: ted,
        dropdowns: {
          minYear: 1990,
          maxYear: null,
          months: true,
          years: true
        },
    });
  }else{
    dp = new Litepicker({
        element: document.getElementById(target),
        autoApply: false,
        singleMode: false,
        numberOfColumns: 2,
        numberOfMonths: 2,
        showWeekNumbers: true,
        format: format,
        dropdowns: {
          minYear: 1990,
          maxYear: null,
          months: true,
          years: true
        },
    });
  }
  return dp;
}

function __DATEPICKER_GET_VALUE_RANGE(target, valueSeparator = "-", dateSeparator = "/") {
  var value = {};

  var t = document.getElementById(target);


  var ts = t.value.split(valueSeparator);

  try{
    if(ts[0].includes("/")) {
      ts[0] = ts[0].replace("/","-");
    }
  }catch(err){}
  try{
    if(ts[0].includes("\\")) {
      ts[0] = ts[0].replace("\\","-");
    }
  }catch(err){}
  try{
    if(ts[0].includes(dateSeparator)) {
      ts[0] = ts[0].replace(dateSeparator,"-");
    }
  }catch(err){}

  try{
    if(ts[1].includes("/")) {
      ts[1] = ts[1].replace("/","-");
    }
  }catch(err){}
  try{
    if(ts[1].includes("\\")) {
      ts[1] = ts[1].replace("\\","-");
    }
  }catch(err){}
  try{
    if(ts[1].includes(dateSeparator)) {
      ts[1] = ts[1].replace(dateSeparator,"-");
    }
  }catch(err){}


  var v1 = new Date(ts[0]);
  var v2 = new Date(ts[1]);

  value = {"startDate":v1, "endDate":v2};

  return value;
}
