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
nomeUtente = encodeURIComponent(document.getElementById("MyUsername").value);
ext_documenti = encodeURIComponent(document.getElementById("MyExt").value);
var richiesta = "http://localhost/Apache_Ajax_PHP/mostra_piu_files_per_download.php?username=" + nomeUtente + "&estensione_documenti=" + ext_documenti;

// richiede l'esecuzione del quickstart.php sul server
xmlHttp.open("GET", richiesta, true);
// definisce la funzione per gestire la risposta del server
xmlHttp.onreadystatechange = handleServerResponse;
//effettua la richiesta al server
xmlHttp.send(null);
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

try
{
// estrae l'XML dalla risposta inviata dal server
xmlResponse = xmlHttp.responseXML;
console.log("Risposta HTML: " + xmlHttp.responseText);
// prende il documento (in particolare la radice del documento della struttura dell' XML)
xmlDocumentElement = xmlResponse.documentElement;
//prende il messaggio di testo che si trova nel primo figlio dell'elemento radice del documento
var files = xmlResponse.getElementsByTagName("file");
var html = "";

for (var i = 0; i < files.length; i++) {
    var href = files[i].getAttribute("href");
    
    // Se l'attributo href esiste, viene creato il link cliccabile
    if (href) { 
        var nomeFile = files[i].textContent;
        html += "<a href='" + href + "' download='" + nomeFile + "'>" + nomeFile + "</a><br>";
    } else {
        // Stampa del messagio di errore.
        html += files[i].textContent + "<br>";
    }
}

document.getElementById("divMessage").innerHTML = html;

}
catch (e)
{
	document.getElementById("divMessage").innerHTML = xmlHttp.responseText;
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