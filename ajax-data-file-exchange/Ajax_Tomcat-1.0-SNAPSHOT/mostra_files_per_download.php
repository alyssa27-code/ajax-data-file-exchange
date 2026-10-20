<?php
//error_reporting(E_ERROR | E_PARSE);

include "database_connection.php";
	
/*$select = "SELECT percorso FROM file_utenti WHERE id_utente 
='$_SESSION[nomeUtente]'"; */

$file = $_POST['nome_file'].$_POST['estensione_file']; 

$percorso = "./fileUtenti/$_POST[nomeUtente]/$file"; 

$select = "SELECT percorso FROM file_utenti WHERE id_utente ='$_POST[nomeUtente]' AND percorso = '$percorso'";

$result = $conn->query($select);
	
if($result->num_rows<=0){
	echo "Non ci sono file per l'utente "."<b>"."$_POST[nomeUtente]"."</b>";
	exit;
}
echo "<b>"."I tuoi documenti :"."</b><br>";
echo "<br>";
while ($riga = $result->fetch_assoc()){
echo "<a href='$riga[percorso]'>$riga[percorso]</a><br><br>";
	
}
echo "<b>"."Per scaricare il / i file :"."</b><br>";
echo "<ol><li>Fare click sul link del file con il tasto destro e aprirlo in una nuova finestra.</li>
<li>Arrivati al contenuto del file, fare di nuovo click con il tasto destro e selezionare 'Salva come / Save as'.</li>
<li>Salvare il file nella sezione desiderata.</li></ol><br>";
echo "<b>"."Per visualizzare il contenuto del / dei file senza aprire una nuova finestra :"."</b><br>";
echo "<ol><li>Fare click sul link del file con il tasto sinistro.</li></ol><br>";

//header('Content Type: application/ocset-stream');
?>

