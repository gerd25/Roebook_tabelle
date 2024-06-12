  <html>

<head>
  <title>Untitled</title>
</head>

<body>

<div style="position:relative; left:172px; top:1.0cm">

 <form method="post" action="http://localhost/test/Abfrage_datum2.php" >


       <label for="DatumVon">Datum von </label>

       <input id="DatumVon" name="datumVon" type="date" value="DatumVon">

       <label for="DatumBis">bis </label>

       <input id="DatumBis" name="datumBis" type="date" value="DatumBis">


     <input type="submit" value="Submit">





<?php

 session_start();

//  Zugang Datenbank

$zugang = pg_connect("host=localhost dbname=orthanc_db user=postgres password=user");
 if(!$zugang) {
      echo "Error : Unable to open database\n";
  } else {
     // echo "Opened database successfully\n";
   }
   $stat = pg_connection_status($zugang);
    if($stat === PGSQL_CONNECTION_OK){
 // echo 'Connection OK';
    } else {
        echo 'An error occurred';
    }

$result = pg_query($zugang, "SELECT * FROM datenaustausch");
       while ($row = pg_fetch_row($result)) {
     // echo "phpid: $row[0]  patid: $row[1]";
       echo "<br />\n";
      $PID = $row[1];
     // echo $PID;
 }
       //  echo $PID;
          $_SESSION["newpaID"]=$PID;

    if (!$result) {
       echo "Ein Fehler ist aufgetreten.\n";
    exit;
    }

  $eintrag = pg_query($zugang,"INSERT INTO public.datenaustausch(phpid,patid,lastname,firstname,birthday,street,city,zip,sex,confirm,commit) VALUES ('29',$PID,'oeller','Manni','03.03.2020','Weg','Herne','55555','m','t','OK')");


    if (!$eintrag) {
       echo "Ein Fehler ist aufgetreten.\n";
    exit;
    }


$result = pg_query($zugang, "UPDATE public.datenaustausch SET confirm= 'O', commit='gelesen' WHERE patid ='798'");

   if (!$result) {
      echo "Ein Fehler ist aufgetreten.\n";
   exit;
    }

// The data to send to the API
$postData = array(
    'Level' => 'patients',
   // 'Query' => array('PatientID' =>  $PID),
    'Query' => array('PatientID' =>  '9706'),
    //'title' => 'A new orthanc post',
    //'content' => 'With <b>exciting</b> content...'
);


// Create the context for the request
$context = stream_context_create(array(
    'http' => array(
        'method' => 'POST',
         'header' => "Content-Type: application/json\r\n",
        'content' => json_encode($postData)
        )
        ));


   // Send the request  findet patienten ID
$response = file_get_contents('http://127.0.0.1:8042/tools/find', FALSE, $context);

// Check for errors
   if($response === FALSE){
      die('Error');
   }

//  Patientendaten
// Decode the response
$responseData = json_decode($response, TRUE);

// Print the date from the response
 //echo '<pre>'; print_r($responseData); echo '</pre>';

// echo json_encode($responseData);
 $patientID = $responseData[0];
 // echo $patientID;

 //echo var_dump ($responseData);

 $curl = curl_init();

// Sending GET
curl_setopt($curl, CURLOPT_URL, "http://localhost:8042/patients/$patientID");


// Telling curl to store JSON

curl_setopt($curl,
    CURLOPT_RETURNTRANSFER, true);

// Executing curl
$response = curl_exec($curl);


   if($e = curl_error($curl)) {
      echo $e;
   } else {

    // Decoding JSON data
    $decodedData =
        json_decode($response, true);
    }
