// This function opens a pop-up with the library prep note.
function view_prep_noteWindow(my_library_prep_note_uid) {
  descWin = window.open('display_image_prep_note.php?library_prep_note_uid=' + my_library_prep_note_uid,'ViewPN','width=900,height=700,scrollbars=1,resizable=1,menubar=1,titlebar=1,toolbar=1');
  }  // function view_prep_noteWindow 
// This function opens a pop-up with library prep note upload.
function upload_prep_noteWindow(my_library_prep_note_uid) {
    descWin = window.open('upload_to_prep_note.php?library_prep_note_uid=' + my_library_prep_note_uid,'UploadPN','width=600,height=500,scrollbars=1,resizable=1,menubar=1,titlebar=1,toolbar=1');
  }  // function upload_prep_noteWindow 
// This function sets the field value to blank
// if it is not a non-negative integer.
function testIntField (field) {
  var regExpr = RegExp("^[0-9]+$");
  if (!regExpr.test(field.value)) {
    field.value = "";
  }
}  // function testIntField
// This function sets the field value to blank if it is not a positive integer.
function testPosIntField (field) {
  var regExpr = RegExp("^[1-9]\d*");
  if (!regExpr.test(field.value)) {
    field.value = "";
  }
}  // function testPosIntField
// This function sets the field value to blank if it is not a single digit 1-8.
function test1to8Field (field) {
  var regExpr = RegExp("^[1-8]$");
  if (!regExpr.test(field.value)) {
    field.value = "";
  }
}  // function testPosIntField
// This function sets the field value to blank if it is not a positive real.
function testPosRealField (field) {
  var regExpr = RegExp("(^[0-9]+(\.){0,1}[0-9]*$)|(^(\.)[0-9]+$)");
  if ((!regExpr.test(field.value)) || (field.value < 0)) {
    field.value = "";
  }
}  // function testPosRealField
// This function sets the field value to blank if it is not one of the
// characters {acgtACGT-}.
function testDNAntField (field) {
  var regExpr = RegExp("^[ACTGactg-]+$");
  if (!regExpr.test(field.value)) {
    field.value = "";
  } else {
    field.value = field.value.toUpperCase ();
  }
}  // function testDNAntField
// This function opens a pop-up window with project details.
function projectWindow(input_project_uid) {
  ProjectWindow = window.open('project_pop_up.php?project_uid=' + input_project_uid, 'ProjectDescription', 'width=600,height=600,scrollbars=yes,resizable=yes');
}  // function projectWindow
// This function opens a pop-up window with sample details.
function sampleWindow(input_sample_uid) {
  SampleWindow = window.open('sample_pop_up.php?sample_uid=' + input_sample_uid, 'SampleDescription', 'width=600,height=500,scrollbars=yes,resizable=yes');
}  // function sampleWindow
// This function opens a pop-up window with primary investigator details.
function primary_investigatorWindow(input_primary_investigator_uid) {
  Primary_InvestigatorWindow = window.open('primary_investigator_pop_up.php?primary_investigator_uid=' + input_primary_investigator_uid, 'Primary_InvestigatorDescription', 'width=600,height=300,scrollbars=yes,resizable=yes');
}  // function primary_investigatorWindow
// This function opens a pop-up window with contact details.
function contactWindow(input_contact_uid) {
  ContactWindow = window.open('contact_pop_up.php?contact_uid=' + input_contact_uid, 'ContactDescription', 'width=600,height=300,scrollbars=yes,resizable=yes');
}  // function contactWindow
// This function opens a pop-up window with active samples for the project.
function activeSamplesWindow(input_project_uid) {
  ActiveSampleWindow = window.open('active_samples_for_project.php?project_uid=' + input_project_uid, 'ActiveSamplesDescription', 'width=800,height=500,scrollbars=yes,resizable=yes,toolbar=1');
}  // function activeSamplesWindow
// This function opens a pop-up window with of the barcode reference table.
function barcodeReferenceWindow() {
  BarcodeReferenceWindow = window.open('barcode_reference.php', 'BarcodeList', 'width=600,height=700,scrollbars=yes,resizable=yes');
}  // function barcodeReferenceWindow
// This function opens a pop-up window with run details.
function runWindow(input_run_uid) {
  RunWindow = window.open('run_pop_up.php?run_uid=' + input_run_uid, 'RunDescription', 'width=600,height=600,scrollbars=yes,resizable=yes');
}  // function runWindow
function deleteRowButtonElement(input_class, input_value, input_title) {
  // Create an input element for the table cell.
  var oElement = document.createElement("input");
  oElement.setAttribute("type", "button");
  oElement.setAttribute("class", input_class);
  // The following line is for the IE browser.
  oElement.setAttribute("className", input_class);
  oElement.setAttribute("value", input_value);
  // The following line is for the IE browser.
  oElement.setAttribute("valueName", input_value);
  oElement.setAttribute("title", input_title);
  // The following line is for the IE browser.
  oElement.setAttribute("titleName", input_title);
  if (oElement.addEventListener)
  {
    oElement.addEventListener("click", function(){removeRow(this.parentNode.parentNode.rowIndex)}, false);
  } else if (oElement.attachEvent) {
    oElement.attachEvent("onclick", function(){removeRow(window.event.srcElement.parentNode.parentNode.rowIndex)});
  }  // if (oElement.addEventListener)
  return oElement;
}  // function deleteRowButtonElement
function copyRowButtonElementParm (
 input_class, input_value, input_title,
 input_parameter1, input_parameter2,
 input_parameter3, input_parameter4)
{
  // Create an input element for the table cell.
  var oElement = document.createElement("input");
  oElement.setAttribute("type", "button");
  oElement.setAttribute("class", input_class);
  // The following line is for the IE browser.
  oElement.setAttribute("className", input_class);
  oElement.setAttribute("value", input_value);
  // The following line is for the IE browser.
  oElement.setAttribute("valueName", input_value);
  oElement.setAttribute("title", input_title);
  // The following line is for the IE browser.
  oElement.setAttribute("titleName", input_title);
  if (oElement.addEventListener)
  {
    oElement.addEventListener("click", function(){copyRow(this.parentNode.parentNode.rowIndex,input_parameter1,input_parameter2,input_parameter3,input_parameter4)}, false);
  } else if (oElement.attachEvent) {
    oElement.attachEvent("onclick", function(){copyRow(window.event.srcElement.parentNode.parentNode.rowIndex,input_parameter1,input_parameter2,input_parameter3,input_parameter4)});
  }  // if (oElement.addEventListener)
  return oElement;
}  // function copyRowButtonElementParm
var inputTextElement = function(
 input_size, input_class, input_name, input_title, input_text) {
  // Create an input element for the table cell.
  var oElement = document.createElement("input");
  oElement.setAttribute("type", "text");
  oElement.setAttribute("size", input_size);
  // The following line is for the IE browser.
  oElement.setAttribute("sizeName", input_size);
  oElement.setAttribute("class", input_class);
  // The following line is for the IE browser.
  oElement.setAttribute("className", input_class);
  oElement.setAttribute("name", input_name);
  if (typeof input_title != "undefined")
  {
    oElement.setAttribute("title", input_title);
  }
  // The following line is for the IE browser.
  oElement.setAttribute("titleName", input_title);
  if (typeof input_text != "undefined")
  {
    oElement.setAttribute("value", input_text);
  }
  return oElement;
}  // function inputTextElement
var textareaElement = function(
 input_cols, input_rows, input_class, input_name, input_text) {
  var oElement = document.createElement("textarea");
  oElement.setAttribute("cols", input_cols);
  // The following line is for the IE browser.
  oElement.setAttribute("colsName", input_cols);
  oElement.setAttribute("rows", input_rows);
  // The following line is for the IE browser.
  oElement.setAttribute("rowsName", input_rows);
  oElement.setAttribute("class", input_class);
  // The following line is for the IE browser.
  oElement.setAttribute("className", input_class);
  oElement.setAttribute("name", input_name);
  if (typeof input_text != "undefined")
  {
    var textValue = document.createTextNode(input_text);
    oElement.appendChild(textValue);
  }
  return oElement;
}  // function textareaElement
function selectElement(input_class, input_name, selected_index, optionArray,
 displayArray)
{
  // Set default value of display array.
  if (typeof displayArray === "undefined") displayArray=optionArray;
  // Create a select element.
  var oSelect = document.createElement("select");
  oSelect.setAttribute("class", input_class);
  // The following line is for the IE browser.
  oSelect.setAttribute("className", input_class);
  oSelect.setAttribute("name", input_name);
  oSelect.setAttribute("id", input_name);
  // Create the options, then add to the select.
  for (i=0; i < optionArray.length; i++)
  {
    var oOption = document.createElement("option");
    oSelect.appendChild(oOption);
    var optionValue = document.createTextNode(displayArray[i]);
    oOption.appendChild(optionValue);
    if (i == selected_index)
    {
      oOption.setAttribute("selected", "selected");
      // The following line is for the IE browser.
      oOption.setAttribute("selectedName", "selected");
      oOption.setAttribute("value", optionArray[i]);
      // The following line is for the IE browser.
      oOption.setAttribute("valueName", optionArray[i]);
    }
  }  // for (i=0; i < optionArray.length; i++)
  return oSelect;
}  // function selectElement
// This function determines whether the designated element is null.
// If null then the element will be set to the current date.
// If not null then the element will be set to null.
function switchDateTodayOrNull(dateId)
{
  // Determine if the designated element is empty.
  var dateElement = document.getElementById(dateId);
  if (dateElement && dateElement.value)
  {
    dateElement.value = "";
  } else {
    // Get the current date.
    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth()+1;
    var yyyy = today.getFullYear();
    if (dd < 10) {dd = '0'+dd}
    if (mm < 10) {mm = '0'+mm}
    today = yyyy+'-'+mm+'-'+dd;
    document.getElementById(dateId).value = today;
    dateElement.value = today;
  }  // if (dateElement && dateElement.value)
}  // function selectElement
// This function checks or unchecks all the check boxes on the page.
function checkAll(input_check) {
  var checkboxes = new Array();
  checkboxes = document.getElementsByTagName('input');
  for (var i=0; i < checkboxes.length; i++)
  {
    if (checkboxes[i].type == 'checkbox' &&
        checkboxes[i].getAttribute("disabled") != "disabled")
    {
      checkboxes[i].checked = input_check.checked;
    }  // if (checkboxes[i].type == 'checkbox' &&...
  }  // for (var i=0; i < checkboxes.length; i++)
}  // function checkAll
// This function checks or unchecks all the check boxes in the input table.
function checkAllTable(input_table, input_check) {
  var checkboxes = new Array();
  var my_table = document.getElementById(input_table);
  checkboxes = my_table.getElementsByTagName('input');
  for (var i=0; i < checkboxes.length; i++)
  {
    if (checkboxes[i].type == 'checkbox' &&
        checkboxes[i].getAttribute("disabled") != "disabled")
    {
      checkboxes[i].checked = input_check.checked;
    }  // if (checkboxes[i].type == 'checkbox')
  }  // for (var i=0; i < checkboxes.length; i++)
}  // function checkAllTable
function example_pop_up(URL) {
day = new Date();
id = day.getTime();
eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,width=750,height=280');");
}
function sizable_example_pop_up(URL, in_width, in_height) {
day = new Date();
id = day.getTime();
eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,width=" + in_width + ",height=" + in_height + "');");
}
//Nested Side Bar Menu (Mar 20th, 09)
//By Dynamic Drive: http://www.dynamicdrive.com/style/

