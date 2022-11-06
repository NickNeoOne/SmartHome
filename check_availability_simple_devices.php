# The script is intended for use in the "Scenarios" of the "majordomo"  smart home system 
# and is used to generate a web page with the availability status of simple devices, 
# as well as notifications of unavailability. It is launched either by reference or through the periodic execution setting.
# Thanks for the idea and sample code to the user "Bagir", you can read more here: https://mjdm.ru/forum/viewtopic.php?f=4&t=7657

// получаем список комнат и сенсоров в виде массивов
$objRooms=getObjectsByClass('Rooms');
$objSensors=getObjectsByClass('SDevices');

echo '<!DOCTYPE html> <html><head>
<style>
.filterDiv {float: none; display: none; }
.show {display: block;}
.myBtnContainer {float: left; width: 150px; padding: .8em 1em calc(.8em + 3px);  position: sticky;  top: 8px;  }
.container { overflow: hidden;}

/* Стиль кнопок */
.btn {  width: 150px; border: 1px solid grey; outline: none; padding: 12px 16px; background-color: #f1f1f1; cursor: pointer;}
.btn:hover { background-color: #ddd;}
.btn.active { background-color: #666; color: white;}
.btn.reload { background-color: rgb(53,167,110); color: white;}

#customers {font-family: "Trebuchet MS", Arial, Helvetica, sans-serif; border-collapse: collapse; margin: auto;}
#customers td, #customers th { border: 1px solid #ddd; padding: 8px;}
#customers th { padding-top: 12px; padding-bottom: 12px; text-align: center; background-color: #68a8d4; color: white;}
#customers tr:nth-child(even){background-color: #f2f2f2;}
#customers tr:hover {background-color: #ddd;}

hr { margin: 10px 0;	padding: 0;	height: 0;	border: none;	border-top: 2px dotted #ddd;}
span {font-size:30px; }
summary::-webkit-details-marker {display: none;}
.green {color: green;}
.red {color: red;}
.grey {color: grey;}
.blue {color: #0000FF;}
.def_cursor {cursor: default;}
</style>

</head><body>
<div id="myBtnContainer" class="myBtnContainer">
  <button class="btn reload" style="border: 1px solid green;" onClick="window.location.reload( true );"> Обновить </button>
  <button class="btn active" onclick="filterSelection(\'all\')"> Показать все</button>';
foreach($objRooms as $objr) {
  $objr=getObject($objr['TITLE']);

echo '<button class="btn" onclick="filterSelection(\''.$objr->description.'\')">' . $objr->description . '</button>';}
echo '</div><br>';
foreach($objRooms as $objr) {
  $objr=getObject($objr['TITLE']);
echo '<div class="container">  <div class="filterDiv '.$objr->description.'">';

        echo "\n";
        echo '<table id="customers">'; $t=1; echo "\n";
        echo ' <tr>	<th style="background-color: #4792d1;"  colspan="7">'. $objr->description .'</td> </tr>';
         echo ' <tr>'; echo "\n";
        echo '  <th width=20px>ID</th>'; echo "\n";
        echo '  <th width=25%>Объект</th>'; echo "\n";
        echo '  <th width=35%>Имя устройства</th>'; echo "\n";
        echo '  <th width=80px>Статус</th>';    echo "\n";
        echo '  <th width=80px>Питание</th>'; echo "\n";
        echo '  <th width=80px>Активный</th>'; echo "\n";
        echo '  <th width=130px>Обновлен</th>'; echo "\n";
        echo ' </tr>'; echo "\n";
 
  foreach($objSensors as $objs) {
    $objs=getObject($objs['TITLE']);
    if ($objr->object_title == $objs->getProperty('LinkedRoom')) {

       // Получаем время последнего обновления 
       $updt=date("d.m.Y  H:i", ((int)$objs->getProperty('updated')));

    // Разный цвет текста
    switch ($objs->getProperty('alive')) {
    case NULL:
        $dev_alive='<a href="https://mjdm.ru/Hints/SdAliveTimeout?skin=hint" style="color: #CD5C5C; text-decoration: none;" target=_blank color="grey"><span>&#9888;</span></a>'; 
        $cn='<font title="Устройство не передает данные о своем статусе, необходимо настроить его чтобы получать данные" color="grey">'; 
        $ce='</font>';
        break;
    case 1:
        $cn='<font  class="green">'; $ce='</font>'; $dev_alive='<span class="def_cursor" title="Устройство доступно">✓</span>';
        break;
    case 0:
        $Record = SQLSelectOne("SELECT archived FROM devices WHERE LINKED_OBJECT LIKE '{$objs->object_title}'"); 
        $dev_arch = $Record['archived'];
        if ($dev_arch!=1){
        $cn='<font  class="red">'; $ce='</font>'; 
        say('Устройство '.$objs->description. ' находящийся в комнате '. $objr->description .' не передает показания c '.$updt,2); 
        $dev_alive='<span class="def_cursor" title="Устройство НЕ доступно">✗</span>';}
        else { $dev_alive='<span class="def_cursor" title="Устройство НЕ доступно но находится архиве">✗</span>'; 
               $cn='<font  class="blue">'; $ce='</font>'; }
        break;
    }

     // Получаем статус устройства (включено/выключено)
     if ($objs->getProperty('status')==1) { $dev_status='<span class="def_cursor" title="ON" style="color: orange;">☀</span>';}
        elseif ($objs->getProperty('status')==0)  { $dev_status='<span class="def_cursor" title="OFF" style="color: black;">☀</span>';}
     else { $dev_status='<span > </span>'; }

    // Формируем дополнительное описание
	switch ($objs->getProperty('batteryOperated')) {
    case 0:
        $dev_batteryOperated='&#9889;';
        $dev_batteryLevel='"Устройство работает от сети"';
        break;
    case 1:
        if ($objs->getProperty('batteryLevel')!=NULL) { $dev_batteryLevel='"Устройство работает от батареи заряд: '.$objs->getProperty('batteryLevel').' %"';}
    else { $dev_batteryLevel='"Устройство работает от батареи заряд но не передает данные о заряде" '; }    
        $dev_batteryOperated='&#128267;';
        break;
    case NULL:
        $dev_batteryOperated='&#9889;';
        $dev_batteryLevel='"Устройство работает от сети"';
        break;
    }    

      // Вывод объектов
      echo ' <tr>'; echo "\n";
      echo '  <td align=right>'.$cn.$objs->id.$ce.'</td>';echo "\n";
      echo '  <td> <a href="/panel/linkedobject.html?op=redirect&object='.$objs->object_title.'" target=_blank>'.$cn.$objs->object_title.$ce.'</a></td>'; echo "\n";
      echo '  <td>'.$cn.$objs->description.$ce.'</td>'; echo "\n";
      echo '  <td align=center>'.$cn.$dev_status.$ce.'</td>';      echo "\n";
      echo '  <td align=center>'.$cn.'<span class="def_cursor" title='.$dev_batteryLevel.'>'.$dev_batteryOperated.$ce.'<span></td>'; echo "\n";
      echo '  <td align=center>'.$cn.$dev_alive.$ce.'</td>'; echo "\n";
      echo '  <td nowrap align=center>'.$cn.$updt.$ce.'</td>'; echo "\n";
      echo ' </tr>'; echo "\n";
    }
  }

echo '</table><br></div>';
}
echo '<script>
filterSelection("all")
function filterSelection(c) {
  var x, i;
  x = document.getElementsByClassName("filterDiv");
  if (c == "all") c = "";
  for (i = 0; i < x.length; i++) {
    w3RemoveClass(x[i], "show");
    if (x[i].className.indexOf(c) > -1) w3AddClass(x[i], "show");
  }
}

function w3AddClass(element, name) {
  var i, arr1, arr2;
  arr1 = element.className.split(" ");
  arr2 = name.split(" ");
  for (i = 0; i < arr2.length; i++) {
    if (arr1.indexOf(arr2[i]) == -1) {element.className += " " + arr2[i];}
  }
}

function w3RemoveClass(element, name) {
  var i, arr1, arr2;
  arr1 = element.className.split(" ");
  arr2 = name.split(" ");
  for (i = 0; i < arr2.length; i++) {
    while (arr1.indexOf(arr2[i]) > -1) {
      arr1.splice(arr1.indexOf(arr2[i]), 1);     
    }
  }
  element.className = arr1.join(" ");
}

// Добавьте активный класс к текущей кнопке (выделите его)
var btnContainer = document.getElementById("myBtnContainer");
var btns = btnContainer.getElementsByClassName("btn");
for (var i = 0; i < btns.length; i++) {
  btns[i].addEventListener("click", function(){
    var current = document.getElementsByClassName("active");
    current[0].className = current[0].className.replace(" active", "");
    this.className += " active";
  });
}
</script>';
