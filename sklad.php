<html>

<head>
  
  <meta charset="utf-8">
  
 
  <style>
    
    input {
      vertical-align: top;
      width: 175px;
      height: 35px;
      font-size: 20px;
      font-weight: bold;
    }
    
    select {
      vertical-align: top;
      width: 290px;
      height: 35px;
      font-size: 20px;
    } 
  
  </style>

</head>

<body>
  
  <div style="text-align: center;">
    
    <form action="" method="POST">
      <select name="FIN" >
<?php
$finsource = array
(
  '6561491'   => "все источники",
  '80265314'   => "ОМС(Территориальный)",
  '169375059'  =>" ОМС(Федеральный)",
  '80265583'   =>"Средства граждан",
  '80265441'  => "Федеральный бюджет",
  '130359249'   =>"ББР",
  '80265227'   =>"ВМП",
  '107972388'  =>"Внебюджет",
  '119997617'   =>"ВТМП 2019",
  '119258817'  =>"КИ*"
  
);
foreach ($finsource as $key=>$value) {
     printf('<option value="%s">%s</option>', $key, $value);

}
?>
 
      <?php
      printf('<input type="date" id="d1" name="d1" placeholder="Дата с" value="%s">', (array_key_exists("d1", $_POST) ? $_POST["d1"] : ''));
      printf('<input type="date" id="d2" name="d2" placeholder="Дата по" value="%s">', (array_key_exists("d2", $_POST) ? $_POST["d2"] : ''));
      ?>

      <!-- <select name="dep" >
        <?php
        $deps = array
        (
        '0'   => " Все отделения ",
        '10'  => "Травматология",
        '138' =>"Травматология и ортопедия",
        '2'   =>"Гастроэнтерология",
        '7'   =>"Кардиология",
        '18'  =>"ЦДС",
        '8'   =>"Невроолгия",
        '4'   =>"Гинекология",
        '15'  =>"Токсикология",
        '14'  =>"Терапия",
        '6'   =>"ГХИ",
        '17'  =>"Хирургия"
        );
        foreach ($deps as $key=>$value) {
        printf('<option value="%s">%s</option>', $key, $value);
      }
      ?> -->
      <input type="text" name="search"  class="search" placeholder="Поиск операции">
      <input type="text" name="search_mod"  class="search_mod" placeholder="Поиск модификации">

      <input type="submit" name="" value="ОК">
      


    </form>

   

  </div>

  <?php

   include 'functions.php';

    echo '<center>'."<h2>Журнал операций</h2>".'</center>';


    if(!count($_POST) == 0)
  {
    $DATE1 = substr($_POST["d1"], 8, 2) . "." . substr($_POST["d1"], 5, 2) . "." . substr($_POST["d1"], 2, 2);
    $DATE2 = substr($_POST["d2"], 8, 2) . "." . substr($_POST["d2"], 5, 2) . "." . substr($_POST["d1"], 2, 2);
    /*$DEP = $_POST["dep"];
    $FINSOURCE = $_POST["FIN"];*/ //  добавил 

    if($_POST['d1'] .$_POST['d2'] =="") 
    {
     echo '<center>'."<h2>Введите необходимые даты</h2>".'</center>';
     EXIT();
   }
     else
   {
     echo    '<center>'."Интервал запроса " ."c"." " . $DATE1 ." "."по"  ." " .$DATE2.'</center>';

   }



   $sql = <<<'SQL_TEXT'
              select   
                t.STORYOPER_CODE,
                t.MOD_CODE,
                t.MOD_NAME, 
                t.PACK_CODE FULL_MOD_NAME,
                t.STORE_FROM_NAME STORE_NAME,
                t.STORE_TO_NAME,
                t.DATE_OPER,
                t.DOCUMENT,
                t.NOM_CODE,
                t.NOM_NAME,
                t.PARTY_SER,
                t.FINANSOURCE_NAME,
                t.ACC_FP_NAME
                         from D_V_JURSTORE_EX t
            Where date_oper  between  to_date(:DATE1,'DD.MM.YY') and  to_date(:DATE2,'DD.MM.YY')
            and t.STORYOPER_CODE like :SEARCH
             
            SQL_TEXT;

  $params = ['DATE1' => $DATE1, 'DATE2' => $DATE2,  'SEARCH' => '%' . $_POST['search'] . '%'];

  if(!empty($_POST['search_mod'])){
    $sql = $sql . ' and t.MOD_NAME like :SEARCH_MOD';
     $params['SEARCH_MOD'] = '%' . $_POST['search_mod'] . '%';
  }
 

  /*if(!$DEP == '0'){
      $sql = $sql . ' and pj.dep_code =:DEP';
      $params['DEP'] = $DEP;
  }    

  if(!$FINSOURCE == '0'){
      $sql = $sql . ' and FINANSOURCE = :FINSOURCE';
      $params['FINSOURCE'] = $FINSOURCE;
  }

  $sql = $sql . ' order by hh.DATE_OUT';*/

 

  echo "<table  border='1'>\n";
  echo "<tr>\n";
  echo "<td>Операция</td>\n";
  echo "<td>Номенклатура</td>\n";
  echo "<td>Модификация</td>\n";
  echo "<td>Упаковка</td>\n";
  echo "<td>Откуда</td>\n";
  echo "<td>Куда</td>\n";
  echo "<td>Когда</td>\n";
  echo "<td>АКТ</td>\n";
  echo "<td>Группа</td>\n";
  echo "<td>Позиции номенклатуры</td>\n";
  echo "<td>Серия</td>\n";
  echo "<td>Источник ФИН</td>\n";
  echo "<td>Раздел</td>\n";

  /*получить_массив_данных(текст_запроса, массив_параметров, признак_подключения_к_рабочей_базе)*/
  $result = get_data_array($sql,$params, true);

  $k=0;

  foreach ($result as  $row) 
  {
   echo "<tr>\n"; 
   foreach ($row as $key => $value) {
     echo "    <td>" . $value . "</td>\n";
   }
   echo "</tr>\n";
   ++$k;
 }
 echo "</table>\n";

 echo '</br>'."колличество операций"." ".$k;

}
/*
if(isset($_POST['submit'])){
$search = $_POST['search'];
while ($row = oci_fetch_assoc($sql)) {
   echo  '$row';

}
}*/

?>

</body>

</html>