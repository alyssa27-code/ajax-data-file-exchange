<?php
// error_reporting(E_ERROR | E_PARSE);

include "database_connection.php";
	
header("Access-Control-Allow-Origin: http://localhost:8080");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: text/xml');
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
echo '<response>';

// Viene verificato che l'array dei file sia stato effettivamente inviato
if (isset($_POST['nome_file']) && is_array($_POST['nome_file'])) {

    // Viene eseguito un ciclo sui file inviati, in modo da evitare errori se qualche input è vuoto
    foreach ($_POST['nome_file'] as $file_inviato) {
        
        // basename() ha il compito di estrarre il nome di un file o di una cartella dall'intero path.
        // Funziona anche se l'utente scrive semplicemente "nome_file.estensione_file", senza specificare l'intero path assoluto o relativo.
        $file = basename(trim($file_inviato)); 
        
        if ($file === "") continue;

        // Si trova la posizione della directory del progetto (Apache_Ajax_PHP).
        // Path assoluto completo (C:\xampp\htdocs\Apache_Ajax_PHP).
        // __DIR__ è una costante che restituisce il percorso assoluto della cartella in cui si trova il file attualmente in esecuzione.
        $cartella_progetto = str_replace('\\', '/', __DIR__);

        // Viene costruito il path completo.
        $percorso = $cartella_progetto . "/fileUtenti/$_POST[username]/$file"; 

        // Viene verificato che il file sia presente nel DB e nel filesystem
        $controllo_db = "SELECT * FROM ajax_file_utenti WHERE percorso = '$percorso' AND id_utente ='$_POST[username]'";
        $risultato_controllo = $conn->query($controllo_db);

        if (file_exists($percorso) || $risultato_controllo->num_rows > 0) { 
            
            // Il file corrente viene rimosso dal DB.
            $delete = "DELETE FROM ajax_file_utenti WHERE percorso = '$percorso' AND id_utente ='$_POST[username]'";
            $conn->query($delete);
            
            // Il file corrente viene rimosso dal filesystem.
            if (file_exists($percorso)) {
                unlink($percorso); 
            }
            
            echo "<messaggio>Il file " . $file . " è stato cancellato con successo.</messaggio>";
        } 
        else { 
            echo "<messaggio>Impossibile trovare il file " . $file . " nel sistema.</messaggio>"; 
        }
    }
} else {
    echo "<messaggio>Nessun file trovato.</messaggio>";
}

echo '</response>';
?>