//var_dump($decodedData);
       $row =  $decodedData;
    //   var_dump($row);

   $patientGeb = $decodedData['MainDicomTags']['PatientBirthDate'];
   $PatientenID = $decodedData['MainDicomTags']['PatientID'];
       // echo($PatientenID);
   $Patientenname = $decodedData['MainDicomTags']['PatientName'];
   $PatientenWM = $decodedData['MainDicomTags']['PatientSex'];
   $Patype = $decodedData['Type'];

     // Studien Patient

 // $studien = $decodedData['Studies'][0];
     $studien = $decodedData['Studies'];

    //if($decodedData != Null) {
    foreach( $studien as $studs){
      curl_setopt($curl, CURLOPT_URL, "http://localhost:8042/studies/$studs");
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  // Executing curl
      $response = curl_exec($curl);
      $decodedData =
        json_decode($response, true);
     //  var_dump($decodedData);

     // Decode the response
     $responseData = json_decode($response, TRUE);

    // $AccesNr =  $decodedData['MainDicomTags']['AccessionNumber'];
     if(isset($decodedData['MainDicomTags']['AccessionNumber'])){ $AccesNr =$decodedData['MainDicomTags']['AccessionNumber']; } else
                    {$AccesNr = "keine Daten";}
     //$StudDate = $decodedData['MainDicomTags']['StudyDate'];
     if(isset($decodedData['MainDicomTags']['StudyDate'])){ $StudDate =$decodedData['MainDicomTags']['StudyDate']; } else
                    {$StudDate = "keine Daten";}




     $StudID =  $decodedData['MainDicomTags']['StudyID'];
     $StudyINS =  $decodedData['MainDicomTags']['StudyInstanceUID'];
     $STudyZeit= $decodedData['MainDicomTags']['StudyTime'];
     $ParentPat =  $decodedData['ParentPatient'][0];
     $Serien =  $decodedData['Series'];
     // $Serien =  $decodedData['Series'];
    // echo $Serien;
      // var_dump($Serien) ;




         foreach( $Serien as $reihen){
          curl_setopt($curl, CURLOPT_URL, "http://localhost:8042/series/$reihen");
          curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
          $response = curl_exec($curl);
       // var_dump ($response) ;
          $decodedData = json_decode($response, true);

     // var_dump($decodedData);
         }
        $update = $decodedData['LastUpdate'];
        $bodypart = $decodedData['MainDicomTags']['BodyPartExamined'];
        $Manfac = $decodedData['MainDicomTags']['Manufacturer'];
        $modali = $decodedData['MainDicomTags']['Modality'];
        $date = $decodedData['MainDicomTags']['SeriesDate'];
        $descript = $decodedData['MainDicomTags']['SeriesDescription'];
        $Instance = $decodedData['MainDicomTags']['SeriesInstanceUID'];
        $Nummer =  $decodedData['MainDicomTags']['SeriesNumber'];
        $date = $decodedData['MainDicomTags']['SeriesDate'];
        $descript = $decodedData['MainDicomTags']['SeriesDescription'];
        $Instance = $decodedData['MainDicomTags']['SeriesInstanceUID'];
        $Nummer =  $decodedData['MainDicomTags']['SeriesNumber'];
        $time = $decodedData['MainDicomTags']['SeriesTime'];
        $station = $decodedData['MainDicomTags']['StationName'];
        $study = $decodedData['ParentStudy'];
        $stati = $decodedData['Status'];
        $typ = $decodedData['Type'];
        $Instanzen = $decodedData['Instances'];
       //  var_dump($Instanzen);


            foreach( $Instanzen as $value){
          // var_dump($value);
              curl_setopt($curl, CURLOPT_URL, "http://localhost:8042/instances/$value/tags");

             //curl_setopt($curl, CURLOPT_URL, "http://localhost:8042/instances/$Instanzen");
             curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
             $response = curl_exec($curl);
             $decodedData = json_decode($response, true);
         //  var_dump($decodedData);
        //   if( $decodedData != Null) {                                        0
          // $fileG = $decodedData['FileSize'];
          // $fileU = $decodedData['FileUuid'];
          // $IDnr = $decodedData['ID'];
          // $IDIserie =  $decodedData['IndexInSeries'];

        $aquiNR =    $decodedData['0020,0012']['Value'];
        $creatdate = $decodedData['0008,0012']['Value'];
        $creattime = $decodedData['0008,0013']['Value'];
        $nummerI =   $decodedData['0020,0013']['Value'];
        $AnzahlF =   $decodedData['0028,0008']['Value'];
        $type =      $decodedData['0008,1090']['Value'];
               if(isset($decodedData['0018,0060']['Value'])){ $kvp_Wert = $decodedData['0018,0060']['Value']; } else
                    {$kvp_Wert = "keine Daten";}
               if(isset($decodedData['0018,1150']['Value'])){ $expotime = $decodedData['0018,1150']['Value']; } else
                    {$expotime = "keine Daten";}
               if(isset($decodedData['0018,1151']['Value'])){ $xrayTC = $decodedData['0018,1151']['Value'];} else
                    {$xrayTC = "keine Daten";}
        $modali =    $decodedData['0008,0060']['Value'];
        $bodypart =  $decodedData['0018,0015']['Value'];
        $station  =  $decodedData['0008,1010']['Value'];
        $PatientenWM =$decodedData['0010,0040']['Value'];

         ?>



          <div style='position:relative;top:2px;left:50px'>
        <table cellspacing=0 border=1px solid align:center >

          <tr>
          <th><img src="http://127.0.0.1:8042/instances/<?php echo $value; ?>/preview".' alt="?" height="75" width="120" </th>
          <th style='border:1; width:180'>AcquisitionNumber</th>
          <th style='border:1; width:180'>InstanceCreationDate</th>
          <th style='border:1; width:150'>InstanceNumber</th>
          <th style='border:1; width:120'>KVP</th>
          <th style='border:1; width:130'>Exposure Time</th>
          <th style='border:1; width:160'>xRay Tube Current</th>
          <th style='border:1; width:130'>Modality</th>
          <th style='border:1; width:150'>Koerperbereich</th>
          </tr>

          <tr>
          <td></td>
          <td align=center><?php echo $aquiNR; ?></td>
          <td align=center><?php echo $creatdate; ?></td>
          <td align=center><?php echo $nummerI; ?></td>
          <td align=center><?php echo $kvp_Wert; ?></td>
          <td align=center><?php echo $expotime; ?></td>
          <td align=center><?php echo $xrayTC; ?></td>
          <td align=center><?php echo $modali; ?></td>
          <td align=center><?php echo $bodypart;?></td>
          </tr>
          </div>
         </table>





         <?php

        // echo "<div style='position:absolute;top:10cm ;left:50px'>\n"  ;
        // echo "<div style='position:absolute; top:10px; left:50px'>\n"  ;

         //echo "<table ,cellspacing=1, border=1px solid, align:center, >\n";

         //echo "<tr>\n";
        //echo "<div style='position:absolute;top:6cm;left:50px'>\n"  ;
        //echo "<table cellspacing=0 border=1px solid align:center >\n";
        //echo "<tr>\n";

         //( http://127.0.0.1:8042/instances/dce124bb-4de12a17-d3da45cd-7cf4c0d4-ae6574b5/preview
        //echo "<th> ".'<img src='."http://127.0.0.1:8042/instances/2b9459cf-60f068a3-f285146f-ca4db519-98757af0/preview".' alt="?" height="75" width="120"/>'."</th>";
         //echo "<th> ".'<img src='."http://127.0.0.1:8042/instances/$value/preview".' alt="?" height="45" width="90"/>'."</th>";

        //echo "<th style='border:2 ; width:170px'>AcquisitionNumber</th>\n";
        //echo "<th style='border:1 ; width:160px'>InstanceCreationDate</th>\n";
        //echo "<th style='border:1 ; width:100px'>InstanceNumber</th>\n";
        //echo "<th style='border:1 ; width:70px'>KVP</th>\n";
        //echo "<th style='border:1 ; width:130px'>Exposure Time</th>\n";
        //echo "<th style='border:1 ; width:150px'>xRay Tube Current</th>\n";
        //echo "<th style='border:1 ; width:70px'>Modality</th>\n";
        //echo "<th style='border:1 ; width:150px'>Koerperbereich</th>\n";

        //echo "</tr>\n";

        //echo "<tr style='align:center'>\n";
           //  echo "<td> ".'<img src='."http://127.0.0.1:8042/instances/2b9459cf-60f068a3-f285146f-ca4db519-98757af0/preview".' alt="?" height="100" width="120"/>'."</td>";


             //echo "<td style='border:0, align= center'></td>\n";

             //echo "<td align='center'>$aquiNR</td>\n";
             //echo "<td align='center'>$creatdate</td>\n";
             //echo "<td align='center'> $nummerI</td>\n";
             //echo "<td align='center'>$kvp_Wert</td>";
             //echo "<td align='center'>$expotime</td>\n";
             //echo"<td align='center'>$xrayTC</td>";
             //echo "<td align='center'>$modali</td>\n";
             //echo "<td align='center'>$bodypart</td>\n";;


          //echo "</tr>\n";

            //echo "</table>\n";

         //echo  "</div>" ;



                  }
                  }

    // Closing curl
    curl_close($curl);


 ?>





    <div position:absolute; left:50px; top:1px>

   <table border = 4 cellspacing="0"  height= "90">
   <caption align="top">Roentgenbuch</caption>
   <tr>
    <tr></tr>
   <tr>
    <th style='border:1; width:180'>Aufnahme Datum</th>
    <th style='border:1; width:180'>PatientID</th>
    <th style='border:1; width:180'>Patient</th>
    <th style='border:1; width:180'>Geburtsdatum</th>
    <th style='border:1; width:180'>Geschlecht</th>
    <th style='border:1; width:180'>Bildparameter</th>
    <th style='border:1; width:180'>Hersteller</th>
   </tr>

   <tr>

    <td align=center><?php echo $StudDate; ?></td>
    <td align=center><?php echo $PatientenID; ?></td>
    <td align=center><?php echo $Patientenname; ?></td>
    <td align=center><?php echo $patientGeb; ?></td>
    <td align=center><?php echo $PatientenWM; ?></td>
    <td align=center><?php echo $descript; ?></td>
    <td align=center><?php echo $Manfac; ?></td>
   </tr>

  </table>
  </div >
   </body>
   </html>


