<?php
//error_reporting(E_ERROR | E_PARSE);

include "database_connection.php";
include "conversione_file.php";

header("Access-Control-Allow-Origin: http://localhost:8080");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: text/xml');
// genera il prologo XML
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// scrittura dell'elemento (tag XML) che contiene la risGETa
echo '<response>';

$numeroFiles= $_POST['numero_file'];

for($i = 0; $i<$numeroFiles; $i++){

$file_prima = $_POST['file_prima'][$i];

$file_dopo = $_POST['file_dopo'][$i];

$percorso_prima = "./fileUtenti/$_POST[username]/$file_prima";

$punto_prima = strrpos($file_prima, ".");
$nome_prima =  substr($file_prima,0, $punto_prima);
$estensione_ricavata_prima = substr($file_prima, $punto_prima);

$punto_dopo = strrpos($file_dopo, ".");
$nome_dopo =  substr($file_dopo,0, $punto_dopo); 
$estensione_ricavata_dopo = substr($file_dopo, $punto_dopo);

switch (true){

case ($estensione_ricavata_prima != $estensione_ricavata_dopo && $nome_prima!= $nome_dopo):
    $file_dopo = $nome_dopo.$estensione_ricavata_dopo;
    break;

case ($estensione_ricavata_prima == $estensione_ricavata_dopo && $nome_prima != $nome_dopo):
    $file_dopo = $nome_dopo.$estensione_ricavata_prima;
    break;

case ($estensione_ricavata_prima != $estensione_ricavata_dopo && $nome_prima == $nome_dopo):
    $file_dopo = $nome_prima.$estensione_ricavata_dopo;
    break;

case ($estensione_ricavata_prima == $estensione_ricavata_dopo && $nome_prima == $nome_dopo):
    $file_dopo = $file_prima;
    break;
}

$percorso_dopo = "./fileUtenti/$_POST[username]/$file_dopo"; 

if($percorso_dopo == $percorso_prima){ 
echo "<messaggio>Nessuna modifica da effettuare per il file ".$file_prima."</messaggio>"; 
continue; 
} 

if(file_exists($percorso_dopo)){ 
echo "<messaggio>Il file ".$file_dopo." possiede già quel nome.</messaggio>"; 
continue; 
}

if(!file_exists($percorso_prima)){ 
echo "<messaggio>Il file originale non esiste.</messaggio>"; 
continue; 
}
else {
echo "<messaggio>File prima dell'update: ".$file_prima."</messaggio>";

//Il file viene convertito con la funzione coverti_file() acquisita dal file "coversione_file.php"
$conversione = converti_file($percorso_prima, $percorso_dopo);

// Si attende per evitare che il file convertito non venga riconoscito, poichè potrebbe apparire dopo.
// Esempio di errore: "Conversione non supportata per $file_dopo", ma il file convertito appare dopo del filesystem
// e il DB, di conseguenza, non si aggiorna.
$attesa = 0;
while (!file_exists($percorso_dopo) && $attesa < 10) {
    usleep(100000);
    $attesa++;
}

// Se la conversione non è riuscita, non si cancella il file originale.
if (!$conversione || !file_exists($percorso_dopo) || filesize($percorso_dopo) === 0) {
    echo "<messaggio>Conversione non supportata per $file_dopo</messaggio>";
    continue;
}

// Si trova la posizione della directory del progetto (Apache_Ajax_PHP).
// Path assoluto completo (C:\xampp\htdocs\Apache_Ajax_PHP).
// __DIR__ è una costante che restituisce il percorso assoluto della cartella in cui si trova il file attualmente in esecuzione.
$cartella_progetto = str_replace('\\', '/', __DIR__); 

// Si creano i percorsi assoluti dei file.
$percorso_prima = $cartella_progetto . "/fileUtenti/$_POST[username]/$file_prima";
$percorso_dopo  = $cartella_progetto . "/fileUtenti/$_POST[username]/$file_dopo";

// Viene aggiornato il DB.
$update = "UPDATE ajax_file_utenti SET percorso = '$percorso_dopo' WHERE id_utente ='$_POST[username]' AND percorso = '$percorso_prima'";
$result = $conn->query($update);

// Viene cancellato il file originale solo dopo la conversione e l'update.
unlink(realpath($percorso_prima));

echo "<messaggio>File dopo l'update: ".$file_dopo."</messaggio>";

}
}
echo '</response>';

?>

