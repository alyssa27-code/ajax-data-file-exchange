<?php
//error_reporting(E_ERROR | E_PARSE);

include "database_connection.php";

header("Access-Control-Allow-Origin: http://localhost:8080");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: text/xml');
// genera il prologo XML
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// scrittura dell'elemento (tag XML) che contiene la risGETa
echo '<response>';

$estensione_file = $_GET['estensione_documenti'];

$select = "SELECT percorso FROM ajax_file_utenti WHERE id_utente ='$_GET[username]'";

$result = $conn->query($select);
	
if($result->num_rows<=0){
	echo "<messaggio>Non ci sono file per l'utente $_GET[username]</messaggio>";
	echo '</response>';
}
else{
$file_trovato = false;

while ($riga = $result->fetch_assoc()){

//La funzione substr() serve per tagliare una stringa, come la funzione substr() in C++
//In PHP: substr(stringa da tagliare, punto di inizio, punto di fine)

//La funzione strrpos() serve per trovare la posizione di un carattere in una stringa, come la funzione at() in C++
//In PHP: strrpos(stringa da esaminare, carattere da trovare)
$punto = strrpos($riga['percorso'], "."); 

//Per trovare l'estensione del file partendo da suo percorso, la stringa da tagliare sarà $riga['percorso'], 
//mentre il punto di partenza sarà la posizione del punto trovata, arrivando fino alla fine della stringa
$estensione_ricavata = substr($riga['percorso'], $punto);
if($estensione_file != $estensione_ricavata){ 
    //$file_trovato = false;
    continue; //Se l'estensione del file inviata non corrisponde all'estensione ricavata dal percoso, si passa alla prossima iterazione
} 
else{
$file_trovato = true;
// Viene estratto solo il nome e l'estensione (es. "Prova_1.txt")
    $nome_file = basename($riga['percorso']);

    // Viene preso il file di dati binari appena letto e
    // viene tradotto usando 64 caratteri della tabella ASCII, ovvero
    // le lettere da A a Z maiuscole e minuscole, i numeri da 0 a 9, e i simboli + e /.
    $contenuto_file = file_get_contents($riga['percorso']);
    $base64 = base64_encode($contenuto_file);
    
    // data: il file è contenuto direttamente dentro questa specifica.
    // application/octet-stream: il file finale viene visto come un file da scaricare.
    // ;base64,: specifica che il testo che segue subito dopo è codificato in Base64, quindi va decodificato.
    $data_url = "data:application/octet-stream;base64," . $base64;

    // Il $data_url viene scritto nell'href e solo il nome del file come testo del link finale.
    echo "<file href='" . $data_url . "'>" . $nome_file . "</file>";
}
}
if(!$file_trovato){ 
echo "<messaggio>Nessun file trovato con estensione $estensione_file</messaggio>";
}
//header('Content Type: application/ocset-stream');
}
echo '</response>';
?>

