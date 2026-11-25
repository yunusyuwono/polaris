<?php 
error_reporting(0);
if(isset($_POST['upload']))
{
    $file = $_FILES["upl"]["tmp_name"];
    include("PHPExcel/IOFactory.php"); // Add PHPExcel Library in this code
    $objPHPExcel = PHPExcel_IOFactory::load($file); // create object of PHPExcel library by using load() method and in load method define path of selected file

    foreach ($objPHPExcel->getWorksheetIterator() as $worksheet)
    {
        $no=1;
        $highestRow = $worksheet->getHighestRow();
        for($row=2; $row<=$highestRow; $row++)
        {
        
        $kriteria          = trim(mysqli_real_escape_string($kon, $worksheet->getCellByColumnAndRow(2, $row)->getValue())," ");
        $pertanyaan        = trim(mysqli_real_escape_string($kon, $worksheet->getCellByColumnAndRow(3, $row)->getValue())," ");
        $noindikator       = trim(mysqli_real_escape_string($kon, $worksheet->getCellByColumnAndRow(4, $row)->getValue())," ");
        $opsi1              = trim(mysqli_real_escape_string($kon, $worksheet->getCellByColumnAndRow(5, $row)->getValue())," ");
        $opsi2              = trim(mysqli_real_escape_string($kon, $worksheet->getCellByColumnAndRow(6, $row)->getValue())," ");
        $benar              = trim(mysqli_real_escape_string($kon, $worksheet->getCellByColumnAndRow(7, $row)->getValue())," ");
            if(!empty($kriteria)) // if none of the data are empty
            {
            $query = mysqli_query($kon,"INSERT into pertanyaan
                (idpaket,kriteria,pertanyaan,noindikator,opsi1,opsi2,benar) values 
                ('$paket','$kriteria','$pertanyaan','$noindikator','$opsi1','$opsi2','$benar')");
                
            }
            $no++;
        }
        echo '<div class="alert alert-success alert-dismiss">';
        echo ($no-1)." Pertanyaan berhasil diimport";
        echo '</div>';
    }

}
?>