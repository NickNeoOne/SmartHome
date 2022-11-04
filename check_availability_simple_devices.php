# The script is intended for use in the "Scenarios" of the "majordomo"  smart home system 
# and is used to generate a web page with the availability status of simple devices, 
# as well as notifications of unavailability. It is launched either by reference or through the periodic execution setting.
# Thanks for the idea and sample code to the user "Bagir", you can read more here: https://mjdm.ru/forum/viewtopic.php?f=4&t=7657
#
$f=0; // флаг начала комнаты
$t=0; // флаг начала таблицы
//$locat=['','Дом','Балкон','Подсобка','Улица','Теплица','Курятник'];

// получаем список комнат и сенсоров в виде массивов
$objRooms=getObjectsByClass('Rooms');
$objSensors=getObjectsByClass('SDevices');
//$objSensors=getObjectsByClass('SSensors');
echo '<!DOCTYPE html> <html><head>
<style>
#customers {font-family: "Trebuchet MS", Arial, Helvetica, sans-serif; border-collapse: collapse; margin: auto;}
#customers td, #customers th { border: 1px solid #ddd; padding: 8px;}
#customers th { padding-top: 12px; padding-bottom: 12px; text-align: center; background-color: #4792d1; color: white;}
#customers tr:nth-child(even){background-color: #f2f2f2;}
#customers tr:hover {background-color: #ddd;}

hr { margin: 10px 0;	padding: 0;	height: 0;	border: none;	border-top: 2px dotted #ddd;}
a {text-decoration: none;}
a:hover {text-decoration: underline;}
a.button { font-weight: 700; color: white; text-decoration: none; padding: .8em 1em calc(.8em + 3px); border-radius: 3px; background: rgb(64,199,129); box-shadow: 0 -3px rgb(53,167,110) inset; transition: 0.2s;} 
a.button:hover { background: rgb(53, 167, 110); }
a.button:active { background: rgb(33,147,90); box-shadow: 0 3px rgb(33,147,90) inset;}

span {font-size:30px; }
summary::-webkit-details-marker {display: none;}
.green {color: green;}
.red {color: red;}
.grey {color: grey;}
.room {width:70%; cursor: pointer; border: 1px; font-size:30px; font-family: Open Sans, sans-serif; color: rgb(66, 139, 202);}
.def_cursor {cursor: default;}
.sticky {position: sticky;  top: 2em;  min-height: 2em;  }
</style>
</head><body> <div class="sticky" align="right"><a href="#" class="button" onClick="window.location.reload( true );">Обновить</a> </div> ';

foreach($objRooms as $objr) {
  $objr=getObject($objr['TITLE']);
 
  foreach($objSensors as $objs) {
    $objs=getObject($objs['TITLE']);
    if ($objr->object_title == $objs->getProperty('LinkedRoom')) {
    
      // При первом разе открыть спойлер с именем комнаты  
      if (!$f) {
       $f=1;
       echo '<center><details open>'.'<summary class="room">'. $objr->description .'<br>'.'<hr>'.
       //'<b style="color:#ff0000">'. $objr->getProperty('Alarms').'</b>'.
       '</summary>';
       }    

       if (!$t) { 
        echo "\n";
        echo '<table id="customers">'; $t=1; echo "\n";
        echo ' <tr>'; echo "\n";
        echo '  <th width=20px>ID</th>'; echo "\n";
        echo '  <th width=25%>Объект</th>'; echo "\n";
        echo '  <th width=35%>Имя устройства</th>'; echo "\n";
        echo '  <th width=80px>Статус</th>';    echo "\n";
        echo '  <th width=80px>Питание</th>'; echo "\n";
        //echo '  <th width=20%>Локация</th>';
        echo '  <th width=80px>Активный</th>'; echo "\n";
        echo '  <th width=130px>Обновлен</th>'; echo "\n";
        echo ' </tr>'; echo "\n";
       }  

       // Получаем время последнего обновления 
       $updt=date("d.m.Y  H:i", ((int)$objs->getProperty('updated')));

    // Разный цвет текста
    switch ($objs->getProperty('alive')) {
    case NULL:
        $dev_alive='<a href="https://mjdm.ru/Hints/SdAliveTimeout?skin=hint" target=_blank color="grey"><span>⁈</span></a>'; 
        $cn='<font title="Устройство не передает данные о своем статусе, необходимо настроить его чтобы получать данные" color="grey">'; 
        $ce='</font>';
        break;
    case 1:
        $cn='<font  class="green">'; $ce='</font>'; $dev_alive='<span class="def_cursor" title="Устройство доступно">✓</span>';
        break;
    case 0:
        $cn='<font  class="red">'; $ce='</font>'; 
        say('Устройство '.$objs->description. ' находящийся в комнате '. $objr->description .' не передает показания c '.$updt,2); 
        $dev_alive='<span class="def_cursor" title="Устройство НЕ доступно">✗</span>';
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
      //echo '  <td>'.$cn.$locat[$objs->location_id].$ce.'</td>'; echo "\n";
      echo '  <td align=center>'.$cn.$dev_alive.$ce.'</td>'; echo "\n";
      echo '  <td nowrap align=center>'.$cn.$updt.$ce.'</td>'; echo "\n";
      echo ' </tr>'; echo "\n";
    }
  }
  if ($t) { echo '</table>'; $t=0; echo "\n";} 
  if ($f) { echo '</details></div><br>'; $f=0;} 
}
