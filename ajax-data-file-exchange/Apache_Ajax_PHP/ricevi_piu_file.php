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

// Gestione dell'invio più file 
$numeroFiles= $_POST['numero_file'];
$cartella_progetto = str_replace('\\', '/', __DIR__); // __DIR__ è una costante che serve per rivelatre 
                                                      // dinamicamente la cartella del file corrente.
$uploadDirectory = $cartella_progetto . "/fileUtenti/$_POST[username]/";
$name_and_surname = $_POST['nome_e_cognome'];
$posizione_spazio = strpos($name_and_surname, " "); 
$name = substr($name_and_surname,0, $posizione_spazio); 
$surname = substr($name_and_surname, $posizione_spazio + 1);


//Se l'utente ha inserito uno username uguale a quello di un altro utente
$select = "SELECT * FROM ajax_utenti WHERE username = '$_POST[username]'"; 
$result = $conn->query($select); 

if($result->num_rows == 1){ 
    $riga = $result->fetch_assoc(); 
    if($riga['nome'] != $name || $riga['cognome'] != $surname){ 
        echo "<messaggio>Esiste già un utente con username $_POST[username], ma con nome e/o cognome diversi.</messaggio>"; 
        echo '</response>';
        exit; 
    }
}

// CASO 1: la cartella con il nome dell'utente non esiste nel filesystem del WS
// CASO 2: la creazione degli utenti nel DB deve essere svolta dinamicamente

$controllo_utente = "SELECT * FROM ajax_utenti WHERE username = '$_POST[username]'"; 
$result = $conn->query($controllo_utente); 

//L'utente viene creato automaticamente all'interno del DB, se non esiste il nome utente inviato
if($result->num_rows == 0){ 
    mkdir($uploadDirectory, 0777, true); 
    $insert = "INSERT INTO ajax_utenti (username,nome,cognome) VALUES ('$_POST[username]', '$name', '$surname')"; 
    $conn->query($insert); 
}
//Controllo se la cartella dell'utente non è ancora presente nel filesystem del WS, nel caso in cui 
// l'utente fosse già presente all'interno del DB, ma la sua cartella fosse stata modificata o cancellata
else if(!file_exists($uploadDirectory)){ 
    mkdir($uploadDirectory, 0777, true); 
}


if(file_exists($uploadDirectory)){
for($i=0;$i<$numeroFiles;$i++){
	$tmp = $_FILES["file_utente"]["tmp_name"][$i]; 
    $file = $_FILES["file_utente"]["name"][$i]; 
	$uploadFile = $uploadDirectory . $file;

// Se il file esiste già nella cartella dell'utente, si passa alla prossima iterazione utilizzando continue;, 
// in modo da caricare il resto dei file che non sono presenti all'interno della cartella e nel DB

    if(file_exists($uploadFile)){  
    echo "<messaggio>Il file "." (".$file.") "." è già presente nella cartella.</messaggio>";
    continue; 
    }

	echo "<messaggio>File da caricare: " .$uploadFile."</messaggio>";

    $percorso_completo = $uploadDirectory.$file;

 	if (move_uploaded_file($tmp, $uploadFile)) {
	
    //Per evitare percorsi duplicati all'interno del DB, in $controllo_db viene fatta una SELECT di tutti i record del DB
    // con percorso = '$uploadFile' per verificare che il percorso esista già, oppure non esista ancora, all'interno del DB (l'attributo percorso è UNIQUE)
    $controllo_db = "SELECT * FROM ajax_file_utenti WHERE percorso = '$uploadFile'";
    $result = $conn->query($controllo_db);
    
    //Se nel risultato non sono presenti percorsi uguali al percorso $uploadFile, quest'ultimo verrà inserito all'interno del DB
		if($result->num_rows==0){
        $insert = "INSERT INTO ajax_file_utenti (percorso,id_utente) VALUES ('$uploadFile','$_POST[username]')";

		$conn->query($insert);
        echo "<messaggio>Il file "." (".$file.") ". " è valido ed è stato caricato con successo.</messaggio>";
}
else{ 
if(unlink($uploadFile)){
echo "<messaggio>Problemi nell'operazione di inserimento. Il file $uploadFile"." (".$file.") "."è stato cancellato</messaggio>";
    	}
}

}//else di move_uploaded_file
 else {
		echo "<messaggio>File " ." (".$file.") ". " scartato possibile minaccia</messaggio>";
	}	
}
}
echo '</response>';
?>
