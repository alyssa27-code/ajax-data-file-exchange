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
var nome = document.getElementById("MyName").value; 
var cognome = document.getElementById("MySurname").value; 
var num_documenti = parseInt(document.getElementById("NumDocs").value); 
var formData = new FormData(); 
formData.append("username", nomeUtente); 
formData.append("nome_e_cognome", nome + " " + cognome); 
formData.append("numero_file", num_documenti); 

for (var i = 0; i < num_documenti; i++) { 
	var file = document.getElementById("input_" + i); 
	if (!file || !file.files || file.files.length === 0) { 
		continue;
	}
	formData.append("file_utente[]", file.files[0]); 
} 
xmlHttp.open("POST", "http://localhost/Apache_Ajax_PHP/ricevi_piu_file.php", true); 
xmlHttp.onreadystatechange = handleServerResponse; 
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

try
{
// estrae l'XML dalla risposta inviata dal server
xmlResponse = xmlHttp.responseXML;
console.log("Risposta HTML: " + xmlHttp.responseText);
// prende il documento (in particolare la radice del documento della struttura dell' XML)
xmlDocumentElement = xmlResponse.documentElement;

var messaggi = xmlDocumentElement.getElementsByTagName("messaggio");
helloMessage = "";

for (var i = 0; i < messaggi.length; i++) {
    helloMessage += messaggi[i].textContent + "<br/>";
}

}
catch (e)
{
	helloMessage = "";
}	

//aggiorna la pagina web del client usando i dati ricevuti dal server
document.getElementById("divMessage").innerHTML =
'<i>' + helloMessage + '</i>';
}
// uno stato HTTP diverso da 200 sta segnalando un errore
else
{
alert("C'è stato qualche problema nell'accesso al server: " + xmlHttp.statusText);
}
}
}