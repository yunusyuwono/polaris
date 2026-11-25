<?php
/*
* Supported File Formats: .XLS | .XLS | .CSV  (Excel 1997-2007)
* 
* Table structure:
* +---------+-----------+------------+
* |   id    |   name    |    email   |
* +---------+-----------+------------+
* | int(11) | char(250) | char(300)  |
* +----+----+-----------+------------+
* #3c763d
* #0cb313b3
*/

$kon = mysqli_connect("localhost", "root", "", "ami");
$output = '';
if(isset($_POST["import"]))
{
    $fileex=$_FILES['excel']['name'];
 $exten = explode(".",$fileex); // For getting Extension of selected file
 $extension=end($exten);
 $allowed_extension = array("xls", "xlsx", "csv"); //allowed extension
 if(in_array($extension, $allowed_extension)) //check selected file extension is present in allowed extension array
 {
  $file = $_FILES["excel"]["tmp_name"]; // getting temporary source of excel file

  include("PHPExcel/IOFactory.php"); // Add PHPExcel Library in this code
  $objPHPExcel = PHPExcel_IOFactory::load($file); // create object of PHPExcel library by using load() method and in load method define path of selected file

  foreach ($objPHPExcel->getWorksheetIterator() as $worksheet)
  {
    $no=1;
   $highestRow = $worksheet->getHighestRow();
   for($row=2; $row<=$highestRow; $row++)
   {
    $idpaket = mysqli_real_escape_string($kon, $worksheet->getCellByColumnAndRow(0, $row)->getValue());
    $kriteria = trim(mysqli_real_escape_string($kon, $worksheet->getCellByColumnAndRow(1, $row)->getValue())," ");
    $pertanyaan = trim(mysqli_real_escape_string($kon, $worksheet->getCellByColumnAndRow(2, $row)->getValue())," ");
    $noindikator = trim(mysqli_real_escape_string($kon, $worksheet->getCellByColumnAndRow(3, $row)->getValue())," ");
    $opsi1 = trim(mysqli_real_escape_string($kon, $worksheet->getCellByColumnAndRow(4, $row)->getValue())," ");
    $opsi2 = trim(mysqli_real_escape_string($kon, $worksheet->getCellByColumnAndRow(5, $row)->getValue())," ");
    $benar = trim(mysqli_real_escape_string($kon, $worksheet->getCellByColumnAndRow(6, $row)->getValue())," ");
    $vberkas = trim(mysqli_real_escape_string($kon, $worksheet->getCellByColumnAndRow(7, $row)->getValue())," ");

    if(!empty($pertanyaan)) // if none of the data are empty
    {
      $import=mysqli_query($kon,"insert into pertanyaan (idpaket,kriteria,pertanyaan,noindikator,opsi1,opsi2,benar,vberkas) values 
        ('$idpaket','$kriteria','$pertanyaan','$noindikator','$opsi1','$opsi2','$benar','$vberkas')");
      if($import)
      {
        echo "$no. Pertanyaan : $pertanyaan ---- berhasil diimport<br>";
      }
      else
      {
        echo "$no. Pertanyaan : $pertanyaan xxxx gagal diimport<br>";
      }

    }
    $no++;

   }

  }

 }
 else
 {
  $output = '<label class="text-danger">Invalid File</label>'; //if non excel file then
 }
}
?>

<html>
 <head>
  <title>PHP Excel Importer</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
  <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" />
  <style>
  body
  {
   margin:0;
   padding:0;
   background-color: #0cb313b3;
  }
  .box
  {
   width:700px;
   border:1px solid #ccc;
   background-color:#fff;
   border-radius:5px;
   margin-top:100px;
  }
  input[type="file"]{
    border:1px solid gray;
  }
  
  </style>
 </head>
 <body>
  <div class="container box">
   <form method="post" enctype="multipart/form-data">
    <div class="container-fluid">
      <h3 align="center" class="text-success" style="font-weight:600;">Konversi Soal XLS ke TXT</h3><br />
      <div class="row" style="margin-bottom:20px">
        <div class="col-md-4 col-xs-4 col-sm-4"></div>  <!-- Blank Div -->
        
      </div>
      <div class="row">
        <div class="col-sm-12 col-md-12 col-lg-12">
          <label>Pilih File Excel*</label>
        </div>
        <div class="col-xs-6 col-md-5 col-sm-6 col-lg-5">
            <input type="file" name="excel" />
        </div>
        <div class="col-xs-7 col-md-7 col-sm-6 col-lg-7">
            <input type="submit" name="import" class="btn btn-info" value="Import" style="padding:2px 20px;"/>
        </div>
        <?php 
        if(isset($_POST['import']))
        {
            ?>
            <button id="button1" class="btn btn-success btn-sm" onclick="CopyToClipboard('copy')">Salin Hasil</button>
            <script type="text/javascript">
                function CopyToClipboard(containerid) {
                  if (document.selection) {
                    var range = document.body.createTextRange();
                    range.moveToElementText(document.getElementById(containerid));
                    range.select().createTextRange();
                    document.execCommand("copy");
                  } else if (window.getSelection) {
                    var range = document.createRange();
                    range.selectNode(document.getElementById(containerid));
                    window.getSelection().addRange(range);
                    document.execCommand("copy");
                    alert("Soal berhasil disalin");
                    history.back();
                  }
                }
            </script>
            <?php
        }
        ?>
      </div>
  </div>
   </form>
   <br />
   <br />
   <?php
      echo $output;
      echo @$fileUploadMsg;
      echo "<hr/>
			<p style='float:left'>* Supported Formats: .xls | .xlsx | .csv</p>
			<p style='float:right'><a href='export.php'>Exporter &#8594;</a></p>";
      mysqli_close($kon);
   ?>
  </div>
 </body>
</html>


