<?php
if (isset($_POST['submit']))
 {

 $host="127.0.0.1";
 $port = 1234;
 $no_rek = $_POST['no_rek'];



 //===========================================
 // =========== ISO-8583 BUILDER =============
 //=========================================== 
 include_once('JAK8583.class.php');
 $jak = new JAK8583();
 $jak->addMTI('1800');
 $jak->addData(2, $no_rek);
 $jak->addData(54, '000000');
 $jak->addData(43, 'XXXXXXXXX');
 $jak->addData(7, '000000'); 
 $message = $jak->getISO();
 $socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Error: SOCKET\n");
 $result = socket_connect($socket, $host, $port) or die("Error: NETWORK\n");
 socket_read ($socket, 2048) or die("Error: RESP\n");
 $message = $message . "\n";
 socket_write($socket, $message, strlen($message)) or die("Error: DATA\n");
 $result = socket_read($socket, 2048) or die("Error: RESP\n");
 socket_write($socket, "quit", 4) or die("Error: QUIT\n");   


 //===========================================
 // =========== ISO-8583 PARSER ==============
 //===========================================

 $jak = new JAK8583();
 $jak->addISO($result);
 if ($jak->getMTI()=='1810')
  {  
   
  $data_element=$jak->getData();
  $no_id=$data_element[2];
  $nama=$data_element[43];
  $nominal=$data_element[54];
  $bln=$data_element[7];



  if ($nama=='XXXXXXXXX')
   {
   header('Location: err.php');
   exit;
   }  
  }
 else
  {
  header('Location: err.php');
  exit;  
}
 }
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>POC-ISO</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>


<body bgcolor="#0066CC">
<p align="center"><font color="#FFFFFF"><strong><font size="3">CONFIRMATION OF PAYMENT
  <br>
  ========================= </font></strong></font></p>
<p align="center">&nbsp;</p>
<form name="fInput" method="post" action="output.php">
  <div align="center">
    <table width="75%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td width="90%" rowspan="3"><div align="center">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td width="53%"><font color="#FFFFFF" size="3"><strong>CUSTOMER ID</strong></font></td>
                <td width="2%"><font color="#FFFFFF" size="3"><strong>:</strong></font></td>
                <td width="45%"><font color="#FFFFFF" size="3"><strong><?=$no_id?></strong></font></td>
              </tr>
              <tr>
                <td><font color="#FFFFFF" size="3"><strong>NAME</strong></font></td>
                <td><font color="#FFFFFF" size="3"><strong>:</strong></font></td>
                <td><font color="#FFFFFF" size="3"><strong><?=$nama?></strong></font></td>
              </tr>
              <tr>
                <td><font color="#FFFFFF" size="3"><strong>MONTH BILLS</strong></font></td>
                <td><font color="#FFFFFF" size="3"><strong>:</strong></font></td>
                <td><font color="#FFFFFF" size="3"><strong><?=$bln?></strong></font></td>
              </tr>
              <tr>
                <td><font color="#FFFFFF" size="3"><strong>NUMBER OF BILLS</strong></font></td>
                <td><font color="#FFFFFF" size="3"><strong>:</strong></font></td>
                <td><font color="#FFFFFF" size="3"><strong><?=$nominal?></strong></font></td>
              </tr>
            </table>
          </div></td>
        <td width="10%"><div align="right">
            <input type="submit" name="submit" value="--&gt; PAY">
          </div></td>
      </tr>
      <tr>
        <td><div align="right"> </div></td>
      </tr>
      <tr>
        <td><div align="right">
            <input type="button" name="Button3" value="--&gt; CANCEL" onClick="document.location.href='input.php'">
          </div></td>
      </tr>
    </table>
  </div>
</form>


<table width="75%" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td><font color="#FFFFFF" size="3"><strong>RESP :</strong></font><font color="#FFFFFF" size="3"><strong>
      <br>
      <?=$message?>
      </strong></font></td>
  </tr>
  <tr>
    <td><font color="#FFFFFF" size="3"><strong>RECP :</strong></font><font color="#FFFFFF" size="3"><strong>
      <br><?=$result?>
      </strong></font></td>
  </tr>
</table>
</body>
</html>