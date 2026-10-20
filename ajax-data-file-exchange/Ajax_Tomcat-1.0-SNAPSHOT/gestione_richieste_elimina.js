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
nomeUtente = document.getElementById("MyUsername").value.trim(); 
num_documenti = document.getElementById("NumDocs").value.trim();
var formData = new FormData(); 
formData.append("username", nomeUtente); 
formData.append("numero_file", num_documenti);

var presenza_di_file = false;

for (var i = 0; i < num_documenti; i++) { 
	var file = document.getElementById("input_" + i).value.trim(); 
	if (file === "") continue;
	formData.append("nome_file[]", file);
	presenza_di_file = true;
} 

if(presenza_di_file === true){
// richiede l'esecuzione del quickstart.php sul server
xmlHttp.open("POST", "http://localhost/Apache_Ajax_PHP/elimina_file.php", true);
// definisce la funzione per gestire la risposta del server
xmlHttp.onreadystatechange = handleServerResponse;
//effettua la richiesta al server
xmlHttp.send(formData);
}

}
// se la connessione è occupata riprova dopo un secondo
//setTimeout('process()', 1000);
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

try
{
// estrae l'XML dalla risposta inviata dal server
xmlResponse = xmlHttp.responseXML;
console.log("Risposta HTML: " + xmlHttp.responseText);
// Recupera tutti i tag <m> singolarmente
    var messaggi = xmlResponse.getElementsByTagName("messaggio");
    var html = "";
    
    // Cicla i messaggi aggiungendo un a capo <br> tra uno e l'altro
    if (xmlResponse) {
                    var messaggi = xmlResponse.getElementsByTagName("messaggio");
                    var html = "";
                    
                    for (var i = 0; i < messaggi.length; i++) {
                        html += messaggi[i].textContent + "<br>";
                    }
	document.getElementById("divMessage").innerHTML = html;
                    
	}
}
catch (e)
{
	document.getElementById("divMessage").innerHTML = "Errore";
}	


//aggiorna la pagina web del client usando i dati ricevuti dal server

}
// uno stato HTTP diverso da 200 sta segnalando un errore
else
{
alert("C'è stato qualche problema nell'accesso al server: " + xmlHttp.statusText);
}
}
}