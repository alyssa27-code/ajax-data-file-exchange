<?php
//error_reporting(E_ERROR | E_PARSE);

include "database_connection.php";
	
/*if(isset($_POST['file_utente'])){
	echo "Nome file:  $_POST[file_utente]";
}*/

// ------  Gestione invio file singolo ------- 

$nome_utente = $_POST['nome_utente'];
$cognome_utente = $_POST['cognome_utente'];
$utente = $_POST['nomeUtente'];

$uploadDirectory = "./fileUtenti/$_POST[nomeUtente]/"; 
//Se l'utente ha inserito uno username uguale a quello di un altro utente 
$select = "SELECT * FROM utenti WHERE username = '$_POST[nomeUtente]'"; 
$result = $conn->query($select); 

if($result->num_rows == 1){ 
    $riga = $result->fetch_assoc(); 
    if($riga['nome'] != $_POST['nome_utente'] || $riga['cognome'] != $_POST['cognome_utente']){ 
        echo "Esiste già un utente con username <b>$_POST[nomeUtente]</b>, ma con nome e/o cognome diversi."; 
        exit; 
    }
}

// CASO 1: la cartella con il nome dell'utente non esiste nel filesystem del WS
// CASO 2: la creazione degli utenti nel DB deve essere svolta dinamicamente

$controllo_utente = "SELECT * FROM utenti WHERE username = '$_POST[nomeUtente]'"; 
$result = $conn->query($controllo_utente); 

//L'utente viene creato automaticamente all'interno del DB, se non esiste il nome utente inviato
if($result->num_rows == 0){ 
    mkdir($uploadDirectory, 0777, true); 
    $insert = "INSERT INTO utenti (username,nome,cognome) VALUES ('$_POST[nomeUtente]', '$_POST[nome_utente]', '$_POST[cognome_utente]')"; 
    $conn->query($insert); 
}
//Controllo se la cartella dell'utente non è ancora presente nel filesystem del WS, nel caso in cui 
// l'utente fosse già presente all'interno del DB, ma la sua cartella fosse stata modificata o cancellata
else if(!file_exists($uploadDirectory)){ 
    mkdir($uploadDirectory, 0777, true); 
}

$uploadFile = $uploadDirectory . $_FILES['file_utente']['name'];

// Se la cartella con il nome dell'utente ed il file al suo interno esistono già 

if(file_exists($uploadDirectory) && file_exists($uploadFile)){  
    echo "Il file è già presente nella cartella.";
    exit; 

}

// Se il file non esiste nella cartella dell'utente precedentemente creata
if(file_exists($uploadDirectory) && !file_exists($uploadFile)){
if (move_uploaded_file($_FILES['file_utente']['tmp_name'], $uploadFile)){

    $insert = "INSERT INTO file_utenti (percorso,id_utente) VALUES ('$uploadFile','$_POST[nomeUtente]')";
  //echo $insert;
  try{
  $conn->query($insert);
  if($conn->affected_rows>0){
	echo "File caricato correttamente .\n";
  }
  else{
    /*if(unlink($uploadFile)){
echo "Problemi nell'operazione di inserimento. Il file $uploadFile è stato cancellato";*/
echo "Il file è già esistente.";
    }
  
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
    if(unlink($uploadFile)){
        echo "Problemi nell'operazione di inserimento. Il file $uploadFile è stato cancellato";
            }
   
}
  
}
else { //la funzione move_uploaded_file ha rilevato un problema
    echo "File scartato. Errore in fase di upload\n";
}
}

?>
