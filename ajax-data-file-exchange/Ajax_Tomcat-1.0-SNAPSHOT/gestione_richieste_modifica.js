// crea il riferimento in memoria all'oggetto XMLHttpRequest
var xmlHttp = createXmlHttpRequestObject();

// crea l'oggetto XMLHttpRequest e lo lega alla variabile dichiarata precedentemente

function createXmlHttpRequestObject()
{
var xmlHttp;
try
{
	xmlHttp = new XMLHttpRequest();
}
catch (e)
{
	xmlHttp = false;
}
/* controlla che l'oggetto non sia NULL e restituisce l'oggetto creato oppure mostra un messaggio
di errore */

if (!xmlHttp)
	alert("Error creating the XMLHttpRequest object.");
else
return xmlHttp;
}


//fa una richiesta asincrona attraverso l'oggetto XMLHttpRequest
function process()
{
// procede solo se l'oggetto non è occupato
if (xmlHttp.readyState == 4 || xmlHttp.readyState == 0)
{

// recupera il nome digitato dall'utente nel form
var nomeUtente = document.getElementById("MyUsername").value; 
var num_documenti = parseInt(document.getElementById("NumDocs").value); 
var formData = new FormData(); 
formData.append("username", nomeUtente); 
formData.append("numero_file", num_documenti); 

for(var i = 0; i < num_documenti; i++){
	var nome_prima = document.getElementById("input_doc_" + i).value.trim();
    var estensione_prima = document.getElementById("input_ext_" + i).value.trim();
	if(nome_prima === "" || estensione_prima === "") continue;
    file_prima = nome_prima + estensione_prima;
	formData.append("file_prima[]", file_prima); 
}

for(var i = 0; i < num_documenti; i++){
	var nome_dopo = document.getElementById("nuovo_input_doc_" + i).value.trim();
    var estensione_dopo = document.getElementById("nuovo_input_ext_" + i).value.trim();
	if(nome_dopo === "" || estensione_dopo === "") continue;
    file_dopo = nome_dopo + estensione_dopo;
	formData.append("file_dopo[]", file_dopo); 
}
// richiede l'esecuzione del quickstart.php sul server
xmlHttp.open("POST", "http://localhost/Apache_Ajax_PHP/modifica_file.php", true);
// definisce la funzione per gestire la risposta del server
xmlHttp.onreadystatechange = handleServerResponse;
//effettua la richiesta al server
xmlHttp.send(formData);
}
}


// questa funzione viene eseguita automaticamente quando viene ricevuta la risposta dal server
function handleServerResponse()
{
// va avanti solo se la transazione è conclusa
if (xmlHttp.readyState == 4)
{
// la transazione è stata completata con successo (status 200)
if (xmlHttp.status == 200)
{

try {
    // Estrae l'XML dalla risposta
    xmlResponse = xmlHttp.responseXML;
    
    // UTILI PER DEBUG: Se c'è un errore PHP, lo vedrai stampato nella console del browser (tasto F12)
    console.log("Risposta XML del server:", xmlResponse);
    console.log("Contenuto testuale del server:", xmlHttp.responseText);

    if (xmlResponse && xmlResponse.getElementsByTagName("messaggio").length > 0) {
        var messaggi = xmlResponse.getElementsByTagName("messaggio");
        var html = "";
        
        for (var i = 0; i < messaggi.length; i++) {
            html += messaggi[i].textContent + "<br>";
        }
        document.getElementById("divMessage").innerHTML = html;
    } else {
        // Se xmlResponse è null, mostra il testo grezzo inviato dal server PHP per capire l'errore
        document.getElementById("divMessage").innerHTML = "Errore nel formato di risposta del server.";
    }
}
catch (e) {
    console.error(e);
    document.getElementById("divMessage").innerHTML = "Errore nella gestione della risposta client.";
}

//aggiorna la pagina web del client usando i dati ricevuti dal server
// ricomincia la sequenza
//setTimeout('process()', 1000);
}
// uno stato HTTP diverso da 200 sta segnalando un errore
else
{
alert("C'è stato qualche problema nell'accesso al server: " + xmlHttp.statusText);
}
}
}