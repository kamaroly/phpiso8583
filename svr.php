<?php
error_reporting(E_ALL);
ob_implicit_flush();
$address = '127.0.0.1';
$port = 1234;
//===========================================
// DB
//===========================================
include_once('JAK8583.class.php');
ini_set('display_error',1);
define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASS", "root");
define("DB_NAME", "iso8583");

$link=@mysql_connect(DB_HOST,DB_USER,DB_PASS);
@mysql_select_db(DB_NAME);
@mysql_query("SET time_zone='Asia/Jakarta'");
//===========================================

$sock = socket_create(AF_INET, SOCK_STREAM, 0);
if (socket_bind($sock, $address, $port) === false)
 {
 if (!socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1))
  {
  echo socket_strerror(socket_last_error($sock));
  exit;
  }
 }
if (socket_listen($sock, 5) === false)
 {
 echo "socket_listen() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
 }


do
 {
 if (($msgsock = socket_accept($sock)) === false)
  {
  echo "socket_accept() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
  break;
 }
 $msg = "\nPOC-ISO Telnet Test. \n" .
 "ketik 'quit' buat keluar, cuy...\n";
        socket_write($msgsock, $msg, strlen($msg));
        do
         {
         if (false === ($buf = socket_read($msgsock, 2048)))
          {
   echo "socket_read() failed: reason: " . socket_strerror(socket_last_error($msgsock)) . "\n";
   break 2;
   }

  if (!$buf = trim($buf))
   {
    continue;
   }
  if ($buf == 'quit')
   {
   break;
   }

  $jak = new JAK8583();

  $jak->addISO($buf);  

  if ($jak->getMTI()=='1800') // Dari Client
   {
   $data_element=$jak->getData();
   $no_id=$data_element[2];
   $perintah="select * from trx where no_id='$no_id'";
   $hasil=@mysql_query($perintah);
   $n_data=@mysql_num_rows($hasil);
   $jak = new JAK8583();
   $jak->addMTI('1810');


   if ($n_data==1) { 

    while ($row=@mysql_fetch_array($hasil))
     {

     $no_id=$row["no_id"];
     $nama=$row["nama"];
     $bln=$row["bln"];
     $nominal=$row["nominal"];
}
$jak->addData(2, $no_id);
$jak->addData(43, $nama);
$jak->addData(54, $nominal);         
$jak->addData(7, $bln);     
}    
else     
{     
$no_id = 'ERR000';$jak->addData(2, $no_id);     
$jak->addData(43, 'XXXXXXXXX');     
$jak->addData(54, '000000');     
$jak->addData(7, '000000');     
}    
}   
$talkback = $jak->getISO();  $talkback = $talkback . "\n";      socket_write($msgsock, $talkback, strlen($talkback));  //===========================================   
echo "$n_data|$buf|$perintah|$buf|$talkback|$no_id|$nama|$bln|$nominal\n";  
  }
 while (true);
 socket_close($msgsock);
 }
while (true);
socket_close($sock);
?>