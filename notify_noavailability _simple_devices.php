// Скрипт проверки доступности ПУ в УД majordomo 
// Данный скрипт проверяет свойство alive у простых устройств,а также проверяет является ли устройство "Архивными"
// если ПУ недоступно и НЕ добавлено в группу "Архивные", пришлет уведомление через say

//получаем список комнат и сенсоров в виде массивов
$objRooms=getObjectsByClass('Rooms');
$objSensors=getObjectsByClass('SDevices');
foreach($objRooms as $objr) 
{
 $objr=getObject($objr['TITLE']);
 foreach($objSensors as $objs) 
 {
  $objs=getObject($objs['TITLE']);
  if ($objr->object_title == $objs->getProperty('LinkedRoom')) 
   {
   $updt=date("d.m.Y  H:i", $objs->getProperty('updated'));    // Получаем время последнего обновления 
    if ($objs->getProperty('alive')==0 and $objs->getProperty('alive')!=NULL)    // Проверяем значение alive
     { 
      $Record = SQLSelectOne("SELECT archived FROM devices WHERE LINKED_OBJECT LIKE '{$objs->object_title}'"); // Проверяем ПУ в группе "Архивные"
      $dev_arch = $Record['archived'];
      if ($dev_arch!=1){
      say('Устройство '.$objs->description. ' находящийся в комнате '. $objr->description .' не передает показания c '.$updt,2); }
     }
   }
 }
}
