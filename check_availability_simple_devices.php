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
#customers {
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
    border-collapse: collapse;
    margin: auto;
}

#customers td, #customers th {
    border: 1px solid #ddd;
    padding: 8px;
}

#customers tr:nth-child(even){background-color: #f2f2f2;}
#customers tr:hover {background-color: #ddd;}
b {font-size:30px; font-family: Open Sans, sans-serif; color: rgb(66, 139, 202);}
a {text-decoration: none;}
a:hover {text-decoration: underline;}
span {font-size:30px;}
summary::-webkit-details-marker {display: none;}
.green {color: green;}
.red {color: red;}
.grey {color: grey;}

#customers th {
    padding-top: 12px;
    padding-bottom: 12px;
    text-align: center;
    background-color: #4792d1;
    color: white;
}
</style>
</head><body>';

foreach($objRooms as $objr) {
  $objr=getObject($objr['TITLE']);
 
  foreach($objSensors as $objs) {
    $objs=getObject($objs['TITLE']);
    if ($objr->object_title == $objs->getProperty('LinkedRoom')) {
    
      // При первом разе открыть спойлер с именем комнаты  
      if (!$f) {
       $f=1;
       echo '<center><details open>'.
         '<summary><big>'.
         '<b>'. $objr->description .' </b><br>'.
         '<hr style="width:50%; margin: 20px 0;	padding: 0;	height: 0;	border: none;	border-top: 2px dotted #ddd;">'.
 //         '<b style="color:#ff0000">'. $objr->getProperty('Alarms').'</b>'.
         '</big></summary>';
       }    

      if (!$t) { 
       echo '<table id="customers">'; $t=1;
       echo '<tr>';
       echo '<th width=20px>ID</td>';
       echo '<th width=20%>Название</td>';
       echo '<th width=40px>Статус</td>';    
       echo '<th width=50%>Описание</td>';
//       echo '<th width=20%>Локация</td>';
       echo '<th width=30px>Живой</td>';
       echo '<th width=130px>Обновлен</td>';
       echo '</tr>';
      }  
    // Получаем время последнего обновления 
    $updt=date("d.m.Y  H:i", $objs->getProperty('updated'));
      // Разный цвет текста
       if ($objs->getProperty('alive')==1) { $cn='<font title="Устройство доступно" class="green">'; $ce='</font>'; $dev_alive='<span>✓</span>';}
       elseif ($objs->getProperty('alive')==NULL ) { 
              $dev_alive='<a href="https://mjdm.ru/Hints/SdAliveTimeout?skin=hint" target=_blank title="Устройство не передает данные о своем статусе, необходимо настроить его чтобы получать данные" color="grey"><span>⁈</span></a>'; 
              $cn='<font color="grey">'; 
               $ce='</font>'; 
              }
       elseif ($objs->getProperty('alive')==0) { 
              $cn='<font title="Устройство НЕ доступно" class="red">'; $ce='</font>'; 
// Если нужно(не нужно) проговаривание уведомления через функцию say раскоментируйте (закоментируйте) строку ниже.
              say('Устройство '.$objs->description. ' находящийся в комнате '. $objr->description .' не передает показания c '.$updt,2); 
              $dev_alive='<span>✗</span>';
              } 
      else { $cn=''; $ce=''; }
 // Получаем статус устройства (включено/выключено)
     if ($objs->getProperty('status')==1) { $dev_status='<span style="color: orange;">☀</span>';}
       elseif ($objs->getProperty('status')==0)  { $dev_status='<span style="color: black;">☀</span>';}
     else { $dev_status='<span > </span>'; }    
      // Напечатать имена и описание объектов
      echo '<tr>';
      echo '<td align=right>'.$cn.$objs->id.$ce.'</td>';
      echo '<td> <a href="/panel/linkedobject.html?op=redirect&object='.$objs->object_title.'" target=_blank>'.$cn.$objs->object_title.$ce.'</a></td>';
      echo '<td align=center>'.$cn.$dev_status.$ce.'</td>';      
      echo '<td>'.$cn.$objs->description.$ce.'</td>';
 //     echo '<td>'.$cn.$locat[$objs->location_id].$ce.'</td>';
      echo '<td align=center>'.$cn.$dev_alive.$ce.'</td>';
      echo '<td nowrap align=center>'.$cn.$updt.$ce.'</td>';
      echo '</tr>';
    }
  }
  if ($t) { echo '</table>'; $t=0;} 
  if ($f) { echo '</details></div><br />'; $f=0;} 
}
