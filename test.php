<html>

<head>
  
  <meta charset="utf-8">
  
 <!--  <script>
 -->
    <!-- function get_period_onload() {
      
      try{

      var nowDateTime = new Date();

      var nowDateTimeStr = nowDateTime.toJSON();

      var indexOfT = nowDateTimeStr.indexOf("T");

      var nowDateStr = nowDateTimeStr.slice(0, indexOfT);

      var data2 = document.getElementById("d2");

      data2.value = nowDateStr;

      var data1 = document.getElementById("d1");
      
      data1.value = nowDateStr.replace(nowDateStr.slice(8, 2), "01");

      //lert(nowDateStr);
      
      }catch(err){

          alert(err.message); -->

    <!--   }
    
    }

    window.onload = get_period_onload;

  </script>
 -->
  <style>
    
    input {
      vertical-align: top;
      width: 160px;
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
  '0'   => "Источник финансирования",
  '1'   => "ОМС(Территориальный)",
  '12'  =>" ОМС(Федеральный)",
  '2'   =>"Средства граждан",
  '4'   =>"ДМС",
  '8'   =>"ВМП РФ",
  '11'  =>"ВМП ОМС",
  '7'   =>"Бюджет федеральный",
  '55'  =>"Бюджет местный"
  
);
foreach ($finsource as $key=>$value) {
     printf('<option value="%s">%s</option>', $key, $value);

}
?>
      
<!--       <input type="date" id="d1" name="d1" placeholder="Дата с">
      <input type="date" id="d2" name="d2" placeholder="Дата по">
 -->
      <?php
      printf('<input type="date" id="d1" name="d1" placeholder="Дата с" value="%s">', (array_key_exists("d1", $_POST) ? $_POST["d1"] : ''));
      printf('<input type="date" id="d2" name="d2" placeholder="Дата по" value="%s">', (array_key_exists("d2", $_POST) ? $_POST["d2"] : ''));
      ?>

      <select name="dep" >
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
      ?>
      <input type="submit" name="" value="ОК">
    
    </form>
  
  </div>

  <?php

  include 'functions.php';

  echo '<center>'."<h2>Журнал выписанных пациентов за период</h2>".'</center>';

  if(!count($_POST) == 0)
  {
    $DATE1 = substr($_POST["d1"], 8, 2) . "." . substr($_POST["d1"], 5, 2) . "." . substr($_POST["d1"], 2, 2);
    $DATE2 = substr($_POST["d2"], 8, 2) . "." . substr($_POST["d2"], 5, 2) . "." . substr($_POST["d1"], 2, 2);
    $DEP = $_POST["dep"];
    $FINSOURCE = $_POST["FIN"]; //  добавил 

    if($_POST['d1'] .$_POST['d2'] =="") 
    {
     echo '<center>'."<h2>Введите необходимые даты</h2>".'</center>';
     EXIT();
   }
   else
   {
    echo    '<center>'."Интервал запроса " ."c"." " . $DATE1 ." "."по"  ." " .$DATE2.'</center>';

  }

  /*define("TestBARS","(DESCRIPTION=(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 192.168.110.125)(PORT = 1521)))(CONNECT_DATA=(SID=MSCH123)))");

  $conn = oci_connect('dev', ',deve1ope8', TestBARS, 'AL32UTF8');*/

  $sql = <<<'SQL_TEXT'
  select 
    hh.date_in,
    hh.DATE_OUT,
    pj.patient,
    pj.hosp_history_numb_full,
    pj.payment_kind,
    pj.dep,
    hh.mkb_clinic,
    hh.mkb_clinic_name 
  from 
    D_V_HPK_PLAN_JOURNALS PJ
      join d_v_hosp_histories hh
        on PJ.PATIENT_ID =hh.PATIENT_ID
  where
    cast(hh.DATE_OUT as date) between to_date(:DATE1, 'DD/MM/YY') and to_date(:DATE2, 'DD/MM/YY') 
    and hh.date_in = pj.hosp_history_date_in
  SQL_TEXT;
  

  $params = ['DATE1' => $DATE1, 'DATE2' => $DATE2];

  if(!$DEP == '0'){
      $sql = $sql . ' and pj.dep_code =:DEP';
      $params['DEP'] = $DEP;
  }    

  if(!$FINSOURCE == '0'){
      $sql = $sql . ' and pj.PAYMENT_KIND_CODE = :FINSOURCE';
      $params['FINSOURCE'] = $FINSOURCE;
  }

  $sql = $sql . ' order by hh.DATE_OUT';

  // if(!$DEP == '0'){
  //   $sql = $sql . 'and pj.PAYMENT_KIND_CODE = :FINSOURCE and pj.dep_code =:DEP order by hh.DATE_OUT';
  //   //$sql = $sql . 'and pj.PAYMENT_KIND_CODE = :FINSOURCE order by hh.DATE_OUT';
  //   $params['DEP'] = $DEP;
  //   $params['FINSOURCE'] = $FINSOURCE;
  // }elseif{

  //   $params['FINSOURCE'] = $FINSOURCE;  

  // }else{
  //   $sql = $sql . 'order by hh.DATE_OUT';    
  // }

  echo "<table  border='1'>\n";
  echo "<tr>\n";
  echo "<td>Госпитализация</td>\n";
  echo "<td>Выписка</td>\n";
  echo "<td>Ф.И.О.</td>\n";
  echo "<td>И.Б.</td>\n";
  echo "<td>ВИД ОМС</td>\n";
  echo "<td>отделене</td>\n";
  echo "<td>MKB</td>\n";
  echo "<td>Наименование </td>\n";

  $dnm = <<<'TXT'
  $ef = 'exec_function';
  $$ef = 'get_data_array';
  $result = $exec_function($sql,$params,true);
  TXT;

  eval($dnm);

  //$result = get_data_array($sql,$params,true);

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

 echo '</br>'."Количество выписанных пациентов"." ".$k;

}
?>

</body>

</html>