var menuids=["sidebarmenu1"] //Enter id(s) of each Side Bar Menu's main UL, separated by commas

function initsidebarmenu(){
for (var i=0; i<menuids.length; i++){
  var ultags=document.getElementById(menuids[i]).getElementsByTagName("ul")
    for (var t=0; t<ultags.length; t++){
    ultags[t].parentNode.getElementsByTagName("a")[0].className+=" subfolderstyle"
  if (ultags[t].parentNode.parentNode.id==menuids[i]) //if this is a first level submenu
   ultags[t].style.left=ultags[t].parentNode.offsetWidth+"px" //dynamically position first level submenus to be width of main menu item
  else //else if this is a sub level submenu (ul)
    ultags[t].style.left=ultags[t-1].getElementsByTagName("a")[0].offsetWidth+"px" //position menu to the right of menu item that activated it
    ultags[t].parentNode.onmouseover=function(){
    this.getElementsByTagName("ul")[0].style.display="block"
    }
    ultags[t].parentNode.onmouseout=function(){
    this.getElementsByTagName("ul")[0].style.display="none"
    }
    }
  for (var t=ultags.length-1; t>-1; t--){ //loop through all sub menus again, and use "display:none" to hide menus (to prevent possible page scrollbars
  ultags[t].style.visibility="visible"
  ultags[t].style.display="none"
  }
  }
}

if (window.addEventListener)
window.addEventListener("load", initsidebarmenu, false)
else if (window.attachEvent)
window.attachEvent("onload", initsidebarmenu)
